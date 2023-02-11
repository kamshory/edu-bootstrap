<?php
include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
if(empty(@$auth_school_id))
{
include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
}
include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
if(isset($_GET['school_id']))
{
	$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
}
$pageTitle = "Profil";
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
