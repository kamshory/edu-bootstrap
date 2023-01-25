<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(isset($_POST['sort']))
{
	$array_question = kh_filter_input(INPUT_POST, 'array_question', FILTER_SANITIZE_STRING_NEW);
	$arr = explode(",", trim($array_question, " , "));
	foreach($arr as $key=>$val)
	{
		$order = $key+1;
		$val = addslashes($val);
		$sql = "update `edu_question` set `order` = '$order' where `question_id` = '$val'";
		$database->executeUpdate($sql);
	}
}

