<?php
require_once dirname(dirname(__DIR__))."/lib.inc/auth-siswa.php";
$arg1 = "";
$arg2 = "";
require_once __DIR__."/lib.inc/query-parser.php";
$picoTest = new \Pico\PicoTest($database);
$test_id = addslashes(isset($_GET['test_id'])?$_GET['test_id']:'');
$token = addslashes(isset($_GET['token'])?$_GET['token']:'');
$eligible = false;
$eduTest = $picoTest->getTest($test_id);
try
{
    $eligible = $picoTest->eligible($studentLoggedIn, $eduTest, $token);
    require_once __DIR__."/lib.inc/test-page-handler.php";
}
catch(\Exception $e)
{
    require_once __DIR__."/lib.inc/exception-handler.php";
}
