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
require_once dirname(__FILE__) . "/classes/TeacherAuth.php";

$memberLoggedIn = new \AdminAuth($database, $username, $password, false);

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
    require_once dirname(__FILE__) . "/classes/TeacherAuth.php";
    $memberLoggedIn = new \TeacherAuth($database, $username, $password, false);
    if(empty($memberLoggedIn->teacher_id))
    {
        $user_id = $memberLoggedIn->teacher_id;
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

$fileUploadBaseDir = $applicationRoot."/lib.sync/file/upload";
$fileDownloadBaseDir = $applicationRoot."/lib.sync/file/download";
$filePoolBaseDir = $applicationRoot."/lib.sync/file/pool";
$filePoolName = "pool";
$filePoolRollingPrefix = "pool_";
$filePoolExtension = ".txt";

$databaseUploadBaseDir = $applicationRoot."/lib.sync/database/upload";
$databaseDownloadBaseDir = $applicationRoot."/lib.sync/database/download";
$databasePoolBaseDir = $applicationRoot."/lib.sync/database/pool";
$databasePoolName = "pool";
$databasePoolRollingPrefix = "pool_";
$databasePoolExtension = ".txt";

