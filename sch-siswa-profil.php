<?php
require_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";
$pageTitle = "Siswa";
require_once dirname(__FILE__) . "/lib.inc/cfg.pagination.php";

if (isset($_POST['save']) && @$_GET['option'] == 'edit') {
	$reg_number_national = kh_filter_input(INPUT_POST, "reg_number_national", FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, "birth_place", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, "birth_day", FILTER_SANITIZE_STRING_NEW);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	$address = kh_filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
	$religion_id = kh_filter_input(INPUT_POST, "religion_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$admin_create = $admin_edit = $admin_id;
	$sql = "UPDATE `edu_student` SET 
	`reg_number_national` = '$reg_number_national', `name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit'
	WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	if ($email != '') {
		$sql = "UPDATE `edu_student` SET 
		`email` = '$email'
		WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	if ($password != '') {
		$sql = "UPDATE `edu_student` SET 
		`password` = md5(md5('$password')), `password_initial` = ''
		WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
		$sql = "UPDATE `member` SET 
		`password` = md5(md5('$password'))
		WHERE `member_id` = '$student_id'  ";
		$database->executeUpdate($sql, true);
		$_SESSION['password'] = md5($password);
		$ksession->forcesave();
	}
	header("Location: profil.php");
}
if (@$_GET['option'] == 'edit') {
	include_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
	$sql = "SELECT `edu_student`.* 
	FROM `edu_student` 
	WHERE `edu_student`.`school_id` = '$school_id'
	AND `edu_student`.`student_id` = '$student_id'
	";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
		<form name="formedu_student" id="formedu_student" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Nama</td>
					<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>NISN</td>
					<td><input type="text" class="form-control input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Jenis Kelamin</td>
					<td><select class="form-control input-select" name="gender" id="gender">
							<option value="M"<?php echo $picoEdu->ifMatch($data['gender'], 'M', PicoConst::SELECT_OPTION_SELECTED);?>>Laki-Laki</option>
							<option value="W"<?php echo $picoEdu->ifMatch($data['gender'], 'W', PicoConst::SELECT_OPTION_SELECTED);?>>Perempuan</option>
						</select></td>
				</tr>
				<tr>
					<td>Tempat Lahir</td>
					<td><input type="text" class="form-control input-text input-text-long" name="birth_place" id="birth_place" value="<?php echo $data['birth_place']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Tanggal Lahir</td>
					<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Telepon
					</td>
					<td><input type="tel" class="form-control input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Email</td>
					<td><input type="email" class="form-control input-text input-text-long" name="email" id="email" value="<?php echo $data['email']; ?>" autocomplete="off" data-type="email" /></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input type="password" class="form-control input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td><textarea name="address" class="form-control input-text input-text-long" id="address" autocomplete="off"><?php echo $data['address']; ?></textarea></td>
				</tr>
			</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td></td>
					<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan" class="btn com-button btn-success" onclick="window.location='profil.php'" /></td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="warning">Data tidak ditemukan. <a href="profil.php">Klik di sini untuk kembali.</a></div>
	<?php
	}
	include_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
} else {
	include_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
	$nt = '';
	$sql = "SELECT `edu_student`.* , `edu_school`.`name` AS `school_name`, `edu_school`.`open` AS `school_open`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_edit`) AS `admin_edit`,
(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` limit 0,1) AS `class_id`
FROM `edu_student` 
LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_student`.`school_id`)
WHERE `edu_student`.`school_id` = '$school_id'
AND `edu_student`.`student_id` = '$student_id'
";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
		<form name="formedu_student" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Nama</td>
					<td><?php echo $data['name'];?> </td>
				</tr>
				<tr>
					<td>NIS</td>
					<td><?php echo $data['reg_number'];?> </td>
				</tr>
				<tr>
					<td>NISN</td>
					<td><?php echo $data['reg_number_national'];?> </td>
				</tr>
				<?php
				if ($data['school_name']) {
				?>
					<tr>
						<td>Sekolah</td>
						<td><?php echo $data['school_name'];?> </td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td>Tingkat</td>
					<td><?php
					echo $picoEdu->getGradeName($data['grade_id']);
						
						?>
					<td>
				</tr>
				<tr>
					<td>Kelas</td>
					<td><?php echo $data['class_id'];?> </td>
				</tr>
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
					<td>Telepon
					</td>
					<td><?php echo $data['phone'];?> </td>
				</tr>
				<tr>
					<td>Email</td>
					<td><?php echo $data['email'];?> </td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td><?php echo $data['address'];?> </td>
				</tr>
				<tr>
					<td>Dibuat</td>
					<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
				</tr>
				<tr>
					<td>Diubah</td>
					<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
				</tr>
			</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td></td>
					<td>
						<input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='profil.php?option=edit'" />
						<input type="button" name="selectschool" id="selectschool" class="btn com-button btn-success" value="Pilih Sekolah" onclick="window.location='../siswa/ganti-sekolah.php'" />
						<?php
						if ($data['school_open'] == '1') {
						?>
							<input type="button" name="selectclass" id="selectclass" class="btn com-button btn-success" value="Pilih Kelas" onclick="window.location='../siswa/ganti-kelas.php'" />
						<?php
						}
						?>
					</td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="warning">Data tidak ditemukan. <a href="profil.php">Klik di sini untuk kembali.</a></div>
<?php
	}
	include_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
}
?>