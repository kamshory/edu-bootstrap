<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$cfg->page_title = "Jurusan";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
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
			$sql = "UPDATE `edu_school_program` SET `active` = true WHERE `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
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
			$sql = "UPDATE `edu_school_program` SET `active` = false WHERE `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
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
			$sql = "DELETE FROM `edu_school_program` WHERE `school_program_id` = '$school_program_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_school_program` SET 
	`name` = '$name', `sort_order` = '$sort_order', `default` = '$default', `time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	WHERE `school_program_id` = '$school_program_id2'";
	$database->execute($sql);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&school_program_id=$school_program_id");
}
if(@$_GET['option'] == 'edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
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
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
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
		</table>
<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
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
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school_program`.* ,
(select `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_school_program`.`school_id`) as `school_id`
FROM `edu_school_program` 
WHERE `edu_school_program`.`school_program_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school_program" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
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
		</table>
<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
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
    $sql2 = "SELECT * FROM `edu_school` where 1 ORDER BY `time_create` desc";
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
  <span class="search-label">Jurusan</span>
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
$sql_filter .= " and (`edu_school_program`.`name` like '%".addslashes($pagination->query)."%' )";
}
if(!empty($school_id)){
$pagination->array_get[] = 'school_id';
$sql_filter .= " and (`edu_school_program`.`school_id` = '$school_id' )";
}


$nt = '';

$sql = "SELECT `edu_school_program`.*,
(select `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_school_program`.`school_id`) as `school_id`
FROM `edu_school_program`
WHERE 1 $sql_filter
ORDER BY `edu_school_program`.`school_id` desc, `edu_school_program`.`sort_order` asc
";
$sql_test = "SELECT `edu_school_program`.*
FROM `edu_school_program`
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
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(11), .hide-some-cell tr td:nth-child(12){
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
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn com-button btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn com-button btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn com-button btn-danger delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
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