<?php

require_once dirname(__DIR__)."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	exit();
}

$test_id = addslashes($_POST['test_id']);
$answer_id = addslashes($_POST['answer_id']);
$answer = addslashes(json_encode($_POST['answer']));
$session_id = md5(session_id());

$sql = "UPDATE `edu_answer` SET `answer` = '$answer', `last_session_id` = '$session_id' 
WHERE `test_id` = '$test_id' AND `answer_id` = '$answer_id' AND `student_id` = '$student_id' ";

$database->executeUpdate($sql, true);