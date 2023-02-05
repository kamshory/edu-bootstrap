<?php
include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
$cfg->page_title = "Profil Guru";
if(count(@$_POST) && isset($_POST['save']))
{
	$reg_number = kh_filter_input(INPUT_POST, "reg_number", FILTER_SANITIZE_SPECIAL_CHARS);
	$reg_number_national = kh_filter_input(INPUT_POST, "reg_number_national", FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, "birth_place", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, "birth_day", FILTER_SANITIZE_STRING_NEW);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	$address = kh_filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $member_login->member_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_teacher` SET 
	`reg_number` = '$reg_number', `reg_number_national` = '$reg_number_national', `name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit'
	WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
	$database->execute($sql);
	
	if($email != '')
	{
		$sql = "UPDATE `edu_teacher` SET 
		`email` = '$email'
		WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
		$database->execute($sql);
	}
	if($password != '')
	{
		$sql = "UPDATE `edu_teacher` SET 
		`password` = md5(md5('$password')), `password_initial` = ''
		WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
		$database->execute($sql);
		
		$sql = "UPDATE `member` SET 
		`password` = md5(md5('$password'))
		WHERE `member_id` = '$teacher_id'  ";
		$database->execute($sql);
		$_SESSION['password'] = md5($password);
		$ksession->forcesave();
	}
	
	header("Location: profil.php");
}
if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$sql = "SELECT `edu_teacher`.* , `edu_school`.`name` as `school_name`
FROM `edu_teacher` 
left join(`edu_school`) on(`edu_school`.`school_id` = `edu_teacher`.`school_id`)
WHERE `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" id="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nomor Induk</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number" id="reg_number" value="<?php echo $data['reg_number'];?>" autocomplete="off" /><input type="hidden" name="teacher_id2" id="teacher_id2" value="<?php echo $data['teacher_id'];?>" /></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="form-control input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M"<?php echo $picoEdu->ifMatch($data['gender'], 'M', PicoConst::SELECT_OPTION_SELECTED);?>>Laki-Laki</option>
		<option value="W"<?php echo $picoEdu->ifMatch($data['gender'], 'W', PicoConst::SELECT_OPTION_SELECTED);?>>Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="form-control input-text input-text-long" name="birth_place" id="birth_place" value="<?php echo $data['birth_place'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><input type="tel" class="form-control input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="form-control input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="form-control input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat
		</td><td><textarea name="address" class="form-control input-text input-text-long" id="address" autocomplete="off"><?php echo $data['address'];?></textarea></td>
		</tr>
</table>
<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">	
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan" class="btn com-button btn-success" onclick="window.location='profil.php'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="profil.php">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$nt = '';
$sql = "SELECT `edu_teacher`.* , `edu_school`.`name` as `school_name`
FROM `edu_teacher` 
left join(`edu_school`) on(`edu_school`.`school_id` = `edu_teacher`.`school_id`)
WHERE `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Nomor Induk</td>
		<td><?php echo $data['reg_number'];?> </td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><?php echo $data['reg_number_national'];?> </td>
		</tr>
        <?php
		if($data['school_name'])
		{
		?>
		<tr>
		<td>Sekolah</td>
		<td><?php echo $data['school_name'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?> </td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><?php echo $data['birth_place'];?> </td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?> </td>
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
		<td>Alamat
		</td><td><?php echo $data['address'];?> </td>
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
		<td>IP Buat
		</td><td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah
		</td><td><?php echo $data['ip_edit'];?> </td>
		</tr>
		</table>
<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td>
        <input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah Data" onclick="window.location='profil.php?option=edit'" />
        <input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Pilih Sekolah" onclick="window.location='../guru/ganti-sekolah.php'" />
        </td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="profil.php">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
?>