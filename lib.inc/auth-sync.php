<?php
require_once __DIR__."/functions-pico.php";
require_once __DIR__."/sessions.php";

$username = "";
$password = "";
$user_id = "";

if(isset($_SESSION['admin_username']))
{
	$username = $_SESSION['admin_username'];
}
if(isset($_SESSION['admin_password']))
{
	$password = $_SESSION['admin_password'];
}

$memberLoggedIn = new \Pico\AuthAdmin($database, $username, $password, false);
$memberLoggedIn->login();

if(empty($memberLoggedIn->admin_id))
{
    if(isset($_SESSION['teacher_username']))
    {
        $username = $_SESSION['teacher_username'];
    }
    if(isset($_SESSION['teacher_password']))
    {
        $password = $_SESSION['teacher_password'];
    }
    $memberLoggedIn = (new \Pico\AuthTeacher($database, $username, $password, false))->login();
    if(!empty($memberLoggedIn->getTeacherId()))
    {
        $user_id = $memberLoggedIn->getTeacherId();
        
    }
}
else
{
    $user_id = $memberLoggedIn->admin_id;

}

if(empty($user_id))
{
    exit();
}


$applicationRoot = dirname(__DIR__);
$permission = 0755;

$fileUploadBaseDir = $syncConfigs->volume_sync_file_upload;
$fileDownloadBaseDir =$syncConfigs->volume_sync_file_download;
$filePoolBaseDir = $syncConfigs->volume_sync_file_pool;
$filePoolName = $syncConfigs->sync_file_pool_name;
$filePoolRollingPrefix = $syncConfigs->sync_database_rolling_prefix;
$filePoolExtension = $syncConfigs->sync_database_extension;



$databaseUploadBaseDir = $syncConfigs->volume_sync_database_upload;
$databaseDownloadBaseDir = $syncConfigs->volume_sync_database_download;
$databasePoolBaseDir = $syncConfigs->sync_database_base_dir;
$databasePoolName = $syncConfigs->sync_file_pool_name;
$databasePoolRollingPrefix = $syncConfigs->sync_database_rolling_prefix;
$databasePoolExtension = $syncConfigs->sync_database_extension;

