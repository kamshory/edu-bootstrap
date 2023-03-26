<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	require_once __DIR__."/login-form.php";
	exit();
}
if(count(@$_POST) || true)
{
    require_once dirname(__DIR__)."/lib.inc/add-student.php";
}
else
{
    require_once dirname(__DIR__)."/lib.inc/template-generator-student.php";
}
exit();