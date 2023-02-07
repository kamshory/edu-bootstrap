<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$cfg->page_title = "Daftar Paket";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(isset($_POST['count']) && isset($_POST['test_collection_id']))
{
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data"; //NOSONAR
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	$test_id = $_POST['test_collection_id'];
	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
		$nquestion = 0;
		$noption = 0;
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$file_path = $data['file_path'];
			$real_path = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data/".$file_path;
			$md5 = md5_file($real_path);
			$sha1 = sha1_file($real_path);
			$s = file_get_contents($real_path);
			$test_data = simplexml_load_string($s);
			foreach($test_data->item as $index_question => $question)
			{
				// petanyaan
				$nquestion++;
				$opt = 0;
				if(count(@$question->answer->option) > 0)
				{
					foreach($question->answer->option as $index_option => $option)
					{
						$opt++;
					}
					if($opt>$noption)
					{
						$noption = $opt;
					}
				}
			}
			$sql = "UPDATE `edu_test_collection` 
			set `number_of_question` = '$nquestion', `number_of_option` = '$noption', `file_md5` = '$md5', `file_sha1` = '$sha1'
			WHERE `test_collection_id` = '$test_collection_id' ";
			$database->execute($sql);
		}
	}
	header("Location: ".basename($_SERVER['REQUEST_URI'])); //NOSONAR
	exit();
}
if(isset($_POST['set_active']) && isset($_POST['test_collection_id']))
{
	$test_id = $_POST['test_collection_id'];
	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "UPDATE `edu_test_collection` SET `active` = true WHERE `test_collection_id` = '$test_collection_id' ";
		$database->execute($sql);
	}
	header("Location: ".basename($_SERVER['REQUEST_URI'])); //NOSONAR
	exit();
}
if(isset($_POST['set_inactive']) && isset($_POST['test_collection_id']))
{
	$test_id = $_POST['test_collection_id'];
	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "UPDATE `edu_test_collection` SET `active` = false WHERE `test_collection_id` = '$test_collection_id' ";
		$database->execute($sql);
	}
	header("Location: ".basename($_SERVER['REQUEST_URI'])); //NOSONAR
	exit();
}
if(isset($_POST['delete']) && isset($_POST['test_collection_id']))
{
	$test_id = $_POST['test_collection_id'];
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data"; //NOSONAR
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$file_path = $data['file_path'];
			$sql = "DELETE FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
			$database->execute($sql);
			$real_path = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data/".$file_path;
			if(file_exists($real_path))
			{
				@unlink($real_path);
			}
		}
	}
	header("Location: ".basename($_SERVER['REQUEST_URI'])); //NOSONAR
	exit();
}


if(isset($_POST['save']) && @$_GET['option'] == 'add' && isset($_FILES['file']))
{
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data"; //NOSONAR
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_NUMBER_INT);
	$target_dir = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data"; //NOSONAR
	$base_name = md5(session_id()."-".time()."-".mt_rand(111111, 999999)).".xml";
	$file_path = $target_dir."/".$base_name;

	if(move_uploaded_file($_FILES['file']['tmp_name'], $file_path))
	{
		$fileSync->createFile($file_path, true);
		$file_name = addslashes(basename(trim($_FILES['file']['name'])));
		$file_size = filesize($file_path);
		$file_md5 = md5_file($file_path);
		$file_sha1 = sha1_file($file_path);

		$number_of_question = 0;
		$number_of_option = 0;
		$string_data = file_get_contents($file_path);
		$test_data = simplexml_load_string($string_data);
		$files = array();
		$questions = array();
		$options = array();
		$sort_order = 0;
		foreach($test_data->item as $index_question => $question)
		{
			// petanyaan
			$text_pertanyaan = trim(@$question->question->text);
			$number_of_question++;
		}
		foreach($test_data->item as $index_question => $question)
		{
			// petanyaan
			$text_pertanyaan = trim(@$question->question->text);
			if(count(@$question->answer->option) > 0)
			{
				foreach($question->answer->option as $index_option => $option)
				{
					$number_of_option++;
				}
			}
			break;
		}


		$time_create = $time_edit = $picoEdu->getLocalDateTime();
		$ip_create = $ip_edit = addslashes($_SERVER['REMOTE_ADDR']);
		$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
	
		$file_path = basename($file_path);
		$sql = "INSERT INTO `edu_test_collection` 
		(`name`, `grade_id`, `file_name`, `file_path`, `file_size`, `file_md5`, `file_sha1`, `number_of_question`, `number_of_option`,
		`time_create`, `time_edit`, `ip_create`, `ip_edit`, `active`) VALUES
		('$name', '$grade_id', '$file_name', '$file_path', '$file_size', '$file_md5', '$file_sha1', '$number_of_question', '$number_of_option',
		'$time_create', '$time_edit', '$ip_create', '$ip_edit', '$active')";
		$database->execute($sql);
		$id = $database->getDatabaseConnection()->lastInsertId();
		header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&test_collection_id=$id");
	}
}
if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data"; //NOSONAR
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_NUMBER_INT);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$ip_create = $ip_edit = addslashes($_SERVER['REMOTE_ADDR']);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);

	$sql = "UPDATE `edu_test_collection` SET 
	`name` = '$name', `grade_id` = '$grade_id', `time_edit` = '$time_edit', `ip_edit` = '$ip_edit', `active` = '$active'
	WHERE `test_collection_id` = '$test_collection_id'";
	$database->execute($sql);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&test_collection_id=$test_collection_id");
}
if(@$_GET['option'] == 'add')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<form name="formedu_test_collection" id="formedu_test_collection" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Nama Ujian</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><select class="form-control input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php
		echo $picoEdu->createGradeOption(null);
		?>
		</select></td>
		</tr>
		<tr>
		<td>File</td>
		<td><label for="file"></label>
		  <input type="file" name="file" id="file" /></td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Aktif</label>
		</td>
		</tr>
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_test_collection', array('name','grade_id','file_name','file_path','file_size','file_md5','file_sha1','time_create','time_edit','ip_create','ip_edit','taken','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test_collection`.* 
FROM `edu_test_collection` 
WHERE `edu_test_collection`.`test_collection_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_test_collection" id="formedu_test_collection" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Nama Ujian</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" />
        <input type="hidden" name="test_collection_id" id="test_collection_id" value="<?php echo $data['test_collection_id'];?>" /></td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><select class="form-control input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php
	echo $picoEdu->createGradeOption($data['grade_id']);
	?>
		
		</select></td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test_collection`.* $nt
FROM `edu_test_collection` 
WHERE `edu_test_collection`.`test_collection_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_test_collection" action="" method="post" enctype="multipart/form-data">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Nama Ujian</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
		<tr>
		<td>Jumlah Soal</td>
		<td><?php echo $data['number_of_question'];?> </td>
		</tr>
		<tr>
		<td>Jumlah Soal</td>
		<td><?php echo $data['number_of_option'];?> </td>
		</tr>
		<tr>
		<td>Nama File</td>
		<td><?php echo $data['file_name'];?> </td>
		</tr>
		<tr>
		<td>Lokasi File</td>
		<td><?php echo $data['file_path'];?> </td>
		</tr>
		<tr>
		<td>Ukuran</td>
		<td><?php echo $data['file_size'];?> </td>
		</tr>
		<tr>
		<td>File MD5</td>
		<td><?php echo $data['file_md5'];?> </td>
		</tr>
		<tr>
		<td>File SHA1</td>
		<td><?php echo $data['file_sha1'];?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo $data['time_create'];?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Create</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Edit</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Diambil</td>
		<td><?php echo $data['taken'];?> </td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_collection_id=<?php echo $data['test_collection_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$grade_id = kh_filter_input(INPUT_GET, "grade_id", FILTER_SANITIZE_NUMBER_INT);
?>
<style type="text/css">
#test-preview h3 {
    text-align: center;
    font-size: 22px;
    font-weight: normal;
    text-transform: uppercase;
	margin:0;
	padding:4px 0;
}
#test-preview h4 {
    text-align: center;
    font-size: 18px;
    font-weight: normal;
    text-transform: uppercase;
	margin:0 0 10px 0;
	padding:4px 0;
}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
    $(document).on('click', '.load-collection', function(e){
		var id = $(this).attr('data-collection-id');
		$.get('ajax-preview-question-store.php', {id:id,rand:Math.random()}, function(answer){
			var html = '<div id="test-preview" style="width:900px; height:400px; overflow:auto; position:relative;">'+answer+'</div>';
			overlayDialog(html, 900, 400);
		});
		e.preventDefault();
	});
    $(document).on('click', '.load-word, .load-key', function(e){
		var id = $(this).attr('data-collection-id');
		var data = {id:id};
		if($(this).hasClass('load-key'))
		{
			data.key = 1;
		}
		else
		{
			data.key = 0;
		}
		$.get('../admin/ajax-preview-question-store-word.php', data, function(answer){
			var doc = $('<div>'+answer+'</div>');
			var title = doc.find('.test-header h3').text().trim();
			doc.find('.test-header h3, .test-header h4').css({'text-align':'center'});
			if(title == '')
			{
				title = 'test';
			}
			var content = doc.html(); 
			var style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
			content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
			var converted = new Blob([content], {type:'text/html'});
			saveAs(converted, title+'.html');
		});
		e.preventDefault();
	});
});
</script>

<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Tingkat</span>
    <select class="form-control input-select" name="grade_id" id="grade_id">
    <option value=""></option>
    <?php
	echo $picoEdu->createGradeOption($grade_id);
	?>
    </select>
    <span class="search-label">Nama</span>
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
$sql_filter .= " AND (`edu_test_collection`.`name` like '%".addslashes($pagination->query)."%' )";
}


if($grade_id){
$pagination->array_get[] = 'grade_id';
$sql_filter .= " AND (`edu_test_collection`.`grade_id` = '$grade_id' )";
}

$nt = '';


$sql = "SELECT `edu_test_collection`.* $nt
FROM `edu_test_collection`
WHERE 1 $sql_filter
ORDER BY `edu_test_collection`.`test_collection_id` DESC
";
$sql_test = "SELECT `edu_test_collection`.*
FROM `edu_test_collection`
WHERE 1 $sql_filter
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
<style type="text/css">
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(11), .hide-some-cell tr td:nth-child(13){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(12), .hide-some-cell tr td:nth-child(14), .hide-some-cell tr td:nth-child(15){
		display:none;
	}
}
</style>
<form name="form1" method="post" action="">
<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-test_collection_id" id="control-test_collection_id" class="checkbox-selector" data-target=".test_collection_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Browse" border="0" /></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-download-16" alt="Download" border="0" /></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-key-16" alt="Key" border="0" /></td>
      <td width="25">No</td>
      <td>Nama Ujian</td>
      <td>Tingkat</td>
      <td>Nama File</td>
      <td>Ukuran</td>
      <td>Diambil</td>
      <td>Soal</td>
      <td>Pilihan</td>
      <td>Aktif</td>
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
      <td><input type="checkbox" name="test_collection_id[]" id="test_collection_id" value="<?php echo $data['test_collection_id'];?>" class="test_collection_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_collection_id=<?php echo $data['test_collection_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td><a class="load-collection" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Browse" border="0" /></a></td>
      <td><a class="load-word" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-download-16" alt="Download" border="0" /></a></td>
      <td><a class="load-key" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>&key=1"><img src="lib.tools/images/trans.gif" class="icon-16 icon-key-16" alt="Key" border="0" /></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_size'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['taken'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['number_of_question'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['number_of_option'];?></a></td>
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
  <input type="submit" name="count" id="count" value="Hitung Soal" class="btn com-button btn-success" />
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