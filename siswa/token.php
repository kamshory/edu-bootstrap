<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
if(isset($_POST['token']) && isset($_POST['test_id']))
{
    $picoTest = new \Pico\PicoTest($database);
    $test_id = addslashes($_POST['test_id']);
    $token = addslashes($_POST['token']);
    $eduTest = $picoTest->getTest($test_id);
    $eduToken = $picoTest->getToken($token, $eduTest, $student_id);
    if(!empty($eduToken->token_id))
    {
        header("Location: ujian/".$test_id."/".$token);
        exit();
    }
}
else if(isset($_POST['token']))
{
    $picoTest = new \Pico\PicoTest($database);
    $token = addslashes($_POST['token']);
    $eduToken = $picoTest->getToken($token, null, $student_id);
    $test_id = $eduToken->test_id;
    if(!empty($eduToken->token_id))
    {
        header("Location: ujian/".$test_id."/".$token);
        exit();
    }
}
if(basename($_SERVER['REQUEST_URI']) != basename($_SERVER['PHP_SELF']))
{
    header("Location: ".$_SERVER['REQUEST_URI']);
}
else
{
    header("Location: ./");
}
