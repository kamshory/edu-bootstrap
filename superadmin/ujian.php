<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$pageTitle = "Ujian";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
	$test_id2 = kh_filter_input(INPUT_POST, "test_id2", FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['test_id']))
	{
		$test_id = $test_id2;
	}
	$name = trim(kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS));
	if(empty($name))
	{
		$name = "{NAMA UJIAN}";
	}
	$class = kh_filter_input(INPUT_POST, "classlist", FILTER_SANITIZE_SPECIAL_CHARS);
	$subject = kh_filter_input(INPUT_POST, "subject", FILTER_SANITIZE_SPECIAL_CHARS);
	
	$school_program_id = kh_filter_input(INPUT_POST, "school_program_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$teacher_id = kh_filter_input(INPUT_POST, "teacher_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$description = kh_filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);
	$guidance = kh_filter_input(INPUT_POST, "guidance", FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, "open", FILTER_SANITIZE_NUMBER_UINT);
	$has_limits = kh_filter_input(INPUT_POST, "has_limits", FILTER_SANITIZE_NUMBER_UINT);
	$trial_limits = kh_filter_input(INPUT_POST, "trial_limits", FILTER_SANITIZE_NUMBER_UINT);
	$threshold = kh_filter_input(INPUT_POST, "threshold", FILTER_SANITIZE_NUMBER_FLOAT);
	$assessment_methods = kh_filter_input(INPUT_POST, "assessment_methods", FILTER_SANITIZE_SPECIAL_CHARS);
	$number_of_question = kh_filter_input(INPUT_POST, "number_of_question", FILTER_SANITIZE_NUMBER_UINT);
	$number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
	$question_per_page = kh_filter_input(INPUT_POST, "question_per_page", FILTER_SANITIZE_NUMBER_UINT);
	$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
	$autosubmit = kh_filter_input(INPUT_POST, "autosubmit", FILTER_SANITIZE_NUMBER_UINT);	
	
	$sort_order = 0;
	$has_alert = kh_filter_input(INPUT_POST, "has_alert", FILTER_SANITIZE_NUMBER_UINT);
	$alert_message = kh_filter_input(INPUT_POST, "alert_message", FILTER_SANITIZE_SPECIAL_CHARS);
	$standard_score = kh_filter_input(INPUT_POST, "standard_score", FILTER_SANITIZE_NUMBER_FLOAT);
	$penalty = kh_filter_input(INPUT_POST, "penalty", FILTER_SANITIZE_NUMBER_FLOAT);
	$score_notification = kh_filter_input(INPUT_POST, "score_notification", FILTER_SANITIZE_NUMBER_UINT);
	$publish_answer = kh_filter_input(INPUT_POST, "publish_answer", FILTER_SANITIZE_NUMBER_UINT);
	$time_answer_publication = kh_filter_input(INPUT_POST, "time_answer_publication", FILTER_SANITIZE_STRING_NEW);
	$test_availability = kh_filter_input(INPUT_POST, "test_availability", FILTER_SANITIZE_SPECIAL_CHARS);
	$available_from = kh_filter_input(INPUT_POST, "available_from", FILTER_SANITIZE_STRING_NEW);
	$available_to = kh_filter_input(INPUT_POST, "available_to", FILTER_SANITIZE_STRING_NEW);

	$time_edit = $database->getLocalDateTime();

	$time_answer_publication = $picoEdu->fixInputTimeSQL($time_answer_publication);
	$available_from = $picoEdu->fixInputTimeSQL($available_from);
	$available_to = $picoEdu->fixInputTimeSQL($available_to);
	
	$role_create = $role_edit = 'A';
	$ip_edit = $_SERVER['REMOTE_ADDR'];

	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
	
	$duration = kh_filter_input(INPUT_POST, "duration", FILTER_SANITIZE_SPECIAL_CHARS);
	$alert_time = kh_filter_input(INPUT_POST, "alert_time", FILTER_SANITIZE_SPECIAL_CHARS);
	
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
		$duration = ((int) $duration)*60;
	}
	if(stripos($alert_time, ":") !== false)
	{
		$arr = explode(":", $alert_time);
		if(count($arr) == 2 || count($arr) == 3)
		{
			$alert_time = (3600*@ltrim($arr[0], '0')) + (60*@ltrim($arr[1], '0'));
		}
	}
}

if(isset($_POST['set_active']) && isset($_POST['test_id']))
{
	$tests = @$_POST['test_id'];
	if(isset($tests) && is_array($tests))
	{
		foreach($tests as $key=>$val)
		{
			$test_id = addslashes($val);
			$sql = "UPDATE `edu_test` SET `active` = true WHERE `test_id` = '$test_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['test_id']))
{
	$tests = @$_POST['test_id'];
	if(isset($tests) && is_array($tests))
	{
		foreach($tests as $key=>$val)
		{
			$test_id = addslashes($val);
			$sql = "UPDATE `edu_test` SET `active` = false WHERE `test_id` = '$test_id' ";
			$database->executeUpdate($sql, true);
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
			$sql = "SELECT * FROM `edu_test` WHERE `test_id` = '$test_id' ";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
				$database->executeTransaction("start transaction", true);
				$sql = "DELETE FROM `edu_answer` WHERE `test_id` = '$test_id' ";
				$database->executeDelete($sql, true);
				$sql = "DELETE FROM `edu_question` WHERE `test_id` = '$test_id' ";
				$database->executeDelete($sql, true);
				$sql = "DELETE FROM `edu_test` WHERE `test_id` = '$test_id' ";
				$database->executeDelete($sql, true);
				// destroy all test file
				$database->executeTransaction("commit", true);
			}
		}
	}
}


if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_test` SET 
	`name` = '$name', `class` = '$class', `school_program_id` = '$school_program_id', `subject` = '$subject', `teacher_id` = '$teacher_id', `description` = '$description', 
	`guidance` = '$guidance', `open` = '$open', `has_limits` = '$has_limits', `trial_limits` = '$trial_limits', `threshold` = '$threshold', 
	`assessment_methods` = '$assessment_methods', `number_of_question` = '$number_of_question', `number_of_option` = '$number_of_option', 
	`question_per_page` = '$question_per_page', `random` = '$random', `duration` = '$duration', `has_alert` = '$has_alert', 
	`alert_time` = '$alert_time', `alert_message` = '$alert_message', `autosubmit` = '$autosubmit', `standard_score` = '$standard_score', 
	`penalty` = '$penalty', `score_notification` = '$score_notification', `publish_answer` = '$publish_answer', 
	`time_answer_publication` = $time_answer_publication, `test_availability` = '$test_availability', 
	`available_from` = $available_from, `available_to` = $available_to, `time_edit` = '$time_edit', 
	`member_edit` = '$member_edit', `role_edit` = '$role_edit', 
	`ip_edit` = '$ip_edit', `active` = '$active'
	WHERE `test_id` = '$test_id2' ";
	$database->executeUpdate($sql, true);
	$picoEdu->addSubject($subject);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&test_id=$test_id");
}
if(@$_GET['option'] == 'edit')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.* 
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$edit_key' 
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$school_id = $data['school_id'];

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
<?php
$sqlc = "SELECT `edu_class`.`class_id`, `edu_class`.`name`, `edu_class`.`school_program_id` 
FROM `edu_class` 
LEFT JOIN (`edu_school_program`) ON (`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`)
WHERE `edu_class`.`active` = true AND `edu_class`.`school_id` = '$school_id' AND `edu_class`.`name` != '' 
ORDER BY `edu_school_program`.`sort_order` ASC , `edu_class`.`sort_order` ASC 
";
$arrc = array();
$stmt = $database->executeQuery($sqlc);
if($stmt->rowCount() > 0)
{
	$arrc = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<script type="text/javascript">
var classList = <?php echo json_encode($arrc);?>;
</script>
<style type="text/css">
.toggle-tr{
	display:none;
}
.class-list li label{
	display:block; 
	width:100%;
}
label > span{
	display:inline-block;
	width:100%;
}
</style>
<script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.assets/script/test-creator.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/bootstrap-4-autocomplete.min.js"></script>
<?php
$subjectList = $picoEdu->getSubjectList();
?>
<script type="text/javascript">
	var src = <?php echo json_encode($subjectList);?>;
	$(document).ready(function(){
		$('#subject').autocomplete({
			source: src,
			highlightClass: 'text-primary',
			treshold: 2,
		});
	});
</script>

<form name="formedu_test" id="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><input type="text" class="form-control input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" required="required" />
		  <input type="hidden" name="test_id2" id="test_id2" value="<?php echo $data['test_id'];?>" /></td>
		</tr>
		<tr>
		<tr>
		<td>Jurusan</td>
        <td><select class="form-control input-select" name="school_program_id" id="school_program_id">
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
					'value'=>$data['school_program_id']
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
		</select></td>
		</tr>
		<td>Kelas
		</td>
        <td><input type="hidden" name="classlist" id="classlist" value="<?php echo $data['class'];?>" autocomplete="off" />
        <button type="button" class="btn btn-tn btn-primary" id="select-class">
		Atur Kelas
		</button>
        </td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><input type="text" class="form-control input-text input-text-long" name="subject" id="subject" value="<?php echo $data['subject'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Guru</td>
        <td><select class="form-control input-select" name="teacher_id" id="teacher_id">
		<option value=""></option>
		<?php 
		$sql2 = "SELECT `edu_teacher`.* FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = '$school_id' ORDER BY `name` ASC ";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'teacher_id')
				),
				'selectCondition'=>array(
					'source'=>'teacher_id',
					'value'=>$data['teacher_id']
				),
				'caption'=>array(
					'delimiter'=>PicoEdu::RAQUO,
					'values'=>array(
						'reg_number',
						'name'
					)
				)
			)
		);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><textarea name="description" class="form-control input-text input-text-long" id="description" autocomplete="off"><?php echo $data['description'];?></textarea></td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><textarea name="guidance" class="form-control input-text input-text-long" id="guidance" autocomplete="off"><?php echo $data['guidance'];?></textarea></td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"<?php echo $picoEdu->ifMatch($data['open'], 1, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Terbuka</label>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_limits" value="1" id="has_limits"<?php echo $picoEdu->ifMatch($data['has_limits'], 1, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Dibatasi</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_limits" data-condition="<?php echo $data['has_limits'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Batas Percobaan</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="trial_limits" id="trial_limits" value="<?php echo $data['trial_limits'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Nilai Kelulusan
		</td><td><input type="number" step="any" class="form-control input-text input-text-medium" name="threshold" id="threshold" value="<?php echo $data['threshold'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><select class="form-control input-select" name="assessment_methods" id="assessment_methods">
		<option value=""></option>
		<option value="H"<?php echo $picoEdu->ifMatch($data['assessment_methods'], 'H', PicoConst::SELECT_OPTION_SELECTED);?>>Tertinggi</option>
		<option value="N"<?php echo $picoEdu->ifMatch($data['assessment_methods'], 'N', PicoConst::SELECT_OPTION_SELECTED);?>>Terbaru</option>
		</select></td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><input type="number" class="form-control input-text input-text-medium" name="number_of_question" id="number_of_question" value="<?php echo $data['number_of_question'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><input type="number" class="form-control input-text input-text-medium" name="number_of_option" id="number_of_option" value="<?php echo $data['number_of_option'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><input type="number" class="form-control input-text input-text-medium" name="question_per_page" id="question_per_page" value="<?php echo $data['question_per_page'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Pengacakan Soal</td>
        <td><label><input type="checkbox" class="input-checkbox" name="random" value="1" id="random"<?php echo $picoEdu->trueFalse($data['random']==1, PicoConst::INPUT_CHECKBOX_CHECKED, '');?>> Soal Diacak</label></td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><input type="text" data-type="duration" class="form-control input-text input-text-medium" name="duration" id="duration" value="<?php echo $data['duration']/60;?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><label><input type="checkbox" class="input-checkbox" name="has_alert" value="1" id="has_alert"<?php echo $picoEdu->trueFalse($data['has_alert']==1, PicoConst::INPUT_CHECKBOX_CHECKED, '');?>> Beri Peringatan</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-condition="<?php echo $data['has_alert'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Waktu Peringatan</td>
		<td><select class="form-control" name="alert_time" id="alert_time">
        	<option value="120"<?php echo $picoEdu->ifMatch($data['alert_time'], 120,  PicoConst::SELECT_OPTION_SELECTED);?>>2 menit</option>
        	<option value="300"<?php echo $picoEdu->ifMatch($data['alert_time'], 300,  PicoConst::SELECT_OPTION_SELECTED);?>>5 menit</option>
        	<option value="600"<?php echo $picoEdu->ifMatch($data['alert_time'], 600,  PicoConst::SELECT_OPTION_SELECTED);?>>10 menit</option>
        	<option value="900"<?php echo $picoEdu->ifMatch($data['alert_time'], 900,  PicoConst::SELECT_OPTION_SELECTED);?>>15 menit</option>
        </select>
        </td>
		</tr>
		<tr class="toggle-tr" data-toggle="has_alert" data-condition="<?php echo $data['has_alert'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Pesan Peringatan</td>
		<td><textarea name="alert_message" class="form-control input-text input-text-long" id="alert_message" autocomplete="off"><?php echo $data['alert_message'];?></textarea></td>
		</tr>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="autosubmit" value="1" id="autosubmit"<?php echo $picoEdu->ifMatch($data['autosubmit'], 1, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Otomatis</label>
		</td>
		</tr>
		<tr>
		<td>Nilai Standard</td>
		<td><input type="number" step="any" class="form-control input-text input-text-medium" name="standard_score" id="standard_score" value="<?php echo $data['standard_score'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Penalti</td>
        <td><input type="number" step="any" class="form-control input-text input-text-medium" name="penalty" id="penalty" value="<?php echo $data['penalty'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Notifikasi Nilai</td>
		<td><label><input type="checkbox" class="input-checkbox" name="score_notification" value="1" id="score_notification"<?php echo $picoEdu->ifMatch($data['score_notification'], 1, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Notifikasi Nilai</label>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><label><input type="checkbox" class="input-checkbox" name="publish_answer" value="1" id="publish_answer"<?php echo $picoEdu->ifMatch($data['publish_answer'], 1, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Umumkan Kunci Jawaban</label>
		</td>
		</tr>
		<tr class="toggle-tr" data-toggle="publish_answer" data-condition="<?php echo $data['publish_answer'];?>" data-show-condition="1" data-hide-condition="0">
		<td>Pengumuman Kunci Jawaban</td>
		<td><input type="datetime-local" class="form-control input-text input-text-datetime" name="time_answer_publication" id="time_answer_publication" value="<?php echo $data['time_answer_publication'];?>" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><select class="form-control input-select" name="test_availability" id="test_availability">
		<option value=""></option>
		<option value="F"<?php echo $picoEdu->ifMatch($data['test_availability'], 'F', PicoConst::SELECT_OPTION_SELECTED);?>>Selamanya</option>
		<option value="L"<?php echo $picoEdu->ifMatch($data['test_availability'], 'L', PicoConst::SELECT_OPTION_SELECTED);?>>Terbatas</option>
		</select></td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-condition="<?php echo $data['test_availability'];?>" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Mulai</td>
		<td><input type="datetime-local" class="form-control input-text input-text-datetime" name="available_from" id="available_from" value="<?php echo $data['available_from'];?>" autocomplete="off" /> </td>
		</tr>
		<tr class="toggle-tr" data-toggle="test_availability" data-condition="<?php echo $data['test_availability'];?>" data-show-condition="L" data-hide-condition="F">
		<td>Tersedia Hingga</td>
		<td><input type="datetime-local" class="form-control input-text input-text-datetime" name="available_to" id="available_to" value="<?php echo $data['available_to'];?>" autocomplete="off" /> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
		</td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<!-- Modal -->
<div class="modal fade" id="select-class-modal" tabindex="-1" role="dialog" aria-labelledby="selectClassTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="selectClassTitle">Pilih Kelas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="class-list-container"></div>
      </div>
      <div class="modal-footer">
		<button type="button" class="btn btn-primary" id="update-class">Terapkan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batalkan</button>
      </div>
    </div>
  </div>
</div>
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
else if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$array_class = $picoEdu->getArrayClass($school_id);
$edit_key = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher_id`,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_test`.`school_program_id`) AS `school_program_id`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_create`) AS `member_create`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_edit`) AS `member_edit`
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$edit_key' 
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Jurusan
		</td><td><?php echo $data['school_program_id'];?> </td>
		</tr>
		<tr>
		<td>Kelas
		</td><td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><?php echo $data['subject'];?> </td>
		</tr>
		<tr>
		<td>Guru
		</td><td><?php echo $data['teacher_id'];?> </td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><?php echo $data['description'];?> </td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><?php echo $data['guidance'];?> </td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><?php echo $picoEdu->trueFalse($data['has_limits'], 'Ya', 'Tidak');?> </td>
		</tr>
        <?php
		if($data['has_limits'])
		{
		?>
		<tr>
		<td>Batas Percobaan</td>
		<td><?php echo $data['trial_limits'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Nilai Kelulusan
		</td><td><?php echo $data['threshold'];?> </td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><?php echo $picoEdu->selectFromMap($data['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><?php echo $data['question_per_page'];?> </td>
		</tr>
		<tr>
		<td>Soal Diacak
		</td><td><?php echo $picoEdu->trueFalse($data['random'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><?php echo gmdate('H:i:s', $data['duration']);?> </td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><?php echo $picoEdu->trueFalse($data['has_alert'], 'Ya', 'Tidak');?> </td>
		</tr>
        <?php
		if($data['has_alert'])
		{
		?>
		<tr>
		<td>Waktu Peringatan</td>
		<td><?php echo (int) ($data['alert_time']/60);?> menit</td>
		</tr>
		<tr>
		<td>Pesan Peringatan</td>
		<td><?php echo $data['alert_message'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
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
		<td>Notifikasi Nilai</td>
		<td><?php echo ($data['score_notification'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><?php echo ($data['publish_answer'])?'Ya':'Tidak';?> </td>
		</tr>
        <?php
		if($data['publish_answer'])
		{
		?>
		<tr>
		<td>Pengumuman Kunci Jawaban</td>
		<td><?php echo $data['time_answer_publication'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><?php echo $picoEdu->selectFromMap($data['test_availability'], array('F'=>'Selamanya', 'L'=>'Terbatas'));?> </td>
		</tr>
        <?php
		if($data['test_availability'] == 'L')
		{
		?>
		<tr>
		<td>Tersedia Mulai</td>
		<td><?php echo $data['available_from'];?> </td>
		</tr>
		<tr>
		<td>Tersedia Hingga</td>
		<td><?php echo $data['available_to'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['member_create'];?> (<?php echo $data['role_create'];?>)</td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['member_edit'];?> (<?php echo $data['role_edit'];?>)</td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
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
				'delimiter'=>PicoEdu::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);
    ?>
    </select>
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
				'delimiter'=>PicoEdu::RAQUO,
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
if(!empty($school_id)){
$pagination->appendQueryName('school_id');
$sql_filter .= " AND (`edu_test`.`school_id` = '$school_id' )";
}

if($class_id != '')
{
	$pagination->appendQueryName('class_id');
	$sql_filter .= " and (concat(',',`edu_test`.`class`,',') like '%,$class_id,%')";
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_test`.`school_id` limit 0,1) AS `school_name`,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher`
FROM `edu_test`
WHERE (1=1) $sql_filter
ORDER BY `edu_test`.`test_id` DESC
";
$sql_test = "SELECT `edu_test`.*
FROM `edu_test`
WHERE (1=1) $sql_filter
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
<style type="text/css">
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(8){
		display:none;
	}
}
</style>
<form name="form1" method="post" action="">
<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-test_id" id="control-test_id" class="checkbox-selector" data-target=".test_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>Ujian</td>
      <td>Pelajaran</td>
      <td>Terbuka</td>
      <td>Soal</td>
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
      <td><input type="checkbox" name="test_id[]" id="test_id" value="<?php echo $data['test_id'];?>" class="test_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['school_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?></a></td>
      <td><a href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
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
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
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
