<?php
require_once dirname(dirname(dirname(__FILE__)))."/planetbiru/lib.inc/auth.php";

$pageTitle = "Edu Test Collection";
$pagination = new \Pico\PicoPagination();
if(count(@$_POST))
{
	$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$test_collection_id2 = kh_filter_input(INPUT_POST, "test_collection_id2", FILTER_SANITIZE_NUMBER_UINT);
	if(!isset($_POST['test_collection_id']))
	{
		$test_collection_id = $test_collection_id2;
	}
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_STRING_NEW);
	$file_name = kh_filter_input(INPUT_POST, "file_name", FILTER_SANITIZE_SPECIAL_CHARS);
	$file_path = kh_filter_input(INPUT_POST, "file_path", FILTER_SANITIZE_SPECIAL_CHARS);
	$file_size = kh_filter_input(INPUT_POST, "file_size", FILTER_SANITIZE_NUMBER_INT);
	$file_md5 = kh_filter_input(INPUT_POST, "file_md5", FILTER_SANITIZE_SPECIAL_CHARS);
	$file_sha1 = kh_filter_input(INPUT_POST, "file_sha1", FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = kh_filter_input(INPUT_POST, "time_create", FILTER_SANITIZE_STRING_NEW);
	$time_edit = kh_filter_input(INPUT_POST, "time_edit", FILTER_SANITIZE_STRING_NEW);
	$ip_create = kh_filter_input(INPUT_POST, "ip_create", FILTER_SANITIZE_SPECIAL_CHARS);
	$ip_edit = kh_filter_input(INPUT_POST, "ip_edit", FILTER_SANITIZE_SPECIAL_CHARS);
	$taken = kh_filter_input(INPUT_POST, "taken", FILTER_SANITIZE_NUMBER_UINT);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
}

if(isset($_POST['set_active']) && isset($_POST['test_collection_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['test_collection_id'], "edu_test_collection", 'test_collection_id', 1);
}
if(isset($_POST['set_inactive']) && isset($_POST['test_collection_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['test_collection_id'], "edu_test_collection", 'test_collection_id', 0);
}
if(isset($_POST['delete']) && isset($_POST['test_collection_id']))
{
	$picoEdu->deleterecord($_POST['test_collection_id'], "edu_test_collection", 'test_collection_id');
}


if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$test_collection_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_test_collection` 
	(`test_collection_id`, `name`, `grade_id`, `file_name`, `file_path`, `file_size`, `file_md5`, `file_sha1`, `time_create`, `time_edit`, `ip_create`, `ip_edit`, `taken`, `active`) VALUES
	('$test_collection_id', '$name', '$grade_id', '$file_name', '$file_path', '$file_size', '$file_md5', '$file_sha1', '$time_create', '$time_edit', '$ip_create', '$ip_edit', '$taken', '$active')";
	$database->executeInsert($sql, true);

	if(empty($test_collection_id))
	{
		$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	}
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&test_collection_id=$test_collection_id");
}
if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_test_collection` SET 
	`name` = '$name', `grade_id` = '$grade_id', `file_name` = '$file_name', `file_path` = '$file_path', `file_size` = '$file_size', `file_md5` = '$file_md5', `file_sha1` = '$file_sha1', `time_create` = '$time_create', `time_edit` = '$time_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `taken` = '$taken', `active` = '$active'
	WHERE `test_collection_id` = '$test_collection_id2'";
	$database->executeUpdate($sql, true);
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&test_collection_id=$test_collection_id");
}
if(@$_GET['option'] == 'add')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<form name="formedu_test_collection" id="formedu_test_collection" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Name</td>
		<td><input type="text" class="form-control input-text" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Grade</td>
		<td><select class="form-control input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php 
		echo $picoEdu->createGradeOption(null);
		?>
		</select></td>
		</tr>
		<tr>
		<td>File Name</td>
		<td><input type="text" class="form-control input-text" name="file_name" id="file_name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File Path</td>
		<td><input type="text" class="form-control input-text" name="file_path" id="file_path" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File Size</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="file_size" id="file_size" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File MD5</td>
		<td><input type="text" class="form-control input-text" name="file_md5" id="file_md5" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File SHA1</td>
		<td><input type="text" class="form-control input-text" name="file_sha1" id="file_sha1" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="form-control input-text input-text-datetime" name="time_create" id="time_create" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span> JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><input type="text" class="form-control input-text input-text-datetime" name="time_edit" id="time_edit" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span> JJ:MM:DD</td>
		</tr>
		<tr>
		<td>IP Create</td>
		<td><input type="text" class="form-control input-text" name="ip_create" id="ip_create" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>IP Edit</td>
		<td><input type="text" class="form-control input-text" name="ip_edit" id="ip_edit" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Taken</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="taken" id="taken" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Active</label>
		</td>
		</tr>
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_test_collection', array('name','grade_id','file_name','file_path','file_size','file_md5','file_sha1','time_create','time_edit','ip_create','ip_edit','taken','active')); ?>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'edit')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test_collection`.* 
FROM `edu_test_collection` 
WHERE `edu_test_collection`.`test_collection_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_test_collection" id="formedu_test_collection" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Name</td>
		<td><input type="text" class="form-control input-text" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /><input type="hidden" name="test_collection_id2" id="test_collection_id2" value="<?php echo $data['test_collection_id'];?>" /></td>
		</tr>
		<tr>
		<td>Grade</td>
		<td><select class="form-control input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php echo $picoEdu->createGradeOption($data['grade_id']); ?>
		</select></td>
		</tr>
		<tr>
		<td>File Name</td>
		<td><input type="text" class="form-control input-text" name="file_name" id="file_name" value="<?php echo $data['file_name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File Path</td>
		<td><input type="text" class="form-control input-text" name="file_path" id="file_path" value="<?php echo $data['file_path'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File Size</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="file_size" id="file_size" value="<?php echo $data['file_size'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File MD5</td>
		<td><input type="text" class="form-control input-text" name="file_md5" id="file_md5" value="<?php echo $data['file_md5'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>File SHA1</td>
		<td><input type="text" class="form-control input-text" name="file_sha1" id="file_sha1" value="<?php echo $data['file_sha1'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="form-control input-text input-text-datetime" name="time_create" id="time_create" value="<?php echo $data['time_create'];?>" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span> JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><input type="text" class="form-control input-text input-text-datetime" name="time_edit" id="time_edit" value="<?php echo $data['time_edit'];?>" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span> JJ:MM:DD</td>
		</tr>
		<tr>
		<td>IP Create</td>
		<td><input type="text" class="form-control input-text" name="ip_create" id="ip_create" value="<?php echo $data['ip_create'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>IP Edit</td>
		<td><input type="text" class="form-control input-text" name="ip_edit" id="ip_edit" value="<?php echo $data['ip_edit'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Taken</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="taken" id="taken" value="<?php echo $data['taken'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$nt = $picoEdu->createSubSelect('edu_test_collection', array('grade_id'=>'grade_id'));
$sql = "SELECT `edu_test_collection`.* $nt
FROM `edu_test_collection` 
WHERE `edu_test_collection`.`test_collection_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_test_collection" action="" method="post" enctype="multipart/form-data">
	<table width="800" border="0" class="table two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Name</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Grade</td>
		<td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
		<tr>
		<td>File Name</td>
		<td><?php echo $data['file_name'];?> </td>
		</tr>
		<tr>
		<td>File Path</td>
		<td><?php echo $data['file_path'];?> </td>
		</tr>
		<tr>
		<td>File Size</td>
		<td><?php echo $data['file_size'];?> </td>
		</tr>
		<tr>
		<td>File MD5</td>
		<td><?php echo $data['file_md5'];?> </td>
		</tr>
		<tr>
		<td>File SHA1</td>
		<td><?php echo $data['file_sha1'];?> </td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><?php echo $data['time_create'];?> </td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><?php echo $data['time_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Create</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Edit</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Taken</td>
		<td><?php echo $data['taken'];?> </td>
		</tr>
		<tr>
		<td>Active</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&test_collection_id=<?php echo $data['test_collection_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
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
  Edu Test Collection
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_test_collection`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}


$nt = '';

$nt = $picoEdu->createSubSelect('edu_test_collection', array('grade_id'=>'grade_id'));

$sql = "SELECT `edu_test_collection`.* $nt
FROM `edu_test_collection`
WHERE (1=1) $sql_filter
ORDER BY `edu_test_collection`.`test_collection_id` ASC
";
$sql_test = "SELECT `edu_test_collection`.*
FROM `edu_test_collection`
WHERE (1=1) $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
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
      <td width="16"><input type="checkbox" name="control-test_collection_id" id="control-test_collection_id" class="checkbox-selector" data-target=".test_collection_id" value="1"></td>
      <td width="16"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Name</td>
      <td>Grade</td>
      <td>File Name</td>
      <td>File Path</td>
      <td>File Size</td>
      <td>File MD5</td>
      <td>File SHA1</td>
      <td>Time Create</td>
      <td>Time Edit</td>
      <td>IP Create</td>
      <td>IP Edit</td>
      <td>Taken</td>
      <td>Active</td>
</tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td><input type="checkbox" name="test_collection_id[]" id="test_collection_id" value="<?php echo $data['test_collection_id'];?>" class="test_collection_id" /></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&test_collection_id=<?php echo $data['test_collection_id'];?>"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_path'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_size'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_md5'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_sha1'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['time_create'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['time_edit'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['ip_create'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['ip_edit'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['taken'];?></a></td>
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
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=add'" />
  </div>
</form>
<?php
}
else if(@$_GET['q'] != '')
{
?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>