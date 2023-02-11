<?php
include_once dirname(__FILE__)."/lib.inc/auth-admin.php";
$pageTitle = "Profil";
if(!empty($admin_id))
{
	$base_dir = 'admin/';
	include_once dirname(__FILE__)."/admin-profil.php";
}
else
{
	include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
	if(!empty($auth_teacher_id))
	{
		include_once dirname(__FILE__)."/teacher-profil.php";
	}
	else
	{
		include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
		if(!empty(@$student_id))
		{
			include_once dirname(__FILE__)."/student-profil.php";
		}
		else
		{
			include_once dirname(__FILE__)."/bukan-pengguna.php";
		}
	}
}
