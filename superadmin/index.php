<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(@$cfg->protocol == 'http')
{
	header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
}
if(!@$adminLoggedIn->admin_id)
{
	require_once dirname(__FILE__)."/login-form.php";
        exit();
}
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$cfg->page_title = "Super Admin";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
