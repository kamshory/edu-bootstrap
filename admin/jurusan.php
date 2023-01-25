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

$cfg->module_title = "Jurusan";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST))
{
	$school_program_id = kh_filter_input(INPUT_POST, 'school_program_id', FILTER_SANITIZE_STRING_NEW);
	$school_program_id2 = kh_filter_input(INPUT_POST, 'school_program_id2', FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['school_program_id']))
	{
		$school_program_id = $school_program_id2;
	}
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$order = kh_filter_input(INPUT_POST, 'order', FILTER_SANITIZE_NUMBER_INT);
	$default = kh_filter_input(INPUT_POST, 'default', FILTER_SANITIZE_NUMBER_UINT);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_UINT);
}

if(isset($_POST['set_active']) && isset($_POST['school_program_id']))
{
	$school_program = @$_POST['school_program_id'];
	if(isset($school_program) && is_array($school_program))
	{
		foreach($school_program as $key=>$val)
		{
			$school_program_id = addslashes($val);
			$sql = "update `edu_school_program` set `active` = '1' where `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['school_program_id']))
{
	$school_program = @$_POST['school_program_id'];
	if(isset($school_program) && is_array($school_program))
	{
		foreach($school_program as $key=>$val)
		{
			$school_program_id = addslashes($val);
			$sql = "update `edu_school_program` set `active` = '0' where `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['school_program_id']))
{
	$school_program = @$_POST['school_program_id'];
	if(isset($school_program) && is_array($school_program))
	{
		foreach($school_program as $key=>$val)
		{
			$school_program_id = addslashes($val);
			$sql = "DELETE FROM `edu_school_program` where `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
			$database->executeDelete($sql);
		}
	}
}


if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$school_program_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_school_program` 
	(`school_program_id`, `school_id`, `name`, `order`, `default`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) values
	('$school_program_id', '$school_id', '$name', '$order', '$default', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->executeInsert($sql);
	$id = $database->getDatabaseConnection()->lastInsertId();
	if($id == 0)
	{
		$id = kh_filter_input(INPUT_POST, "school_program_id", FILTER_SANITIZE_NUMBER_UINT);
	}
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&school_program_id=$id");
}
if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `edu_school_program` set 
	`name` = '$name', `order` = '$order', `default` = '$default', `time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	where `school_program_id` = '$school_program_id2'";
	$database->executeUpdate($sql);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&school_program_id=$school_program_id");
}
if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<form name="formedu_school_program" id="formedu_school_program" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><input type="number" class="input-text input-text-medium" name="order" id="order" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Default</td>
		<td><label><input type="checkbox" class="input-checkbox" name="default" value="1" id="default"> Default</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Active</label>
		</td>
		</tr>
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_school_program', array('name','order','default','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'school_program_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_school_program`.* 
from `edu_school_program` 
where 1
and `edu_school_program`.`school_program_id` = '$edit_key' and `school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school_program" id="formedu_school_program" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" />
		  <input type="hidden" name="school_program_id2" id="school_program_id2" value="<?php echo $data['school_program_id'];?>" /></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><input type="number" class="input-text input-text-medium" name="order" id="order" value="<?php echo $data['order'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Default</td>
		<td><label><input type="checkbox" class="input-checkbox" name="default" value="1" id="default"<?php if($data['default']==1) echo ' checked="checked"';?>> Default</label>
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
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'school_program_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school_program`.* ,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_school_program`.`school_id`) as `school_id`
from `edu_school_program` 
where 1
and `edu_school_program`.`school_program_id` = '$edit_key' and `school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school_program" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><?php echo ($data['school_id']);?></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><?php echo $data['order'];?></td>
		</tr>
		<tr>
		<td>Default</td>
		<td><?php echo ($data['default'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo $data['time_create'];?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit'];?></td>
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
		<td>Aktif</td>
		<td><?php echo $data['active']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_program_id=<?php echo $data['school_program_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
  <span class="search-label">Jurusan</span>
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
$sql_filter .= " and (`edu_school_program`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `edu_school_program`.*
from `edu_school_program`
where 1 and `school_id` = '$school_id' $sql_filter
order by `edu_school_program`.`school_program_id` asc
";
$sql_test = "SELECT `edu_school_program`.*
from `edu_school_program`
where 1 and `school_id` = '$school_id' $sql_filter
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
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-school_program_id" id="control-school_program_id" class="checkbox-selector" data-target=".school_program_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Nama</td>
      <td>Order</td>
      <td>Default</td>
      <td width="60">Aktif</td>
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
      <td><input type="checkbox" name="school_program_id[]" id="school_program_id" value="<?php echo $data['school_program_id'];?>" class="school_program_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_program_id=<?php echo $data['school_program_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_program_id=<?php echo $data['school_program_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_program_id=<?php echo $data['school_program_id'];?>"><?php echo $data['order'];?></a></td>
      <td><?php echo ($data['default'])?'Ya':'Tidak';?></td>
      <td><?php echo $data['active']?'Ya':'Tidak';?></td>
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>