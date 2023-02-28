<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	exit();
}
if(isset($_POST['sort']))
{
	$array_question = kh_filter_input(INPUT_POST, "array_question", FILTER_SANITIZE_STRING_NEW);
	$arr = explode(",", trim($array_question, " , "));
	foreach($arr as $key=>$val)
	{
		$sort_order = $key+1;
		$val = addslashes($val);
		$sql = "UPDATE `edu_question` SET `sort_order` = '$sort_order' WHERE `question_id` = '$val'";
		$database->executeUpdate($sql, true);
	}
}

