<?php
include_once dirname(dirname(dirname(__FILE__)))."/planetbiru/lib.inc/auth.php";

$cfg->module_title = "Edu School";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST))
{
	$school_id = kh_filter_input(INPUT_POST, 'school_id', FILTER_SANITIZE_STRING_NEW);
	$school_id2 = kh_filter_input(INPUT_POST, 'school_id2', FILTER_SANITIZE_NUMBER_INT);
	if(!isset($_POST['school_id']))
	{
		$school_id = $school_id2;
	}
	$school_code = kh_filter_input(INPUT_POST, 'school_code', FILTER_SANITIZE_SPECIAL_CHARS);
	$token_school = kh_filter_input(INPUT_POST, 'token_school', FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$description = kh_filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
	$school_type_id = kh_filter_input(INPUT_POST, 'school_type_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$school_grade_id = kh_filter_input(INPUT_POST, 'school_grade_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$public_private = kh_filter_input(INPUT_POST, 'public_private', FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, 'open', FILTER_SANITIZE_NUMBER_INT);
	$principal = kh_filter_input(INPUT_POST, 'principal', FILTER_SANITIZE_SPECIAL_CHARS);
	$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
	$language = kh_filter_input(INPUT_POST, 'language', FILTER_SANITIZE_SPECIAL_CHARS);
	$country_id = kh_filter_input(INPUT_POST, 'country_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$state_id = kh_filter_input(INPUT_POST, 'state_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$city_id = kh_filter_input(INPUT_POST, 'city_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$student = kh_filter_input(INPUT_POST, 'student', FILTER_SANITIZE_NUMBER_INT);
	$prevent_change_school = kh_filter_input(INPUT_POST, 'prevent_change_school', FILTER_SANITIZE_NUMBER_INT);
	$prevent_resign = kh_filter_input(INPUT_POST, 'prevent_resign', FILTER_SANITIZE_NUMBER_INT);
	$use_token = kh_filter_input(INPUT_POST, 'use_token', FILTER_SANITIZE_NUMBER_INT);
	$time_import_first = kh_filter_input(INPUT_POST, 'time_import_first', FILTER_SANITIZE_STRING_NEW);
	$time_import_last = kh_filter_input(INPUT_POST, 'time_import_last', FILTER_SANITIZE_STRING_NEW);
	$admin_import_first = kh_filter_input(INPUT_POST, 'admin_import_first', FILTER_SANITIZE_NUMBER_INT);
	$admin_import_last = kh_filter_input(INPUT_POST, 'admin_import_last', FILTER_SANITIZE_NUMBER_INT);
	$ip_import_first = kh_filter_input(INPUT_POST, 'ip_import_first', FILTER_SANITIZE_SPECIAL_CHARS);
	$ip_import_last = kh_filter_input(INPUT_POST, 'ip_import_last', FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = kh_filter_input(INPUT_POST, 'time_create', FILTER_SANITIZE_STRING_NEW);
	$time_edit = kh_filter_input(INPUT_POST, 'time_edit', FILTER_SANITIZE_STRING_NEW);
	$admin_create = kh_filter_input(INPUT_POST, 'admin_create', FILTER_SANITIZE_NUMBER_INT);
	$admin_edit = kh_filter_input(INPUT_POST, 'admin_edit', FILTER_SANITIZE_NUMBER_INT);
	$ip_create = kh_filter_input(INPUT_POST, 'ip_create', FILTER_SANITIZE_SPECIAL_CHARS);
	$ip_edit = kh_filter_input(INPUT_POST, 'ip_edit', FILTER_SANITIZE_SPECIAL_CHARS);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);
}

if(isset($_POST['set_active']) && isset($_POST['school_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['school_id'], DB_PREFIX."edu_school", 'school_id', 1);
}
if(isset($_POST['set_inactive']) && isset($_POST['school_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['school_id'], DB_PREFIX."edu_school", 'school_id', 0);
}
if(isset($_POST['delete']) && isset($_POST['school_id']))
{
	deleterecord($_POST['school_id'], DB_PREFIX."edu_school", 'school_id');
}


if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$sql = "INSERT INTO `".DB_PREFIX."edu_school` 
	(`school_id`, `school_code`, `token_school`, `name`, `description`, `school_type_id`, `school_grade_id`, `public_private`, `open`, `principal`, `address`, `phone`, `email`, `language`, `country_id`, `state_id`, `city_id`, `student`, `prevent_change_school`, `prevent_resign`, `use_token`, `time_import_first`, `time_import_last`, `admin_import_first`, `admin_import_last`, `ip_import_first`, `ip_import_last`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) values
	('$school_id', '$school_code', '$token_school', '$name', '$description', '$school_type_id', '$school_grade_id', '$public_private', '$open', '$principal', '$address', '$phone', '$email', '$language', '$country_id', '$state_id', '$city_id', '$student', '$prevent_change_school', '$prevent_resign', '$use_token', '$time_import_first', '$time_import_last', '$admin_import_first', '$admin_import_last', '$ip_import_first', '$ip_import_last', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->execute($sql);
	$sql = "select last_insert_id()";
	$res = mysql_query($sql);
	$dt = mysql_fetch_row($res);
	$id = $dt[0];
	if($id == 0)
	{
		$id = kh_filter_input(INPUT_POST, "school_id", FILTER_SANITIZE_NUMBER_INT);
	}
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&school_id=$id");
}
if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `".DB_PREFIX."edu_school` set 
	`school_id` = '$school_id', `school_code` = '$school_code', `token_school` = '$token_school', `name` = '$name', `description` = '$description', `school_type_id` = '$school_type_id', `school_grade_id` = '$school_grade_id', `public_private` = '$public_private', `open` = '$open', `principal` = '$principal', `address` = '$address', `phone` = '$phone', `email` = '$email', `language` = '$language', `country_id` = '$country_id', `state_id` = '$state_id', `city_id` = '$city_id', `student` = '$student', `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign', `use_token` = '$use_token', `time_import_first` = '$time_import_first', `time_import_last` = '$time_import_last', `admin_import_first` = '$admin_import_first', `admin_import_last` = '$admin_import_last', `ip_import_first` = '$ip_import_first', `ip_import_last` = '$ip_import_last', `time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	where `school_id` = '$school_id2'";
	$database->execute($sql);
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&school_id=$school_id");
}
if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">School</td>
		<td><input type="number" class="input-text input-text-medium" name="school_id" id="school_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>School Code</td>
		<td><input type="text" class="input-text input-text-long" name="school_code" id="school_code" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Token School</td>
		<td><input type="text" class="input-text input-text-long" name="token_school" id="token_school" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Name</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Description</td>
		<td><input type="text" class="input-text input-text-long" name="description" id="description" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>School Type</td>
		<td><input type="text" class="input-text input-text-long" name="school_type_id" id="school_type_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>School Grade</td>
		<td><select class="input-select" name="school_grade_id" id="school_grade_id">
		<option value=""></option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		</select></td>
		</tr>
		<tr>
		<td>Public Private</td>
		<td><select class="input-select" name="public_private" id="public_private">
		<option value=""></option>
		<option value="U">U</option>
		<option value="I">I</option>
		</select></td>
		</tr>
		<tr>
		<td>Open</td>
		<td><input type="number" class="input-text input-text-medium" name="open" id="open" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Principal</td>
		<td><input type="text" class="input-text input-text-long" name="principal" id="principal" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Address</td>
		<td><input type="text" class="input-text input-text-long" name="address" id="address" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="text" class="input-text input-text-long" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="text" class="input-text input-text-long" name="email" id="email" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Language</td>
		<td><input type="text" class="input-text input-text-long" name="language" id="language" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Country</td>
		<td><input type="text" class="input-text input-text-long" name="country_id" id="country_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>State</td>
		<td><input type="text" class="input-text input-text-long" name="state_id" id="state_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>City</td>
		<td><input type="text" class="input-text input-text-long" name="city_id" id="city_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Student</td>
		<td><input type="number" class="input-text input-text-medium" name="student" id="student" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Prevent Change School</td>
		<td><input type="number" class="input-text input-text-medium" name="prevent_change_school" id="prevent_change_school" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Prevent Resign</td>
		<td><input type="number" class="input-text input-text-medium" name="prevent_resign" id="prevent_resign" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Use Token</td>
		<td><input type="number" class="input-text input-text-medium" name="use_token" id="use_token" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Import First</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_import_first" id="time_import_first" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Import Last</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_import_last" id="time_import_last" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Import First</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_import_first" id="admin_import_first" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin Import Last</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_import_last" id="admin_import_last" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Import First</td>
		<td><input type="text" class="input-text input-text-long" name="ip_import_first" id="ip_import_first" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Import Last</td>
		<td><input type="text" class="input-text input-text-long" name="ip_import_last" id="ip_import_last" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_create" id="time_create" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_edit" id="time_edit" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_create" id="admin_create" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin Edit</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_edit" id="admin_edit" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Create</td>
		<td><input type="text" class="input-text input-text-long" name="ip_create" id="ip_create" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Edit</td>
		<td><input type="text" class="input-text input-text-long" name="ip_edit" id="ip_edit" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><input type="number" class="input-text input-text-medium" name="active" id="active" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues(DB_PREFIX.'edu_school', array('school_id','school_code','token_school','name','description','school_type_id','school_grade_id','public_private','open','principal','address','phone','email','language','country_id','state_id','city_id','student','prevent_change_school','prevent_resign','use_token','time_import_first','time_import_last','admin_import_first','admin_import_last','ip_import_first','ip_import_last','time_create','time_edit','admin_create','admin_edit','ip_create','ip_edit','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `".DB_PREFIX."edu_school`.* 
from `".DB_PREFIX."edu_school` 
where 1
and `".DB_PREFIX."edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">School</td>
		<td><input type="number" class="input-text input-text-medium" name="school_id" id="school_id" value="<?php echo ($data['school_id']);?>" autocomplete="off" /><input type="hidden" name="school_id2" id="school_id2" value="<?php echo ($data['school_id']);?>" /></td>
		</tr>
		<tr>
		<td>School Code</td>
		<td><input type="text" class="input-text input-text-long" name="school_code" id="school_code" value="<?php echo $data['school_code'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Token School</td>
		<td><input type="text" class="input-text input-text-long" name="token_school" id="token_school" value="<?php echo ($data['token_school']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Name</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Description</td>
		<td><input type="text" class="input-text input-text-long" name="description" id="description" value="<?php echo $data['description'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>School Type</td>
		<td><input type="text" class="input-text input-text-long" name="school_type_id" id="school_type_id" value="<?php echo ($data['school_type_id']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>School Grade</td>
		<td><select class="input-select" name="school_grade_id" id="school_grade_id">
		<option value=""></option>
		<option value="1"<?php if($data['school_grade_id'] == '1') echo " selected=\"selected\"";?>>1</option>
		<option value="2"<?php if($data['school_grade_id'] == '2') echo " selected=\"selected\"";?>>2</option>
		<option value="3"<?php if($data['school_grade_id'] == '3') echo " selected=\"selected\"";?>>3</option>
		<option value="4"<?php if($data['school_grade_id'] == '4') echo " selected=\"selected\"";?>>4</option>
		<option value="5"<?php if($data['school_grade_id'] == '5') echo " selected=\"selected\"";?>>5</option>
		<option value="6"<?php if($data['school_grade_id'] == '6') echo " selected=\"selected\"";?>>6</option>
		</select></td>
		</tr>
		<tr>
		<td>Public Private</td>
		<td><select class="input-select" name="public_private" id="public_private">
		<option value=""></option>
		<option value="U"<?php if($data['public_private'] == 'U') echo " selected=\"selected\"";?>>U</option>
		<option value="I"<?php if($data['public_private'] == 'I') echo " selected=\"selected\"";?>>I</option>
		</select></td>
		</tr>
		<tr>
		<td>Open</td>
		<td><input type="number" class="input-text input-text-medium" name="open" id="open" value="<?php echo ($data['open']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Principal</td>
		<td><input type="text" class="input-text input-text-long" name="principal" id="principal" value="<?php echo $data['principal'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Address</td>
		<td><input type="text" class="input-text input-text-long" name="address" id="address" value="<?php echo $data['address'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><input type="text" class="input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="text" class="input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Language</td>
		<td><input type="text" class="input-text input-text-long" name="language" id="language" value="<?php echo ($data['language']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Country</td>
		<td><input type="text" class="input-text input-text-long" name="country_id" id="country_id" value="<?php echo $data['country_id'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>State</td>
		<td><input type="text" class="input-text input-text-long" name="state_id" id="state_id" value="<?php echo $data['state_id'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>City</td>
		<td><input type="text" class="input-text input-text-long" name="city_id" id="city_id" value="<?php echo $data['city_id'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Student</td>
		<td><input type="number" class="input-text input-text-medium" name="student" id="student" value="<?php echo ($data['student']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Prevent Change School</td>
		<td><input type="number" class="input-text input-text-medium" name="prevent_change_school" id="prevent_change_school" value="<?php echo ($data['prevent_change_school']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Prevent Resign</td>
		<td><input type="number" class="input-text input-text-medium" name="prevent_resign" id="prevent_resign" value="<?php echo ($data['prevent_resign']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Use Token</td>
		<td><input type="number" class="input-text input-text-medium" name="use_token" id="use_token" value="<?php echo ($data['use_token']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Import First</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_import_first" id="time_import_first" value="<?php echo ($data['time_import_first']);?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Import Last</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_import_last" id="time_import_last" value="<?php echo ($data['time_import_last']);?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Import First</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_import_first" id="admin_import_first" value="<?php echo $data['admin_import_first'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin Import Last</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_import_last" id="admin_import_last" value="<?php echo $data['admin_import_last'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Import First</td>
		<td><input type="text" class="input-text input-text-long" name="ip_import_first" id="ip_import_first" value="<?php echo $data['ip_import_first'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Import Last</td>
		<td><input type="text" class="input-text input-text-long" name="ip_import_last" id="ip_import_last" value="<?php echo $data['ip_import_last'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_create" id="time_create" value="<?php echo $data['time_create'];?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_edit" id="time_edit" value="<?php echo $data['time_edit'];?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_create" id="admin_create" value="<?php echo $data['admin_create'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin Edit</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_edit" id="admin_edit" value="<?php echo $data['admin_edit'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Create</td>
		<td><input type="text" class="input-text input-text-long" name="ip_create" id="ip_create" value="<?php echo $data['ip_create'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Ip Edit</td>
		<td><input type="text" class="input-text input-text-long" name="ip_edit" id="ip_edit" value="<?php echo $data['ip_edit'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><input type="number" class="input-text input-text-medium" name="active" id="active" value="<?php echo ($data['active']);?>" autocomplete="off" /></td>
		</tr>
		<tr><td>&nbsp;</td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$edit_key = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `".DB_PREFIX."edu_school`.* $nt
from `".DB_PREFIX."edu_school` 
where 1
and `".DB_PREFIX."edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">School</td>
		<td><?php echo ($data['school_id']);?></td>
		</tr>
		<tr>
		<td>School Code</td>
		<td><?php echo $data['school_code'];?></td>
		</tr>
		<tr>
		<td>Token School</td>
		<td><?php echo ($data['token_school']);?></td>
		</tr>
		<tr>
		<td>Name</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Description</td>
		<td><?php echo $data['description'];?></td>
		</tr>
		<tr>
		<td>School Type</td>
		<td><?php echo ($data['school_type_id']);?></td>
		</tr>
		<tr>
		<td>School Grade</td>
		<td><?php echo ($data['school_grade_id']);?></td>
		</tr>
		<tr>
		<td>Public Private</td>
		<td><?php echo ($data['public_private']);?></td>
		</tr>
		<tr>
		<td>Open</td>
		<td><?php echo ($data['open']);?></td>
		</tr>
		<tr>
		<td>Principal</td>
		<td><?php echo $data['principal'];?></td>
		</tr>
		<tr>
		<td>Address</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Language</td>
		<td><?php echo ($data['language']);?></td>
		</tr>
		<tr>
		<td>Country</td>
		<td><?php echo $data['country_id'];?></td>
		</tr>
		<tr>
		<td>State</td>
		<td><?php echo $data['state_id'];?></td>
		</tr>
		<tr>
		<td>City</td>
		<td><?php echo $data['city_id'];?></td>
		</tr>
		<tr>
		<td>Student</td>
		<td><?php echo ($data['student']);?></td>
		</tr>
		<tr>
		<td>Prevent Change School</td>
		<td><?php echo ($data['prevent_change_school']);?></td>
		</tr>
		<tr>
		<td>Prevent Resign</td>
		<td><?php echo ($data['prevent_resign']);?></td>
		</tr>
		<tr>
		<td>Use Token</td>
		<td><?php echo ($data['use_token']);?></td>
		</tr>
		<tr>
		<td>Time Import First</td>
		<td><?php echo ($data['time_import_first']);?></td>
		</tr>
		<tr>
		<td>Time Import Last</td>
		<td><?php echo ($data['time_import_last']);?></td>
		</tr>
		<tr>
		<td>Admin Import First</td>
		<td><?php echo $data['admin_import_first'];?></td>
		</tr>
		<tr>
		<td>Admin Import Last</td>
		<td><?php echo $data['admin_import_last'];?></td>
		</tr>
		<tr>
		<td>Ip Import First</td>
		<td><?php echo $data['ip_import_first'];?></td>
		</tr>
		<tr>
		<td>Ip Import Last</td>
		<td><?php echo $data['ip_import_last'];?></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><?php echo $data['time_create'];?></td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><?php echo $data['time_edit'];?></td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Edit</td>
		<td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>Ip Create</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>Ip Edit</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><?php echo ($data['active']);?></td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_id=<?php echo $data['school_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
  Edu School
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
$sql_filter .= " and (`".DB_PREFIX."edu_school`.`nama` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `".DB_PREFIX."edu_school`.* $nt
from `".DB_PREFIX."edu_school`
where 1 $sql_filter
order by `".DB_PREFIX."edu_school`.`school_id` asc
";
$sql_test = "SELECT `".DB_PREFIX."edu_school`.*
from `".DB_PREFIX."edu_school`
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

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label">Baris <?php echo $pagination->start;?> hingga <?php echo $pagination->end;?> dari <?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-school_id" id="control-school_id" class="checkbox-selector" data-target=".school_id" value="1"></td>
      <td width="16"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>School</td>
      <td>School Code</td>
      <td>Token School</td>
      <td>Name</td>
      <td>Description</td>
      <td>School Type</td>
      <td>School Grade</td>
      <td>Public Private</td>
      <td>Open</td>
      <td>Principal</td>
      <td>Address</td>
      <td>Phone</td>
      <td>Email</td>
      <td>Language</td>
      <td>Country</td>
      <td>State</td>
      <td>City</td>
      <td>Student</td>
      <td>Prevent Change School</td>
      <td>Prevent Resign</td>
      <td>Use Token</td>
      <td>Time Import First</td>
      <td>Time Import Last</td>
      <td>Admin Import First</td>
      <td>Admin Import Last</td>
      <td>Ip Import First</td>
      <td>Ip Import Last</td>
      <td>Time Create</td>
      <td>Time Edit</td>
      <td>Admin Create</td>
      <td>Admin Edit</td>
      <td>Ip Create</td>
      <td>Ip Edit</td>
      <td>Active</td>
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
      <td><input type="checkbox" name="school_id[]" id="school_id" value="<?php echo $data['school_id'];?>" class="school_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_id=<?php echo $data['school_id'];?>"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['school_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['school_code'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['token_school']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['description'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['school_type_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['school_grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['public_private']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['open']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['principal'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['address'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['phone'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['email'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['language']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['country_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['state_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['city_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['student']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['prevent_change_school']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['prevent_resign']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['use_token']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['time_import_first']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['time_import_last']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['admin_import_first'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['admin_import_last'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['ip_import_first'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['ip_import_last'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['time_create'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['time_edit'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['admin_create'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['admin_edit'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['ip_create'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['ip_edit'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo ($data['active']);?></a></td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label">Baris <?php echo $pagination->start;?> hingga <?php echo $pagination->end;?> dari <?php echo $pagination->total_record;?></div>
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>