<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$cfg->page_title = "Pilih Kelas";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option'] == 'select')
{
	$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELECT `edu_class`.`class_id`, `edu_student`.`student_id`
	FROM `edu_school`
	INNER JOIN (`edu_student`) ON (`edu_student`.`school_id` = `edu_school`.`school_id`)
	INNER JOIN (`edu_class`) ON (`edu_class`.`school_id` = `edu_school`.`school_id`)
	WHERE `edu_student`.`student_id` = '$auth_student_id' 
	AND `edu_class`.`class_id` = '$class_id'
	AND `edu_school`.`open` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$sql = "UPDATE `edu_student` SET `class_id` = '$class_id' WHERE `student_id` = '$auth_student_id' ";
		$database->executeUpdate($sql, true);
		header('Location: index.php');
		exit();
	}

}
$base_dir = 'siswa/';
$school_code_from_parser = 'student';
if(@$_GET['option'] == 'detail')
{
require_once dirname(dirname(__FILE__))."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_class`.* $nt,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) AS `school_program_id`,
(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_class`.`admin_create` limit 0,1) AS `admin_create`,
(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_class`.`admin_edit` limit 0,1) AS `admin_edit`
FROM `edu_class` 
WHERE `edu_class`.`school_id` = '$school_id'
AND `edu_class`.`class_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_class" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Kode Kelas</td>
		<td><?php echo $data['class_code'];?> </td>
		</tr>
		<tr>
		<td>Tingkat
		</td><td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
		<tr>
		<td>Jurusan</td>
		<td><?php echo $data['school_program_id'];?> </td>
		</tr>
		<tr>
		<td>Nama Kelas
		</td><td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Time Create</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Time Edit</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Create</td>
		<td><?php echo $data['admin_create'];?> </td>
		</tr>
		<tr>
		<td>Admin Edit</td>
		<td><?php echo $data['admin_edit'];?> </td>
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
		<td>Order</td>
		<td><?php echo $data['sort_order'];?> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="select" id="select" class="btn com-button btn-success" value="Pilih" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=select&class_id=<?php echo $data['class_id'];?>'" /> 
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
require_once dirname(dirname(__FILE__))."/lib.inc/footer.php";

}
else
{
require_once dirname(dirname(__FILE__))."/lib.inc/header.php";
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Nama Kelas</span>
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
	$sql_filter .= " AND (`edu_class`.`name` like '%".addslashes($pagination->query)."%' )";
}

$sql_filter .= " AND `edu_class`.`school_id` = '$school_id' ";

$nt = '';


$sql = "SELECT `edu_class`.* $nt,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) AS `school_program_id`,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`class_id` = `edu_class`.`class_id`) AS `num_student`
FROM `edu_class`
WHERE (1=1) $sql_filter
ORDER BY `edu_class`.`sort_order` ASC
";
$sql_test = "SELECT `edu_class`.*
FROM `edu_class`
WHERE (1=1) $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql . $pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $pagination->createPagination('kelas.php', $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset,  true); 
$paginationHTML = $pagination->createPaginationHtml();
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(3), .hide-some-cell tr td:nth-child(5){
		display:none;
	}
}
</style>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>Nama Kelas</td>
      <td>Tingkat</td>
      <td>Jurusan</td>
      <td>Siswa</td>
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
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo 'ganti-kelas.php';?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo 'ganti-kelas.php';?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo 'ganti-kelas.php';?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['school_program_id'];?></a></td>
      <td><a href="<?php echo 'ganti-kelas.php';?>?option=detail&amp;class_id=<?php echo $data['class_id'];?>"><?php echo $data['num_student'];?></a></td>
      </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
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
require_once dirname(dirname(__FILE__))."/lib.inc/footer.php";
}
?>