<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}

$pageTitle = "Jurusan";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school_program`.* ,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_school_program`.`school_id`) AS `school_id`
FROM `edu_school_program` 
WHERE `edu_school_program`.`school_program_id` = '$edit_key' AND `school_id` = '$school_id'
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
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
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
  <span class="search-label">Jurusan</span>
  <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_school_program`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}


$nt = '';

$sql = "SELECT `edu_school_program`.*
FROM `edu_school_program`
WHERE `school_id` = '$school_id' $sql_filter
ORDER BY `edu_school_program`.`school_program_id` ASC
";
$sql_test = "SELECT `edu_school_program`.*
FROM `edu_school_program`
WHERE `school_id` = '$school_id' $sql_filter
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
      <td width="25">No</td>
      <td>Nama</td>
      <td>Order</td>
      <td>Default</td>
      <td width="60">Aktif</td>
</tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td align="right"><?php echo $no;?> </td>
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

</form>
<?php
}
else if(@$_GET['q'] != '')
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