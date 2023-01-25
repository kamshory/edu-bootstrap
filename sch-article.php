<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
include_once dirname(__FILE__)."/lib.inc/dom.php";
include_once dirname(__FILE__)."/lib.inc/auth.php";
if(!@$member_login->member_id)
{
include_once dirname(__FILE__)."/lib.inc/header.php";
if(@$school_id != 0)
{
	if(isset($_GET['article_id']))
	{
		$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
		$sql_filter_article = " and `edu_article`.`article_id` = '$article_id' ";
	
		$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' and `edu_article`.`open` = '1' ";
		$sql = "select `edu_article`.*, `member`.`name` as `creator`
		from `edu_article` 
		left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
		where 1 $sql_filter_article ";
		include_once dirname(__FILE__)."/lib.inc/header.php";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC)
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
			<div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
			<div class="article-content"><?php echo $data['content'];?></div>
			<div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
			<div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
			<div class="article-link">
				<a href="artikel.php">Lihat Semua</a>
			</div>
			<?php
		}
		include_once dirname(__FILE__)."/lib.inc/footer.php";
	}
	else
	{
	$sql_filter_article = "";
	$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' and `edu_article`.`open` = '1'";
	$sql = "select `edu_article`.*, `member`.`name` as `creator`
	from `edu_article` 
	left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
	where 1 $sql_filter_article 
	order by `edu_article`.`article_id` desc
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
        <div class="article-list">
        <?php
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($rows as $data)
		{

			$obj = parsehtmldata('<html><body>'.($data['content']).'</body></html>');
			$arrparno = array();
			$arrparlen = array();
			$cntmax = ""; // do not remove
			$content = ""; // do not remove
			$i = 0;
			$minlen = 300;
			
			if(isset($obj->p) && count($obj->p)>0)
			{
				$max = 0;
				foreach($obj->p as $parno=>$par)
				{
					$arrparlen[$i] = strlen($par);
					if($arrparlen[$i]>$max)
					{
						$max = $arrparlen[$i];
						$cntmax = $par;
					}
					if($arrparlen[$i] >= $minlen)
					{
						$content = $par;
						break;
					}
				}
				if(!$content)
				{
					
					$content = $cntmax;
				}
			}
			if(!$content)
			{
				$content = "&nbsp;";
			}
			$maxlen = 300;
			if(strlen($content)>$maxlen)
			{
				$content.=" ";
				$pos = stripos($content, ". ", $maxlen);
				if($pos===false){
				$pos = stripos($content, ".", $maxlen);
				}
				if($pos===false){
				$pos = stripos($content, " ", $maxlen);
				}
				if($pos===false) $pos = $maxlen;
				$content = substr($content, 0, $pos+1);
				$content = tidyHTML($content);
			}
		
			?>
            <div class="article-item">
                <div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
                <div class="article-content"><?php echo $content;?></div>
                <div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
                <div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
                <div class="article-link">
                	<a href="artikel.php?article_id=<?php echo $data['article_id'];?>">Baca</a>
                </div>
            </div>
            <?php
		}
		?>
        </div>
        <?php	
	}
	}
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
exit();
}
else
{
include_once dirname(__FILE__)."/lib.inc/auth-guru.php";

if($auth_teacher_id == 0)
{
	include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
}
else
{
if(isset($_POST['publish']) || isset($_POST['draff']))
{
	$option = kh_filter_input(INPUT_POST, 'option', FILTER_SANITIZE_SPECIAL_CHARS);
	$title = trim(kh_filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
	if($title == '')
	{
		$title = '(Tanpa Judul)';
	}
	
	$content = kh_filter_input(INPUT_POST, 'content');
	$content = addslashes($content);
	
	$open = kh_filter_input(INPUT_POST, 'open', FILTER_SANITIZE_NUMBER_UINT);
	$class = kh_filter_input(INPUT_POST, 'class', FILTER_SANITIZE_STRING_NEW);
	
	$admin_id = $auth_teacher_id;
	$school_id = $auth_teacher_school_id;
	
	$active = 0;
	$time = $picoEdu->getLocalDateTime();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if(isset($_POST['publish']))
	{
		$active = 1;
	}
	if($option == 'add')
	{
		$sql = "insert into `edu_article`
		(`school_id`, `title`, `content`, `open`, `class`, `time_create`, `time_edit`, `member_create`, `role_create`, `member_edit`, `role_edit`, `ip_create`, `ip_edit`, `active`) values	
		('$school_id', '$title', '$content', '$open', '$class', '$time', '$time', '$admin_id', 'T', '$admin_id', 'T', '$ip', '$ip', '$active')
		";
		$stmt = $database->executeQuery($sql);
		$article_id = $stmt->rowCount();
		header("Location: artikel.php?option=detail&article_id=$article_id");
	}
	else if($option == 'edit')
	{
		$article_id = kh_filter_input(INPUT_POST, 'article_id');
		$sql = "update `edu_article` set
		`title` = '$title', `content` = '$content', `open` = '$open', `class` = '$class', 
		`time_edit` = '$time', `member_edit` = '$admin_id', `role_edit` = 'T', `ip_edit` =  '$ip', `active` = '$active'
		where `article_id` = '$article_id' and `school_id` = '$school_id' and `member_create` = '$admin_id'
		";
		$database->execute($sql);
		header("Location: artikel.php?option=detail&article_id=$article_id");
	}
}

}
if(isset($_GET['school_id']))
{
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
}
$sqlc = "select `class_id`, `name` from `edu_class` where `active` = '1' and `school_id` = '$school_id' and `name` != '' order by `order` asc ";
$stmt = $database->executeQuery($sql);
$arrc = array();
if($stmt->rowCount())
{
		$arrc = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if((@$_GET['option'] == 'edit' && isset($_GET['article_id']) && $auth_teacher_id) || (@$_GET['option'] == 'add' && $auth_teacher_id))
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	?>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="../lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="../lib.assets/theme/default/js/article-editor.js"></script>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
</script>    
    <?php
	if(@$_GET['option'] == 'edit' && isset($_GET['article_id']) && $auth_teacher_id)
	{
	$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
	$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "select * from `edu_article` where `article_id` = '$article_id' and `school_id` = '$school_id' and `member_create` = '$auth_teacher_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
	<script type="text/javascript">
	var editState = 'edit';
	var defaultdir = 'lib.content/media/article/';
	</script>
	<form id="articleform" method="post" enctype="multipart/form-data" action="">
	<div class="input-block">
	<input type="text" id="title" name="title" class="input-text input-text-full input-text-title" value="<?php echo $data['title'];?>" placeholder="Judul Artikel" autocomplete="off" required="required" />
	</div>
	<div class="input-block">
	<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"><?php echo htmlspecialchars($data['content']);?></textarea>
    <input type="hidden" name="class" id="classlist" value="<?php echo $data['class'];?>" />
	</div>
	<div class="input-block">
	<input type="button" id="select-class" value="Atur Kelas" />
	<input type="submit" id="publish" name="publish" value="Publikasikan" />
	<input type="submit" id="draff" name="draff" value="Simpan Konsep" />
	<input type="button" id="cancel" name="publish" value="Batalkan" onclick="window.location='artikel.php?article_id=<?php echo $article_id;?>'" />
	<input type="hidden" name="option" id="option" value="edit" />
	<input type="hidden" name="article_id" id="article_id" value="<?php echo $article_id;?>" />
	</div>
	</form>
	<?php
	}
	else
	{
		?>
        <div class="warning">Maaf. Anda tidak bisa mengubah artikel ini.</div>
        <?php
	}
	}
	else if(@$_GET['option'] == 'add' && $auth_teacher_id)
	{
		?>
	<script type="text/javascript">
	var editState = 'add';
	var defaultdir = 'lib.content/media/article/';
	</script>
	<form id="articleform" method="post" enctype="multipart/form-data" action="">
	<div class="input-block">
	<input type="text" id="title" name="title" class="input-text input-text-full input-text-title" placeholder="Judul Artikel" autocomplete="off" required="required" />
	</div>
	<div class="input-block">
	<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"></textarea>
    <input type="hidden" name="class" id="classlist" value="" />
	</div>
	<div class="input-block">
	<input type="button" id="select-class" value="Atur Kelas" />
	<input type="submit" id="publish" name="publish" value="Publikasikan" />
	<input type="submit" id="draff" name="draff" value="Simpan Konsep" />
	<input type="hidden" name="option" id="option" value="add" />
	</div>
	</form>

        <?php
	}
	else
	{
	}

	include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else if(isset($_GET['article_id']))
{
	$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
	$sql_filter_article = " and `edu_article`.`article_id` = '$article_id' ";

	if(isset($school_id))
	{
		$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "select `edu_article`.*, `member`.`name` as `creator`
	from `edu_article` 
	left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
	where (`edu_article`.`active` = '1' or `edu_article`.`member_create` = '$member_id') $sql_filter_article ";
	include_once dirname(__FILE__)."/lib.inc/header.php";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		if(@$auth_teacher_id)
		{
		?>
		<script type="text/javascript">
        $(document).ready(function(e) {
            $(document).on('click', '.delete-post', function(e){
                var article_id = $(this).attr('data-id');
                if(confirm('Apakah Anda akan menghapus artikel ini?'))
                {
                    $.post('../lib.ajax/ajax-delete-artikel.php', {article_id:article_id, option:'delete'}, function(asnwer){
                        window.location = 'artikel.php';
                    });
                }
                e.preventDefault();
            });
        });
        </script>
        <?php
		}
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
        <div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
        <div class="article-content"><?php echo $data['content'];?></div>
        <div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
        <div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
        <div class="article-link">
            <a href="artikel.php">Lihat Semua</a>
			<?php
            if($auth_teacher_id && $auth_teacher_school_id && $auth_teacher_school_id == $data['school_id'] && $auth_teacher_id == $data['member_create'])
            {
                ?>
                <a href="artikel.php?option=edit&article_id=<?php echo $data['article_id'];?>">Ubah</a>
                <a class="delete-post" data-id="<?php echo $data['article_id'];?>" href="artikel.php?option=delete&article_id=<?php echo $data['article_id'];?>">Hapus</a>
                <a class="add-post" data-id="<?php echo $data['article_id'];?>" href="artikel.php?option=add">Hapus</a>
                <?php
            }
            ?>
        </div>
        <?php
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	$sql_filter_article = "";
	if(isset($school_id))
	{
		$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "select `edu_article`.*, `member`.`name` as `creator`
	from `edu_article` 
	left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
	where (`edu_article`.`active` = '1' or `edu_article`.`member_create` = '$member_id') $sql_filter_article 
	order by `edu_article`.`article_id` desc
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		if(@$auth_teacher_id)
		{
		?>
		<script type="text/javascript">
        $(document).ready(function(e) {
            $(document).on('click', '.delete-post', function(e){
                var article_id = $(this).attr('data-id');
				var obj = $(this).closest('.article-item');
                if(confirm('Apakah Anda akan menghapus artikel ini?'))
                {
                    $.post('../lib.ajax/ajax-delete-artikel.php', {article_id:article_id, option:'delete'}, function(asnwer){
                        obj.fadeOut(200, function(){
							obj.remove();
						});
                    });
                }
                e.preventDefault();
            });
        });
        </script>
        <?php
		}
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
        <div class="article-list">
        <?php
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($rows as $data)
		{

			$obj = parsehtmldata('<html><body>'.($data['content']).'</body></html>');
			$arrparno = array();
			$arrparlen = array();
			$cntmax = ""; // do not remove
			$content = ""; // do not remove
			$i = 0;
			$minlen = 300;
			
			if(isset($obj->p) && count($obj->p)>0)
			{
				$max = 0;
				foreach($obj->p as $parno=>$par)
				{
					$arrparlen[$i] = strlen($par);
					if($arrparlen[$i]>$max)
					{
						$max = $arrparlen[$i];
						$cntmax = $par;
					}
					if($arrparlen[$i] >= $minlen)
					{
						$content = $par;
						break;
					}
				}
				if(!$content)
				{
					
					$content = $cntmax;
				}
			}
			if(!$content)
			{
				$content = "&nbsp;";
			}
			$maxlen = 300;
			if(strlen($content)>$maxlen)
			{
				$content.=" ";
				$pos = stripos($content, ". ", $maxlen);
				if($pos===false){
				$pos = stripos($content, ".", $maxlen);
				}
				if($pos===false){
				$pos = stripos($content, " ", $maxlen);
				}
				if($pos===false) $pos = $maxlen;
				$content = substr($content, 0, $pos+1);
				$content = tidyHTML($content);
			}
		
			?>
            <div class="article-item">
                <div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
                <div class="article-content"><?php echo $content;?></div>
                <div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
                <div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
                <div class="article-link">
                	<a href="artikel.php?article_id=<?php echo $data['article_id'];?>">Baca</a>
                    <?php
					if($auth_teacher_id && $auth_teacher_school_id && $auth_teacher_school_id == $data['school_id'] && $auth_teacher_id = $data['member_create'])
					{
						?>
                        <a href="artikel.php?option=edit&article_id=<?php echo $data['article_id'];?>">Ubah</a>
                        <a class="delete-post" data-id="<?php echo $data['article_id'];?>" href="artikel.php?option=delete&article_id=<?php echo $data['article_id'];?>">Hapus</a>
                        <?php
					}
					?>
                </div>
            </div>
            <?php
		}
		?>
        </div>
        <?php
		if($auth_teacher_id && $auth_teacher_school_id)
		{
		?>
        <div class="article-link">
        	<a href="artikel.php?option=add">Buat Baru</a>
        </div>
        <?php
		}
			
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php";
}
}

?>