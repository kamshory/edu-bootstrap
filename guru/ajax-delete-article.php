<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	exit();
}
$article_id = kh_filter_input(INPUT_POST, "article_id", FILTER_SANITIZE_STRING_NEW);
if(@$_POST['option'] == 'delete')
{
	$sql = "DELETE FROM `edu_article` WHERE `article_id` = '$article_id' AND `school_id` = '$school_id' AND `teacher_id` = '$auth_teacher_id' ";
	$database->executeDelete($sql, true);
}
