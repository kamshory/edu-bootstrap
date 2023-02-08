<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$cfg->page_title = "Pilih Sekolah";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option'] == 'select')
{
	$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_school`.* 
	FROM `edu_member_school`
	INNER JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_member_school`.`school_id`)
	WHERE `edu_member_school`.`member_id` = '$admin_id' AND `edu_member_school`.`role` = 'A' 
	ORDER BY `edu_school`.`school_id` ASC
	";
	$stmt = $database->executeQuery($sql);

	if($stmt->rowCount() > 0)
	{

		$sql = "SELECT * FROM `member` WHERE `member_id` = '$admin_id' ";
		$stmt2 = $database->executeQuery($sql);
		$member_data = $stmt2->fetch(PDO::FETCH_ASSOC);
		
		$name = addslashes($member_data['name']);				
		$gender = addslashes($member_data['gender']);				
		$birth_place = addslashes($member_data['birth_place']);				
		$birth_day = addslashes($member_data['birth_day']);				
		$phone = addslashes($member_data['phone']);				
		$email = addslashes($member_data['email']);				
		$password = addslashes($member_data['password']);				
		$token_admin = md5($school_id.'-'.$admin_id.'-'.time().'-'.mt_rand(111111, 999999));

		
		$sql = "INSERT INTO `edu_admin` 
		(`admin_id`, `token_admin`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, 
		`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
		('$admin_id', '$token_admin', '$school_id', '$name', '$gender', '$birth_place', '$birth_day', '$phone', '$email', '$password',  
		'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 0, 1)
		";
		$database->executeInsert($sql, true);
		
		$sql = "UPDATE `edu_admin` SET `school_id` = '$school_id' WHERE `admin_id` = '$admin_id' ";
		$database->executeUpdate($sql, true);
		header("Location: index.php");
	}

}
if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id`) AS `student`,
(SELECT `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) AS `country_id`,
(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) AS `admin_edit`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Token Sekolah</td>
		<td><?php echo $data['token_school'];?> </td>
		</tr>
		<tr>
		<td>Nama Sekolah</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><a href="../<?php echo $data['school_code'];?>" target="_blank"><?php echo $data['school_code'];?></a></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?> </td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?> </td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?> </td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
		<tr>
		<td>Bahasa</td>
		<td><?php echo $picoEdu->selectFromMap($data['language'], array('en'=>'English', 'id'=>'Bahasa Indonesia'));?> </td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?> </td>
		</tr>
		<tr>
		<td>Provinsi</td>
		<td><?php echo $data['state_id'];?> </td>
		</tr>
		<tr>
		<td>Kabupaten/Kota</td>
		<td><?php echo $data['city_id'];?> </td>
		</tr>
		<tr>
		<td>Jumlah Siswa</td>
		<td><?php echo $data['student'];?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?> </td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Pilih" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=select&school_id=<?php echo $data['school_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Sekolah</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_school`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}


$nt = '';

$sql = "SELECT `edu_school`.* $nt,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) AS `admin_edit`
FROM `edu_member_school`
INNER JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_member_school`.`school_id`)
WHERE `edu_member_school`.`member_id` = '$admin_id' AND `edu_member_school`.`role` = 'A' $sql_filter
ORDER BY `edu_school`.`school_id` ASC
";
$sql_test = "SELECT `edu_school`.*
FROM `edu_member_school`
INNER JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_member_school`.`school_id`)
WHERE `edu_member_school`.`member_id` = '$admin_id' AND `edu_member_school`.`role` = 'A' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination(basename($_SERVER['PHP_SELF']), true); 
$paginationHTML = $pagination->buildHTML();
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
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>Nama Sekolah</td>
      <td>Jenjang</td>
      <td>N/S</td>
      <td>Kepala Sekolah</td>
      </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	$cls = "";
	if($data['active'])
	{
		$cls .= "data-active";
	}
	else 
	{
		$cls .= "data-inactive";
	}
	if($data['school_id'] == @$auth_school_id) 
	{
		$cls .= " data-default";
	}
	?>
    <tr class="<?php echo $cls;?>">
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['principal'];?></a></td>
      </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
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
<div class="warning">Data tidak ditemukan. <a href="impor-data.php?option=add">Klik di sini untuk membuat sekolah.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>