<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
$basename = "ujian-soal.php";
$test_id = 0;
$edit_mode = '';
if (@$school_id != 0 && isset($_POST['question_text']) && isset($_POST['test_id']) && @$_POST['option'] == 'add') {
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$edit_mode = kh_filter_input(INPUT_POST, 'edit_mode', FILTER_SANITIZE_NUMBER_UINT);
	$sql = "select * from `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		// Format Plain
		$picoEdu->sortQuestion($test_id);
		$sql = "SELECT `edu_test`.*, 
		(select `edu_question`.`order` from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
		from `edu_test`
		where `edu_test`.`test_id` = '$test_id'
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$time_create = $picoEdu->getLocalDateTime();
			$time_edit = $picoEdu->getLocalDateTime();
			$member_create = $admin_id;
			$member_edit = $admin_id;
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$random = $data['random'];
			$order = $data['order'];
			$score_standar = $data['standard_score'];
			$xml_data = kh_filter_input(INPUT_POST, 'question_text', FILTER_DEFAULT);
			$clear_data = parseRawQuestion($xml_data);
			$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school";
			if (!file_exists($base_dir)) {
				mkdir($base_dir);
			}
			$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id";
			if (!file_exists($base_dir)) {
				mkdir($base_dir);
			}
			$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test";
			if (!file_exists($base_dir)) {
				mkdir($base_dir);
			}
			$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
			if (!file_exists($base_dir)) {
				mkdir($base_dir);
			}
			$base_src = "media.edu/school/$school_id/test/$test_id";
			$database->executeTransaction('start transaction');
			$oke = 1;
			foreach ($clear_data as $question_no => $question) {
				$object = parseQuestion($question);
				if (isset($object['question']) && isset($object['numbering']) && isset($object['option'])) {
					$content = nl2br(UTF8ToEntities(filter_html(addImages(@$object['question'], $base_dir, $base_src))));

					$picoEdu->log($content);

					$content = $picoEdu->brToNewLineEncoded($content);
					$content = addslashes($content);
					$numbering = addslashes($object['numbering']);
					$digest = md5($object['question']);



					$order++;

					$question_id = $database->generateNewId();

					$sql1 = "INSERT INTO `edu_question` 
					(`question_id`, `content`, `test_id`, `order`, `multiple_choice`, `random`, `numbering`, `digest`, 
					`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
					('$question_id', '$content', '$test_id', '$order', '1', '$random', '$numbering', '$digest', 
					'$time_create', '$member_create', '$time_edit', '$member_edit', '1');
					";
					$picoEdu->log($sql1);
					$stmt = $database->executeInsert($sql1);
					if ($stmt->rowCount() == 0) {
						$oke = $oke * 0;
					} else {
						if (@is_array($object['option']) && count($object['option'])) {
							foreach ($object['option'] as $option_no => $option) {
								$content_option = addslashes(nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $base_dir, $base_src)))));
								$content_option = $picoEdu->brToNewLineEncoded($content_option);
								$order_option = $option_no + 1;
								$score_option = addslashes(@$option['value'] * $score_standar);
								if ($score_option == 0) {
									$score_option = addslashes(@$option['score'] * $score_standar);
								}

								$option_id = $database->generateNewId();

								$sql2 = "INSERT INTO `edu_option` 
								(`option_id`, `question_id`, `content`, `order`, `score`, 
								`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
								('$option_id', '$question_id', '$content_option', '$order_option', '$score_option', 
								'$time_create', '$member_create', '$time_edit', '$member_edit', '1');
								";
								$stmt2 = $database->executeInsert($sql2);
								if ($stmt2->rowCount() == 0) {
									$oke = $oke * 0;
								}
							}
						}
					}
				}
			}
			if ($oke) {
				$database->executeTransaction('commit');
			} else {
				$database->executeTransaction('rollback');
			}

		}
	}
}

if (@$school_id != 0) {
	if ($test_id == 0) {
		$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	}

	$sql = "select * from `edu_question` where `test_id` = '$test_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
	?>
	<ol class="question-ol">
	<?php
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $data) {
		$question_id = $data['question_id'];
		?>
	<li><span><?php echo $data['content']; ?><?php if ($edit_mode) { 
		?><span class="edit-question-ctrl"><a href="<?php echo $basename; ?>?option=edit&question_id=<?php echo $question_id; ?>" target="_blank"><span></span></a></span><?php } ?></span>
	<ol class="option-ol" style="list-style-type:<?php 
	echo $data['numbering']; ?>">
	<?php
		$sql2 = "select * from `edu_option` where `question_id` = '$question_id' ";
		$stmt2 = $database->executeQuery($sql2);
		if ($stmt2->rowCount() > 0) {
			$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows2 as $data2) {
				?><li class="option-li"><?php if ($data2['score'] > 0) { 
					?><span class="score"></span><?php 
				} ?><?php echo $data2['content']; ?></li>
		<?php
			}
		}
		?>
	</ol>
	</li>
	<?php
		}
		?>
	</ol>
	<?php
	}
}

?>