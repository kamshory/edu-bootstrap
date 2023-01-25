<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$test_id2 = kh_filter_input(INPUT_POST, 'test_id2', FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['test_id']))
	{
		$test_id = $test_id2;
	}
	$name = trim(kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
	if($name == '')
	{
		$name = '{NAMA UJIAN}';
	}
	$class = kh_filter_input(INPUT_POST, 'classlist', FILTER_SANITIZE_SPECIAL_CHARS);
	$subject = kh_filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
	$description = kh_filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
	$guidance = kh_filter_input(INPUT_POST, 'guidance', FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, 'open', FILTER_SANITIZE_NUMBER_UINT);
	$has_limits = kh_filter_input(INPUT_POST, 'has_limits', FILTER_SANITIZE_NUMBER_UINT);
	$trial_limits = kh_filter_input(INPUT_POST, 'trial_limits', FILTER_SANITIZE_NUMBER_UINT);
	$threshold = kh_filter_input(INPUT_POST, 'threshold', FILTER_SANITIZE_NUMBER_FLOAT);
	$assessment_methods = kh_filter_input(INPUT_POST, 'assessment_methods', FILTER_SANITIZE_SPECIAL_CHARS);
	$number_of_question = kh_filter_input(INPUT_POST, 'number_of_question', FILTER_SANITIZE_NUMBER_UINT);
	$number_of_option = kh_filter_input(INPUT_POST, 'number_of_option', FILTER_SANITIZE_NUMBER_UINT);
	$question_per_page = kh_filter_input(INPUT_POST, 'question_per_page', FILTER_SANITIZE_NUMBER_UINT);
	$random = kh_filter_input(INPUT_POST, 'random', FILTER_SANITIZE_NUMBER_UINT);
	$autosubmit = kh_filter_input(INPUT_POST, 'autosubmit', FILTER_SANITIZE_NUMBER_UINT);
		
	
	$order = 0;
	$has_alert = kh_filter_input(INPUT_POST, 'has_alert', FILTER_SANITIZE_NUMBER_UINT);
	$alert_message = kh_filter_input(INPUT_POST, 'alert_message', FILTER_SANITIZE_SPECIAL_CHARS);
	$standard_score = kh_filter_input(INPUT_POST, 'standard_score', FILTER_SANITIZE_NUMBER_FLOAT);
	$penalty = kh_filter_input(INPUT_POST, 'penalty', FILTER_SANITIZE_NUMBER_FLOAT);
	$score_notification = kh_filter_input(INPUT_POST, 'score_notification', FILTER_SANITIZE_NUMBER_UINT);
	$publish_answer = kh_filter_input(INPUT_POST, 'publish_answer', FILTER_SANITIZE_NUMBER_UINT);
	$time_answer_publication = kh_filter_input(INPUT_POST, 'time_answer_publication', FILTER_SANITIZE_STRING_NEW);
	$test_availability = kh_filter_input(INPUT_POST, 'test_availability', FILTER_SANITIZE_SPECIAL_CHARS);
	$available_from = kh_filter_input(INPUT_POST, 'available_from', FILTER_SANITIZE_STRING_NEW);
	$available_to = kh_filter_input(INPUT_POST, 'available_to', FILTER_SANITIZE_STRING_NEW);

	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$member_create = $member_edit = $teacher_id;
	$role_create = $role_edit = 'T';
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];

	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_UINT);
	
	$duration = kh_filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_SPECIAL_CHARS);
	$alert_time = kh_filter_input(INPUT_POST, 'alert_time', FILTER_SANITIZE_SPECIAL_CHARS);
	
	if(stripos($duration, ":") !== false)
	{
		$arr = explode(":", $duration);
		if(count($arr) == 2)
		{
			$duration = (3600*@ltrim($arr[0], '0')) + (60*@ltrim($arr[1], '0'));
		}
		else if(count($arr) == 3)
		{
			$duration = (3600*@ltrim($arr[0], '0')) + (60*@ltrim($arr[1], '0')) + (@ltrim($arr[2], '0'));
		}
	}
	else
	{
		$duration = $duration*60;
	}
	if(stripos($alert_time, ":") !== false)
	{
		$arr = explode(":", $alert_time);
		if(count($arr) == 2)
		{
			$alert_time = (3600*@ltrim($arr[0], '0')) + (60*@ltrim($arr[1], '0'));
		}
		else if(count($arr) == 3)
		{
			$alert_time = (3600*@ltrim($arr[0], '0')) + (60*@ltrim($arr[1], '0'));
		}
	}
}

if(isset($_POST['set_active']) && isset($_POST['test_id']))
{
	$tests = @$_POST['test_id'];
	if(isset($tests))
	{
		if(is_array($tests))
		{
			foreach($tests as $key=>$val)
			{
				$test_id = addslashes($val);
				$sql = "update `edu_test` set `active` = '1' where `test_id` = '$test_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id' ";
				$database->executeUpdate($sql);
			}
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['test_id']))
{
	$tests = @$_POST['test_id'];
	if(isset($tests))
	{
		if(is_array($tests))
		{
			foreach($tests as $key=>$val)
			{
				$test_id = addslashes($val);
				$sql = "update `edu_test` set `active` = '0' where `test_id` = '$test_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id' ";
				$database->executeUpdate($sql);
			}
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['test_id']))
{
	$tests = @$_POST['test_id'];
	if(isset($tests) && is_array($tests))
	{
		foreach($tests as $key=>$val)
		{
			$test_id = addslashes($val);
			$sql = "select * from `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id' ";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
				$database->executeTransaction('start transaction');
				$sql = "DELETE FROM `edu_answer` where `test_id` = '$test_id' ";
				$database->executeDelete($sql);
				$sql = "DELETE FROM `edu_question` where `test_id` = '$test_id' ";
				$database->executeDelete($sql);
				$sql = "DELETE FROM `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id'";
				$database->executeDelete($sql);
				$dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
				$destroyer = new DirectoryDestroyer($dir);
				$destroyer->destroy();
				$database->executeTransaction("commit");
			}
		}
	}
}


if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$test_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_test` 
	(`test_id`, `school_id`, `name`, `class`, `subject`, `teacher_id`, `description`, `guidance`, `open`, `has_limits`, `trial_limits`, `threshold`, `assessment_methods`, `number_of_question`, `number_of_option`, `question_per_page`, `random`, `duration`, `has_alert`, `alert_time`, `alert_message`, `autosubmit`, `standard_score`, `penalty`, `order`, `score_notification`, `publish_answer`, `time_answer_publication`, `test_availability`, `available_from`, `available_to`, `time_create`, `time_edit`, `member_create`, `role_create`, `member_edit`, `role_edit`, `ip_create`, `ip_edit`, `active`) values
	('$test_id', '$school_id', '$name', '$class', '$subject', '$teacher_id', '$description', '$guidance', '$open', '$has_limits', '$trial_limits', '$threshold', '$assessment_methods', '$number_of_question', '$number_of_option', '$question_per_page', '$random', '$duration', '$has_alert', '$alert_time', '$alert_message', '$autosubmit', '$standard_score', '$penalty', '$order', '$score_notification', '$publish_answer', '$time_answer_publication', '$test_availability', '$available_from', '$available_to', '$time_create', '$time_edit', '$member_create', '$role_create', '$member_edit', '$role_edit', '$ip_create', '$ip_edit', '$active')";
	$database->executeInsert($sql);

	$id = kh_filter_input(INPUT_POST, 'collection', FILTER_SANITIZE_NUMBER_UINT);
	if($id)
	{
		$selection = kh_filter_input(INPUT_POST, 'selection', FILTER_SANITIZE_STRING_NEW);
		$selection_index = json_decode($selection);
		include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
		$time_create = $time_edit = $picoEdu->getLocalDateTime();	
		$member_create = $member_edit = $auth_admin_id;
		
		
		$sql = "select * from `edu_test_collection` where `test_collection_id` = '$id' and `active` = '1' ";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$basename = $data['file_path'];
			$file_path = dirname(dirname(__FILE__))."/media.edu/question-collection/data/".$basename;
			if(file_exists($file_path))
			{
	
				$sql = "SELECT `edu_test`.*, 
				(select `edu_question`.`order` from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
				from `edu_test`
				where `edu_test`.`test_id` = '$test_id' and `edu_test`.`teacher_id` = '$auth_teacher_id'
				";
				$stmt = $database->executeQuery($sql);
				if($stmt->rowCount() > 0)
				{
					$data = $stmt->fetch(PDO::FETCH_ASSOC);
			
					$random = $data['random'];
					$order = $data['order'];
					$score_standar = $data['standard_score'];
	
					$test_dir = dirname(dirname(__FILE__))."/media.edu/school";
					if(!file_exists($test_dir))
					{
						mkdir($test_dir);
					}
					$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id";
					if(!file_exists($test_dir))
					{
						mkdir($test_dir);
					}
					$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test";
					if(!file_exists($test_dir))
					{
						mkdir($test_dir);
					}
					$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
					if(!file_exists($test_dir))
					{
						mkdir($test_dir);
					}
					$base_src = "media.edu/school/$school_id/test/$test_id";
					
					$temp_dir = $test_dir;
					
					$basename = md5($_SERVER['REMOTE_ADDR'].'.'.time().'.'.mt_rand(111111, 999999));		
		
					$xml_data = file_get_contents($file_path);
					$test_data = simplexml_load_string($xml_data);
					$number_of_question = count(@$test_data->item);
		
					if($number_of_question)
					{
						$idx = 0;
						foreach($test_data->item as $index_question => $question)
						{
							// petanyaan
							if(($selection_index[$idx] == 1 || $selection == ""  || $selection == "[]"))
							{
								$text_pertanyaan = trim(@$question->question->text);
								$random = trim(@$question->question->random)*1;
								$numbering = addslashes(trim(@$question->question->numbering));
								$competence = addslashes(trim(@$question->question->competence));
								$order++;
								$array_search = array();
								$array_replace = array();
								if(count(@$question->question->file))
								{
									foreach($question->question->file as $index_file_question => $file)
									{
										$name_file = trim(@$file->name, " \r\n\t ");
										$type_file = trim(@$file->type, " \r\n\t ");
										$encoding_file = trim(@$file->encoding, " \r\n\t ");
										$data_file = trim(@$file->data, " \r\n\t ");
										if(stripos($encoding_file, "base64") !== false)
										{
											$data_file = base64_decode($data_file);
										}
										$name_file_repaired = str_replace(".svg+xml", ".svg", $name_file);
										$array_search[] = $name_file;
										$array_replace[] = $name_file_repaired;
										file_put_contents($test_dir."/".$name_file_repaired, $data_file);
									}
								}
								$pertanyaan = htmlspecialchars_decode(htmlentities(replaceBase($text_pertanyaan, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
								$pertanyaan = str_replace($array_search, $array_replace, $pertanyaan);
								$digest = md5($pertanyaan);
								$pertanyaan = addslashes($pertanyaan);
								
								$question_id = $database->generateNewId();		
								$sql1 = "INSERT INTO `edu_question` 
								(`question_id`, `content`, `test_id`, `multiple_choice`, `order`, `random`, `numbering`, `digest`, `basic_competence`, 
								`time_create`, `member_create`, `time_edit`, `member_edit`) values
								('$question_id', '$pertanyaan', '$test_id', '1', '$order', '$random', '$numbering', '$digest', '$competence',
								'$time_create', '$member_create', '$time_edit', '$member_edit'); 
								";
								$res1 = $database->executeInsert($sql1);
								
								if(count(@$question->answer->option) > 0)
								{
									foreach($question->answer->option as $index_option => $option)
									{
										$text_option = trim(@$option->text);
										$score = trim(@$option->value)*1;
										$array_search = array();
										$array_replace = array();
										if(count(@$option->file))
										{
											foreach($option->file as $index_file_question => $file)
											{
												$name_file = trim(@$file->name, " \r\n\t ");
												$type_file = trim(@$file->type, " \r\n\t ");
												$encoding_file = trim(@$file->encoding, " \r\n\t ");
												$data_file = trim(@$file->data, " \r\n\t ");
												
												if(stripos($encoding_file, "base64") !== false)
												{
													$data_file = base64_decode($data_file);
												}
												$name_file_repaired = str_replace(".svg+xml", ".svg", $name_file);
												$array_search[] = $name_file;
												$array_replace[] = $name_file_repaired;
												file_put_contents($test_dir."/".$name_file_repaired, $data_file);
											}
										}
										$option = htmlspecialchars_decode(htmlentities(replaceBase($text_option, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
										$option = str_replace($array_search, $array_replace, $option);
										$digest = md5($option);
										$option = addslashes($option);
										
										$order = $index_option + 1;
										$option_id = $database->generateNewId();

										$sql2 = "INSERT INTO `edu_option` 
										(`option_id`, `question_id`, `content`, `order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
										('$option_id', '$question_id', '$option', '$order', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit'); 
										";
										
										$res2 = $database->executeInsert($sql2);
									}
								}
							}
							$idx++;
						}
					}
				}
			}
		}
	}

	header("Location: ujian-soal.php?option=detail&test_id=$test_id");
	exit();
}
if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `edu_test` set 
	`name` = '$name', `class` = '$class', `subject` = '$subject', `teacher_id` = '$teacher_id', `description` = '$description', 
	`guidance` = '$guidance', `open` = '$open', `has_limits` = '$has_limits', `trial_limits` = '$trial_limits', `threshold` = '$threshold', 
	`assessment_methods` = '$assessment_methods', `number_of_question` = '$number_of_question', `number_of_option` = '$number_of_option', 
	`question_per_page` = '$question_per_page', `random` = '$random', `duration` = '$duration', `has_alert` = '$has_alert', 
	`alert_time` = '$alert_time', `alert_message` = '$alert_message', `autosubmit` = '$autosubmit', `standard_score` = '$standard_score', 
	`penalty` = '$penalty', `score_notification` = '$score_notification', `publish_answer` = '$publish_answer', 
	`time_answer_publication` = '$time_answer_publication', `test_availability` = '$test_availability', 
	`available_from` = '$available_from', `available_to` = '$available_to', 
	`time_create` = '$time_create', `time_edit` = '$time_edit', `member_create` = '$member_create', `role_create` = '$role_create', 
	`member_edit` = '$member_edit', `role_edit` = '$role_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `active` = '$active'
	where `test_id` = '$test_id2' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id'";
	$database->executeUpdate($sql);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&test_id=$test_id");
}
if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$collection = kh_filter_input(INPUT_GET, 'collection', FILTER_SANITIZE_NUMBER_UINT);
$selection = kh_filter_input(INPUT_GET, 'selection', FILTER_SANITIZE_STRING_NEW);
$sqlc = "SELECT `class_id`, `name` from `edu_class` where `active` = '1' and `school_id` = '$school_id' and `name` != '' order by `order` asc ";
$stmtc = $database->executeQuery($sqlc);
$arrc = array();
if($stmtc->rowCount() > 0)
{
	$arrc = $stmtc->fetchAll(PDO::FETCH_ASSOC);
}
?>
<style type="text/css">
input#duration{
	background:url(lib.assets/theme/default/css/images/duration-bg.png) no-repeat right center;
	padding-right:60px;
	box-sizing:border-box;
}
.toggle-tr{
	display:none;
}
</style>
<form name="formedu_test" id="formedu_test" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="collection" id="collection" value="<?php echo $collection;?>" />
  <input type="hidden" name="selection" id="selection" value="<?php echo $selection;?>" />
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" required="required" /></td>
		</tr>
		<tr>
		<td>Kelas
		</td><td><input type="hidden" name="classlist" id="classlist" autocomplete="off" />
        <input type="button" id="select-class" value="Pilih Kelas" />
        </td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><input type="text" class="input-text input-text-long" name="subject" id="subject" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><textarea name="description" class="input-text input-text-long" id="description" autocomplete="off"></textarea></td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><textarea name="guidance" class="input-text input-text-long" id="guidance" autocomplete="off">Pilihlah jawaban yang paling tepat!</textarea></td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"> Terbuka</label>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_limits" value="1" id="has_limits"> Dibatasi</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_limits" data-show-condition="1" data-hide-condition="0">
		<td>Batas Percobaan</td>
		<td><input type="number" class="input-text input-text-medium" name="trial_limits" id="trial_limits" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nilai Kelulusan
		</td><td><input type="number" step="any" class="input-text input-text-medium" name="threshold" id="threshold" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><select class="input-select" name="assessment_methods" id="assessment_methods">
		<option value=""></option>
		<option value="H">Tertinggi</option>
		<option value="N">Terbaru</option>
		</select></td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><input type="number" class="input-text input-text-medium" name="number_of_question" id="number_of_question" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><input type="number" class="input-text input-text-medium" name="number_of_option" id="number_of_option" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><input type="number" class="input-text input-text-medium" name="question_per_page" id="question_per_page" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Pengacakan Soal
		</td><td><label><input type="checkbox" class="input-checkbox" name="random" value="1" id="random"> Soal Diacak</label></td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><input type="text" data-type="duration" class="input-text input-text-medium" name="duration" id="duration" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_alert" value="1" id="has_alert"> Beri Peringatan</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-show-condition="1" data-hide-condition="0">
		<td>Waktu Peringatan</td>
		<td>
        <select name="alert_time" id="alert_time">
        	<option value="120">2 menit</option>
        	<option value="300">5 menit</option>
        	<option value="600">10 menit</option>
        	<option value="900">15 menit</option>
        </select>
        </td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-show-condition="1" data-hide-condition="0">
		<td>Pesan Peringatan</td>
		<td><textarea name="alert_message" class="input-text input-text-long" id="alert_message" autocomplete="off">Lihat waktu sisa Anda. Silakan periksa kembali jawaban Anda. Segera kirimkan jawaban sebelum waktunya habis.</textarea></td>
		</tr>
		<tr>
		<td>Otomtais Kirim Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="autosubmit" value="1" id="autosubmit"> Otomatis</label>
		</td>
		</tr>
		<tr>
		<td>Nilai Standard</td>
		<td><input type="number" step="any" min="0" class="input-text input-text-medium" name="standard_score" id="standard_score" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Penalti
		</td><td><input type="number" step="any" min="0" class="input-text input-text-medium" name="penalty" id="penalty" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Notifikasi Nilai</td>
		<td><label><input type="checkbox" class="input-checkbox" name="score_notification" value="1" id="score_notification"> Notifikasi Nilai</label>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="publish_answer" value="1" id="publish_answer"> Umumkan Kunci Jawaban</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="publish_answer" data-show-condition="1" data-hide-condition="0">
		<td>Pengumuman Kunci Jawaban</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="time_answer_publication" id="time_answer_publication" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><select class="input-select" name="test_availability" id="test_availability">
		<option value=""></option>
		<option value="F">Selamanya</option>
		<option value="L">Terbatas</option>
		</select></td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Mulai</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="available_from" id="available_from" autocomplete="off" /> </td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Hingga</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="available_to" id="available_to" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Aktif</label>
		</td>
		</tr>
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
function updateGoggle()
{
	$('.toggle-tr').each(function(index, element) {
		var row = $(this)
        var sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').change();
	});
}
function initToggle()
{
	$('.toggle-tr').each(function(index, element) {
		var row = $(this)
        var sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').on('change', function(){
			var val = "0";
			if(($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio'))
			{
				if($(this)[0].checked)
				{
					val = $(this).attr('value');
				}
				else
				{
					val = "0";
				}
			}
			else
			{
				val = $(this).val();
			}
			if(!val) val = "0";
			if(val == row.attr('data-show-condition'))
			{
				row.css({'display':'table-row'});
			}
			if(val == row.attr('data-hide-condition'))
			{
				row.css({'display':'none'});
			}
		});
    });
}
function buildClassOption(list, value){
	var i, j, k;
	var html = '';
	var sel = '';
	var vals = value.split(",");
	html += '<ul class="class-list">';
	
	for(i in list)
	{
		if($.inArray(list[i].class_id, vals) != -1)
		{
			sel = ' class-item-selected';
		}
		else 
		{
			sel = '';
		}
		html += '<li class="class-item'+sel+'" data-class-id="'+list[i].class_id+'"><a href="javascript:;">'+list[i].name+'</a></li>'; 
	}
	
	html += '</ul>';
	return html;
}
function selectClass()
{
	var val = $('#formedu_test #classlist').val();
	var html = ''+
	'<div class="overlay-dialog-area">\r\n'+
	'	<h3>Pilih Kelas</h3>\r\n'+
	'    <div class="select-class-area">\r\n'+
	buildClassOption(classList, val)+
	'    </div>\r\n'+
	'    <div class="button-area" style="text-align:center">\r\n'+
	'    	<input type="button" class="com-button" id="update-class" value="Terapkan" />\r\n'+
	'    	<input type="button" class="com-button" id="cancel-class" value="Batalkan" />\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	overlayDialog(html, 400, 360);
	$('.class-item').each(function(index, element) {
        $(this).find('a').on('click', function(e){
			$(this).parent().toggleClass('class-item-selected');
			e.preventDefault();
		});
    });
	$('#update-class').on('click', function(e){
		var arr = [];
		$('.class-item').each(function(index, element) {
			if($(this).hasClass('class-item-selected'))
			{
				arr.push($(this).attr('data-class-id'));
			}
		});
		$('#formedu_test #classlist').val(arr.join(','));
		closeOverlayDialog();
	});
	$('#cancel-class').on('click', function(e){
		closeOverlayDialog();
	});
}
$(document).ready(function(e) {
    setTimeout(function(){
		initToggle();
		updateGoggle();
	}, 100);
    setTimeout(function(){
		var duration = parseInt(parseInt($('#duration').val() || '0')/60);
		$('#duration').val(duration);
	}, 500);
	$(document).on('click', '#select-class', function(e){
		selectClass();
		e.preventDefault();
	}); 
		
});
</script>
<?php getDefaultValues($database, 'edu_test', array('name','class','subject','teacher_id','open','has_limits','trial_limits','threshold','assessment_methods','number_of_question','number_of_option','question_per_page','random','duration','has_alert','alert_time','standard_score','penalty','order','score_notification','publish_answer','time_answer_publication','test_availability','available_from','available_to','active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.* 
from `edu_test` 
where 1
and `edu_test`.`test_id` = '$edit_key' and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$sqlc = "SELECT `class_id`, `name` from `edu_class` where `active` = '1' and `school_id` = '$school_id' and `name` != '' order by `order` asc ";
$stmtc = $database->executeQuery($sqlc);
$arrc = array();
if($stmtc->rowCount() > 0)
{
	$arrc = $stmtc->fetchAll(PDO::FETCH_ASSOC);
}
?>
<style type="text/css">
input#duration{
	background:url(lib.assets/theme/default/css/images/duration-bg.png) no-repeat right center;
	padding-right:60px;
	box-sizing:border-box;
}
.toggle-tr{
	display:none;
}
</style>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
function updateGoggle()
{
	$('.toggle-tr').each(function(index, element) {
		var row = $(this)
        var sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').change();
	});
}
function initToggle()
{
	$('.toggle-tr').each(function(index, element) {
		var row = $(this)
        var sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').on('change', function(){
			var val = "0";
			if(($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio'))
			{
				if($(this)[0].checked)
				{
					val = $(this).attr('value');
				}
				else
				{
					val = "0";
				}
			}
			else
			{
				val = $(this).val();
			}
			if(!val) val = "0";
			if(val == row.attr('data-show-condition'))
			{
				row.css({'display':'table-row'});
			}
			if(val == row.attr('data-hide-condition'))
			{
				row.css({'display':'none'});
			}
		});
    });
}
function buildClassOption(list, value){
	var i, j, k;
	var html = '';
	var sel = '';
	var vals = value.split(",");
	html += '<ul class="class-list">';
	
	for(i in list)
	{
		if($.inArray(list[i].class_id, vals) != -1)
		{
			sel = ' class-item-selected';
		}
		else 
		{
			sel = '';
		}
		html += '<li class="class-item'+sel+'" data-class-id="'+list[i].class_id+'"><a href="javascript:;">'+list[i].name+'</a></li>'; 
	}
	
	html += '</ul>';
	return html;
}
function selectClass()
{
	var val = $('#formedu_test #classlist').val();
	var html = ''+
	'<div class="overlay-dialog-area">\r\n'+
	'	<h3>Pilih Kelas</h3>\r\n'+
	'    <div class="select-class-area">\r\n'+
	buildClassOption(classList, val)+
	'    </div>\r\n'+
	'    <div class="button-area" style="text-align:center">\r\n'+
	'    	<input type="button" class="com-button" id="update-class" value="Terapkan" />\r\n'+
	'    	<input type="button" class="com-button" id="cancel-class" value="Batalkan" />\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	overlayDialog(html, 400, 360);
	$('.class-item').each(function(index, element) {
        $(this).find('a').on('click', function(e){
			$(this).parent().toggleClass('class-item-selected');
			e.preventDefault();
		});
    });
	$('#update-class').on('click', function(e){
		var arr = [];
		$('.class-item').each(function(index, element) {
			if($(this).hasClass('class-item-selected'))
			{
				arr.push($(this).attr('data-class-id'));
			}
		});
		$('#formedu_test #classlist').val(arr.join(','));
		closeOverlayDialog();
	});
	$('#cancel-class').on('click', function(e){
		closeOverlayDialog();
	});
}
$(document).ready(function(e) {
    setTimeout(function(){
		initToggle();
		updateGoggle();
	}, 100);
	$(document).on('click', '#select-class', function(e){
		selectClass();
		e.preventDefault();
	}); 
		
});
</script>
<form name="formedu_test" id="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" required="required" />
		  <input type="hidden" name="test_id2" id="test_id2" value="<?php echo ($data['test_id']);?>" /></td>
		</tr>
		<tr>
		<td>Kelas
		</td>
        <td><input type="hidden" name="classlist" id="classlist" value="<?php echo ($data['class']);?>" autocomplete="off" />
        <input type="button" id="select-class" value="Atur Kelas" />
        </td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><input type="text" class="input-text input-text-long" name="subject" id="subject" value="<?php echo $data['subject'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><textarea name="description" class="input-text input-text-long" id="description" autocomplete="off"><?php echo $data['description'];?></textarea></td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><textarea name="guidance" class="input-text input-text-long" id="guidance" autocomplete="off"><?php echo $data['guidance'];?></textarea></td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"<?php if($data['open']==1) echo ' checked="checked"';?>> Terbuka</label>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_limits" value="1" id="has_limits"<?php if($data['has_limits']==1) echo ' checked="checked"';?>> Dibatasi</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_limits" data-condition="<?php echo $data['has_limits'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Batas Percobaan</td>
		<td><input type="number" class="input-text input-text-medium" name="trial_limits" id="trial_limits" value="<?php echo ($data['trial_limits']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nilai Kelulusan
		</td><td><input type="number" step="any" class="input-text input-text-medium" name="threshold" id="threshold" value="<?php echo ($data['threshold']);?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><select class="input-select" name="assessment_methods" id="assessment_methods">
		<option value=""></option>
		<option value="H"<?php if($data['assessment_methods'] == 'H') echo ' selected="selected"';?>>Tertinggi</option>
		<option value="N"<?php if($data['assessment_methods'] == 'N') echo ' selected="selected"';?>>Terbaru</option>
		</select></td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><input type="number" class="input-text input-text-medium" name="number_of_question" id="number_of_question" value="<?php echo $data['number_of_question'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><input type="number" class="input-text input-text-medium" name="number_of_option" id="number_of_option" value="<?php echo $data['number_of_option'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><input type="number" class="input-text input-text-medium" name="question_per_page" id="question_per_page" value="<?php echo $data['question_per_page'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Pengacakan Soal</td>
        <td><label><input type="checkbox" class="input-checkbox" name="random" value="1" id="random"<?php if($data['random']==1) echo ' checked="checked"';?>> Soal Diacak</label></td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><input type="text" data-type="duration" class="input-text input-text-medium" name="duration" id="duration" value="<?php echo $data['duration']/60;?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_alert" value="1" id="has_alert"<?php if($data['has_alert']==1) echo ' checked="checked"';?>> Beri Peringatan</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-condition="<?php echo $data['has_alert'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Waktu Peringatan</td>
		<td><select name="alert_time" id="alert_time">
        	<option value="120"<?php if($data['alert_time'] == 120) echo ' selected="selected"';?>>2 menit</option>
        	<option value="300"<?php if($data['alert_time'] == 300) echo ' selected="selected"';?>>5 menit</option>
        	<option value="600"<?php if($data['alert_time'] == 600) echo ' selected="selected"';?>>10 menit</option>
        	<option value="900"<?php if($data['alert_time'] == 900) echo ' selected="selected"';?>>15 menit</option>
        </select>
        </td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-condition="<?php echo $data['has_alert'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Pesan Peringatan</td>
		<td><textarea name="alert_message" class="input-text input-text-long" id="alert_message" autocomplete="off"><?php echo ($data['alert_message']);?></textarea></td>
		</tr>
		<tr>
		<td>Otomtais Kirim Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="autosubmit" value="1" id="autosubmit"<?php if($data['autosubmit']==1) echo ' checked="checked"';?>> Otomatis</label>
		</td>
		</tr>
		<tr>
		<td>Nilai Standard</td>
		<td><input type="number" step="any" class="input-text input-text-medium" name="standard_score" id="standard_score" value="<?php echo $data['standard_score'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Penalti</td>
        <td><input type="number" step="any" class="input-text input-text-medium" name="penalty" id="penalty" value="<?php echo $data['penalty'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Notifikasi Nilai</td>
		<td><label><input type="checkbox" class="input-checkbox" name="score_notification" value="1" id="score_notification"<?php if($data['score_notification']==1) echo ' checked="checked"';?>> Notifikasi Nilai</label>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="publish_answer" value="1" id="publish_answer"<?php if($data['publish_answer']==1) echo ' checked="checked"';?>> Umumkan Kunci Jawaban</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="publish_answer" data-condition="<?php echo $data['publish_answer'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Pengumuman Kunci Jawaban</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="time_answer_publication" id="time_answer_publication" value="<?php echo ($data['time_answer_publication']);?>" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><select class="input-select" name="test_availability" id="test_availability">
		<option value=""></option>
		<option value="F"<?php if($data['test_availability'] == 'F') echo ' selected="selected"';?>>Selamanya</option>
		<option value="L"<?php if($data['test_availability'] == 'L') echo ' selected="selected"';?>>Terbatas</option>
		</select></td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-condition="<?php echo $data['test_availability'];?>" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Mulai</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="available_from" id="available_from" value="<?php echo ($data['available_from']);?>" autocomplete="off" /> </td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-condition="<?php echo $data['test_availability'];?>" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Hingga</td>
		<td><input type="datetime-local" class="input-text input-text-datetime" name="available_to" id="available_to" value="<?php echo ($data['available_to']);?>" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php if($data['active']==1) echo ' checked="checked"';?>> Aktif</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$array_class = $picoEdu->getArrayClass($school_id);
$edit_key = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher_id`,
(select `member`.`name` from `member` where `member`.`member_id` = `edu_test`.`member_create`) as `member_create`,
(select `member`.`name` from `member` where `member`.`member_id` = `edu_test`.`member_edit`) as `member_edit`
from `edu_test` 
where 1
and `edu_test`.`test_id` = '$edit_key' and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Kelas
		</td><td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><?php echo $data['subject'];?></td>
		</tr>
		<tr>
		<td>Guru
		</td><td><?php echo $data['teacher_id'];?></td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><?php echo $data['description'];?></td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><?php echo $data['guidance'];?></td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><?php echo ($data['open'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><?php echo ($data['has_limits'])?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['has_limits'])
		{
		?>
		<tr>
		<td>Batas Percobaan</td>
		<td><?php echo ($data['trial_limits']);?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Nilai Kelulusan
		</td><td><?php echo ($data['threshold']);?></td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><?php if($data['assessment_methods'] == 'H') echo "Nilai Tertinggi"; if($data['assessment_methods'] == 'N') echo "Nilai Terbaru";?></td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?></td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?></td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><?php echo $data['question_per_page'];?></td>
		</tr>
		<tr>
		<td>Acak
		</td><td><?php echo ($data['random'])?"Ya":"Tidak";?></td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><?php echo gmdate('H:i:s', $data['duration']);?></td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><?php echo ($data['has_alert'])?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['has_alert'])
		{
		?>
		<tr>
		<td>Waktu Peringatan</td>
		<td><?php echo ($data['alert_time']/60);?> menit</td>
		</tr>
		<tr>
		<td>Pesan Peringatan</td>
		<td><?php echo ($data['alert_message']);?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo ($data['autosubmit'])?'Ya':'Tidak';?></td>
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
		<td>Notifikasi Nilai</td>
		<td><?php echo ($data['score_notification'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><?php echo ($data['publish_answer'])?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['publish_answer'])
		{
		?>
		<tr>
		<td>Pengumuman Kunci Jawaban</td>
		<td><?php echo ($data['time_answer_publication']);?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><?php if($data['test_availability'] == 'F') echo 'Selamanya'; if($data['test_availability'] == 'L') echo 'Terbatas';?></td>
		</tr>
        <?php
		if($data['test_availability'] == 'L')
		{
		?>
		<tr>
		<td>Tersedia Mulai</td>
		<td><?php echo ($data['available_from']);?></td>
		</tr>
		<tr>
		<td>Tersedia Hingga</td>
		<td><?php echo ($data['available_to']);?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo ($data['member_create']);?> (<?php echo ($data['role_create']);?>)</td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo ($data['member_edit']);?> (<?php echo ($data['role_edit']);?>)</td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
$array_class = $picoEdu->getArrayClass($school_id);
?>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
	$(document).on('click', 'a[data-availability="L"]', function(e){
		var from = $(this).attr('data-from');
		var to = $(this).attr('data-to');
		alert('Tersedia antara '+from+' hingga '+to);
		e.preventDefault();
	});
	$(document).on('click', 'a[data-availability="F"]', function(e){
		alert('Tersedia selamanaya ');
		e.preventDefault();
	});
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Kelas</span> 
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
	$pagination->array_get[] = 'class_id';
	$sql_filter .= " and (concat(',',`edu_test`.`class`,',') like '%,$class_id,%')";
}


$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
order by `edu_test`.`test_id` desc
";
$sql_test = "SELECT `edu_test`.*
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
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
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9)
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
      <td width="16"><input type="checkbox" name="control-test_id" id="control-test_id" class="checkbox-selector" data-target=".test_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Ujian</td>
      <td>Kelas</td>
      <td>Pelajaran</td>
      <td>Guru</td>
      <td>Terbuka</td>
      <td>Soal</td>
      <td>Tersedia</td>
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
    <tr<?php echo (@$data['active'])?" class=\"data-active\"":" class=\"data-inactive\"";?>>
      <td><input type="checkbox" name="test_id[]" id="test_id" value="<?php echo $data['test_id'];?>" class="test_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['teacher']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Ya':'Tidak';?></a></td>
      <td><a href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>" data-availability="<?php echo $data['test_availability'];?>" data-from="<?php echo $data['available_from'];?>" data-to="<?php echo $data['available_to'];?>"><?php if($data['test_availability']=='F') echo 'Selamanya'; if($data['test_availability']=='L') echo 'Terbatas';?></a></td>
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

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="com-button" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="com-button" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
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
<div class="warning">Data tidak ditemukan. <a href="ujian-daftar.php?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>