<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
	exit();
}
if(isset($_POST['save']) && isset($_POST['question_id']))
{
	$question_id = kh_filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_STRING_NEW);
	$basic_competence = trim(kh_filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING_NEW));
	$basic_competence = preg_replace("/[^0-9]/i", ".", $basic_competence);
	$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");
	$sql = "update `edu_question` set `basic_competence` = '$basic_competence' 
	where `question_id` = '$question_id' 
	";
	$database->executeUpdate($sql);
}
