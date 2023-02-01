<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(isset($_POST['sort']))
{
	$array_question = kh_filter_input(INPUT_POST, 'array_question', FILTER_SANITIZE_STRING_NEW);
	$arr = explode(",", trim($array_question, " , "));
	foreach($arr as $key=>$val)
	{
		$sort_order = $key+1;
		$val = addslashes($val);
		$sql = "UPDATE `edu_question` set `order` = '$sort_order' where `question_id` = '$val'";
		$database->executeUpdate($sql, true);
	}
}
