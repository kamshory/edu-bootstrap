<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$time_create = $time_edit = $picoEdu->getLocalDateTime();
$member_create = $member_edit = $admin_id;

if(isset($_POST['data']))
{
	parse_str($_POST['data'], $_POST);
}
if(isset($_POST['question']))
{
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$picoEdu->sortQuestion($test_id);
	$sql = "select * from `edu_question` where `test_id` = '$test_id' order by `order` desc";
	$stmt = $database->executeQuery($sql);
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$order = (@$data['order'])+1;
	$number_of_option = kh_filter_input(INPUT_POST, 'number_of_option', FILTER_SANITIZE_NUMBER_UINT);
	$numbering = kh_filter_input(INPUT_POST, 'numbering', FILTER_SANITIZE_STRING_NEW);
	$basic_competence = trim(kh_filter_input(INPUT_POST, 'basic_competence', FILTER_SANITIZE_STRING_NEW));
	$basic_competence = preg_replace("/[^0-9]/i", ".", $basic_competence); //NOSONAR
	$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");
	$random = kh_filter_input(INPUT_POST, 'random', FILTER_SANITIZE_NUMBER_UINT);
	
	$directory = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
	$prefiks = "media.edu/school/$school_id/test/$test_id";

	$question = kh_filter_input(INPUT_POST, 'question');
	$question = UTF8ToEntities($question);
	$question = addslashes(removeparagraphtag(extractImageData($question, $directory, $prefiks))); 	
	$question = $picoEdu->brToNewLineEncoded($question);
	
	$digest = md5($question);
	
	$sql = "select * from `edu_question` where `digest` = '$digest' and `test_id` = '$test_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() == 0)
	{
		$database->executeTransaction('start transaction');

		$question_id = $database->generateNewId();

		$sql = "INSERT INTO `edu_question` 
		(`question_id`, `content`, `basic_competence`, `test_id`, `order`, `multiple_choice`, `random`, `numbering`, `digest`, 
		`time_create`, `member_create`, `time_edit`, `member_edit`) values
		('$question_id', '$question', '$basic_competence', '$test_id', '$order', '1', '$random', '$numbering', '$digest', 
		'$time_create', '$member_create', '$time_edit', '$member_edit'); ";
		$database->executeInsert($sql);

		$order = 0;
		$oke = 1;
		for($i=1;$i<=$number_of_option;$i++)
		{
			$order++;
			$id2 = $i;
			
			$option = kh_filter_input(INPUT_POST, 'option_'.$id2);
			$option = UTF8ToEntities($option);
			$option = addslashes(removeparagraphtag(extractImageData($option, $directory, $prefiks)));
			$option = $picoEdu->brToNewLineEncoded($option);
			 	
			$score = kh_filter_input(INPUT_POST, 'score_'.$id2, FILTER_SANITIZE_NUMBER_FLOAT);

			$option_id = $database->generateNewId();

			$sql = "INSERT INTO `edu_option` 
			(`option_id`, `question_id`, `content`, `order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
			('$option_id', '$question_id', '$option', '$order', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit'); ";
			$stmt = $database->executeInsert($sql);
			if($stmt->rowCount() > 0)
			{
				$oke = $oke*1;
			}
			else
			{
				$oke = 0;
			}
		}
		$ret['duplicated'] = 0;
		if($oke)
		{
			$database->executeTransaction('commit');
		}
		else
		{
			$database->executeTransaction('rollback');
		}
	}
	else
	{
		$ret['duplicated'] = 1;
	}
	$sql = "select * from `edu_question` where `test_id` = '$test_id' ";
	$stmt = $databasa->executeQuery($sql);
	$collection = $stmt->rowCount();
	$ret['collection'] = $collection;
	echo json_encode($ret);
}
