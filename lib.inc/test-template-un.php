<?php
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/lib.assets/theme/default/css/images/favicon.png" />
<title><?php echo $data['name'];?> - <?php echo $cfg->app_name;?></title>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-un.min.css" />

<script>
	let testId = '<?php echo $test_id;?>';
	let websocketURL = '<?php echo $picoEdu->getWebsocketHost();?>/?module=test&test_id='+testId;
</script>
<script src="../lib.assets/script/test-ws.js"></script>
<script src="../lib.assets/script/test-ws-student.js"></script>

<div class="modal fade" id="test-alert" tabindex="-1" role="dialog" aria-labelledby="test-alert-title" aria-hidden="true">
<div class="modal-dialog modal-md" role="document">
	<div class="modal-content">
	<div class="modal-header">
		<h5 class="modal-title" id="test-alert-title">Pesan Pengawas</h5>
	</div>
		<div class="modal-body">
		</div>
	</div>
</div>
</div>


</head>
<body>
<?php
$question_package = addslashes($question_package);
$sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) AS `sort_order`
FROM `edu_question`
where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
ORDER BY `sort_order` ASC
";
$stmt = $database->executeQuery($sql);
$number_of_question = $stmt->rowCount();
$no_halaman_awal = 0;
$no_halaman_akhir = 0;

if($number_of_question)
{
	$offset_maksimum = floor($number_of_question/$question_per_page);
	$jumlah_halaman = floor($number_of_question/$question_per_page);
	if($offset_maksimum == $number_of_question/$question_per_page)
	{
		$offset_maksimum = ($number_of_question/$question_per_page) - 1;
	}
	$question_per_page = $question_per_page * 1;
	
	$sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) AS `sort_order`
	FROM `edu_question`
	where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
	ORDER BY `sort_order`
	";
	$stmt1 = $database->executeQuery($sql);
	$question_set = array();
	$questions = array();
	if ($stmt1->rowCount() > 0) {
		$rows1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
		foreach($rows1 as $data1)
		{
			$soal = $data1['question_id'];
			$question_set[] = $soal;
			if ($data['random']) {
				$sql2 = "SELECT `edu_option`.* , rand() AS `rand`
				FROM `edu_option`
				WHERE `edu_option`.`question_id` = '$soal'
				ORDER BY `rand` ASC
				";
			} else {
				$sql2 = "SELECT `edu_option`.* , rand() AS `rand`
				FROM `edu_option`
				WHERE `edu_option`.`question_id` = '$soal'
				ORDER BY `sort_order` ASC
				";
			}
			$options = array();
			$stmt2 = $database->executeQuery($sql2);
			if ($stmt2->rowCount() > 0) {
				$rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
					$answer = @$_SESSION['answer_tmp'][$student_id][$test_id]['answer_' . $data2['question_id']];
					$option = new StdClass();
					$option->option_id = $data2['option_id'];
					$option->text = $data2['content'];
					$options[] = $option;
				}
			}
			$question = new StdClass();
			$question->question_id = $data1['question_id'];
			$question->text = $data1['content'];
			$question->numbering = $data1['numbering'];
			$question->random = $data1['random'];
			$question->options = $options;
			$questions[] = $question;
		}
	}
}
$storage_key = md5($student_id."-".$test_id."|".implode(",",$question_set)); 
?>
<script type="text/javascript">
var questionData = <?php echo json_encode($questions);?>;
var questionSet = <?php echo json_encode($question_set);?>;
var storageKey = '<?php echo $storage_key;?>'; 
var alert_time = <?php echo $alert_time;?>;
var test = '<?php echo $test_id;?>';
var autosubmit = <?php echo $autosubmit*1;?>;
var due_time = <?php echo @$_SESSION['session_test'][$student_id][$test_id]['due_time']-time();?>;
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/test-un.js"></script>
<div class="all">
<div class="header">
<h3><?php echo $data['name'];?> <?php echo ($data['subject'])?(" : ".$data['subject']):"";?></h3>
</div>
<div class="container">
<div class="test-question-control">
    <div class="before">
    <a href="#">&nbsp;</a>
    </div>
    <div class="number-list-container">
        <ul class="question-number">
        </ul>
    </div>
    <div><strong>Keterangan Warna</strong></div>
    <div><span class="legend-color legend-answered"></span> Sudah dijawab</div>
    <div><span class="legend-color legend-unanswered"></span> Belum dijawab</div>
    <div><span class="legend-color legend-doubt"></span> Ragu-ragu</div>
    <div><span class="legend-color legend-unread"></span> Belum dilihat</div>
</div>
<div class="current-number-placeholder"><div class="current-number">1</div></div>
<div class="timer-placeholder"><div class="timer">00:00</div></div>
<div class="alert-placeholder" data-hidden="true"><div class="alert"><div><a href="#" class="alert-closer">??</a><?php echo $alert_message;?></div></div></div>
<div class="main-container">
    <div class="question-area">
    </div>
    <div class="navigation-bottom">
    	<input type="button" class="button" id="prev" value="&laquo; Sebelumnya">
        <input type="button" class="button warning" id="doubt" value="&#9635; &#9634; Ragu-Ragu">
    	<input type="button" class="button" id="next" value="Berikutnya &raquo;">
    	<input type="button" class="button" id="submit" value="Kirim Jawaban">
    </div>
</div>
</div>
</div>
<form name="testfrm" id="testfrm" method="post" action="<?php echo $cfg->base_url."siswa/ujian/";?>" enctype="multipart/form-data">
<?php
$start = @$_SESSION['session_test'][$student_id][$test_id]['start'];
if($start == '' || $start == '0000-00-00 00:00:00')
{
	$start = $database->getLocalDateTime();
	$_SESSION['session_test'][$student_id][$test_id]['start'] = $start;
}
foreach($question_set as $idx=>$question_id)
{
?>
<input type="hidden" id="answer_<?php echo $question_id;?>" name="answer_<?php echo $question_id;?>" value="0">
<?php
}
?>
<input type="hidden" name="submit_test" value="Kirim">
<input type="hidden" name="time_start" value="<?php echo $start;?>">
<input type="hidden" name="test_id" value="<?php echo $test_id;?>">
</form>
</body>
</html>
