<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-siswa.php";
require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/mobile-detector.php";
$auth_student_id = @$auth_student_id . '';
$class_id = @$class_id . '';
$test_id = addslashes((@$_GET['test_id']));
if(empty($test_id))
{
	$test_id = addslashes((@$_POST['test_id']));
}
$offset = addslashes(abs(@$_GET['offset']));

if(empty(@$auth_student_id) || empty(@$auth_school_id))
{
	exit();
}
	
if(@$_GET['option'] == 'login')
{
	header("Location: ".$cfg->base_url."siswa/ujian/index.php?confirm-login-to-test=yes&test_id=$test_id"); //NOSONAR
	exit();
}

if(isset($_POST['save']) || strlen(@$_POST['submit_test']))
{
	$start = addslashes(@$_SESSION['session_test'][$auth_student_id][$test_id]['start']);
	if($start == '' || $start == '0000-00-00 00:00:00')
	{
		$start = kh_filter_input(INPUT_POST, "time_start", FILTER_SANITIZE_STRING_NEW);
	}
	$end = date('Y-m-d H:i:s'); //NOSONAR
	
	$sql = "SELECT * FROM `edu_test` WHERE `test_id` = '$test_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$test_id_name = $data['name'];
		if ($data['standard_score'] == 0) {
			$data['standard_score'] = 1;
		}

		foreach ($_POST as $key => $val) {
			if (stripos($key, 'answer_') === 0) {
				$field = addslashes($key);
				$value = addslashes($val);
				$_SESSION['answer_tmp'][$auth_student_id][$test_id][$field] = $value;
			}
		}

		// check score dari answer
		$arr = $_SESSION['answer_tmp'][$auth_student_id][$test_id];
		$answer_arr = array();

		$true = 0;
		$false = 0;
		$score = 0;
		$jumlah_soal = 0;

		foreach ($arr as $key => $value) {
			$soal = addslashes(substr($key, strlen('answer_')));
			$answer = addslashes($value);
			$answer_arr[] = "[" . $soal . "," . $answer . "]";

			$sql = "SELECT * FROM `edu_option` WHERE `option_id` = '$answer' ";
			$stmt2 = $database->executeQuery($sql);
			if($stmt2->rowCount() > 0)
			{
				$dt = $stmt2->fetch(\PDO::FETCH_ASSOC);
				if ($dt['score'] > 0) {
					$true++;
					$score += $dt['score'];
				} else {
					$false++;
				}
			}
			else
			{
				$false++;
			}

			
			$jumlah_soal++;
		}
		if ($jumlah_soal == 0) {
			$jumlah_soal = 1;
		}
		$str_soal = @$_SESSION['session_test'][$auth_student_id][$test_id]['soal'];
		$str_soal = trim(str_replace(array('[', ']'), array('', ','), $str_soal), ',');

		$penalty = $false * $data['penalty'];
		$final_score = $score - $penalty;
		$percent = 100 * ($final_score / ($jumlah_soal * $data['standard_score']));
		$proses = false;
		$answer_str = addslashes(implode(",", $answer_arr)); // catatan answer
		if ($data['has_limits']) {
			$sql = "SELECT * FROM `edu_answer` WHERE `student_id` = '$auth_student_id' AND `test_id` = '$test_id' ";
			$stmt2 = $database->executeQuery($sql);
			$nujian = $stmt2->rowCount();
			if ($nujian < $data['trial_limits']) {
				$proses = true;
			} else {
				$proses = false;
			}
		} else {
			$proses = true;
		}
		$question_set = str_replace(array('[', ']'), array(',', ','), @$_SESSION['session_test'][$auth_student_id][$test_id]['soal']);
		$question_set = trim(str_replace(",,", ",", $question_set), ",");
		$storage_key = md5($auth_student_id . "-" . $test_id . "|" . $question_set);
		$storage_key;


		if ($proses) {
			$_SESSION['session_test'][$auth_student_id][$test_id] = null;
			unset($_SESSION['session_test'][$auth_student_id][$test_id]);
			$_SESSION['answer_tmp'][$auth_student_id][$test_id] = null;
			unset($_SESSION['answer_tmp'][$auth_student_id][$test_id]);
			$_SESSION['session_test'] = array();

			// simpan di tabel answer
			$competence_score = addslashes(json_encode($picoEdu->getTextScoreFromString($answer_str, true)));
			if($competence_score == null || $competence_score = 'null')
			{
				$competence_score = "{}";
			}

			$answer_id = $database->generateNewId();

			$sql = "INSERT INTO `edu_answer` 
			(`answer_id`, `school_id`, `test_id`, `student_id`, `start`, `end`, `answer`, `competence_score`, 
			`true`, `false`, `initial_score`, `penalty`, `final_score`, `percent`, `active`) VALUES
			('$answer_id', '$school_id', '$test_id', '$auth_student_id', '$start', '$end', '$answer_str', '$competence_score', 
			'$true', '$false', '$score', '$penalty', '$final_score', '$percent', '1') ";
			$stmt = $database->executeInsert($sql, true);

			$picoEdu->logoutTest($school_id, $auth_student_id, $test_id, session_id(), date('Y-m-d H:i:s'), addslashes($_SERVER['REMOTE_ADDR']));
			
			
			require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
			
			?>
			<div class="alert alert-success">Jawaban berhasil dikirim.</div>
			<script type="text/javascript">
			var test = '<?php echo $test_id; ?>';
			window.localStorage.removeItem('<?php echo $storage_key; ?>-answer-set');
			window.localStorage.removeItem('<?php echo $storage_key; ?>-current-index');
			window.localStorage.removeItem('jwb_'+test);
			window.location = '<?php echo $cfg->base_url . "siswa/ujian/" . $picoEdu->gateBaseSelfName(); ?>?option=sent&test_id=<?php echo $test_id; ?>';
			</script>
			<?php
			require_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
			exit();
		}
	}
}

$sql_filter = "";
$sql_filter .= " 
	and (
	`edu_test`.`open` = '1'
	or (
		((`edu_test`.`class` = '' OR `edu_test`.`class` = '||')))
	or (concat(',',`edu_test`.`class`,',') like '%,$class_id,%')
	)
";

$now = date('Y-m-d H:i:s');

$sql = "SELECT `edu_test`.*  
FROM `edu_test` 
WHERE `edu_test`.`active` = true
AND `edu_test`.`test_id` = '$test_id' 
AND (`edu_test`.`test_availability` = 'F' OR `edu_test`.`available_to` >= '$now')
AND `edu_test`.`school_id` = '$school_id'
$sql_filter
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
$question_per_page = $data['question_per_page'];
$alert_message = $data['alert_message'];
$has_alert = $data['has_alert'];
$alert_time = $data['alert_time'];
$autosubmit = 0;
if(isset($data['autosubmit']))
{
	$autosubmit = $data['autosubmit'];
}
$curtime = date('Y-m-d H:is');
if($data['test_availability'] != 'F' && ($data['available_from'] > $curtime || $data['available_to'] < $curtime))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<blockquote>
<p>Anda tidak dapat bisa mengikuti test <strong><?php echo $data['name'];?></strong> karena tidak dalam masa ujian. <a href="../">Klik di sini untuk kembali</a>
</p>
</blockquote>
<?php	
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
$guidance_text = $data['guidance'];
$test_id = $data['test_id'];

if(@$_GET['option'] == 'sent')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-success">
<?php
$sql = "SELECT * FROM `edu_answer` WHERE `student_id` = '$auth_student_id' AND `test_id` = '$test_id' ORDER BY `start` DESC ";
$stmt = $database->executeQuery($sql);
$ntest = $stmt->rowCount();
if($ntest)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<p>Anda telah mengerjakan ujian <strong><?php echo $data['name'];?></strong>. <a href="ujian.php?option=history&test_id=<?php echo $test_id;?>">Klik di sini untuk kembali</a>
</p>
<script type="text/javascript">
window.localStorage.removeItem('jwb_<?php echo $test_id;?>');
</script>
<?php
}
else
{
?>
<p>Pengiriman jawaban gagal. <a href="javascipt:history.go(-1)">Klik di sini untuk kembali</a></p>
<?php
}
?>
</div>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
exit();
}

else if(@$_GET['confirm-login-to-test'] == 'yes' || @$_GET['option'] == 'login-to-test')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="label">
Informasi Singkat Ujian
</div>
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
    <td>Nama Ujian</td><td><?php echo $data['name'];?></td>
    </tr>
    <?php
	if($data['subject'])
	{
	?>
    <tr>
    <td>Mata Pelajaran</td><td><?php echo $data['subject'];?></td>
    </tr>
    <?php
	}
	?>
    <tr>
    <td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?></td>
    </tr>
    <tr>
    <td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?></td>
    </tr>
    <tr>
    <td>Nilai Standard</td>
    <td><?php echo $data['standard_score'];?></td>
    </tr>
    <tr>
    <td>Penalti
    </td><td><?php echo $data['penalty'];?></td>
    </tr>
    <tr>
    <td>Otomatis Kirim Jawaban</td>
    <td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?></td>
    </tr>
</table>
<div class="button-area">
<input type="button" value="Masuk Ke Ujian" onclick="window.location='<?php echo $cfg->base_url."/siswa/ujian/".$picoEdu->gateBaseSelfName()."?login-to-test=yes&test_id=".$data['test_id'];?>'">
<input type="button" value="Batal" onclick="window.location='<?php echo $cfg->base_url."/siswa/ujian.php";?>'">
</div>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
exit();
}

else if(@$_GET['option'] == 'limited')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-warning">
<p>Anda telah melaksanakan ujian sebanyak <strong><?php echo $ntest;?></strong> kali. Ujian terahir pada tanggal<strong> <?php echo translatedate(date('j F Y', strtotime($test_id_terakhir)));?></strong> jam <strong><?php echo date('H:i:s', strtotime($test_id_terakhir));?></strong>. <a href="../">Klik di sini untuk kembali</a>
</p>
</div>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
exit();
}

else if(@$_GET['login-to-test']=="yes")
{
	// /////////////////////////////////////////////////////////////////////////////
	// 
	// Begin sign in to test
	// 
	// /////////////////////////////////////////////////////////////////////////////
	
	// Only applied when $use_token is false or 0
	
	$auth_test = false;
	$dur_obj = $picoEdu->secondsToTime($data['duration']);
	if($data['has_limits'])
	{
		$sql = "SELECT * FROM `edu_answer` WHERE `student_id` = '$auth_student_id' AND `test_id` = '$test_id' ORDER BY `start` DESC ";
		$stmt = $database->executeQuery($sql);
		$ntest = $stmt->rowCount();
		if($ntest < $data['trial_limits'])
		{
			$proses = true;
			$auth_test = true;
		}
		else
		{
			$proses = false;
			$dt = $stmt->fetch(\PDO::FETCH_ASSOC);
			$test_id_terakhir = $dt['start'];
			header("Location: ".$picoEdu->gateBaseSelfName()."?option=limited");
		}
	}
	else
	{
		$proses = true;
	}
	
	if(!$use_token)
	{
		if($proses)
		{
			$question_package = @$_SESSION['session_test'][$auth_student_id][$test_id]['soal'];
			if(empty($question_package))
			{
				$number_of_question = $data['number_of_question'];
				$duration = $data['duration'];
				$question_per_page = $data['question_per_page'];
				$due_time = time()+$duration;
				$_SESSION['session_test'][$auth_student_id][$test_id]['start'] = date('Y-m-d H:i:s');
				$_SESSION['session_test'][$auth_student_id][$test_id]['due_time'] = $due_time;
				$alert_message = $data['alert_message'];
				
				if($data['random'])
				{	
					$sql = "SELECT `question_id` , rand() AS `rand`
					FROM `edu_question` WHERE `test_id` = '$test_id'
					ORDER BY `rand` ASC
					LIMIT 0, $number_of_question
					";
				}
				else
				{
					$sql = "SELECT `question_id` , `sort_order`
					FROM `edu_question` WHERE `test_id` = '$test_id'
					ORDER BY `sort_order` ASC, `question_id` ASC
					LIMIT 0, $number_of_question
					";
				}
				$stmt = $database->executeQuery($sql);
				$arr = array();
				if($stmt->rowCount() > 0)
				{
					$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
					foreach($rows as $dt)
					{
						$arr[] = $dt['question_id'];
					}
				}
				$question_package = $str = '['.implode('][', $arr).']';
				$_SESSION['session_test'][$auth_student_id][$test_id]['soal'] = $str;
				$picoEdu->loginTest($school_id, $auth_student_id, $test_id, session_id(), date('Y-m-d H:i:s'), addslashes($_SERVER['REMOTE_ADDR']));
				header("Location: ".$picoEdu->gateBaseSelfName()."?test_id=$test_id");
			}
			else
			{
				header("Location: ".$picoEdu->gateBaseSelfName()."?test_id=$test_id");
			}
		}
	}
	else
	{
		header("Location: ../ujian.php?option=enter-token");
	}
	
	// /////////////////////////////////////////////////////////////////////////////
	// 
	// End sign in to test
	// 
	// /////////////////////////////////////////////////////////////////////////////
	
}

}
}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-success">
<p>Ujian ini tidak tersedia untuk Anda. <a href="../ujian.php">Klik di sini untuk kembali</a></p>
</div>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
exit();
}

if(isset($_SESSION['session_test'][$auth_student_id][$test_id]))
{
	$session_id = session_id();

	$question_package = @$_SESSION['session_test'][$auth_student_id][$test_id]['soal'];
	

	if(@!$mobileBrowser)
	{
		require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/test-template-un.php";
	}
	else
	{
		require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/test-template-us.php";
	}
}
?>