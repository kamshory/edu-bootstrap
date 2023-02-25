<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-siswa.php";
$arg1 = "";
$arg2 = "";
if(isset($_GET) && !empty($_GET))
{
    if(isset($_GET['arg1']))
    {
        $arg1 = trim($_GET['arg1']);
    }
    if(isset($_GET['arg2']))
    {
        $arg2 = trim($_GET['arg2']);
    }
}
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/lib.inc/login-form.php";
	exit();
}
