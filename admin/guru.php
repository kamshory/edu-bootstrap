<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-admin.php";
exit();
}
if(empty(@$real_school_id))
{
include_once dirname(__FILE__)."/belum-ada-sekolah.php";
exit();
}
$cfg->page_title = "Guru";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$teacher_id = kh_filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
	$teacher_id2 = kh_filter_input(INPUT_POST, 'teacher_id2', FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['teacher_id']))
	{
		$teacher_id = $teacher_id2;
	}
	$reg_number = kh_filter_input(INPUT_POST, 'reg_number', FILTER_SANITIZE_SPECIAL_CHARS);
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
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$blocked = kh_filter_input(INPUT_POST, 'blocked', FILTER_SANITIZE_NUMBER_INT);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);
}

if(isset($_POST['set_active']) && isset($_POST['teacher_id']))
{
	$teachers = @$_POST['teacher_id'];
	if(isset($teachers) && is_array($teachers))
	{
		foreach($teachers as $key=>$val)
		{
			$teacher_id = addslashes($val);
			$sql = "UPDATE `edu_teacher` SET `active` = true WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['teacher_id']))
{
	$teachers = @$_POST['teacher_id'];
	if(isset($teachers) && is_array($teachers))
	{
		foreach($teachers as $key=>$val)
		{
			$teacher_id = addslashes($val);
			$sql = "UPDATE `edu_teacher` SET `active` = false WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['teacher_id']))
{
	$teachers = @$_POST['teacher_id'];
	if(isset($teachers) && is_array($teachers))
	{
		foreach($teachers as $key=>$val)
		{
			$teacher_id = addslashes($val);
			$sql = "DELETE FROM `edu_member_school` WHERE `member_id` = '$teacher_id' and `role` = 'T' and `school_id` = '$school_id' ";
			$database->executeDelete($sql, true);
			$sql = "UPDATE `edu_teacher` SET `school_id` = '' WHERE `teacher_id` = '$teacher_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$sql = "SELECT * from `edu_school` WHERE `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$country_id = $data['country_id'];
	$language = $data['language'];

	$data = array();

	$phone = $picoEdu->fixPhone($phone);
	$email = $picoEdu->filterEmailAddress($email);
	$gender = $picoEdu->mapGender($gender);
	$token_teacher = md5($school_id.'-'.$reg_number.'-'.time().'-'.mt_rand(111111, 999999));
	
	$password_initial = substr($token_teacher, 5, 6);
	$password = md5(md5($password_initial));

	$name = $picoEdu->trimPunctuation($name);
	$reg_number = $picoEdu->trimPunctuation($reg_number);
	$reg_number_national = $picoEdu->trimPunctuation($reg_number_national);
	$phone = $picoEdu->trimPunctuation($phone);
	$email = $picoEdu->trimPunctuation($email);

	if ($name != '') {

		if ($email == '') {
			$email = $picoEdu->generateAltEmail('planetbiru.com', ($reg_number_national != '') ? 'tc_' . $reg_number_national . '_' . $school_id : '', ($reg_number != '') ? 'tc_' . $reg_number . '_' . $school_id : '', ($phone != '') ? 'ph_' . $country_id . '_' . $phone : '');
		}

		$user_data = array();
		$user_data['name'] = $name;
		$user_data['gender'] = $gender;
		$user_data['email'] = $email;
		$user_data['phone'] = $phone;
		$user_data['password'] = $password;
		$user_data['birth_day'] = $birth_day;
		$user_data['address'] = $address;
		$user_data['country_id'] = $country_id;
		$user_data['language'] = $language;
		if ($name != '' && $email != '') {
			$chk = $picoEdu->getExistsingUser($user_data);
			$teacher_id = $chk['member_id'];
			$username = $chk['username'];
			if ($picoEdu->checkTeacher($school_id, $reg_number, $reg_number_national, $name)) {
				// Do nothing
			} else {
				$sql = "INSERT INTO `edu_teacher` 
				(`teacher_id`, `username`, `token_teacher`, `school_id`, `reg_number`, `reg_number_national`, `name`, 
				`gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, `address`, 
				`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
				('$teacher_id', '$username', '$token_teacher', '$school_id', '$reg_number', '$reg_number_national', '$name', 
				'$gender', '$birth_place', '$birth_day', '$phone', '$email', '$password', '$password_initial', '$address', 
				'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '1')
				";
				$database->executeInsert($sql, true);

				$sql2 = "INSERT INTO `edu_member_school` 
				(`member_id`, `school_id`, `role`, `time_create`, `active`) values
				('$teacher_id', '$school_id', 'T', '$time_create', '1')
				";
				$database->executeInsert($sql2);

				$sql3 = "UPDATE `edu_teacher` SET `school_id` = '$school_id' WHERE `teacher_id` = '$teacher_id' 
				and (`school_id` = '' or `school_id` is null)
				";
				$database->executeUpdate($sql3);
				header("Location: " . basename($_SERVER['PHP_SELF']) . "?option=detail&teacher_id=$teacher_id");
			}
		}
	}
}
if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_teacher` set 
	`reg_number` = '$reg_number', `reg_number_national` = '$reg_number_national', `name` = '$name', `gender` = '$gender', 
	`birth_place` = '$birth_place', `birth_day` = '$birth_day', `address` = '$address', `time_edit` = '$time_edit', 
	`admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
	WHERE `teacher_id` = '$teacher_id2' and `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	
	if($phone != '')
	{
		$sql = "UPDATE `edu_teacher` set 
		`phone` = '$phone'
		WHERE `teacher_id` = '$teacher_id2' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	if($email != '')
	{
		$sql = "UPDATE `edu_teacher` set 
		`email` = '$email'
		WHERE `teacher_id` = '$teacher_id2' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	if($password != '')
	{
		$sql = "UPDATE `edu_teacher` set 
		`password` = md5(md5('$password')), `password_initial` = '$password'
		WHERE `teacher_id` = '$teacher_id2' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&teacher_id=$teacher_id");
}

if(@$_GET['option'] == 'add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<form name="formedu_teacher" id="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>No.Induk</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number" id="reg_number" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number_national" id="reg_number_national" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="form-control input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M">Laki-Laki</option>
		<option value="W">Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="form-control input-text input-text-long" name="birth_place" id="birth_place" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><input type="tel" class="form-control input-text input-text-long" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="form-control input-text input-text-long" name="email" id="email" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="form-control input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat
		</td><td><textarea name="address" class="form-control input-text input-text-long" id="address" autocomplete="off"></textarea></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"> Blokir</label>
		</td>
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
<?php getDefaultValues($database, 'edu_teacher', array('blocked','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_teacher`.* 
from `edu_teacher` 
WHERE `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" id="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>No.Induk</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number" id="reg_number" value="<?php echo $data['reg_number'];?>" autocomplete="off" /><input type="hidden" name="teacher_id2" id="teacher_id2" value="<?php echo $data['teacher_id'];?>" /></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><input type="text" class="form-control input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="form-control input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M"<?php if($data['gender'] == 'M') {echo ' selected="selected"';}?>>Laki-Laki</option>
		<option value="W"<?php if($data['gender'] == 'W') {echo ' selected="selected"';}?>>Perempuan</option>
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
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php echo $picoEdu->ifMatch($data['blocked'], true, ' checked="checked"');?>> Blokir</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, ' checked="checked"');?>> Aktif</label>
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
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option'] == 'print-password')
{
include_once dirname(__FILE__)."/cetak-login-guru.php";
}
else if(@$_GET['option'] == 'detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_teacher`.* $nt,
(select `edu_admin`.`name` from `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_teacher`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_teacher`.`admin_edit`) as `admin_edit`
from `edu_teacher` 
WHERE `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>No.Induk</td>
		<td><?php echo $data['reg_number'];?> </td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><?php echo $data['reg_number_national'];?> </td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
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
		<td>Telepon</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
		<tr>
		<td>Password 
		</td><td><?php echo $data['password_initial'];?> </td>
		</tr>
		<tr>
		<td>Alamat
		</td><td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Buat
		</td>
        <td><?php echo $data['admin_create'];?> </td>
		</tr>
		<tr>
		<td>Admin Ubah
		</td>
        <td><?php echo $data['admin_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Buat
		</td><td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah
		</td>
        <td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><?php echo $data['blocked']?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&teacher_id=<?php echo $data['teacher_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Nama Guru</span>
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
$sql_filter .= " and (`edu_teacher`.`name` like '%".addslashes($pagination->query)."%' )";
}

$nt = '';

$sql = "SELECT `edu_teacher`.* $nt
from `edu_teacher`
WHERE `edu_teacher`.`school_id` = '$school_id' $sql_filter
order by `edu_teacher`.`teacher_id` asc
";
$sql_test = "SELECT `edu_teacher`.*
from `edu_teacher`
WHERE `edu_teacher`.`school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
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
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(10){
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
      <td width="16"><input type="checkbox" name="control-teacher_id" id="control-teacher_id" class="checkbox-selector" data-target=".teacher_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>No.Induk</td>
      <td>NUPTK</td>
      <td>Nama</td>
      <td>L/P</td>
      <td>Telepon</td>
      <td>Email</td>
      <td>Blokir</td>
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
      <td><input type="checkbox" name="teacher_id[]" id="teacher_id" value="<?php echo $data['teacher_id'];?>" class="teacher_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&teacher_id=<?php echo $data['teacher_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo $data['reg_number'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo $data['reg_number_national'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php if($data['gender']=='M') echo 'L'; if($data['gender']=='W') echo 'P';?> </td>
      <td><?php echo $data['phone'];?> </td>
      <td><?php echo $data['email'];?> </td>
      <td><?php echo $data['blocked']?'Ya':'Tidak';?> </td>
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
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn com-button btn-success" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn com-button btn-success" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn com-button btn-success delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn com-button btn-success" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
  <input type="button" name="print" id="print" value="Cetak Password" class="btn com-button btn-success" onclick="window.open('<?php echo basename($_SERVER['PHP_SELF']);?>?option=print-password')" />
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
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>