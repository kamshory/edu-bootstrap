<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-guru.php";
	exit();
}
$pageTitle = "Kelas";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_class`.* $nt,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) AS `school_program_id`
FROM `edu_class` 
WHERE `edu_class`.`active` = true AND `edu_class`.`school_id` = '$school_id'
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
		<td>Order</td>
		<td><?php echo $data['sort_order'];?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$school_program_id = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);
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
	<span class="search-label">Jurusan</span> 
	<select class="form-control input-select" name="school_program_id" id="school_program_id">
	<option value=""></option>
	<?php 
	$sql2 = "SELECT `edu_school_program`.* FROM `edu_school_program` WHERE `edu_school_program`.`school_id` = '$school_id' ORDER BY `name` ASC ";
	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'school_program_id')
			),
			'selectCondition'=>array(
				'source'=>'school_program_id',
				'value'=>$school_program_id
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
    <span class="search-label">Nama Kelas</span>
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
$sql_filter .= " AND (`edu_class`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}
if($school_program_id != '')
{
	$pagination->appendQueryName('school_program_id');
	$sql_filter .= " and `edu_class`.`school_program_id` = '$school_program_id' ";
}

$nt = '';


$sql = "SELECT `edu_class`.* $nt,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_class`.`school_id` limit 0,1) AS `school_name`,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`class_id` = `edu_class`.`class_id`) AS `num_student`,
`edu_school_program`.`name` AS `school_program`
FROM `edu_class`
LEFT JOIN (`edu_school_program`) ON (`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`) 
WHERE `edu_class`.`school_id` = '$school_id' $sql_filter
ORDER BY `edu_class`.`school_id` DESC, `edu_school_program`.`sort_order` ASC, `edu_class`.`sort_order` ASC
";

$sql_test = "SELECT `edu_class`.*
FROM `edu_class`
WHERE `edu_class`.`active` = true AND `edu_class`.`school_id` = '$school_id' $sql_filter
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
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(2), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5){
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
      <td width="25">No</td>
      <td>Kode </td>
      <td>Nama</td>
      <td>Tingkat</td>
      <td>Jurusan</td>
      <td>Siswa</td>
      <td>Order</td>
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
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['class_code'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['school_program'];?></a></td>
      <td><a href="siswa.php?class_id=<?php echo $data['class_id'];?>"><?php echo $data['num_student'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['sort_order'];?></a></td>
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
<div class="warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>