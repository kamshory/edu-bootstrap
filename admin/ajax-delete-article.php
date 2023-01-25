<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
	exit();
}
$article_id = kh_filter_input(INPUT_POST, 'article_id', FILTER_SANITIZE_STRING_NEW);
if(@$_POST['option'] == 'delete')
{
	$sql = "DELETE FROM `edu_article` where `article_id` = '$article_id' and `school_id` = '$school_id' ";
	$database->executeDelete($sql);
}
