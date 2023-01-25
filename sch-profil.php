<?php
include_once dirname(__FILE__) . "/lib.inc/auth-guru.php";
if (isset($_GET['school_id'])) {
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
}
$cfg->module_title = "Profil";
if (@$auth_teacher_id) {
	include_once dirname(__FILE__) . "/sch-guru-profil.php";
} else {
	include_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";
	if (@$student_id) {
		include_once dirname(__FILE__) . "/sch-siswa-profil.php";
	}
}
