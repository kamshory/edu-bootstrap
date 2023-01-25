<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-admin.php";
exit();
}
if(empty(@$real_school_id))
{
include_once dirname(__FILE__)."/belum-ada-sekolah.php";
exit();
}

$cfg->module_title = "Hasil Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(isset($_POST['set_active']) && isset($_POST['answer_id']))
{
	$answer_id = @$_POST['answer_id'];
	if(is_array($answer_id))
	{
		if(count($answer_id) > 0)
		{
			foreach($answer_id as $key=>$val)
			{
				$answer_id = addslashes($val);
				$sql = "update `edu_answer` set `active` = '1' where `answer_id` = '$answer_id' and `school_id` = '$school_id' ";
				$database->executeUpdate($sql);
			}
		}
	}
	header("Location: ".$_SERVER['REQUEST_URI']);
}
if(isset($_POST['set_inactive']) && isset($_POST['answer_id']))
{
	$answer_id = @$_POST['answer_id'];
	if(is_array($answer_id))
	{
		if(count($answer_id) > 0)
		{
			foreach($answer_id as $key=>$val)
			{
				$answer_id = addslashes($val);
				$sql = "update `edu_answer` set `active` = '0' where `answer_id` = '$answer_id' and `school_id` = '$school_id' ";
				$database->executeUpdate($sql);
			}
		}
	}
	header("Location: ".$_SERVER['REQUEST_URI']);
}
if(isset($_POST['delete']) && isset($_POST['answer_id']))
{
	$answer_id = @$_POST['answer_id'];
	if(is_array($answer_id))
	{
		if(count($answer_id) > 0)
		{
			foreach($answer_id as $key=>$val)
			{
				$answer_id = addslashes($val);
				$sql = "DELETE FROM `edu_answer` where `answer_id` = '$answer_id' and `school_id` = '$school_id' ";
				$database->executeDelete($sql);
			}
		}
	}
	header("Location: ".$_SERVER['REQUEST_URI']);
}

if(isset($_POST['recalculation']) && isset($_POST['answer_id']))
{
	$answer_id = @$_POST['answer_id'];
	if(is_array($answer_id))
	{
		if(count($answer_id) > 0)
		{
			foreach($answer_id as $key=>$val)
			{
				$answer_id = addslashes($val);
				$score = $picoEdu->getTextScore($answer_id, true);
				$score_str = addslashes(json_encode($score));
				$sql = "update `edu_answer` set `competence_score` = '$score_str' where `answer_id` = '$answer_id' and `school_id` = '$school_id' ";
				$database->executeUpdate($sql);
			}
		}
	}
	header("Location: ".$_SERVER['REQUEST_URI']);
}

if(@$_GET['option']=='export' && isset($_GET['test_id']))
{
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$bc_array = $picoEdu->getBasicCompetence($test_id);

	$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_test`.* $nt, 
	(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher_id`,
	(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`) as `koleksi_question`
	from `edu_test` 
	where 1
	and `edu_test`.`test_id` = '$test_id' and `edu_test`.`school_id` = '$school_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$assessment_methods = $data['assessment_methods'];

		header("Content-Type: application/vnd.xls");
		header("Content-Disposition: attachment; filename=\"".str_replace(" ", "-", strtolower($data['name'])).".xls\"");

echo '
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
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
<table width="100%" border="0" class="two-side-table" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="2">Ujian</td>
    <td width="1164"><?php echo $data['name'];?></td>
  </tr>
  <tr>
    <td colspan="2">Kelas</td>
    <td><?php $class = $picoEdu->textClass($array_class, $data['class']);  echo $class;?></td>
  </tr>
  <tr>
    <td colspan="2">Mata Pelajaran</td>
    <td><?php echo $data['subject'];?></td>
  </tr>
  <tr>
    <td colspan="2">Guru</td>
    <td><?php echo $data['teacher_id'];?></td>
  </tr>
  <tr>
    <td colspan="2">Metode Penilaian</td>
    <td><?php if($data['assessment_methods'] == 'H') echo "Nilai Tertinggi"; if($data['assessment_methods'] == 'N') echo "Nilai Terbaru";?></td>
  </tr>
  <tr>
    <td colspan="2">Jumlah Soal</td>
    <td><?php echo $data['number_of_question'];?></td>
  </tr>
  <tr>
    <td colspan="2">Nilai Standard</td>
    <td><?php echo $data['standard_score'];?></td>
  </tr>
  <tr>
    <td colspan="2">Penalti</td>
    <td><?php echo $data['penalty'];?></td>
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
	$sql_filter .= " and `edu_student`.`class_id` = '$class_id' ";
}

$threshold = $data['threshold'];
if(isset($_GET['expand']))
{
	$sql = "SELECT `edu_answer`.* , `edu_answer`.`student_id` as `student_id`, `edu_student`.`reg_number`,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) as `timediff` ,
	(select `edu_test`.`number_of_question` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `number_of_question`,
	((select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) as `lewat`,
	(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` and `edu_class`.`school_id` = `edu_student`.`school_id`) as `class`,
	`edu_student`.`name` as `student_name`, `edu_student`.`class_id`

	from `edu_answer` 
	left join(`edu_student`) on(`edu_student`.`student_id` = `edu_answer`.`student_id`)
	left join (`edu_question`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	where  `edu_answer`.`test_id` = '$test_id' $sql_filter
	group by `edu_answer`.`answer_id` 
	order by `edu_student`.`class_id`, `edu_answer`.`student_id` asc, `edu_answer`.`start` asc ";
}
else
{
	if($assessment_methods == 'N')
	{
		$grp = " order by `edu_answer`.`start` desc ";
	}
	else
	{
		$grp = " order by `edu_answer`.`percent` desc ";
	}


	$sql = "select * from (select 
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
	`edu_answer`.`competence_score`,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) as `timediff` , `edu_student`.`reg_number`,
	(select `edu_test`.`number_of_question` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `number_of_question`,
	(select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `waktu_tersedia`,
	((select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) as `lewat`,
	(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` and `edu_class`.`school_id` = `edu_student`.`school_id`) as `class`,
	`edu_student`.`name` as `student_name`, `edu_student`.`class_id`
	from `edu_answer` 
	left join(`edu_student`) on(`edu_student`.`student_id` = `edu_answer`.`student_id`)
	left join (`edu_question`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	where  `edu_answer`.`test_id` = '$test_id' $sql_filter
	group by `edu_answer`.`answer_id` 
	$grp ) as `inv` group by concat(`inv`.`test_id`, '-', `inv`.`student_id`) 
	order by `inv`.`class_id`, `inv`.`student_id` asc, `inv`.`start` asc ";
	
}
$ke = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$array_class = $picoEdu->getArrayClass($school_id);
?>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table">
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
      <?php
	  if($bc_array != null)
	  {
		  foreach($bc_array as $k=>$v)
		  {
			  ?>
              <td>KD <?php echo $v;?></td>
              <?php
		  }
	  }
	  ?>
    </tr>
	</thead>
	<tbody>
	<?php
	$i=0;
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <tr class="row-data<?php if($data['lewat']) echo ' data-error';?>">
      <td align="right"><?php echo $no;?></td>
      <td><?php echo $data['reg_number'];?></td>
      <td><?php echo $data['student_name'];?></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td align="right"><?php echo $ke[$data['student_id']];?></td>
      <td nowrap><?php echo date('Y-m-d H:i:s',strtotime($data['start']));?></td>
      <td nowrap><?php echo $data['timediff'];?></td>
      <td align="right"><?php echo $data['true'];?></td>
      <td align="right"><?php echo $data['false'];?></td>
      <td align="right"><?php echo $picoEdu->numberFormatTrans($data['final_score'], true);?></td>
      <td align="right"><?php echo $picoEdu->numberFormatTrans($data['percent'], true);?></td>
      <td align="right"><?php echo $picoEdu->numberFormatTrans($threshold, true);?></td>
      <td><?php if($data['percent'] >= $threshold) echo 'Ya'; else echo 'Tidak'?></td>
      <?php
	  if($bc_array != null)
	  {
			if(strlen($data['competence_score']) > 5)
			{
				$bc_score = $picoEdu->changeIndexScore(json_decode($data['competence_score'], true));
			}
			else
			{
				$bc_score = $picoEdu->getTextScore($data['answer_id'], true);
				$sql = "update `edu_answer` 
				set `competence_score` = '".addslashes(json_encode($bc_score))."' 
				where `answer_id` = '".$data['answer_id']."' and `student_id` = '".$data['student_id']."' 
				";
				$bc_score = $picoEdu->changeIndexScore($bc_score);
				$database->executeUpdate($sql);
			}
		  foreach($bc_array as $k=>$v)
		  {
			  if(isset($bc_score[$k][1]))
			  {
				  $score = $picoEdu->numberFormatTrans($bc_score[$k][1], true);
			  }
			  else
			  {
				  $score = "";
			  }
			  ?>
              <td><?php echo $score;?></td>
              <?php
		  }
	  }
	  ?>
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
else if(@$_GET['option']=='answerdetail' && isset($_GET['answer_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$array_class = $picoEdu->getArrayClass($school_id);
$answer_id = kh_filter_input(INPUT_GET, 'answer_id', FILTER_SANITIZE_NUMBER_UINT);
$sql = "SELECT `edu_test`.*, `edu_answer`.*, 
timediff(`edu_answer`.`end`,`edu_answer`.`start`) as `duration_test` ,
(select `edu_student`.`name` from `edu_student` where `edu_student`.`student_id` = `edu_answer`.`student_id`) as `student_name`
from `edu_test`
left join (`edu_answer`) on (`edu_answer`.`test_id` = `edu_test`.`test_id`)
where `edu_answer`.`answer_id` = '$answer_id' ";

$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$info = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="10%">NIS</td>
    <td width="15%"><?php echo $info['student_id'];?></td>
    <td width="10%">Metode Penilaian</td>
    <td width="15%"><?php if($info['assessment_methods'] == 'H') echo 'Nilai Tertinggi'; if($info['assessment_methods'] == 'N') echo 'Nilai Terbaru';?></td>
    <td width="10%">Dibuka</td>
    <td width="15%"><?php if($info['available_from'] != '0000-00-00 00:00:00' && $info['available_from'] != '') echo translateDate(date('j M Y H:i', strtotime($info['available_from']))); else echo '-';?></td>
    <td width="10%">Benar</td>
    <td width="15%"><?php echo $info['true'];?></td>
  </tr>
  <tr>
    <td>Nama Siswa</td>
    <td><?php echo $info['student_name'];?></td>
    <td>Jumlah Soal</td>
    <td><?php echo $info['number_of_question'];?></td>
    <td>Ditutup</td>
    <td><?php if($info['available_to'] != '0000-00-00 00:00:00' && $info['available_to'] != '') echo translateDate(date('j M Y H:i', strtotime($info['available_to']))); else echo '-';?></td>
    <td>Salah</td>
    <td><?php echo $info['false'];?></td>
  </tr>
  <tr>
    <td>Kelas</td>
    <td><?php $class = $picoEdu->textClass($array_class, $info['class']); $class_sort = $picoEdu->textClass($array_class, $info['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
    <td>Sekor Benar</td>
    <td><?php echo $info['standard_score'];?></td>
    <td>Pengumuman Hasil</td>
    <td><?php if($info['publish_answer']) echo translateDate(date('j M Y H:i', strtotime($info['time_answer_publication']))); else echo '-';?></td>
    <td>Nilai Awal</td>
    <td><?php echo $info['initial_score'];?></td>
  </tr>
  <tr>
    <td>Ujian</td>
    <td><?php echo $info['name'];?></td>
    <td>Penalti</td>
    <td><?php echo $info['penalty'];?></td>
    <td>Tanggal Ujian</td>
    <td><?php echo translateDate(date('j M Y H:i', strtotime($info['start'])));?></td>
    <td>Nilai Akhir</td>
    <td><?php echo $info['final_score'];?></td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $info['subject'];?></td>
    <td>Waktu Tersedia</td>
    <td><?php echo implode(':', $picoEdu->secondsToTime($info['duration']));?></td>
    <td>Durasi Pengerjaan</td>
    <td><?php echo $info['duration_test'];?></td>
    <td>Persen</td>
    <td><?php echo $picoEdu->numberFormatTrans($info['percent']);?></td>
  </tr>
</table>
</div>
<?php
$random_distribution = array();
if(strlen($info['random_distribution']) > 5)
{
	$bca = json_decode($info['random_distribution'], true);
	$bc_array = array();
	foreach($bca as $key=>$val)
	{
		$bc = str_replace("bc", "", $key);
		$bc = str_replace("_", ".", $bc);
		$arr = explode(".", $bc, 2);
		$major = @$arr[0]*1;
		$minor = str_replace(".", "", @$arr[1]) * 1;
		$idx = $major * 100 + $minor;  
		$bc_array[$idx] = array($bc, $val);
	}
	ksort($bc_array);
	foreach($bc_array as $key=>$val)
	{
		$random_distribution[] = array($val[0], $val[1]);
	}
}
else
{
	$bc_array = array();
	$sql = "SELECT `edu_question`.`basic_competence`, count(distinct `edu_question`.`question_id`) as `num_question`
	from `edu_question`
	where `edu_question`.`test_id` = '$test_id' 
	group by `edu_question`.`basic_competence`
	";
	$stmtx = $database->executeQuery($sql);
	if ($stmtx->rowCount() > 0) {
		$rowsx = $stmtx->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rowsx as $dt) {
			$bc = $dt['basic_competence'];
			if (strlen($bc) > 0) {
				$arr = explode(".", $bc, 2);
				$major = @$arr[0] * 1;
				$minor = str_replace(".", "", @$arr[1]) * 1;
				$idx = $major * 100 + $minor;
				$bc_array[$idx] = $dt;
			}
		}
	}
	ksort($bc_array);
	foreach($bc_array as $key=>$val)
	{
		$random_distribution[] = array($val['basic_competence'], $val['num_question']);
	}
	
}
if(strlen($info['competence_score']) > 5)
{
	$bc_score = json_decode($info['competence_score'], true);
}
else
{
	$bc_score = $picoEdu->getTextScore($answer_id, true);
	if(isset($data['answer_id']) && isset($data['student_id']))
	{
		$sql = "update `edu_answer` 
		set `competence_score` = '".addslashes(json_encode($bc_score))."' 
		where `answer_id` = '".$data['answer_id']."' and `student_id` = '".$data['student_id']."' 
		";
		$database->executeUpdate($sql);
	}
}
if(count($bc_score))
{
$bc_score_value = array();
foreach($bc_score as $key=>$val)
{
	$bc_score_value[$val[0]] = $val[1];
}
	?>
<div class="basic-competence-score">
    <table border="1" cellpadding="0" cellspacing="0">
    <thead>
    	<tr>
        <td>Kompetensi Dasar</td>
        <?php
		foreach($random_distribution as $key=>$value)
		{
			?>
            <td align="right"><?php echo $value[0];?></td>
            <?php
		}
		?>
        </tr>
     </thead>
     <tbody>   
    	<tr>
        <td>Jumlah Soal</td>
        <?php
		foreach($random_distribution as $key=>$value)
		{
			?>
            <td align="right"><?php echo $value[1];?></td>
            <?php
		}
		?>
        </tr>
    	<tr>
        <td>Nilai</td>
        <?php
		foreach($random_distribution as $key=>$value)
		{
			$score = @$bc_score_value[$value[0]] * 1;
			?>
            <td align="right"><?php echo $picoEdu->numberFormatTrans($score, true);?></td>
            <?php
		}
		?>
        </tr>
   	</tbody>
    </table>
</div>
    <?php
}
?>

<?php
$sql = "SELECT `edu_question`.* , `edu_answer`.`answer` as `answer` , instr(`edu_answer`.`answer`,`edu_question`.`question_id`) as `pos`
from `edu_question` 
left join (`edu_answer`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
left join (`edu_test`) on (`edu_test`.`test_id` = `edu_question`.`test_id`)
where `edu_answer`.`answer_id` = '$answer_id' 
group by `edu_question`.`question_id` 
order by `pos` asc ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
<ol class="test-question">
<?php
$i=0;
$no = $pagination->offset;
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $data)
{
$j=$i%2;
$no++;
$qid = $data['question_id'];
$answer = $data['answer'];
?>
<li value="<?php echo $no;?>">
<div class="kd-ctrl"><a href="javascript:;"><span><?php echo $data['basic_competence'];?></span></a></div>
<div class="question">
<?php echo $data['content'];?>
<?php
$sql2 = "SELECT `edu_option`.* , '$answer' like concat('%,',`edu_option`.`option_id`,']%') as `my_answer`
from `edu_option` 
where  `edu_option`.`question_id` = '$qid' group by  `edu_option`.`option_id` order by  `edu_option`.`order` asc";
$stmt2 = $database->executeQuery($sql2);
if($stmt2->rowCount() > 0)
{
?>
<div class="option">
<ol class="listoption" style="list-style-type:<?php echo $data['numbering'];?>">
<?php
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach($rows2 as $data2)
{
?>
<li>
<span class="option-circle<?php if($data2['score']) echo ' option-circle-selected';?>"><?php
        echo $data2['score']*1;
        ?></span>
<div class="list-option-item<?php echo ($data2['my_answer'])?' list-option-item-selected':'';?>">
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
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
}
else if(@$_GET['option']=='detail' && isset($_GET['test_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$q = kh_filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING_NEW);
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
$pagination->array_get[] = 'option';
$pagination->array_get[] = 'test_id';
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
		var url = '<?php echo basename($_SERVER['PHP_SELF']);?>?option=export&test_id='+test_id+'&class_id='+class_id;
		window.open(url);
	});
    $(document).on('click', '#ekspor2', function(e){
		var test_id = '<?php echo $test_id;?>';
		var class_id = $('#class_id').val();
		var q = $('#q').val();
		var url = '<?php echo basename($_SERVER['PHP_SELF']);?>?option=export&expand=1&test_id='+test_id+'&class_id='+class_id;
		window.open(url);
	});
}
</script>

<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Kelas</span> 
    <select id="class_id" name="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
    $sql2 = "select * from `edu_class` where `active` = '1' and `school_id` = '$school_id' order by `order` asc ";
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
    <input type="hidden" name="option" value="detail" /> 
    <input type="hidden" name="test_id" value="<?php echo $test_id;?>" /> 
    <span class="search-label">Siswa</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode(stripslashes(trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="com-button" />
  <input type="button" name="ekspor1" id="ekspor1" value="Ekspor Persiswa" class="com-button" />
  <input type="button" name="ekspor2" id="ekspor2" value="Ekspor Perujian" class="com-button" />
</form>
</div>
<div class="search-result">

<?php
$q1 = basename($_SERVER['PHP_SELF'])."?option=detail&test_id=$test_id&expand=1";
$q2 = basename($_SERVER['PHP_SELF'])."?option=detail&test_id=$test_id";
$nt ='';
$sql = "SELECT `edu_test`.* $nt
from `edu_test` 
where (`edu_test`.`active` = '1' or `edu_test`.`active` = '0')
and `edu_test`.`test_id` = '$test_id'
";
$threshold = 0;
$assessment_methods = '';
$stmtx = $database->executeQuery($sql);
if ($stmtx->rowCount() > 0) {
	$data = $stmtx->fetch(PDO::FETCH_ASSOC);
	$threshold = $data['threshold'];
	$assessment_methods = $data['assessment_methods'];
}

$pagination->array_get = array();
$pagination->array_get[] = 'class_id';
$pagination->array_get[] = 'option';
$pagination->array_get[] = 'test_id';


$sql_filter = "";
if($class_id != "")
{
	$sql_filter .= " and `edu_student`.`class_id` like '$class_id' ";
}
if($q != "")
{
	$sql_filter .= " and `edu_student`.`name` like '%$q%' ";
}

?>

<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<div class="horizontal-bar">
Ujian: <?php echo $data['name'];?>;<?php if($data['subject']!=''){?> Mata Pelajaran: <?php echo $data['subject'];?>;<?php } if($data['class'] != ''){?> Kelas: <?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a>;<?php }?> Soal: <?php echo $data['number_of_question'];?>; Durasi: <?php echo implode(':', $picoEdu->secondsToTime($data['duration']));?>
</div>

<?php
$nt = '';

if(isset($_GET['expand']))
{
	$sql = "SELECT `edu_answer`.* , `edu_answer`.`student_id` as `student_id`, `edu_student`.`reg_number`,
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) as `timediff` ,
	(select `edu_test`.`number_of_question` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `number_of_question`,
	((select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) as `lewat`,
	(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` and `edu_class`.`school_id` = `edu_student`.`school_id`) as `class`,
	`edu_student`.`name` as `student_name`, `edu_student`.`class_id` 

	from `edu_answer` 
	left join(`edu_student`) on(`edu_student`.`student_id` = `edu_answer`.`student_id`)
	left join (`edu_question`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	where  `edu_answer`.`test_id` = '$test_id' $sql_filter
	group by `edu_answer`.`answer_id` having 1 $sql_filter
	order by `edu_student`.`class_id`, `edu_answer`.`student_id` asc, `edu_answer`.`start` asc ";
}
else
{
	if($assessment_methods == 'N')
	{
		$grp = " order by `edu_answer`.`start` desc ";
	}
	else
	{
		$grp = " order by `edu_answer`.`percent` desc ";
	}


	$sql = "select * from (select 
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
	timediff(`edu_answer`.`end`,`edu_answer`.`start`) as `timediff` , `edu_student`.`reg_number`,
	(select `edu_test`.`number_of_question` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `number_of_question`,
	(select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_question`.`test_id`) as `waktu_tersedia`,
	((select `edu_test`.`duration` from `edu_test` where `edu_test`.`test_id` = `edu_answer`.`test_id`) - (UNIX_TIMESTAMP(`edu_answer`.`end`)-UNIX_TIMESTAMP(`edu_answer`.`start`))<0) as `lewat`,
	(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` and `edu_class`.`school_id` = `edu_student`.`school_id`) as `class`,
	`edu_student`.`name` as `student_name`, `edu_student`.`class_id` as `class_id`, `edu_student`.`time_edit` as `time_edit`
	from `edu_answer` 
	left join(`edu_student`) on(`edu_student`.`student_id` = `edu_answer`.`student_id`)
	left join (`edu_question`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
	where  `edu_answer`.`test_id` = '$test_id' $sql_filter
	group by `edu_answer`.`answer_id` having 1 $sql_filter
	$grp ) as `inv` group by concat(`inv`.`test_id`, '-', `inv`.`student_id`) 
	order by `inv`.`class_id`, `inv`.`student_id` asc, `inv`.`start` asc ";
	
}
$ke = array();
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
$cls = (@$obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
?>
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(3), .hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(12), .hide-some-cell tr td:nth-child(14), .hide-some-cell tr td:nth-child(15), .hide-some-cell tr td:nth-child(16){
		display:none;
	}
}
</style>
<form name="form1" method="post" action="" enctype="multipart/form-data">

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-answer_id" data-target=".answer_id" class="checkbox-selector" value="1"></td>
      <td width="25">No</td>
      <td>NIS</td>
      <td>Nama Siswa </td>
      <td>Kelas</td>
      <td width="20">Ke</td>
      <td width="130">Mulai</td>
      <td width="60">Lama</td>
      <td width="40" align="right">Soal </td>
      <td width="50" align="right">Benar</td>
      <td width="50" align="right">Salah </td>
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
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <tr class="row-data<?php if($data['lewat']) echo ' data-error';?>">
      <td><input type="checkbox" name="answer_id[]" id="answer_id" value="<?php echo $data['answer_id'];?>" class="answer_id" /></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=answerdetail&answer_id=<?php echo $data['answer_id'];?>"><?php echo $data['reg_number'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=answerdetail&answer_id=<?php echo $data['answer_id'];?>"><?php echo $data['student_name'];?></a></td>
      <td><?php echo $data['class'];?></td>
      <td align="right"><?php echo $ke[$data['student_id']];?></td>
      <td nowrap><?php echo translateDate(date('d M Y H:i:s',strtotime($data['start'])));?></td>
      <td nowrap><?php echo $data['timediff'];?></td>
      <td align="right"><?php echo $data['number_of_question'];?></td>
      <td align="right"><?php echo $data['true'];?></td>
      <td align="right"><?php echo $data['false'];?></td>
      <td align="right"><?php echo $data['final_score'];?></td>
      <td align="right"><?php echo $picoEdu->numberFormatTrans($data['percent'], true);?></td>
      <td align="right"><?php echo $picoEdu->numberFormatTrans($threshold, true);?></td>
      <td><?php if($data['percent'] >= $threshold) echo 'Ya'; else echo 'Tidak';?></td>
      <td><?php echo (@$data['active']==1)?'Ya':'Tidak';?></td>
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

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktif" class="com-button" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktif" class="com-button" />
  <input type="submit" name="recalculation" id="recalculation" value="Hitung Ulang KD" class="com-button" onclick="return confirm('Perhitungan ulang nilai kompetensi dasar hanya dapat dilakukan jika soal ujian belum dihapus. Apakah Anda yakin untuk menghitung ulang nilai kompetensi dasar?');"/>
  <input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Apakah Anda yakin untuk menghapus data ini?');" />
	<?php
    if(!isset($_GET['expand'])){
    ?>
    <input type="button" name="show-all" id="show-all" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo $q1;?>'" />
    <?php
    }
    else
    {
    if($assessment_methods == 'N')
    {
    ?>
    <input type="button" name="show-newest" id="show-newest" value="Tampilkan Nilai Terbaru" class="com-button" onclick="window.location='<?php echo $q2;?>'" />
    <?php
    }
    else
    {
    ?>
    <input type="button" name="show-highest" id="show-highest" value="Tampilkan Nilai Tertinggi" class="com-button" onclick="window.location='<?php echo $q2;?>'" />
    <?php
    }
	}
    ?>
</div>
</form>
<?php
}
else if(strlen(@$_GET['q']))
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi lagi dengan kata kunci yang lain.</div>
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
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);

?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Kelas</span> 
    <select id="class_id" name="class_id">
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
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode(stripslashes(trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="com-button" />
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
$pagination->array_get = array();

if($class_id != ''){
$pagination->array_get[] = 'class_id';
$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
}
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_test`.`name` like '%".addslashes($pagination->query)."%' )";
}

$sql = "SELECT `edu_test`.*,
(select count(distinct `edu_answer`.`student_id`) from `edu_answer` where `edu_answer`.`test_id` = `edu_test`.`test_id`) as `number_of_student`,
(select `edu_answer`.`start` from `edu_answer` where `edu_answer`.`test_id` = `edu_test`.`test_id` order by `edu_answer`.`start` desc limit 0,1) as `last_test`
from `edu_test`
where `edu_test`.`school_id` = '$school_id' $sql_filter
having 1 and `number_of_student` > 0
order by `last_test` desc, `edu_test`.`test_id` desc
";
$sql_test = "SELECT `edu_test`.*,
(select count(distinct `edu_answer`.`student_id`) from `edu_answer` where `edu_answer`.`test_id` = `edu_test`.`test_id`) as `number_of_student`
from `edu_test`
where `edu_test`.`school_id` = '$school_id' $sql_filter
having 1 and `number_of_student` > 0
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
$cls = (@$obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="" enctype="multipart/form-data">

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

<form name="rowform" method="post" action="">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
  <thead>
    <tr>
        <td width="16"><img src="lib.tools/images/excel.png" /></td>
        <td width="16"><img src="lib.tools/images/excel.png" /></td>
        <td width="25">No</td>
        <td>Ujian</td>
        <td>Kelas</td>
        <td>Terakhir</td>
        <td width="50" align="right">Soal</td>
        <td width="70" align="right">Peserta</td>
      </tr>
	</thead>
	<tbody>
	<?php
	$i=0;
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$j=$i%2;
	$no++;
	?>
    <tr class="row-data row<?php echo $j;?>">
        <td width="16"><a title="Per Ujian" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=export&test_id=<?php echo $data['test_id'];?>&expand=1"><img src="lib.tools/images/excel.png" /></a></td>
        <td width="16"><a title="Per Siswa" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=export&test_id=<?php echo $data['test_id'];?>"><img src="lib.tools/images/excel.png" /></a></td>
        <td align="right"><?php echo $no;?></td>
        <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
        <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
        <td><?php echo translateDate(date('d M Y H:i', strtotime($data['last_test'])));?></td>
        <td align="right"><?php echo $data['number_of_question'];?></td>
        <td align="right"><?php echo $data['number_of_student'];?></td>
      </tr>
	<?php
	$i++;
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
else if(strlen(@$_GET['q']))
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi lagi dengan kata kunci yang lain.</div>
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