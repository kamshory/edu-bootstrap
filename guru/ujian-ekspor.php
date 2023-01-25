<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$cfg->module_title = "Ekspor Soal Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(isset($_POST['export']) && isset($_POST['test_id']))
{
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `name` from `edu_test` where `school_id` = '$school_id' and `test_id` = '$test_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
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
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`) as `number_of_real_question`
from `edu_test` 
where 1
and `edu_test`.`test_id` = '$edit_key' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
      <td>Nama</td>
      <td><?php echo $data['name'];?></td>
    </tr>
    <tr>
      <td>Kelas</td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
    </tr>
    <tr>
      <td>Mata Pelajaran</td>
      <td><?php echo $data['subject'];?></td>
    </tr>
    <tr>
      <td>Guru</td>
      <td><?php echo $data['teacher_id'];?></td>
    </tr>
    <tr>
      <td>Keterangan</td>
      <td><?php echo $data['description'];?></td>
    </tr>
    <tr>
      <td>Petunjuk</td>
      <td><?php echo $data['guidance'];?></td>
    </tr>
    <tr>
      <td>Metode Penilaian</td>
      <td><?php if($data['assessment_methods'] == 'H') echo "Nilai Tertinggi"; if($data['assessment_methods'] == 'N') echo "Nilai Terbaru";?></td>
    </tr>
    <tr>
      <td>Jumlah Soal</td>
      <td><?php echo $data['number_of_question'];?></td>
    </tr>
    <tr>
      <td>Koleksi Soal</td>
      <td><?php echo $data['number_of_real_question'];?></td>
    </tr>
    <tr>
      <td>Jumlah Pilihan</td>
      <td><?php echo $data['number_of_option'];?></td>
    </tr>
    <tr>
      <td>Soal Per Halaman</td>
      <td><?php echo $data['question_per_page'];?></td>
    </tr>
    <tr>
      <td>Nilai Standard</td>
      <td><?php echo $data['standard_score'];?></td>
    </tr>
    <tr>
      <td>Penalti</td>
      <td><?php echo $data['penalty'];?></td>
    </tr>
    <tr>
      <td>Aktif</td>
      <td><?php echo ($data['active'])?'Ya':'Tidak';?>
      <input type="hidden" name="test_id" value="<?php echo $data['test_id'];?>" /></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" name="export" id="export" class="com-button" value="Ekspor Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>'" />
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
    </tr>
  </table>
</form>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$tahun_id = kh_filter_input(INPUT_GET, 'tahun_id', FILTER_SANITIZE_NUMBER_UINT);
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
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
    <span class="search-label">Kelas </span>
    <select class="input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
	$sql2 = "select * from `edu_class` where `school_id` = '$school_id' ";
	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'class_id')
			),
			'selectCondition'=>array(
				'source'=>'class_id',
				'value'=>$class_id
			),
			'caption'=>array(
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
    </select>
    <span class="search-label">Ujian</span>
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
$sql_filter .= " and (`edu_test`.`name` like '%".addslashes($pagination->query)."%' )";
}

if($class_id != '')
{
	$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
	$pagination->array_get[] = 'class_id';
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`)*1 as `number_of_question`
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
order by `edu_test`.`test_id` desc
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);

$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit > 0)
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

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Nama Ujian</td>
      <td>Kelas</td>
      <td>Mata Pelajaran</td>
      <td>Guru</td>
      <td>Sifat</td>
      <td>Soal</td>
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
    <tr<?php $rowclass=""; if(@$data['default']==1) $rowclass.=" data-default"; if(isset($data['active'])){if(@$data['active']==1) $rowclass.=" data-active"; if(@$data['active']==0) $rowclass.=" data-inactive";} $rowclass = trim($rowclass); if(strlen($rowclass)){echo " class=\"$rowclass\"";}?>>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['teacher']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Terbuka':'Tertutup';?></a></td>
      <td><a href="data-soal-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
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
