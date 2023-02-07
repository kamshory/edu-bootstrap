<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
$cfg->page_title = "Artikel";
if(isset($_POST['publish']) || isset($_POST['draff']))
{
	$option = kh_filter_input(INPUT_POST, "option", FILTER_SANITIZE_SPECIAL_CHARS);
	$title = trim(kh_filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS));
	if($title == '')
	{
		$title = '(Tanpa Judul)';
	}
	
	$content = kh_filter_input(INPUT_POST, "content");
	
	$open = kh_filter_input(INPUT_POST, "open", FILTER_SANITIZE_NUMBER_UINT);
	$class = kh_filter_input(INPUT_POST, "class", FILTER_SANITIZE_STRING_NEW);
	$active = 0;
	$time = $picoEdu->getLocalDateTime();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if(isset($_POST['publish']))
	{
		$active = 1;
	}
	if($option == 'add')
	{
		$article_id = $database->generateNewId();
		$sql = "INSERT INTO `edu_article`
		(`article_id`, `school_id`, `title`, `open`, `class`, `time_create`, `time_edit`, `member_create`, `role_create`, `member_edit`, `role_edit`, `ip_create`, `ip_edit`, `active`) VALUES	
		('$article_id', '$school_id', '$title', '$open', '$class', '$time', '$time', '$teacher_id', 'T', '$teacher_id', 'T', '$ip', '$ip', '$active')
		";
		$stmt = $database->executeInsert($sql, true);
		if ($stmt->rowCount() > 0) {

			$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/article/$article_id";
			$base_src = "media.edu/school/$school_id/article/$article_id";

			$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/article/$article_id";
			$dirBase = dirname(dirname(__FILE__));
			$permission = 0755;
			$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

			$content = extractImageData($content, $base_dir, $base_src, $fileSync);
			$content = addslashes($content);
			$sql = "UPDATE `edu_article` SET `content` = '$content' WHERE `article_id` = '$article_id' ";
			$database->executeUpdate($sql, true);

		}
		header("Location: ".basename($_SERVER['PHP_SELF'])."?option=edit&article_id=$article_id");
	}
	else if($option == 'edit')
	{
		$article_id = kh_filter_input(INPUT_POST, "article_id", FILTER_SANITIZE_STRING_NEW);

		$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/article/$article_id";
		$base_src = "media.edu/school/$school_id/article/$article_id";

		$dir2prepared = dirname(dirname(__FILE__))."/media.edu/school/$school_id/article/$article_id";
		$dirBase = dirname(dirname(__FILE__));
		$permission = 0755;
		$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

		$content = extractImageData($content, $base_dir, $base_src, $fileSync);
		$content = addslashes($content);
		$sql = "UPDATE `edu_article` set
		`title` = '$title', `content` = '$content', `open` = '$open', `class` = '$class', 
		`time_edit` = '$time', `member_edit` = '$teacher_id', `role_edit` = 'T', `ip_edit` =  '$ip', `active` = '$active'
		WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `member_create` = '$auth_teacher_id'
		";
		$database->executeUpdate($sql, true);
		header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&article_id=$article_id");
	}
}

if(isset($_POST['set_active']) && isset($_POST['article_id']))
{
	$articles = $_POST['article_id'];
	if(is_array($articles))
	{
		foreach($articles as $article_id)
		{
			$article_id = addslashes($article_id);
			$sql = "UPDATE `edu_article` SET `active` = true 
			WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `edu_article`.`member_create` = '$teacher_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['article_id']))
{
	$articles = $_POST['article_id'];
	if(is_array($articles))
	{
		foreach($articles as $article_id)
		{
			$article_id = addslashes($article_id);
			$sql = "UPDATE `edu_article` SET `active` = false 
			WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `edu_article`.`member_create` = '$teacher_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['article_id']))
{
	$articles = $_POST['article_id'];
	if(is_array($articles))
	{
		foreach($articles as $article_id)
		{
			$article_id = addslashes($article_id);
			$sql = "SELECT `article_id` FROM `edu_article` 
			WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `edu_article`.`member_create` = '$teacher_id' ";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
				$sql = "DELETE FROM `edu_article` 
				WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `edu_article`.`member_create` = '$teacher_id' ";
				$stmt = $database->executeDelete($sql, true);
				if($stmt->rowCount() > 0)
				{
					// destroy directory
					$dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/article/$article_id";
					$destroyer = new DirectoryDestroyer($fileSync);
					$destroyer->destroy($dir, true);
				}
			}
		}
	}
}


if(@$_GET['option'] == 'add')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>

<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/article-editor.min.js"></script>

<?php
$sqlc = "SELECT `class_id`, `name` FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' AND `name` != '' ORDER BY `sort_order` asc ";
$stmtc = $database->executeQuery($sqlc);
$arrc = array();
if($stmtc->rowCount() > 0)
{
	$arrc = $stmtc->fetchAll(PDO::FETCH_ASSOC);
}

?>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
var editState = 'add';
var defaultdir = 'lib.content/media/article/';
</script>
<form id="articleform" method="post" enctype="multipart/form-data" action="">
<div class="input-block">
<input type="text" id="title" name="title" class="form-control input-text input-text-full input-text-title" placeholder="Judul Artikel" autocomplete="off" required="required" />
</div>
<div class="input-block">
<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"></textarea>
<input type="hidden" name="class" id="classlist" value="" />
</div>
<div class="input-block">
<input type="submit" id="publish" name="publish" value="Publikasikan" />
<input type="submit" id="draff" name="draff" value="Simpan Konsep" />
<input type="button" id="select-class" value="Atur Kelas" />
<input type="hidden" name="option" id="option" value="add" />
<input type="button" id="cancel" value="Batalkan" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
</form>
<?php
}
else if(@$_GET['option'] == 'edit' && isset($_GET['article_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>

<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/article-editor.min.js"></script>

<?php
$article_id = kh_filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT * FROM `edu_article` WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `member_create` = '$auth_teacher_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php
$sqlc = "SELECT `class_id`, `name` FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' AND `name` != '' ORDER BY `sort_order` asc ";
$stmtc = $database->executeQuery($sqlc);
$arrc = array();
if($stmtc->rowCount() > 0)
{
	$arrc = $stmtc->fetchAll(PDO::FETCH_ASSOC);
}
?>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
var editState = 'edit';
var defaultdir = 'lib.content/media/article/';
</script>
<form id="articleform" method="post" enctype="multipart/form-data" action="">
<div class="input-block">
<input type="text" id="title" name="title" class="form-control input-text input-text-full input-text-title" value="<?php echo $data['title'];?>" placeholder="Judul Artikel" autocomplete="off" required="required" />
</div>
<div class="input-block">
<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"><?php echo $data['content'];?></textarea>
<input type="hidden" name="class" id="classlist" value="<?php echo $data['class'];?>" />
</div>
<div class="input-block">
<input type="submit" id="publish" name="publish" value="Publikasikan" />
<input type="submit" id="draff" name="draff" value="Simpan Konsep" />
<input type="button" id="select-class" value="Atur Kelas" />
<input type="hidden" name="option" id="option" value="edit" />
<input type="hidden" name="article_id" id="article_id" value="<?php echo $article_id;?>" />
<input type="button" id="cancel" value="Batalkan" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
</form>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(isset($_GET['article_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>

<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/article-editor.min.js"></script>

<?php
$article_id = kh_filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_STRING_NEW);
$sql_filter_article = " AND `edu_article`.`article_id` = '$article_id' ";

if(isset($school_id))
{
	$sql_filter_article .= " AND `edu_article`.`school_id` = '$school_id' ";
}
$sql = "SELECT `edu_article`.*, `member`.`name` as `creator`
FROM `edu_article` 
left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
where (`edu_article`.`member_create` = '$teacher_id' OR `edu_article`.`active` = true) $sql_filter_article ";
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
$(document).on('click', '.delete-post', function(e){
var article_id = $(this).attr('data-id');
if(confirm('Apakah Anda akan menghapus artikel ini?'))
{
	$.post('ajax-delete-artikel.php', {article_id:article_id, option:'delete'}, function(asnwer){
		window.location = 'artikel.php';
	});
}
e.preventDefault();
});
$(document).on('click', '.download-word', function(e){
var title = $('.article-title').text();
var content = $('.article-content').html();
var creator = $('.article-creator').text();
var html = '<div><h1>'+title+'</h1>\r\n<div>'+creator+'</div>'+content+'</div>';
var doc = $(html);
doc = convertImagesToBase64(doc);
var content = doc.html(); 
var style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
var converted = new Blob([content], {type:'text/html'});
saveAs(converted, title+'.html');
e.preventDefault();
});
});
function convertImagesToBase64 (doc) {
var regularImages = doc.find('img');
var canvas = document.createElement('canvas');
var ctx = canvas.getContext('2d');
[].forEach.call(regularImages, function (obj) {
var imgElement = obj;
ctx.clearRect(0, 0, canvas.width, canvas.height);
canvas.width = imgElement.width;
canvas.height = imgElement.height;
ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
var dataURL = canvas.toDataURL();
imgElement.setAttribute('src', dataURL);
imgElement.style.width = canvas.width+'px';
imgElement.style.maxWidth = '100%';
imgElement.style.height = 'auto';
imgElement.removeAttribute('height');
});
canvas.remove();
return doc;
}
	</script>
	<div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
	<div class="article-content"><?php echo $data['content'];?></div>
	<div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
	<div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
	<div class="article-link">
		<a href="artikel.php">Lihat Semua</a>
		<?php
		if($teacher_id && $school_id && $school_id == $data['school_id'] && $teacher_id == $data['member_create'])
		{
			?>
			<a href="artikel.php?option=edit&article_id=<?php echo $data['article_id'];?>">Ubah</a>
			<?php
		}
		?>
		<a href="javascript:;" class="download-word">Download</a>
	</div>
	<?php
}
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
});
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Kelas</span>
  <select class="form-control input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
    $sql2 = "SELECT * FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' ORDER BY `sort_order` asc ";
    echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'class_id')
			),
			'selectCondition'=>array(
				'source'=>'class_id',
				'value'=>$class_id
			),
			'caption'=>array(
				'delimiter'=>PicoEdu::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

    ?>
    </select>
    <span class="search-label">Nama Siswa</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_article`.`name` like '%".addslashes($pagination->query)."%' )";
}
if($class_id != 0)
{
	$pagination->array_get[] = 'class_id';
	$sql_filter .= " and (concat(',',`edu_article`.`class`,',') like '%,$class_id,%')";
}
$sql_filter .= " and (`edu_article`.`school_id` = '$school_id' )";

$nt = '';

$sql = "SELECT `edu_article`.* , `member`.`name` as `creator`
FROM `edu_article` 
left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
where (`edu_article`.`member_create` = '$teacher_id' OR `edu_article`.`active` = true) $sql_filter 
ORDER BY `edu_article`.`article_id` desc
";
$sql_test = "SELECT `edu_article`.`article_id` 
FROM `edu_article` 
where (`edu_article`.`member_create` = '$teacher_id' OR `edu_article`.`active` = true) $sql_filter 
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql . $pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = $picoEdu->createPaginationHtml($pagination);
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(5){
		display:none;
	}
}
</style>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-article_id" id="control-article_id" class="checkbox-selector" data-target=".article_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>Judul</td>
      <td width="200" nowrap="nowrap">Ditulis Oleh</td>
      <td width="140" nowrap="nowrap">Ditulis Pada</td>
      <td width="40">Aktif</td>
    </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td>
      <?php
	  if($data['member_create'] == $teacher_id)
	  {
	  ?>  
      <input type="checkbox" name="article_id[]" id="article_id" value="<?php echo $data['article_id'];?>" class="article_id" />
      <?php
	  }
	  else
	  {
	  ?>  
      <input type="checkbox" disabled="disabled" />
      <?php
	  }
	  ?>
      </td>
      <td>
      <?php
	  if($data['member_create'] == $teacher_id)
	  {
	  ?>  
      <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&article_id=<?php echo $data['article_id'];?>"><i class="fas fa-pencil"></i>
      <?php
	  }
	  else
	  {
	  ?>  
      <img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16-2" alt="Ubah" border="0" />
      <?php
	  }
	  ?>
      </td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&article_id=<?php echo $data['article_id'];?>"><?php echo $data['title'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&article_id=<?php echo $data['article_id'];?>"><?php echo $data['creator'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&article_id=<?php echo $data['article_id'];?>"><?php echo translateDate(date('d M Y H:i', strtotime($data['time_create'])));?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn com-button btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn com-button btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn com-button btn-danger delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
  </div>
</form>
<?php
}
else if(@$_GET['q'])
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>