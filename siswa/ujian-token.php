<?php
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$token_valid = 0;
$account_blocked = 0;
$token_data = array();
$step = 0;
$test_id = 0;
if(isset($_SESSION['vtoken']) && isset($_POST['enter_to_test']))
{
	$token = addslashes(@$_SESSION['vtoken']);
	$now = $database->getLocalDateTime();
	$sql = "SELECT `edu_token`.* , `edu_test`.*
	FROM `edu_token`
	INNER JOIN (`edu_test`) ON (`edu_test`.`test_id` = `edu_token`.`test_id`)
	WHERE `edu_token`.`student_id` = '$auth_student_id'
	AND `edu_token`.`token` = '$token' AND `edu_token`.`active` = true AND `edu_token`.`time_expire` > '$now'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$token_valid = 1;
		$token_data = $data;
		$test_id = $token_data['test_id'];

		// /////////////////////////////////////////////////////////////////////////////
		// 
		// Begin sign in to test
		// 
		// /////////////////////////////////////////////////////////////////////////////
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
				header("Location: ujian/index.php?test_id=$test_id");
			}
		}
		else
		{
			$proses = true;
		}
		
		if($proses)
		{
			$question_package = @$_SESSION['session_test'][$auth_student_id][$test_id]['soal'];
			if(empty($question_package))
			{
				$number_of_question = $data['number_of_question'];
				$duration = $data['duration'];
				$question_per_page = $data['question_per_page'];
				$due_time = time()+$duration;
				$_SESSION['session_test'][$auth_student_id][$test_id]['start'] = $database->getLocalDateTime();
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
				$arr = array();
				$stmtx = $database->executeQuery($sql);
				if ($stmtx->rowCount() > 0) {
					$rowsx = $stmtx->fetchAll(\PDO::FETCH_ASSOC);
					foreach ($rowsx as $dt) {
						$arr[] = $dt['question_id'];
					}
				}
				$question_package = $str = '['.implode('][', $arr).']';
				$_SESSION['session_test'][$auth_student_id][$test_id]['soal'] = $str;
				$picoEdu->loginTest($school_id, $auth_student_id, $test_id, session_id(), $database->getLocalDateTime(), addslashes($_SERVER['REMOTE_ADDR']));

				$sql = "UPDATE `edu_token` SET `active` = false 
				WHERE `edu_token`.`student_id` = '$auth_student_id' AND `edu_token`.`token` = '$token' ";
				$database->executeUpdate($sql, true);
				header("Location: ujian/index.php?test_id=$test_id");
			}
			else
			{
				$sql = "UPDATE `edu_token` SET `active` = false 
				WHERE `edu_token`.`student_id` = '$auth_student_id' AND `edu_token`.`token` = '$token' ";
				$database->executeUpdate($sql, true);
				header("Location: ujian/index.php?test_id=$test_id");
			}
		}
		
		// /////////////////////////////////////////////////////////////////////////////
		// 
		// End sign in to test
		// 
		// /////////////////////////////////////////////////////////////////////////////	
	}
	$token_valid = 1;
}

else if(isset($_POST['token']))
{
	$token = kh_filter_input(INPUT_POST, "token", FILTER_SANITIZE_NUMBER_UINT);
	if($token != 0)
	{
		$now = $database->getLocalDateTime();
		$sql = "SELECT * FROM `edu_token`
		WHERE `student_id` = '$auth_student_id'
		AND `token` = '$token' AND `active` = true AND `time_expire` > '$now'
		";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$token_valid = 1;
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			$token_data = $data;
			$test_id = $token_data['test_id'];
			$step = 1;
			$_SESSION['vtoken'] = $token;
		}
		else
		{
			$token_valid = 0;
			if(!$picoEdu->logInvalidLogin($auth_student_id, 'T', $database->getLocalDateTime(), $cfg->max_invalid_signin_time, $cfg->max_invalid_signin_count))
			{
				$account_blocked = 1;
				$sql = "UPDATE `edu_student` SET `blocked` = true WHERE `student_id` = '$auth_student_id' ";
				$database->executeUpdate($sql, true);
			}
		}
	}
	else
	{
		unset($_POST['token']);
	}
}

$pageTitle = "Token Ujian";
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<title><?php echo $picoEdu->printPageTitle($pageTitle, $cfg->app_name);?></title>
<link rel="shortcut icon" type="image/x-ico" href="<?php echo $cfg->base_assets;?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-token.css" />
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
</head>

<body>
<div class="all">
	<?php
	if($account_blocked)
	{
		?>
        <div class="test-info">
        <?php
		?>
        <form name="" method="post" enctype="multipart/form-data">
		<div class="label-center">
		Akun Anda Terblokir
		</div>
        <p>Anda telah memasukkan token ujian yang salah lebih dari <strong><?php echo $cfg->max_invalid_signin_count;?> kali</strong> dalam waktu <strong><?php echo ($cfg->max_invalid_signin_time/60);?> menit</strong> dan menyebabkan akun siswa Anda terblokir secara otomatis dan tidak dapat lagi masuk serta mengikuti ujian. Silakan hubungi pengawas ujian atau administrator ujian</p>
		<div class="button-area">
		<input type="button" class="btn3 btn-130" value="Kembali" onclick="window.location='ujian.php?option=enter-token'">
		</div>
        </form>
		<?php
	}
	else if($step == 1)
	{
		?>
        <div class="test-info">
        <?php
		$token = abs(@$_SESSION['vtoken']);
		$sql = "SELECT * FROM `edu_test` WHERE `test_id` = '$test_id' ";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		?>
        <form name="" method="post" enctype="multipart/form-data">
		<div class="label-center">
		Informasi Singkat Ujian
		</div>
		  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td>Nama Ujian</td><td><?php echo $data['name'];?> </td>
			</tr>
			<?php
			if($data['subject'])
			{
			?>
			<tr>
			<td>Mata Pelajaran</td><td><?php echo $data['subject'];?> </td>
			</tr>
			<?php
			}
			?>
			<tr>
			<td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
			</tr>
			<tr>
			<td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
			</tr>
			<tr>
			<td>Nilai Standard</td>
			<td><?php echo $data['standard_score'];?> </td>
			</tr>
			<tr>
			<td>Penalti
			</td><td><?php echo $data['penalty'];?> </td>
			</tr>
			<tr>
			<td>Otomatis Kirim Jawaban</td>
			<td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
			</tr>
		</table>
		<div class="button-area">
        <input type="hidden" name="token" value="<?php echo $token;?>">
		<input type="submit" class="btn btn-130" name="enter_to_test" value="Masuk Ke Ujian">
		<input type="button" class="btn3 btn-130" value="Batal" onclick="window.location='../siswa/ujian.php'">
		</div>
        </form>
		<?php
		}
		?>
        </div>
        <?php
		
	}
	else if(trim(@$_POST['token']) != '' && !$token_valid)
	{
		?>
        <div class="test-info">
        <?php
		?>
        <form name="" method="post" enctype="multipart/form-data">
		<div class="label-center">
		Token Salah
		</div>
        <p>Token yang Anda masukkan salah atau sudah tidak berlaku lagi. Silakan hubungi pengawas ujian atau administrator ujian.</p>
        <p>Percobaan memasukkan token ujian yang salah lebih dari <strong><?php echo $cfg->max_invalid_signin_count;?> kali</strong> dalam waktu <strong><?php echo ($cfg->max_invalid_signin_time/60);?> menit</strong> akan menyebabkan akun siswa Anda terblokir secara otomatis dan tidak dapat lagi masuk serta mengikuti ujian.</p>
		<div class="button-area">
		<input type="button" class="btn3 btn-130" value="Kembali" onclick="window.location='ujian.php?option=enter-token'">
		</div>
        </form>
		<?php
	}
	else
	{
	?>
	<div class="wrapper">
    	<div class="box">
        	<div class="student-test-icon-80"></div>
        	<h3>Token Ujian</h3>
            <form name="" method="post" enctype="multipart/form-data">
            	<div class="form-control">
                    <input type="text" name="token" id="token" value="" placeholder="Token" autocomplete="off">
                </div>
            	<div class="form-control">
                    <input type="submit" class="btn" name="enter" id="enter" value="Masuk Ujian">
                </div>
            	<div class="form-control">
                    <input type="button" class="btn3" name="back" id="back" value="Kembali" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'">
                </div>
            </form>
        </div>
    </div>
    <?php
	}
	?>
</div>
</body>
</html>