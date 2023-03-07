<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-siswa.php";
$arg1 = "";
$arg2 = "";
require_once dirname(__FILE__)."/lib.inc/query-parser.php";
$picoTest = new \Pico\PicoTest($database);
$test_id = addslashes($_GET['test_id']);
$token = addslashes($_GET['token']);
$eligible = false;
$eduTest = $picoTest->getTest($test_id);
try
{
    $eligible = $picoTest->eligible($studentLoggedIn, $eduTest, $token);
    require_once dirname(__FILE__)."/lib.inc/test-page-handler.php";
}
catch(\Exception $e)
{
    print_r($e->getMessage());
    print_r($e->getCode()); 
    require_once dirname(__FILE__)."/lib.inc/exception-handler.php";

}
