<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	exit();
}

require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$time_create = $time_edit = $database->getLocalDateTime();

if(isset($_POST['data']))
{
	parse_str($_POST['data'], $_POST);
}
if(isset($_POST['question']))
{
	$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
	$picoEdu->sortQuestion($test_id);
	$sql = "SELECT * FROM `edu_question` WHERE `test_id` = '$test_id' ORDER BY `sort_order` DESC";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);

		$sort_order = (@$data['sort_order'])+1;
		$number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
		$numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
		$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
		
		$direktori = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
		$prefiks = "media.edu/school/$school_id/test/$test_id";

		$question = kh_filter_input(INPUT_POST, "question");
		$question = utf8ToEntities($question);
		$question = addslashes(removeparagraphtag(\Pico\PicoDOM::extractImageData($question, $direktori, $prefiks, $fileSync))); 	

		$question = $picoEdu->brToNewLineEncoded($question);
		
		$digest = md5($question);
		
		$sql = "SELECT * FROM `edu_question` WHERE `digest` = '$digest' AND `test_id` = '$test_id' ";

		$stmt = $database->executeQuery($sql);

		if($stmt->rowCount() == 0)
		{
			$database->executeTransaction("start transaction", true);
			$question_id = $database->generateNewId();
			$sql = "INSERT INTO `edu_question` 
			(`question_id`, `content`, `test_id`, `sort_order`, `multiple_choice`, `random`, `numbering`, `digest`, `time_create`, `member_create`, `time_edit`, `member_edit`) VALUES
			('$question_id', '$question', '$test_id', '$sort_order', '1', '$random', '$numbering', '$digest', '$time_create', '$member_create', '$time_edit', '$member_edit')";
			$database->executeInsert($sql, true);
			$sort_order = 0;
			$oke = 1;
			for($i=1; $i <= $number_of_option; $i++)
			{
				$sort_order++;
				$id2 = $i;
				
				$option = kh_filter_input(INPUT_POST, "option_".$id2);
				$option = utf8ToEntities($option);
				$option = addslashes(removeparagraphtag(\Pico\PicoDOM::extractImageData($option, $direktori, $prefiks, $fileSync)));

				$option = $picoEdu->brToNewLineEncoded($option);
					
				$score = kh_filter_input(INPUT_POST, "score_".$id2, FILTER_SANITIZE_NUMBER_FLOAT);
				$option_id = $database->generateNewId();
				$sql = "INSERT INTO `edu_option` 
				(`option_id`, `question_id`, `content`, `sort_order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) VALUES
				('$option_id', '$question_id', '$option', '$sort_order', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit')";
				$stmt =  $database->executeInsert($sql, true);
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
				$database->executeTransaction("commit", true);
			}
			else
			{
				$database->executeTransaction("rollback", true);
			}
		}
		else
		{
			$ret['duplicated'] = 1;
		}
		$sql = "SELECT * FROM `edu_question` WHERE `test_id` = '$test_id' ";
		$stmt = $database->executeQuery($sql);
		$collection = $stmt->rowCount();
		$ret['collection'] = $collection;
		echo json_encode($ret);
	}
}
