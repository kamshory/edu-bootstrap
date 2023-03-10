<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}



require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$pageTitle = "Ekspor Soal Ujian";
$pagination = new \Pico\PicoPagination();

if(isset($_POST['export']) && isset($_POST['test_id']))
{
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `name` FROM `edu_test` WHERE `test_id` = '$test_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$name = $data['name'];
		$filename = strtolower(str_replace(' ', '-', $name)).'.xml';
		header("Content-Type: text/xml");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		echo exportTest($test_id, dirname(dirname(__FILE__))."/");
	}
	exit();	
}

if(isset($_GET['test_id']))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` GROUP BY `edu_question`.`test_id`) AS `number_of_real_question`
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$edit_key' 
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
      <td>Nama</td>
      <td><?php echo $data['name'];?> </td>
    </tr>
    <tr>
      <td>Kelas</td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
    </tr>
    <tr>
      <td>Mata Pelajaran</td>
      <td><?php echo $data['subject'];?> </td>
    </tr>
    <tr>
      <td>Guru</td>
      <td><?php echo $data['teacher_id'];?> </td>
    </tr>
    <tr>
      <td>Keterangan</td>
      <td><?php echo $data['description'];?> </td>
    </tr>
    <tr>
      <td>Petunjuk</td>
      <td><?php echo $data['guidance'];?> </td>
    </tr>
    <tr>
      <td>Metode Penilaian</td>
      <td><?php echo $picoEdu->selectFromMap($data['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
    </tr>
    <tr>
      <td>Jumlah Soal</td>
      <td><?php echo $data['number_of_question'];?> </td>
    </tr>
    <tr>
      <td>Koleksi Soal</td>
      <td><?php echo $data['number_of_real_question'];?> </td>
    </tr>
    <tr>
      <td>Jumlah Pilihan</td>
      <td><?php echo $data['number_of_option'];?> </td>
    </tr>
    <tr>
      <td>Soal Per Halaman</td>
      <td><?php echo $data['question_per_page'];?> </td>
    </tr>
    <tr>
      <td>Nilai Standard</td>
      <td><?php echo $data['standard_score'];?> </td>
    </tr>
    <tr>
      <td>Penalti</td>
      <td><?php echo $data['penalty'];?> </td>
    </tr>
    <tr>
      <td>Aktif</td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?>
      <input type="hidden" name="test_id" value="<?php echo $data['test_id'];?>" /></td>
    </tr>
    </table>
    <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
      <td></td>
      <td><input type="submit" name="export" id="export" class="btn btn-success" value="Ekspor Soal" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&test_id=<?php echo $data['test_id'];?>'" />
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
    </tr>
  </table>
</form>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_test`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_test`.`school_id`) AS `school`,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher`,
(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` GROUP BY `edu_question`.`test_id`)*1 AS `number_of_question`
FROM `edu_test`
WHERE (1=1)  $sql_filter
ORDER BY `edu_test`.`test_id` DESC
";
$sql_test = "SELECT `edu_test`.`test_id`
FROM `edu_test`
WHERE (1=1)  $sql_filter
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
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8)
	{
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5)
	{
		display:none;
	}
}
</style>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><i class="fas fa-file-export"></i></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>Nama Ujian</td>
      <td>Mata Pelajaran</td>
      <td>Soal</td>
      <td>Aktif</td>
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
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&test_id=<?php echo $data['test_id'];?>"><i class="fas fa-file-export"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['school'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="data-soal-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
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
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>
