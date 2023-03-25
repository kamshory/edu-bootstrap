<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	require_once __DIR__."/login-form.php";
	exit();
}
if(count(@$_POST))
{
    print_r($_FILES);
}
else
{
    require_once dirname(__DIR__)."/lib.inc/template-generator-student.php";
}
exit();