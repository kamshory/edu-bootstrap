<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$info_id = kh_filter_input(INPUT_POST, "info_id", FILTER_SANITIZE_STRING_NEW);
if(@$_POST['option'] == 'delete')
{
	$sql = "DELETE FROM `edu_info` WHERE `info_id` = '$info_id' ";
	$database->executeDelete($sql, true);
}
