<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";

if(!isset($school_id) || empty($school_id))
{
	require_once __DIR__."/bukan-admin.php";
	exit();
}
$pageTitle = "Keterangan Sekolah";
$pagination = new \Pico\PicoPagination();
if(isset($_POST['save']))
{
	$description = kh_filter_input(INPUT_POST, "description");
	
	$school_dir = dirname(__DIR__) . "/media.edu/school/$school_id/description";
	$base_src = "media.edu/school/$school_id/description";

	$dir2prepared = dirname(__DIR__) . "/media.edu/school/$school_id/description";
	$dirBase = dirname(__DIR__);
	$permission = 0755;
	$fileSync->prepareDirectory($school_dir, $dirBase, $permission, true);

	$description = \Pico\PicoDOM::extractImageData($description, $school_dir, $base_src, $fileSync);
	
	$description = addslashes(utf8ToEntities($description));
	
	$time_create = $time_edit = $database->getLocalDateTime();
	
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	
	$sql = "UPDATE `edu_school` SET
	`description` = '$description'
	WHERE `school_id` = '$school_id'
	";
	$database->executeUpdate($sql, true);
	header("Location: ".$picoEdu->gateBaseSelfName());
		
}

if(@$_GET['option'] == 'edit')
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR

$state_list = array();
$city_list = array();
$sql = "SELECT `edu_school`.* 
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
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
<input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
<input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" />
</div>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR

}
else
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(SELECT `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) AS `country_id`,
(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
	$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
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
<input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit'" />
</div>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Anda tidak terdaftar sebagai Administrator sekolah. <a href="impor-data.php">Klik di sini untuk import data.</a></div>	
<?php
}
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
?>