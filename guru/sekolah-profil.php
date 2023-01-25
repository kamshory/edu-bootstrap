<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Profil Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
include_once dirname(__FILE__)."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><?php echo $data['school_code'];?></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Bahasa</td>
		<td><?php if($data['language']=='en') echo 'English'; if($data['language']=='id') echo 'Bahasa Indonesia';?></td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?></td>
		</tr>
		<tr>
		<td>Provinsi</td>
		<td><?php echo $data['state_id'];?></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota</td>
		<td><?php echo $data['city_id'];?></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Anda tidak terdaftar sebagai guru.</div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>