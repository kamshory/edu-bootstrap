<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(@$cfg->protocol == 'http')
{
	header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
}
if(!@$adminLoggedIn->admin_id)
{
	require_once __DIR__."/login-form.php";
        exit();
}
if($adminLoggedIn->admin_level != 1)
{
	require_once __DIR__."/bukan-super-admin.php";
	exit();
}
$pageTitle = "Super Admin";
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
