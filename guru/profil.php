<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
$cfg->module_title = "Profil Guru";
if(count(@$_POST) && isset($_POST['save']))
{
	$reg_number_national = kh_filter_input(INPUT_POST, 'reg_number_national', FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, 'birth_place', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, 'birth_day', FILTER_SANITIZE_STRING_NEW);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
	$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $teacher_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
}

if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `edu_teacher` set 
	`reg_number_national` = '$reg_number_national', `name` = '$name', `gender` = '$gender', 
	`birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', 
	`time_edit` = '$time_edit', `ip_edit` = '$ip_edit'
	where `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	if($email != '')
	{
		$sql = "update `edu_teacher` set 
		`email` = '$email'
		where `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql);
	}
	if($password != '')
	{
		$sql = "update `edu_teacher` set 
		`password` = md5(md5('$password')), `password_initial` = ''
		where `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql);
		$sql = "update `member` set 
		`password` = md5(md5('$password'))
		where `member_id` = '$teacher_id'  ";
		$database->executeUpdate($sql);
		$_SESSION['password'] = md5($password);
	}
	header("Location: profil.php");
}
if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$sql = "SELECT `edu_teacher`.* , `edu_school`.`name` as `school_name`
from `edu_teacher` 
left join(`edu_school`) on(`edu_school`.`school_id` = `edu_teacher`.`school_id`)
where `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" id="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nomor Induk</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number" id="reg_number" value="<?php echo ($data['reg_number']);?>" autocomplete="off" readonly="readonly" />
        <input type="hidden" name="teacher_id2" id="teacher_id2" value="<?php echo $data['teacher_id'];?>" /></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo ($data['reg_number_national']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M"<?php if($data['gender'] == 'M') {echo ' selected="selected"';}?>>Laki-Laki</option>
		<option value="W"<?php if($data['gender'] == 'W') {echo ' selected="selected"';}?>>Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="input-text input-text-long" name="birth_place" id="birth_place" value="<?php echo $data['birth_place'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><input type="tel" class="input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat
		</td><td><textarea name="address" class="input-text input-text-long" id="address" autocomplete="off"><?php echo $data['address'];?></textarea></td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_teacher`.* , `edu_school`.`name` as `school_name`
from `edu_teacher` 
left join(`edu_school`) on(`edu_school`.`school_id` = `edu_teacher`.`school_id`)
where `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Nomor Induk</td>
		<td><?php echo ($data['reg_number']);?></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><?php echo ($data['reg_number_national']);?></td>
		</tr>
        <?php
		if($data['school_name'])
		{
		?>
		<tr>
		<td>Sekolah</td>
		<td><?php echo ($data['school_name']);?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><?php echo $data['birth_place'];?></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Alamat
		</td><td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td></td>
		<td>
        <input type="button" name="edit" id="edit" class="com-button" value="Ubah Data" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit'" />
        <input type="button" name="edit" id="edit" class="com-button" value="Pilih Sekolah" onclick="window.location='ganti-sekolah.php'" />
        </td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Anda tidak terdaftar sebagai guru. <a href="../">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
?>