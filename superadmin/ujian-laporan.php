<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$pageTitle = "Hasil Ujian";
$pagination = new \Pico\PicoPagination();

if(isset($_POST['set_active']) && isset($_POST['answerid']))
{
	$answerid = @$_POST['answerid'];
	if(is_array($answerid) && count($answerid) > 0)
	{
    foreach($answerid as $key=>$val)
    {
      $answer_id = addslashes($val);
      $sql = "UPDATE `edu_answer` SET `active` = true WHERE `answer_id` = '$answer_id' ";
      $database->executeUpdate($sql, true);
		}
	}
	header("Location: ".$_SERVER['REQUEST_URI']); //NOSONAR
}
if(isset($_POST['set_inactive']) && isset($_POST['answerid']))
{
	$answerid = @$_POST['answerid'];
	if(is_array($answerid) && count($answerid) > 0)
	{
    foreach($answerid as $key=>$val)
    {
      $answer_id = addslashes($val);
      $sql = "UPDATE `edu_answer` SET `active` = false WHERE `answer_id` = '$answer_id'  ";
      $database->executeUpdate($sql, true);
    }
	}
	header("Location: ".$_SERVER['REQUEST_URI']); //NOSONAR
}
if(isset($_POST['delete']) && isset($_POST['answerid']))
{
	$answerid = @$_POST['answerid'];
	if(is_array($answerid) && count($answerid) > 0)
	{
    foreach($answerid as $key=>$val)
    {
      $answer_id = addslashes($val);
      $sql = "DELETE FROM `edu_answer` WHERE `answer_id` = '$answer_id'  ";
      $database->executeDelete($sql, true);
    }
	}
	header("Location: ".$_SERVER['REQUEST_URI']); //NOSONAR
}

if(@$_GET['option'] == 'export' && isset($_GET['test_id']))
{
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt, 
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher_id`,
(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` GROUP BY `edu_question`.`test_id`) AS `collection_of_question`
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$test_id' 
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
$assessment_methods = $data['assessment_methods'];

header("Content-Type: application/vnd.xls");
header("Content-Disposition: attachment; filename=\"".str_replace(" ", "-", strtolower($data['name'])).".xls\"");

echo '
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hasil Ujian '.$data['name'].'</title>
</head>
';
?>
<style type="text/css">
body{
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
}
.row-table[border="1"]{
	border-collapse:collapse;
}
.row-table[border="1"] td{
	padding:5px 5px;
}
</style>
<?php
echo '
<body>';
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<table width="100%" border="0" class="table two-side-table" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="2">Ujian</td>
    <td width="1164"><?php echo $data['name'];?> </td>
  </tr>
  <tr>
    <td colspan="2">Kelas</td>
    <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
  </tr>
  <tr>
    <td colspan="2">Mata Pelajaran</td>
    <td><?php echo $data['subject'];?> </td>
  </tr>
  <tr>
    <td colspan="2">Guru</td>
    <td><?php echo $data['teacher_id'];?> </td>
  </tr>
  <tr>
    <td colspan="2">Metode Penilaian</td>
    <td><?php echo $picoEdu->selectFromMap($data['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
  </tr>
  <tr>
    <td colspan="2">Jumlah Soal</td>
    <td><?php echo $data['number_of_question'];?> </td>
  </tr>
  <tr>
    <td colspan="2">Nilai Standard</td>
    <td><?php echo $data['standard_score'];?> </td>
  </tr>
  <tr>
    <td colspan="2">Penalti</td>
    <td><?php echo $data['penalty'];?> </td>
  </tr>
  <tr>
    <td width="45">&nbsp;</td>
    <td width="106">&nbsp;</td>
    <td></td>
  </tr>
</table>


<?php
$sql_filter = "";
if($class_id != '')
{
	$sql_filter .= " AND `edu_student`.`class_id` = '$class_id' ";
}

$threshold = $data['threshold'];
if(isset($_GET['expand']))
{
	$sql = "SELECT `edu_answer`.* , `edu_answer`.`student_id` AS `student_id`, `edu_student`.`reg_number`,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `timediff` ,
	(SELECT `edu_test`.`number_of_question` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `number_of_question`,
	((SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) AS `lewat`,
	(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
	`edu_student`.`name` AS `student_name`, `edu_student`.`class_id`

	FROM `edu_answer` 
	LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
	LEFT JOIN (`edu_question`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
	GROUP BY `edu_answer`.`answer_id` 
	ORDER BY `edu_student`.`class_id`, `edu_answer`.`student_id` ASC, `edu_answer`.`start` ASC ";
}
else
{
	if($assessment_methods == 'N')
	{
		$grp = " ORDER BY `edu_answer`.`start` DESC ";
	}
	else
	{
		$grp = " ORDER BY `edu_answer`.`percent` DESC ";
	}


	$sql = "SELECT * from (select 
	`edu_answer`.`start` , 
	`edu_answer`.`percent` ,
	(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
	`edu_student`.`name` AS `student_name`, `edu_student`.`class_id`
	FROM `edu_answer` 
	LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
	LEFT JOIN (`edu_question`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
	GROUP BY `edu_answer`.`answer_id` 
	$grp ) AS `inv` GROUP BY concat(`inv`.`test_id`, '-', `inv`.`student_id`) 
  ";
	
}
$ke = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$array_class = $picoEdu->getArrayClass($school_id);
?>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>NIS</td>
      <td>Nama Siswa</td>
      <td>Kelas</td>
      <td width="20">Ke</td>
      <td width="130">Mulai</td>
      <td width="60">Lama</td>
      <td width="60" align="right">Benar</td>
      <td width="60" align="right">Salah </td>
      <td width="60" align="right">Nilai</td>
      <td width="60" align="right">Persen</td>
      <td width="60" align="right">KKM</td>
      <td width="80">Tuntas</td>
    </tr>
	</thead>
	<tbody>
	<?php
	$i=0;
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$j=$i%2;
	$no++;
	$i++;
	
	if(!isset($ke[$data['student_id']]))
	{
		$ke[$data['student_id']] = 0;
	}
	$ke[$data['student_id']] ++;
	
	?>
    <tr class="row-data<?php if ($data['lewat']) {
            echo ' data-error';
            }?>">
      <td align="right"><?php echo $no;?> </td>
      <td><?php echo $data['reg_number'];?> </td>
      <td><?php echo $data['student_name'];?> </td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
      <td align="right"><?php echo $ke[$data['student_id']];?> </td>
      <td nowrap><?php echo date(\Pico\PicoConst::DATE_TIME_MYSQL, strtotime($data['start']));?> </td>
      <td nowrap><?php echo $data['timediff'];?> </td>
      <td align="right"><?php echo $data['answer_true'];?> </td>
      <td align="right"><?php echo $data['answer_false'];?> </td>
      <td align="right"><?php echo number_format($data['final_score'], 2);?> </td>
      <td align="right"><?php echo number_format($data['percent'], 2);?> </td>
      <td align="right"><?php echo number_format($threshold, 2);?> </td>
      <td><?php echo $picoEdu->trueFalse($data['percent'] >= $threshold, 'Ya', 'Tidak');?> </td>
      </tr>
	<?php
	}
	?>
	</tbody>
  </table>
<?php
}
}
echo '</body>
</html>';
}
else if(@$_GET['option'] == 'answerdetail' && isset($_GET['test_id']))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.*, `edu_answer`.*, 
timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `duration_test` ,
(SELECT `edu_student`.`name` FROM `edu_student` WHERE `edu_student`.`student_id` = `edu_answer`.`student_id`) AS `student_name`
FROM `edu_test`
LEFT JOIN (`edu_answer`) ON (`edu_answer`.`test_id` = `edu_test`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$test_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$info = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td>NIS</td>
    <td><?php echo $info['student_id'];?> </td>
    <td>Metode Penilaian</td>
    <td><?php echo $picoEdu->selectFromMap($info['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
    <td>Dibuka</td>
    <td><?php echo $picoEdu->trueFalse($info['available_from'] != '0000-00-00 00:00:00' && $info['available_from'] != '', translateDate(date(\Pico\PicoConst::FULL_DATE_TIME_INDONESIA_FORMAT, strtotime($info['available_from']))), '-');?> </td>
    <td>Benar</td>
    <td><?php echo $info['answer_true'];?> </td>
  </tr>
  <tr>
    <td>Nama Siswa</td>
    <td><?php echo $info['student_name'];?> </td>
    <td>Jumlah Soal</td>
    <td><?php echo $info['number_of_question'];?> </td>
    <td>Ditutup</td>
    <td><?php echo $picoEdu->trueFalse($info['available_to'] != '0000-00-00 00:00:00' && $info['available_to'] != '', translateDate(date(\Pico\PicoConst::FULL_DATE_TIME_INDONESIA_FORMAT, strtotime($info['available_to']))), '-');?> </td>
    <td>Salah</td>
    <td><?php echo $info['answer_false'];?> </td>
  </tr>
  <tr>
    <td>Kelas</td>
    <td><?php $class = $picoEdu->textClass($array_class, $info['class']); $class_sort = $picoEdu->textClass($array_class, $info['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
    <td>Sekor Benar</td>
    <td><?php echo $info['standard_score'];?> </td>
    <td>Pengumuman Hasil</td>
    <td><?php echo $picoEdu->trueFalse($info['publish_answer'], translateDate(date(\Pico\PicoConst::FULL_DATE_TIME_INDONESIA_FORMAT, strtotime($info['time_answer_publication']))), '-');?> </td>
    <td>Nilai Awal</td>
    <td><?php echo $info['initial_score'];?> </td>
  </tr>
  <tr>
    <td>Ujian</td>
    <td><?php echo $info['name'];?> </td>
    <td>Penalti</td>
    <td><?php echo $info['penalty'];?> </td>
    <td>Tanggal Ujian</td>
    <td><?php echo translateDate(date(\Pico\PicoConst::FULL_DATE_TIME_INDONESIA_FORMAT, strtotime($info['start'])));?> </td>
    <td>Nilai Akhir</td>
    <td><?php echo $info['final_score'];?> </td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $info['subject'];?> </td>
    <td>Waktu Tersedia</td>
    <td><?php echo implode(':', $picoEdu->secondsToTime($info['duration']));?> </td>
    <td>Durasi Pengerjaan</td>
    <td><?php echo $info['duration_test'];?> </td>
    <td>Persen</td>
    <td><?php echo $picoEdu->numberFormatTrans($info['percent']);?> </td>
  </tr>
</table>
</div>
<?php
$sql = "SELECT `edu_question`.* , `edu_answer`.`answer` AS `answer` , instr(`edu_answer`.`answer`,`edu_question`.`question_id`) AS `pos`
FROM `edu_question` 
LEFT JOIN (`edu_answer`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
LEFT JOIN (`edu_test`) ON (`edu_test`.`test_id` = `edu_question`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$test_id' 
GROUP BY `edu_question`.`question_id` 
ORDER BY `pos` ASC ";

$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
<ol class="test-question">
<?php
$i=0;
$no = $pagination->getOffset();
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
foreach($rows as $data)
{
$j=$i%2;
$no++;
$qid = $data['question_id'];
$answer = $data['answer'];
?>
<li value="<?php echo $no;?>">
<div class="question">
<?php echo $data['content'];?>
<?php
$sql2 = "SELECT `edu_option`.* , '$answer' like concat('%,',`edu_option`.`option_id`,']%') AS `my_answer`
FROM `edu_option` 
WHERE  `edu_option`.`question_id` = '$qid' GROUP BY  `edu_option`.`option_id` ORDER BY  `edu_option`.`sort_order` ASC ";
$stmt2 = $database->executeQuery($sql2);
if($stmt2->rowCount() > 0)
{
?>
<div class="option">
<ol class="listoption" style="list-style-type:<?php echo $data['numbering'];?>">
<?php
$rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
foreach($rows2 as $data2)
{
?>
<li>
<span class="option-circle<?php echo $picoEdu->trueFalse($data2['score'] > 0, ' option-circle-selected', '');?>"><?php
        echo $data2['score']*1;
        ?></span>
<div class="list-option-item<?php echo $picoEdu->trueFalse($data2['my_answer'], ' list-option-item-selected', '');?>">
<div class="option-content">
<?php
echo $data2['content'];
?>
</div>
</div>
</li>
<?php
}
?>
</ol>
</div>
<?php
}
?>
</div>
</li>
<?php
$i++;
}
?>
</ol>
<?php
}
?>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
}
else if(@$_GET['option'] == 'detail' && isset($_GET['test_id']))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$nt = "";
$sql = "SELECT `edu_test`.* $nt
FROM `edu_test` 
WHERE (`edu_test`.`active` = true OR `edu_test`.`active` = false)
AND `edu_test`.`test_id` = '$test_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
  $data = $stmt->fetch(\PDO::FETCH_ASSOC);
  $school_id = $data['school_id'];
$q = kh_filter_input(INPUT_GET, "q", FILTER_SANITIZE_STRING_NEW);
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$pagination->appendQueryName('option');
$pagination->appendQueryName('test_id');
?>
<script type="text/javascript">
window.onload = function()
{
    $(document).on('click', '.fotostudent450x600', function(e){
		var url = $(this).attr('href');
		overlayDialog('<img src="'+url+'" width="450" height="600">', 450, 600); 
		return false;
	});
	$(document).on('change', '#searchform select', function(e){
		$('#searchform').submit();
	});
    $(document).on('click', '#ekspor1', function(e){
		var test_id = '<?php echo $test_id;?>';
		var class_id = $('#class_id').val();
		var q = $('#q').val();
		var url = '<?php echo $picoEdu->gateBaseSelfName();?>?option=export&test_id='+test_id+'&class_id='+class_id;
		window.open(url);
	});
    $(document).on('click', '#ekspor2', function(e){
		var test_id = '<?php echo $test_id;?>';
		var class_id = $('#class_id').val();
		var q = $('#q').val();
		var url = '<?php echo $picoEdu->gateBaseSelfName();?>?option=export&expand=1&test_id='+test_id+'&class_id='+class_id;
		window.open(url);
	});
}
</script>

<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <?php
	if(isset($school_id) && !empty($school_id))
	{
	?>
    <span class="search-label">Kelas</span> 
    <select class="form-control input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
	$sql2 = "SELECT * FROM `edu_class` WHERE `school_id` = '$school_id' ";
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
				'delimiter'=>\Pico\PicoConst::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
    </select>
    <?php
	}
	?>
    <input type="hidden" name="option" value="detail" /> 
    <input type="hidden" name="test_id" value="<?php echo $test_id;?>" /> 
    <span class="search-label">Siswa</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode(stripslashes(trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
  <input type="button" name="ekspor1" id="ekspor1" value="Ekspor Persiswa" class="btn btn-success" />
  <input type="button" name="ekspor2" id="ekspor2" value="Ekspor Perujian" class="btn btn-success" />
</form>
</div>
<div class="search-result">

<?php
$q1 = $picoEdu->gateBaseSelfName()."?option=detail&test_id=$test_id&expand=1";
$q2 = $picoEdu->gateBaseSelfName()."?option=detail&test_id=$test_id";
$nt ='';
$threshold = $data['threshold'];
$assessment_methods = $data['assessment_methods'];



$pagination->appendQueryName('class_id');
$pagination->appendQueryName('option');
$pagination->appendQueryName('test_id');


$sql_filter = "";
if($class_id != "")
{
	$sql_filter .= " AND `edu_student`.`class_id` like '$class_id' ";
}
if($q != "")
{
	$sql_filter .= " AND `edu_student`.`name` like '%$q%' ";
}

?>

<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<div class="horizontal-bar">
Ujian: <?php echo $data['name'];?>;<?php if($data['subject']!=''){?> Mata Pelajaran: <?php echo $data['subject'];?>;<?php } if($data['class'] != ''){?> Kelas: <?php echo $picoEdu->textClass($array_class, $data['class']);  ?>;<?php }?> Soal: <?php echo $data['number_of_question'];?>; Durasi: <?php echo implode(':', $picoEdu->secondsToTime($data['duration']));?>
</div>

<?php
$nt = '';

if(isset($_GET['expand']))
{
	$sql = "SELECT `edu_answer`.* , `edu_answer`.`student_id` AS `student_id`, `edu_student`.`reg_number`,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `timediff` ,
	(SELECT `edu_test`.`number_of_question` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `number_of_question`,
	((SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) AS `lewat`,
	(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
	`edu_student`.`name` AS `student_name`, `edu_student`.`class_id` 

	FROM `edu_answer` 
	LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
	LEFT JOIN (`edu_question`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
	GROUP BY `edu_answer`.`answer_id` having 1 $sql_filter
	ORDER BY `edu_student`.`class_id`, `edu_answer`.`student_id` ASC, `edu_answer`.`start` ASC ";

  $sql_test = "SELECT `edu_answer`.* , `edu_answer`.`student_id` AS `student_id`, `edu_student`.`reg_number`,
  timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `timediff` ,
  (SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
  `edu_student`.`name` AS `student_name`, `edu_student`.`class_id` 

  FROM `edu_answer` 
  LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
  WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
  GROUP BY `edu_answer`.`answer_id` having 1 $sql_filter

  ";


}
else
{
	if($assessment_methods == 'N')
	{
		$grp = " ORDER BY `edu_answer`.`start` DESC ";
	}
	else
	{
		$grp = " ORDER BY `edu_answer`.`percent` DESC ";
	}


	$sql = "SELECT * from (select 
	`edu_answer`.`answer_id`,
	`edu_answer`.`student_id`,
	`edu_answer`.`test_id` ,
	`edu_answer`.`start` , 
	`edu_answer`.`end` ,
	`edu_answer`.`true` ,
	`edu_answer`.`false` ,
	`edu_answer`.`initial_score` ,
	`edu_answer`.`penalty` ,
	`edu_answer`.`final_score` ,
	`edu_answer`.`percent` ,
	`edu_answer`.`active` ,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `timediff` , `edu_student`.`reg_number`,
	(SELECT `edu_test`.`number_of_question` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `number_of_question`,
	(SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `waktu_tersedia`,
	((SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) AS `lewat`,
	(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
	`edu_student`.`name` AS `student_name`, `edu_student`.`class_id` AS `class_id`, `edu_student`.`time_edit` AS `time_edit`
	FROM `edu_answer` 
	LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
	LEFT JOIN (`edu_question`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
	GROUP BY `edu_answer`.`answer_id` having 1 $sql_filter
	$grp ) AS `inv` GROUP BY concat(`inv`.`test_id`, '-', `inv`.`student_id`) 
	ORDER BY `inv`.`class_id`, `inv`.`student_id` ASC, `inv`.`start` ASC ";
	
  $sql_test = "SELECT * from (select 
	`edu_answer`.`answer_id`,
	`edu_answer`.`student_id`,
	`edu_answer`.`test_id` ,
	`edu_answer`.`start` , 
	`edu_answer`.`end` ,
	`edu_answer`.`true` ,
	`edu_answer`.`false` ,
	`edu_answer`.`initial_score` ,
	`edu_answer`.`penalty` ,
	`edu_answer`.`final_score` ,
	`edu_answer`.`percent` ,
	`edu_answer`.`active` ,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) AS `timediff` , `edu_student`.`reg_number`,
	(SELECT `edu_test`.`number_of_question` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `number_of_question`,
	(SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_question`.`test_id`) AS `waktu_tersedia`,
	((SELECT `edu_test`.`duration` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) AS `lewat`,
	(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_student`.`school_id`) AS `class`,
	`edu_student`.`name` AS `student_name`, `edu_student`.`class_id` AS `class_id`, `edu_student`.`time_edit` AS `time_edit`
	FROM `edu_answer` 
	LEFT JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_answer`.`student_id`)
	LEFT JOIN (`edu_question`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	WHERE  `edu_answer`.`test_id` = '$test_id' $sql_filter
	GROUP BY `edu_answer`.`answer_id` having 1 $sql_filter
	$grp ) AS `inv` GROUP BY concat(`inv`.`test_id`, '-', `inv`.`student_id`) 
	ORDER BY `inv`.`class_id`, `inv`.`student_id` ASC, `inv`.`start` ASC 
  ";
	
}
$ke = array();
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{
$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
$paginationHTML = $pagination->buildHTML();
?>
<form name="form1" method="post" action="" enctype="multipart/form-data">

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>


  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-answerid" data-target=".answerid" class="checkbox-selector" value="1"></td>
      <td width="25">No</td>
      <td>NIS</td>
      <td>Nama Siswa </td>
      <td>Kelas</td>
      <td width="20">Ke</td>
      <td width="130">Mulai</td>
      <td width="60">Lama</td>
      <td width="40" align="right">Soal </td>
      <td width="50" align="right">Benar</td>
      <td width="50" align="right"> Salah </td>
      <td width="40" align="right">Nilai</td>
      <td width="40" align="right">Persen</td>
      <td width="40" align="right">KKM</td>
      <td width="60">Tuntas</td>
      <td width="50">Aktif</td>
    </tr>
	</thead>
	<tbody>
	<?php
	$i=0;
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$j=$i%2;
	$no++;
	$i++;
	if(!isset($ke[$data['student_id']]))
	{
		$ke[$data['student_id']] = 0;
	}
	$ke[$data['student_id']] ++;
	?>
    <tr class="row-data<?php echo $picoEdu->trueFalse($data['lewat'] > 0, ' data-error', '');?>">
      <td><input type="checkbox" name="answerid[]" id="answerid" value="<?php echo $data['answer_id'];?>" class="answerid" /></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=answerdetail&test_id=<?php echo $data['answer_id'];?>"><?php echo $data['reg_number'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=answerdetail&test_id=<?php echo $data['answer_id'];?>"><?php echo $data['student_name'];?></a></td>
      <td><?php echo $data['class'];?> </td>
      <td align="right"><?php echo $ke[$data['student_id']];?> </td>
      <td nowrap><?php echo translateDate(date('d M Y H:i:s',strtotime($data['start'])));?> </td>
      <td nowrap><?php echo $data['timediff'];?> </td>
      <td align="right"><?php echo $data['number_of_question'];?> </td>
      <td align="right"><?php echo $data['answer_true'];?> </td>
      <td align="right"><?php echo $data['answer_false'];?> </td>
      <td align="right"><?php echo $data['final_score'];?> </td>
      <td align="right"><?php echo number_format($data['percent']);?> </td>
      <td align="right"><?php echo number_format($threshold);?> </td>
      <td><?php echo $picoEdu->trueFalse($data['percent'] >= $threshold, 'Ya', 'Tidak');?> </td>
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
  <input type="submit" name="set_active" id="set_active" value="Aktif" class="btn btn-success" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktif" class="btn btn-success" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin untuk menghapus data ini?');" />
	<?php
    if(!isset($_GET['expand'])){
    ?>
    <input type="button" name="show-all" id="show-all" value="Tampilkan Semua" class="btn btn-primary" onclick="window.location='<?php echo $q1;?>'" />
    <?php
    }
    else
    {
    if($assessment_methods == 'N')
    {
    ?>
    <input type="button" name="show-newest" id="show-newest" value="Tampilkan Nilai Terbaru" class="btn btn-success" onclick="window.location='<?php echo $q2;?>'" />
    <?php
    }
    else
    {
    ?>
    <input type="button" name="show-highest" id="show-highest" value="Tampilkan Nilai Tertinggi" class="btn btn-success" onclick="window.location='<?php echo $q2;?>'" />
    <?php
    }
	}
    ?>
</div>
</form>
<?php
}
else if(strlen(@$_GET['q'] != ''))
{
?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi lagi dengan kata kunci yang lain.</div>
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
} else {
    require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
    $school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
    $class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);

    ?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Sekolah</span>
  <select class="form-control input-select" name="school_id" id="school_id">
    <option value="">- Pilih Sekolah -</option>
    <?php
    $sql2 = "SELECT * FROM `edu_school` WHERE (1=1) ORDER BY `time_create` DESC";
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
          'delimiter'=>\Pico\PicoConst::RAQUO,
          'values'=>array(
            'name'
          )
        )
      )
    );
    ?>
    </select>
    <?php
    if(isset($school_id) && !empty($school_id)) {
      ?>
    <span class="search-label">Kelas</span> 
    <select class="form-control input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php
        $sql = "SELECT * FROM `edu_class` WHERE `school_id` = '$school_id' ";
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
              'delimiter'=>\Pico\PicoConst::RAQUO,
              'values'=>array(
                'name'
              )
            )
          )
        );
        ?>
    </select>
    <?php
    }
    ?>
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode(stripslashes(trim(@$_GET['q'], " 	
    ")))); ?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
}
</script>
</div>
<div class="search-result">
<?php

        $sql_filter = "";
        


        if ($school_id != 0) {
          $pagination->appendQueryName('school_id');
          $sql_filter .= " AND (`edu_test`.`school_id` = '$school_id' )";
        }
        if ($class_id != '') {
          $pagination->appendQueryName('class_id');
          $sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
        }
        if($pagination->getQuery()) {
          $pagination->appendQueryName('q');
          $sql_filter .= " AND (`edu_test`.`name` like '%" . addslashes($pagination->getQuery()) . "%' )";
        }

      $sql = "SELECT `edu_test`.*,
      (SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_test`.`school_id` limit 0,1) AS `school_name`,
      (SELECT COUNT(DISTINCT `edu_answer`.`student_id`) FROM `edu_answer` WHERE `edu_answer`.`test_id` = `edu_test`.`test_id`) AS `number_of_student`,
      (SELECT `edu_answer`.`start` FROM `edu_answer` WHERE `edu_answer`.`test_id` = `edu_test`.`test_id` ORDER BY `edu_answer`.`start` DESC LIMIT 0, 1) AS `last_test`
      FROM `edu_test`
      WHERE (1=1) $sql_filter
      having 1 AND `number_of_student` > 0
      ORDER BY `last_test` DESC, `edu_test`.`test_id` DESC
      ";
      $sql_test = "SELECT `edu_test`.*,
      (SELECT COUNT(DISTINCT `edu_answer`.`student_id`) FROM `edu_answer` WHERE `edu_answer`.`test_id` = `edu_test`.`test_id`) AS `number_of_student`
      FROM `edu_test`
      WHERE (1=1) $sql_filter
      having 1 AND `number_of_student` > 0
      ";



        $stmt = $database->executeQuery($sql_test);
        $pagination->setTotalRecord($stmt->rowCount());
        $stmt = $database->executeQuery($sql . $pagination->getLimitSql());
        $pagination->setTotalRecordWithLimit($stmt->rowCount());
        if ($pagination->getTotalRecordWithLimit() > 0) {
          $pagination->createPagination($picoEdu->gateBaseSelfName(), true);
          $paginationHTML = $pagination->buildHTML();
          ?>
<?php
            $array_class = $picoEdu->getArrayClass($school_id);
            ?>
<form name="form1" method="post" action="" enctype="multipart/form-data">

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

<form name="rowform" method="post" action="">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
    <tr>
        <td width="16"><img alt="Excel" src="lib.tools/images/excel.png" /></td>
        <td width="16"><img alt="Excel" src="lib.tools/images/excel.png" /></td>
        <td width="25">No</td>
        <td>Sekolah</td>
        <td>Ujian</td>
        <td>Kelas</td>
        <td>Terakhir</td>
        <td width="50" align="right">Soal</td>
        <td width="70" align="right">Peserta</td>
      </tr>
	</thead>
	<tbody>
	<?php
          $i = 0;
          $no = $pagination->getOffset();
          $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
          foreach($rows as $data)
          {
            $j = $i % 2;
            $no++;
            ?>
    <tr class="row-data">
        <td width="16"><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=export&test_id=<?php echo $data['test_id']; ?>&expand=1"><img alt="Excel" src="lib.tools/images/excel.png" /></a></td>
        <td width="16"><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=export&test_id=<?php echo $data['test_id']; ?>"><img alt="Excel" src="lib.tools/images/excel.png" /></a></td>
        <td align="right"><?php echo $no; ?> </td>
        <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['school_name']; ?></a></td>
        <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['name']; ?></a></td>
        <td><?php $class = $picoEdu->textClass($array_class, $data['class']);
        $class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort; ?></a></td>
        <td><?php echo translateDate(date('d M Y H:i', strtotime($data['last_test']))); ?> </td>
        <td align="right"><?php echo $data['number_of_question']; ?> </td>
        <td align="right"><?php echo $data['number_of_student']; ?> </td>
      </tr>
	<?php
              $i++;
          }
          ?>
	</tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

</form>
<?php
        } else if (strlen(@$_GET['q'] != '')) {
          ?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi lagi dengan kata kunci yang lain.</div>
<?php
        } else {
          ?>
<div class="alert alert-warning">Data tidak ditemukan.</div>
<?php
        }
        ?>
</div>

<?php
  }
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>