<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$admin_id = $admin_login->admin_id;

$cfg->module_title = "Guru";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$teacher_id = kh_filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
	$teacher_id2 = kh_filter_input(INPUT_POST, 'teacher_id2', FILTER_SANITIZE_NUMBER_UINT);
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
			$sql = "update `edu_teacher` set `active` = '1' where `teacher_id` = '$teacher_id'  ";
			$database->executeUpdate($sql);
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
			$sql = "update `edu_teacher` set `active` = '0' where `teacher_id` = '$teacher_id'  ";
			$database->executeUpdate($sql);
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
			$sql = "DELETE FROM `edu_member_school` where `member_id` = '$teacher_id' and `role` = 'T'  ";
			$database->executeDelete($sql);
			$sql = "update `edu_teacher` set `school_id` = '0' where `teacher_id` = '$teacher_id'  ";
			$database->executeUpdate($sql);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "SELECT `school_id` from `edu_teacher` where `teacher_id` = '$teacher_id2'  ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$initial = $data['school_id'];

		$sql = "update `edu_teacher` set 
			`school_id` = '$school_id', `reg_number` = '$reg_number', `reg_number_national` = '$reg_number_national', `name` = '$name', 
			`gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', 
			`time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
			where `teacher_id` = '$teacher_id2'  ";
		$database->executeUpdate($sql);

		if ($email != '') {
			$sql = "update `edu_teacher` set 
			`email` = '$email'
			where `teacher_id` = '$teacher_id2'  ";
			$database->executeUpdate($sql);
		}
		if ($password != '') {
			$sql = "update `edu_teacher` set 
				`password` = md5(md5('$password')), `password_initial` = '$password'
				where `teacher_id` = '$teacher_id2'  ";
			$database->executeUpdate($sql);
		}
		if ($initial != $school_id) {
			$sql2 = "INSERT INTO `edu_member_school` 
				(`member_id`, `school_id`, `role`, `time_create`, `active`) values
				('$admin_id', '$school_id', 'T', '$time_create', '1')
				";
			$res2 = $database->executeUpdate($sql2);
		}
	}
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&teacher_id=$teacher_id");
}
if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_teacher`.* 
from `edu_teacher` 
where 1
and `edu_teacher`.`teacher_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" id="formedu_teacher" action="" method="post" enctype="multipart/form-data">
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
            <option value="<?php echo $data2['school_id']; ?>"<?php echo ($data2['school_id'] == $data['school_id']) ? ' selected="selected"' : ''; ?>><?php echo $data2['name']; ?></option>
            <?php
			}
		}
		?>
		</select></td>
		</tr>
		<tr>
		<td>No.Induk</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number" id="reg_number" value="<?php echo ($data['reg_number']);?>" autocomplete="off" /><input type="hidden" name="teacher_id2" id="teacher_id2" value="<?php echo $data['teacher_id'];?>" /></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo ($data['reg_number_national']);?>" autocomplete="off" /></td>
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
else if(@$_GET['option']=='print-password')
{
include_once dirname(__FILE__)."/cetak-login-guru.php";
}
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_teacher`.* $nt,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_teacher`.`school_id` limit 0,1) as `school_name`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_teacher`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_teacher`.`admin_edit`) as `admin_edit`
from `edu_teacher` 
where 1
and `edu_teacher`.`teacher_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><?php echo ($data['school_name']);?></td>
		</tr>
		<tr>
		<td>No.Induk</td>
		<td><?php echo ($data['reg_number']);?></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><?php echo ($data['reg_number_national']);?></td>
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
		<td>Telepon</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Password 
		</td><td><?php echo $data['password_initial'];?></td>
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
		<td>Admin Buat
		</td>
        <td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah
		</td>
        <td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>IP Buat
		</td><td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah
		</td>
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
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&teacher_id=<?php echo $data['teacher_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
    <span class="search-label">Nama Guru</span>
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
$sql_filter .= " and (`edu_teacher`.`name` like '%".addslashes($pagination->query)."%' )";
}
if($school_id != 0){
$pagination->array_get[] = 'school_id';
$sql_filter .= " and (`edu_teacher`.`school_id` = '$school_id' )";
}


$nt = '';

$sql = "SELECT `edu_teacher`.* $nt,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_teacher`.`school_id` limit 0,1) as `school_name`
from `edu_teacher`
where 1 $sql_filter
order by `edu_teacher`.`school_id` desc, `edu_teacher`.`name` asc
";
$sql_test = "SELECT `edu_teacher`.*
from `edu_teacher`
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
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6){
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
      <td width="16"><input type="checkbox" name="control-teacher_id" id="control-teacher_id" class="checkbox-selector" data-target=".teacher_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>No.Induk</td>
      <td>NUPTK</td>
      <td>Nama</td>
      <td>L/P</td>
      <td>Telepon</td>
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
      <td><input type="checkbox" name="teacher_id[]" id="teacher_id" value="<?php echo $data['teacher_id'];?>" class="teacher_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&teacher_id=<?php echo $data['teacher_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo ($data['school_name']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo ($data['reg_number']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo ($data['reg_number_national']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php if($data['gender']=='M') echo 'L'; if($data['gender']=='W') echo 'P';?></td>
      <td><?php echo $data['phone'];?></td>
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
  <input type="button" name="print" id="print" value="Cetak Password" class="com-button" onclick="window.open('<?php echo basename($_SERVER['PHP_SELF']);?>?option=print-password&school_id=<?php echo $school_id;?>')" />
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