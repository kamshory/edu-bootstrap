<?php
include_once dirname(__FILE__)."/lib.inc/auth-admin.php";
$cfg->module_title = "Profil";
if(@$admin_id)
{
	$base_dir = 'admin/';
	include_once dirname(__FILE__)."/admin-profil.php";
}
else
{
	include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
	if(@$auth_teacher_id)
	{
		include_once dirname(__FILE__)."/teacher-profil.php";
	}
	else
	{
		include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
		if(@$student_id)
		{
			include_once dirname(__FILE__)."/student-profil.php";
		}
		else
		{
			include_once dirname(__FILE__)."/bukan-pengguna.php";
		}
	}
}
?>