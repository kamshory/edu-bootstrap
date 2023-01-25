<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$admin_id = $admin_login->admin_id;

$cfg->module_title = "Administrator";

include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$admin_id = $admin_id2 = kh_filter_input(INPUT_POST, 'admin_id2', FILTER_SANITIZE_STRING_NEW);
	$school_id = kh_filter_input(INPUT_POST, 'school_id', FILTER_SANITIZE_STRING_NEW);
	$admin_level = kh_filter_input(INPUT_POST, 'admin_level', FILTER_SANITIZE_NUMBER_INT);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, 'birth_place', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, 'birth_day', FILTER_SANITIZE_STRING_NEW);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$blocked = kh_filter_input(INPUT_POST, 'blocked', FILTER_SANITIZE_NUMBER_INT);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);
	
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
			$sql = "update `edu_admin` set `active` = '1' where `admin_id` = '$admin_id' and `school_id` = '$school_id'";
			$database->executeUpdate($sql);
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
			$sql = "update `edu_admin` set `active` = '0' where `admin_id` = '$admin_id' ";
			$database->executeUpdate($sql);
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
			$sql = "DELETE FROM `edu_member_school` where `member_id` = '$admin_id' and `role` = 'A' ";
			$database->executeDelete($sql);
			$sql = "update `edu_admin` set `school_id` = '0' where `admin_id` = '$admin_id' ";
			$database->executeUpdate($sql);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$sql = "select * from `edu_school` where `school_id` = '$school_id' ";
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

		if ($name != '' && $username != '') {
			$chk = $picoEdu->getExistsingUser($user_data);
			$admin_id = $chk['member_id'];
			$username = $chk['username'];

			$sql = "INSERT INTO `edu_admin` 
			(`admin_id`, `school_id`, `username`, `admin_level`, `name`, `token_admin`, `email`, `phone`, `password`, 
			`password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
			`ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
			('$admin_id', '$school_id', '$username', '$admin_level', '$name', '$token_admin', '$email', '$phone', md5(md5('$password')), 
			'$password', '$gender', '$birth_day', '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
			'$ip_create', '$ip_edit', '0', '1');
			";
			$database->executeInsert($sql);

			$sql2 = "INSERT INTO `edu_member_school` 
			(`member_id`, `school_id`, `role`, `time_create`, `active`) values
			('$admin_id', '$school_id', 'A', '$time_create', '1')
			";
			$res2 = $database->executeInsert($sql2);
			header("Location: " . basename($_SERVER['PHP_SELF']) . "?option=detail&admin_id=$admin_id");
		} else {
			// DO nothing
		}
	}
}

if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "SELECT `school_id` from `edu_admin` where `admin_id` = '$admin_id2'  ";
	$sql = "select * from `edu_school` where `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$initial = $data['school_id'];

		$sql = "update `edu_admin` set 
		`name` = '$name', `admin_level` = '$admin_level', `gender` = '$gender', `birth_place` = '$birth_place', 
		`birth_day` = '$birth_day', `school_id` = '$school_id', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', 
		`ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
		where `admin_id` = '$admin_id2'  ";
		$database->executeUpdate($sql);

		$sql = "update `edu_admin` set 
		`email` = '$email' where `admin_id` = '$admin_id2' ";
		$database->executeUpdate($sql);

		$sql = "update `edu_admin` set 
		`phone` = '$phone' where `admin_id` = '$admin_id2' ";
		$database->executeUpdate($sql);

		if ($username != '') {
			$sql = "update `edu_admin` set 
			`username` = '$username'
			where `admin_id` = '$admin_id2' ";
			$database->executeUpdate($sql);
		}

		if ($password != '') {
			$sql = "update `edu_admin` set 
			`password` = md5(md5('$password'))
			where `admin_id` = '$admin_id2' ";
			$database->executeUpdate($sql);
		}
		if ($initial != $school_id) {
			$sql2 = "INSERT INTO `edu_member_school` 
			(`member_id`, `school_id`, `role`, `time_create`, `active`) values
			('$admin_id', '$school_id', 'A', '$time_create', '1')
			";
			$database->executeInsert($sql2);
		}
	}
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&admin_id=$admin_id");
}
if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><select class="input-select" name="school_id" id="school_id">
		<option value=""></option>
		<?php 
		$sql2 = "select * from `edu_school` where `active` = '1' order by `school_grade_id` asc ";
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
		<td><input type="text" class="input-text input-text-long" name="username" id="username" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><select class="input-select" name="admin_level" id="admin_level">
		<option value="2">Administrator</option>
		<option value="1">Super Administrator</option>
		</select></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M">Laki-Laki</option>
		<option value="W">Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="input-text input-text-long" name="birth_place" id="birth_place" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="input-text input-text-date" name="birth_day" id="birth_day" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="input-text input-text-long" name="email" id="email" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="tel" class="input-text input-text-long" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
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
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_admin', array('blocked','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'admin_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_admin`.* 
from `edu_admin` 
where `edu_admin`.`admin_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><select class="input-select" name="school_id" id="school_id">
		<option value=""></option>
		<?php 
		$sql2 = "select * from `edu_school` where `active` = '1' order by `school_grade_id` asc ";
		$stmt2 = $database->executeQuery($sql2);
		if ($stmt2->rowCount() > 0) {
			$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows2 as $data2) {
				?>
            <option value="<?php echo $data2['school_id']; ?>"<?php if ($data['school_id'] == $data2['school_id'])
					  echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
            <?php
			}
		}
		?>
		</select></td>
		</tr>
		<tr>
		<td>Username</td>
		<td><input type="text" class="input-text input-text-long" name="username" id="username" value="<?php echo ($data['username']);?>" autocomplete="off" />
		  <input type="hidden" name="admin_id2" id="admin_id2" value="<?php echo ($data['admin_id']);?>" /></td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><select class="input-select" name="admin_level" id="admin_level">
		<option value="2"<?php if($data['admin_level'] == '2') echo ' selected="selected"';?>>Administrator</option>
		<option value="1"<?php if($data['admin_level'] == '1') echo ' selected="selected"';?>>Super Administrator</option>
		</select></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
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
		<td><input type="date" class="input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /> TTTT-BB-HH</td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="tel" class="input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php if($data['blocked']==1) echo ' checked="checked"';?>> Blokir</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php if($data['active']==1) echo ' checked="checked"';?>> Aktif</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'admin_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_admin`.* $nt,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_admin`.`school_id` limit 0,1) as `school_name`,
(select `edu_admin1`.`name` from `edu_admin` as `edu_admin1` where `edu_admin1`.`admin_id` = `edu_admin`.`admin_create` limit 0,1) as `admin_create`,
(select `edu_admin2`.`name` from `edu_admin` as `edu_admin2` where `edu_admin2`.`admin_id` = `edu_admin`.`admin_edit` limit 0,1) as `admin_edit`
from `edu_admin` 
where 1 
and `edu_admin`.`admin_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_admin" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><?php echo ($data['school_name']);?></td>
		</tr>
		<tr>
		<td>Username</td>
		<td><?php echo ($data['username']);?></td>
		</tr>
		<tr>
		<td>Level Admin</td>
		<td><?php if($data['admin_level']=='2') echo 'Administrator'; if($data['admin_level']=='1') echo 'Super Administrator';?></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
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
		<td>Token Admin</td>
		<td><?php echo $data['token_admin'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Password </td>
		<td><?php echo $data['password_initial'];?></td>
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
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><?php echo ($data['blocked'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&admin_id=<?php echo $data['admin_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
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
    <select class="input-select" name="school_id" id="school_id">
    <option value="">- Pilih Sekolah -</option>
    <?php 
    $sql2 = "select * from `edu_school` where 1 order by `school_id` desc ";
	$stmt2 = $database->executeQuery($sql2);
	if ($stmt2->rowCount() > 0) {
		$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows2 as $data2) {
			?>
        <option value="<?php echo $data2['school_id']; ?>"<?php if ($school_id == $data2['school_id'])
				  echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
        <?php
		}
	}
    ?>
    </select>
   <span class="search-label">Admin</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_admin`.`name` like '%".addslashes($pagination->query)."%' )";
}
if($school_id != 0){
$pagination->array_get[] = 'school_id';
$sql_filter .= " and (`edu_admin`.`school_id` = '$school_id' )";
}

$nt = '';

$sql = "SELECT `edu_admin`.* $nt,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_admin`.`school_id` limit 0,1) as `school_name`
from `edu_admin`
where 1 $sql_filter
order by `edu_admin`.`school_id` desc, `edu_admin`.`name` asc
";
$sql_test = "SELECT `edu_admin`.*
from `edu_admin`
where 1 $sql_filter
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
$pagination->str_result = "";
foreach($pagination->result as $i=>$obj)
{
$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
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

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-admin_id" id="control-admin_id" class="checkbox-selector" data-target=".admin_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
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
    <tr<?php echo (@$data['active'])?" class=\"data-active\"":" class=\"data-inactive\"";?>>
      <td><input type="checkbox" name="admin_id[]" id="admin_id" value="<?php echo $data['admin_id'];?>" class="admin_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&admin_id=<?php echo $data['admin_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo ($data['school_name']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php if($data['gender']=='M') echo 'L'; if($data['gender']=='W') echo 'P';?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['email'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php if($data['admin_level']=='2') echo 'Administrator'; if($data['admin_level']=='1') echo 'Super Administrator';?></a></td>
      <td><?php echo ($data['blocked'])?'Ya':'Tidak';?></td>
      <td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="com-button" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="com-button" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
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
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>