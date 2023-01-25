<?php
include_once dirname(dirname(dirname(__FILE__)))."/planetbiru/lib.inc/auth.php";

$cfg->module_title = "Edu Info";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST))
{
	$info_id = kh_filter_input(INPUT_POST, 'info_id', FILTER_SANITIZE_STRING_NEW);
	$info_id2 = kh_filter_input(INPUT_POST, 'info_id2', FILTER_SANITIZE_NUMBER_INT);
	if(!isset($_POST['info_id']))
	{
		$info_id = $info_id2;
	}
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$content = kh_filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = kh_filter_input(INPUT_POST, 'time_create', FILTER_SANITIZE_STRING_NEW);
	$time_edit = kh_filter_input(INPUT_POST, 'time_edit', FILTER_SANITIZE_STRING_NEW);
	$admin_create = kh_filter_input(INPUT_POST, 'admin_create', FILTER_SANITIZE_NUMBER_INT);
	$admin_edit = kh_filter_input(INPUT_POST, 'admin_edit', FILTER_SANITIZE_NUMBER_INT);
	$ip_create = kh_filter_input(INPUT_POST, 'ip_create', FILTER_SANITIZE_SPECIAL_CHARS);
	$ip_edit = kh_filter_input(INPUT_POST, 'ip_edit', FILTER_SANITIZE_SPECIAL_CHARS);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);
}

if(isset($_POST['set_active']) && isset($_POST['info_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['info_id'], DB_PREFIX."edu_info", 'info_id', 1);
}
if(isset($_POST['set_inactive']) && isset($_POST['info_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['info_id'], DB_PREFIX."edu_info", 'info_id', 0);
}
if(isset($_POST['delete']) && isset($_POST['info_id']))
{
	deleterecord($_POST['info_id'], DB_PREFIX."edu_info", 'info_id');
}


if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$sql = "INSERT INTO `".DB_PREFIX."edu_info` 
	(`info_id`, `name`, `content`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) values
	('$info_id', '$name', '$content', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->execute($sql);
	$sql = "select last_insert_id()";
	$res = mysql_query($sql);
	$dt = mysql_fetch_row($res);
	$id = $dt[0];
	if($id == 0)
	{
		$id = kh_filter_input(INPUT_POST, "info_id", FILTER_SANITIZE_NUMBER_INT);
	}
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&info_id=$id");
}
if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `".DB_PREFIX."edu_info` set 
	`info_id` = '$info_id', `name` = '$name', `content` = '$content', `time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	where `info_id` = '$info_id2'";
	$database->execute($sql);
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&info_id=$info_id");
}
if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<form name="formedu_info" id="formedu_info" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Info</td>
		<td><input type="number" class="input-text input-text-medium" name="info_id" id="info_id" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Judul Informasi</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Content</td>
		<td><input type="text" class="input-text input-text-long" name="content" id="content" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_create" id="time_create" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_edit" id="time_edit" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_create" id="admin_create" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin</td>
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
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Active</label>
		</td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues(DB_PREFIX.'edu_info', array('info_id','name','content','time_create','time_edit','admin_create','admin_edit','ip_create','ip_edit','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'info_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `".DB_PREFIX."edu_info`.* 
from `".DB_PREFIX."edu_info` 
where 1
and `".DB_PREFIX."edu_info`.`info_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_info" id="formedu_info" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Info</td>
		<td><input type="number" class="input-text input-text-medium" name="info_id" id="info_id" value="<?php echo ($data['info_id']);?>" autocomplete="off" /><input type="hidden" name="info_id2" id="info_id2" value="<?php echo ($data['info_id']);?>" /></td>
		</tr>
		<tr>
		<td>Judul Informasi</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Content</td>
		<td><input type="text" class="input-text input-text-long" name="content" id="content" value="<?php echo $data['content'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_create" id="time_create" value="<?php echo $data['time_create'];?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_edit" id="time_edit" value="<?php echo $data['time_edit'];?>" autocomplete="off" /> TTTT-BB-HH JJ:MM:DD</td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><input type="number" class="input-text input-text-medium" name="admin_create" id="admin_create" value="<?php echo $data['admin_create'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Admin</td>
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
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php if($data['active']==1) echo ' checked="checked"';?>> Active</label>
		</td>
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
$edit_key = kh_filter_input(INPUT_GET, 'info_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `".DB_PREFIX."edu_info`.* $nt
from `".DB_PREFIX."edu_info` 
where 1
and `".DB_PREFIX."edu_info`.`info_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_info" action="" method="post" enctype="multipart/form-data">
	<table width="800" border="0" class="two-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="200">Info</td>
		<td><?php echo ($data['info_id']);?></td>
		</tr>
		<tr>
		<td>Judul Informasi</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Content</td>
		<td><?php echo $data['content'];?></td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><?php echo $data['time_create'];?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit'];?></td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin</td>
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
		<td>Aktif</td>
		<td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&info_id=<?php echo $data['info_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
  Edu Info
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
$sql_filter .= " and (`".DB_PREFIX."edu_info`.`nama` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `".DB_PREFIX."edu_info`.* $nt
from `".DB_PREFIX."edu_info`
where 1 $sql_filter
order by `".DB_PREFIX."edu_info`.`info_id` asc
";
$sql_test = "SELECT `".DB_PREFIX."edu_info`.*
from `".DB_PREFIX."edu_info`
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
      <td width="16"><input type="checkbox" name="control-info_id" id="control-info_id" class="checkbox-selector" data-target=".info_id" value="1"></td>
      <td width="16"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Judul Informasi</td>
      <td>Diubah</td>
      <td>Admin</td>
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
      <td><input type="checkbox" name="info_id[]" id="info_id" value="<?php echo $data['info_id'];?>" class="info_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&info_id=<?php echo $data['info_id'];?>"><img src="tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['time_edit'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['admin_edit'];?></a></td>
      <td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
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