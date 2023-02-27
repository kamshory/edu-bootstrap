<?php
require_once dirname(__FILE__)."/functions-pico.php";
require_once dirname(__FILE__)."/sessions.php";

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

$memberLoggedIn = (new \Pico\AuthAdmin($database, $username, $password, false))->login();

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

$applicationRoot = dirname(dirname(__FILE__));
$permission = 0755;

$fileUploadBaseDir = $applicationRoot."/volume.sync/file/upload";
$fileDownloadBaseDir = $applicationRoot."/volume.sync/file/download";
$filePoolBaseDir = $applicationRoot."/volume.sync/file/pool";
$filePoolName = "pool";
$filePoolRollingPrefix = "pool_";
$filePoolExtension = ".txt";

$databaseUploadBaseDir = $applicationRoot."/volume.sync/database/upload";
$databaseDownloadBaseDir = $applicationRoot."/volume.sync/database/download";
$databasePoolBaseDir = $applicationRoot."/volume.sync/database/pool";
$databasePoolName = "pool";
$databasePoolRollingPrefix = "pool_";
$databasePoolExtension = ".txt";

