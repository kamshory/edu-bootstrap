<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}

$cfg->page_title = "Edu School Program";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST))
{
	$school_program_id = kh_filter_input(INPUT_POST, "school_program_id", FILTER_SANITIZE_STRING_NEW);
	$school_program_id2 = kh_filter_input(INPUT_POST, "school_program_id2", FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['school_program_id']))
	{
		$school_program_id = $school_program_id2;
	}
	$school_id = kh_filter_input(INPUT_POST, "school_id", FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$sort_order = kh_filter_input(INPUT_POST, "sort_order", FILTER_SANITIZE_NUMBER_INT);
	$default = kh_filter_input(INPUT_POST, "default", FILTER_SANITIZE_NUMBER_UINT);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
}

if(isset($_POST['set_active']) && isset($_POST['school_program_id']))
{
	$school_program = @$_POST['school_program_id'];
	if(isset($school_program) && is_array($school_program))
	{
		foreach($school_program as $key=>$val)
		{
			$school_program_id = addslashes($val);
			$sql = "UPDATE `edu_school_program` SET `active` = true WHERE `school_program_id` = '$school_program_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
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
			$sql = "UPDATE `edu_school_program` SET `active` = false WHERE `school_program_id` = '$school_program_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
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
			$sql = "DELETE FROM `edu_school_program` WHERE `school_program_id` = '$school_program_id' AND `school_id` = '$school_id' ";
			$database->executeDelete($sql, true);
		}
	}
}


if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$school_program_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_school_program` 
	(`school_program_id`, `school_id`, `name`, `sort_order`, `default`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
	('$school_program_id', '$school_id', '$name', '$sort_order', '$default', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->executeInsert($sql, true);
	if(empty($school_program_id))
	{
		$school_program_id = kh_filter_input(INPUT_POST, "school_program_id", FILTER_SANITIZE_STRING_NEW);
	}
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&school_program_id=$school_program_id");
}
if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_school_program` SET 
	`name` = '$name', `sort_order` = '$sort_order', `default` = '$default', `time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	WHERE `school_program_id` = '$school_program_id2'";
	$database->executeUpdate($sql, true);
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&school_program_id=$school_program_id2");
}
if(@$_GET['option'] == 'add')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<form name="formedu_school_program" id="formedu_school_program" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="sort_order" id="sort_order" autocomplete="off" /></td>
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
		<td><input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php 
getDefaultValues($database, 'edu_school_program', array('school_id','name','sort_order','default','time_create','time_edit','admin_create','admin_edit','ip_create','ip_edit','active')); ?>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'edit')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_school_program`.* 
FROM `edu_school_program` 
WHERE `edu_school_program`.`school_program_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school_program" id="formedu_school_program" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Nama</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" />
		  <input type="hidden" name="school_program_id2" id="school_program_id2" value="<?php echo $data['school_program_id'];?>" /></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="sort_order" id="sort_order" value="<?php echo $data['sort_order'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Default</td>
		<td><label><input type="checkbox" class="input-checkbox" name="default" value="1" id="default"<?php echo $picoEdu->ifMatch($data['default'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Default</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
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
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school_program`.* ,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_school_program`.`school_id`) AS `school_id`
FROM `edu_school_program` 
WHERE `edu_school_program`.`school_program_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school_program" action="" method="post" enctype="multipart/form-data">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Sekolah</td>
		<td><?php echo $data['school_id'];?> </td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Order</td>
		<td><?php echo $data['sort_order'];?> </td>
		</tr>
		<tr>
		<td>Default</td>
		<td><?php echo ($data['default'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo $data['time_create'];?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit'];?> </td>
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
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_program_id=<?php echo $data['school_program_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  Jurusan
  <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->query){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_school_program`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `edu_school_program`.*,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_school_program`.`school_id`) AS `school_id`
FROM `edu_school_program`
WHERE (1=1) $sql_filter
ORDER BY `edu_school_program`.`school_program_id` ASC
";
$sql_test = "SELECT `edu_school_program`.*
FROM `edu_school_program`
WHERE (1=1) $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination(basename($_SERVER['PHP_SELF']), true); 
$paginationHTML = $pagination->buildHTML();
?>
<form name="form1" method="post" action="">

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-school_program_id" id="control-school_program_id" class="checkbox-selector" data-target=".school_program_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>Nama</td>
      <td>Order</td>
      <td>Default</td>
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
      <td><input type="checkbox" name="school_program_id[]" id="school_program_id" value="<?php echo $data['school_program_id'];?>" class="school_program_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_program_id=<?php echo $data['school_program_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_program_id=<?php echo $data['school_program_id'];?>"><?php echo $data['school_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_program_id=<?php echo $data['school_program_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_program_id=<?php echo $data['school_program_id'];?>"><?php echo $data['sort_order'];?></a></td>
      <td><?php echo ($data['default'])?'Ya':'Tidak';?> </td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>