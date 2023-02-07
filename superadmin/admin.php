<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$admin_id = $admin_login->admin_id;

$cfg->page_title = "Administrator";

include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$admin_id = $admin_id2 = kh_filter_input(INPUT_POST, "admin_id2", FILTER_SANITIZE_STRING_NEW);
	$school_id = kh_filter_input(INPUT_POST, "school_id", FILTER_SANITIZE_STRING_NEW);
	$admin_level = kh_filter_input(INPUT_POST, "admin_level", FILTER_SANITIZE_NUMBER_INT);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, "birth_place", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, "birth_day", FILTER_SANITIZE_STRING_NEW);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$blocked = kh_filter_input(INPUT_POST, "blocked", FILTER_SANITIZE_NUMBER_UINT);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
	
	if($admin_id2 == $admin_login->admin_id)
	{
		$blocked = 0;
		$active = 1;
		$admin_level = 1;
	}
	
}

if(isset($_POST['set_active']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		$admin_id = addslashes($val);
		if($val != $admin_login->admin_id)
		{
			$sql = "UPDATE `edu_admin` SET `active` = true WHERE `admin_id` = '$admin_id' AND `school_id` = '$school_id'";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		$admin_id = addslashes($val);
		if($val != $admin_login->admin_id)
		{
			$sql = "UPDATE `edu_admin` SET `active` = false WHERE `admin_id` = '$admin_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		$admin_id = addslashes($val);
		if($val != $admin_login->admin_id)
		{
			$sql = "DELETE FROM `edu_member_school` WHERE `member_id` = '$admin_id' AND `role` = 'A' ";
			$database->executeDelete($sql, true);
			$sql = "UPDATE `edu_admin` SET `school_id` = '' WHERE `admin_id` = '$admin_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$sql = "SELECT * FROM `edu_school` WHERE `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$country_id = $data['country_id'];
		$language = $data['language'];

		$data = array();

		$gender = $picoEdu->mapGender($gender);
		$phone = $picoEdu->fixPhone($phone);
		$email = $picoEdu->filterEmailAddress($email);

		$time_create = $time_edit = $picoEdu->getLocalDateTime();
		$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];

		$token_admin = md5($phone . "-" . $email . "-" . time() . "-" . mt_rand(111111, 999999));

		$user_data = array();
		$user_data['name'] = $name;
		$user_data['gender'] = $gender;
		$user_data['email'] = $email;
		$user_data['phone'] = $phone;
		$user_data['password'] = md5(md5($password));
		$user_data['birth_day'] = $birth_day;
		$user_data['address'] = $address;
		$user_data['country_id'] = $country_id;
		$user_data['language'] = $language;

		if (!empty($name) && $username != '') {
			$chk = $picoEdu->getExistsingUser($user_data);
			$admin_id = addslashes($chk['member_id']);
			$username = addslashes($chk['username']);

			$sql = "INSERT INTO `edu_admin` 
			(`admin_id`, `school_id`, `username`, `admin_level`, `name`, `token_admin`, `email`, `phone`, `password`, 
			`password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
			`ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
			('$admin_id', '$school_id', '$username', '$admin_level', '$name', '$token_admin', '$email', '$phone', md5(md5('$password')), 
			'$password', '$gender', '$birth_day', '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
			'$ip_create', '$ip_edit', '0', '1');
			";
			$database->executeInsert($sql, true);

			$sql2 = "INSERT INTO `edu_member_school` 
			(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
			('$admin_id', '$school_id', 'A', '$time_create', true)
			";
			$res2 = $database->executeInsert($sql2, true);
			header("Location: " . basename($_SERVER['PHP_SELF']) . "?option=detail&admin_id=$admin_id");
		} else {
			// DO nothing
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "SELECT `school_id` FROM `edu_admin` WHERE `admin_id` = '$admin_id2'  ";
	$sql = "SELECT * FROM `edu_school` WHERE `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$initial = $data['school_id'];

		$sql = "UPDATE `edu_admin` SET 
		`name` = '$name', `admin_level` = '$admin_level', `gender` = '$gender', `birth_place` = '$birth_place', 
		`birth_day` = '$birth_day', `school_id` = '$school_id', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', 
		`ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
		WHERE `admin_id` = '$admin_id2'  ";
		$database->executeUpdate($sql, true);

		$sql = "UPDATE `edu_admin` SET 
		`email` = '$email' WHERE `admin_id` = '$admin_id2' ";
		$database->executeUpdate($sql, true);

		$sql = "UPDATE `edu_admin` SET 
		`phone` = '$phone' WHERE `admin_id` = '$admin_id2' ";
		$database->executeUpdate($sql, true);

		if ($username != '') {
			$sql = "UPDATE `edu_admin` SET 
			`username` = '$username'
			WHERE `admin_id` = '$admin_id2' ";
			$database->executeUpdate($sql, true);
		}

		if ($password != '') {
			$sql = "UPDATE `edu_admin` SET 
			`password` = md5(md5('$password'))
			WHERE `admin_id` = '$admin_id2' ";
			$database->executeUpdate($sql, true);
		}
		if ($initial != $school_id) {
			$sql2 = "INSERT INTO `edu_member_school` 
			(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
			('$admin_id', '$school_id', 'A', '$time_create', true)
			";
			$database->executeInsert($sql2, true);
		}
	}
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&admin_id=$admin_id");
}
if(@$_GET['option'] == 'add')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><select class="form-control input-select" name="school_id" id="school_id">
		<option value=""></option>
		<?php 
		$sql2 = "SELECT * FROM `edu_school` WHERE `active` = true ORDER BY `school_grade_id` asc ";
		$stmt2 = $database->executeQuery($sql2);
		if ($stmt2->rowCount() > 0) {
			$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows2 as $data2) {
				?>
            <option value="<?php echo $data2['school_id']; ?>"><?php echo $data2['name']; ?></option>
            <?php
			}
		}
		?>
		</select></td>
		</tr>
		<tr>
		<td>Username</td>
		<td><input type="text" class="form-control input-text input-text-long" name="username" id="username" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><select class="form-control input-select" name="admin_level" id="admin_level">
		<option value="2">Administrator</option>
		<option value="1">Super Administrator</option>
		</select></td>
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
		<td>Email</td>
		<td><input type="email" class="form-control input-text input-text-long" name="email" id="email" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="tel" class="form-control input-text input-text-long" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="form-control input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
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
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<td></td>
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_admin', array('blocked','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "admin_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_admin`.* 
FROM `edu_admin` 
WHERE `edu_admin`.`admin_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><select class="form-control input-select" name="school_id" id="school_id">
		<option value=""></option>
		<?php 
		$sql2 = "SELECT * FROM `edu_school` WHERE `active` = true ORDER BY `school_grade_id` asc ";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'school_id')
				),
				'selectCondition'=>array(
					'source'=>'school_id',
					'value'=>$data['school_id']
				),
				'caption'=>array(
					'delimiter'=>PicoEdu::RAQUO,
					'values'=>array(
						'name'
					)
				)
			)
		);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Username</td>
		<td><input type="text" class="form-control input-text input-text-long" name="username" id="username" value="<?php echo $data['username'];?>" autocomplete="off" />
		  <input type="hidden" name="admin_id2" id="admin_id2" value="<?php echo $data['admin_id'];?>" /></td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><select class="form-control input-select" name="admin_level" id="admin_level">
		<option value="2"<?php echo $picoEdu->ifMatch($data['admin_level'], '2', PicoConst::SELECT_OPTION_SELECTED);?>>Administrator</option>
		<option value="1"<?php echo $picoEdu->ifMatch($data['admin_level'], '1', PicoConst::SELECT_OPTION_SELECTED);?>>Super Administrator</option>
		</select></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
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
		<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="form-control input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="tel" class="form-control input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="form-control input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php echo $picoEdu->ifMatch($data['blocked'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Blokir</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
		</td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$edit_key = kh_filter_input(INPUT_GET, "admin_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_admin`.* $nt,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_admin`.`school_id` limit 0,1) AS `school_name`,
(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_admin`.`admin_create` limit 0,1) AS `admin_create`,
(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_admin`.`admin_edit` limit 0,1) AS `admin_edit`
FROM `edu_admin` 
where 1 
AND `edu_admin`.`admin_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><?php echo $data['school_name'];?> </td>
		</tr>
		<tr>
		<td>Username</td>
		<td><?php echo $data['username'];?> </td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><?php echo $picoEdu->selectFromMap($data['admin_level'], array('2' => 'Administrator', '1' => 'Super Administrator'));?> </td>
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
		<td>Token Admin</td>
		<td><?php echo $data['token_admin'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Password </td>
		<td><?php echo $data['password_initial'];?> </td>
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
		<td>Blokir</td>
		<td><?php echo $picoEdu->trueFalse($data['blocked'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&admin_id=<?php echo $data['admin_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
});
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Sekolah</span>
    <select class="form-control input-select" name="school_id" id="school_id">
    <option value="">- Pilih Sekolah -</option>
    <?php 
    $sql2 = "SELECT * FROM `edu_school` where 1 ORDER BY `time_create` DESC";

	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'school_id')
			),
			'selectCondition'=>array(
				'source'=>'school_id',
				'value'=>$school_id
			),
			'caption'=>array(
				'delimiter'=>PicoEdu::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

	
    ?>
    </select>
   <span class="search-label">Admin</span>
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
$sql_filter .= " AND (`edu_admin`.`name` like '%".addslashes($pagination->query)."%' )";
}
if(!empty($school_id)){
$pagination->array_get[] = 'school_id';
$sql_filter .= " AND (`edu_admin`.`school_id` = '$school_id' )";
}

$nt = '';

$sql = "SELECT `edu_admin`.* $nt,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_admin`.`school_id` limit 0,1) AS `school_name`
FROM `edu_admin`
WHERE 1 $sql_filter
ORDER BY `edu_admin`.`school_id` DESC, `edu_admin`.`name` asc
";
$sql_test = "SELECT `edu_admin`.*
FROM `edu_admin`
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
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
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
      <td width="16"><input type="checkbox" name="control-admin_id" id="control-admin_id" class="checkbox-selector" data-target=".admin_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>Nama</td>
      <td>L/P</td>
      <td>Email</td>
      <td>Level</td>
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
      <td><input type="checkbox" name="admin_id[]" id="admin_id" value="<?php echo $data['admin_id'];?>" class="admin_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&admin_id=<?php echo $data['admin_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['school_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $picoEdu->selectFromMap($data['gender'], array('M'=>'L', 'W'=>'P'));?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['email'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $picoEdu->selectFromMap($data['admin_level'], array('2' => 'Administrator', '1' => 'Super Administrator'));?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['blocked'], 'Ya', 'Tidak');?> </td>
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
<div class="warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>