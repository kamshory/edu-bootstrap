<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
$cfg->page_title = "Keterangan Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(isset($_POST['save']))
{
	$description = kh_filter_input(INPUT_POST, 'description');
	
	$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/description";
	$base_src = "media.edu/school/$school_id/description";

	$dir2prepared = dirname(dirname(__FILE__))."/media.edu/school/$school_id/description";
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	$description = extractImageData($description, $base_dir, $base_src, $fileSync);
	
	$description = addslashes(UTF8ToEntities($description));
	
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	
	$sql = "UPDATE `edu_school` set
	`description` = '$description'
	WHERE `school_id` = '$school_id'
	";
	$database->executeUpdate($sql, true);
	header("Location: ".basename($_SERVER['PHP_SELF']));
		
}

if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";

$state_list = array();
$city_list = array();
$sql = "SELECT `edu_school`.* 
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style type="text/css">
#description{
	width:100%;
	box-sizing:border-box;
	height:240px;
}
@media screen and (max-width:599px)
{
.button-area-responsive input[type="button"], .button-area-responsive input[type="submit"]{
	display:inline-block;
	width:100%;
	box-sizing:border-box;
	margin:4px 0;
}
}
</style>

<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/wysiwyg-editor.js"></script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
<div class="input-block">
<textarea class="wysiwyg-editor" name="description" id="description"><?php echo htmlspecialchars($data['description']);?></textarea>
</div>
<div class="input-block button-area-responsive">
<input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> 
<input type="button" name="showall" id="showall" value="Kembali" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) as `city_id`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
	$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style type="text/css">
@media screen and (max-width:599px)
{
.button-area-responsive input[type="button"], .button-area-responsive input[type="submit"]{
	display:inline-block;
	width:100%;
	box-sizing:border-box;
	margin:4px 0;
}
}
</style>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
<div class="page-title"><h3><?php echo $data['name'];?></h3></div>
<div class="page-content"><?php echo($data['description']!='')?$data['description']:'<p>[Tulis keterangan tentang sekolah. Klik tombol &quot;Ubah&quot; di bawah ini.]</p>';?></div>
<div class="input-block button-area-responsive">
<input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit'" />
</div>
</form>
<?php
}
else
{
?>
<div class="warning">Anda tidak terdaftar sebagai Administrator sekolah. <a href="impor-data.php">Klik di sini untuk import data.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>