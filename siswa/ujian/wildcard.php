<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-siswa.php";
$arg1 = "";
$arg2 = "";
require_once dirname(dirname(__FILE__))."/lib.inc/query-parser.php";

$testStudent = new \Pico\PicoTest($database);
$test_id = addslashes($_GET['arg1']);
$token = addslashes($_GET['arg2']);
$eligible = false;
try
{
    $eligible = $testStudent->eligible($test_id, $studentLoggedIn, $token);
}
catch(\Exception $e)
{
    print_r($e->getMessage());
    print_r($e->getCode()); 
}
