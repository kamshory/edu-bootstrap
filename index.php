<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";

include_once dirname(__FILE__)."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
	$reg_number = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$password = md5(kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD));

	$passwordHash = md5($password);

	$sql = "SELECT `username`, `student_id`
	FROM `edu_student`
	WHERE (
			(`email` LIKE '$email' AND `email` != '')
			OR 
			(`reg_number` LIKE '$reg_number' AND `reg_number` != '')
			OR 
			(`username` LIKE '$username' AND `username` != '')
			OR 
			(`phone` LIKE '$phone' AND `phone` != '')
		) 
		AND `password` LIKE '$passwordHash'
		AND `active` = true
		AND `blocked` = false
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$_SESSION['student_username'] = $data['username'];
		$_SESSION['student_password'] = $password;
		
		if(isset($_POST['ref']))
		{
			$ref = $_POST['ref'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref); //NOSONAR
			}
			else
			{
				header('Location: '.$picoEdu->gateBaseSelfName()); //NOSONAR
			}
		}
		else if(isset($_SERVER['HTTP_REFERER']))
		{
			$ref = $_SERVER['HTTP_REFERER'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref); //NOSONAR
			}
			else
			{
				header('Location: '.$picoEdu->gateBaseSelfName()); //NOSONAR
			}
		}
		else
		{
			header('Location: '.$picoEdu->gateBaseSelfName()); //NOSONAR
		}
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
	include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
	$username = '';
	$password = '';
	$loged_in = false;
	if(isset($_SESSION['student_username']))
	{
		$username = $_SESSION['student_username'];
	}
	if(isset($_SESSION['student_password']))
	{
		$password = $_SESSION['student_password'];
	}
	
	$studentLoggedIn = new \Pico\AuthStudent($database, $username, $password, false);
	$studentLoggedIn->login();
	
	$student_id = '';
	$school_id = '';
	$class_id = '';
	$auth_student_school_id = '';
	$auth_school_id = '';
	$use_token = 0;
	if($studentLoggedIn->student_id)
	{
		$student_id = $auth_student_id = $studentLoggedIn->student_id;
		$student_name = $studentLoggedIn->name;
		$school_id = $auth_student_school_id = $auth_school_id = $studentLoggedIn->school_id;
		$school_code = $studentLoggedIn->school_code;
		$class_id = $studentLoggedIn->class_id;
		$use_token = $studentLoggedIn->use_token;
	}

	if(!empty($student_id))
	{
		include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php"; //NOSONAR
		?>
		
		<div class="card-container row container container-fluid d-flex justify-content-between">
			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/kelas.php"><div><img alt="img" src="lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/siswa.php"><div><img alt="img" src="lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/guru.php"><div><img alt="img" src="lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/artikel.php"><div><img alt="img" src="lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a>
					</div>
				</div>
			</div>

			</div>

			<div class="card-container row container container-fluid">

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/informasi.php"><div><img alt="img" src="lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/ujian.php"><div><img alt="img" src="lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/profil.php"><div><img alt="img" src="lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="siswa/logout.php"><div><img alt="img" src="lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a>
					</div>
				</div>
			</div>
		</div>

		<?php
		include_once dirname(__FILE__)."/lib.inc/inc-informasi.php"; //NOSONAR
		include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php"; //NOSONAR
		exit();
	}
	
	if(!$loged_in)
	{
		include_once dirname(__FILE__)."/lib.inc/auth-guru.php";
		if(isset($_SESSION['teacher_username']))
		{
			$username = $_SESSION['teacher_username'];
		}	
		if(isset($_SESSION['teacher_password']))
		{
			$password = $_SESSION['teacher_password'];
		}
		
		$teacherLoggedIn = new \Pico\AuthTeacher($database, $username, $password, false);	
		$teacherLoggedIn->login();

		$teacher_id = '';
		$school_id = "";
		$auth_teacher_id = '';
		$auth_school_id = '';
		$auth_teacher_school_id = '';
		$school_code = '';
		$use_token = 0;
		if(!empty($teacherLoggedIn->getTeacherId()))
		{
			$teacher_id = $auth_teacher_id = $teacherLoggedIn->getTeacherId();
			$school_id = $auth_school_id = $auth_teacher_school_id = $teacherLoggedIn->getSchoolId();
			$school_code = $teacherLoggedIn->getSchoolCode();
			$school_name = $teacherLoggedIn->getSchoolName();
			$use_token = $teacherLoggedIn->getUseToken();
			$loged_in = true;
		}
		if(!empty($teacher_id))
		{
			include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php"; //NOSONAR
			?>
			<div class="card-container row container container-fluid d-flex justify-content-between">
			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/kelas.php"><div><img alt="img" src="lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/siswa.php"><div><img alt="img" src="lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/guru.php"><div><img alt="img" src="lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/artikel.php"><div><img alt="img" src="lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a>
					</div>
				</div>
			</div>

			</div>

			<div class="card-container row container container-fluid">

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/informasi.php"><div><img alt="img" src="lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/ujian.php"><div><img alt="img" src="lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/profil.php"><div><img alt="img" src="lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="guru/logout.php"><div><img alt="img" src="lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a>
					</div>
				</div>
			</div>

		</div>
			<?php
			include_once dirname(__FILE__)."/lib.inc/inc-informasi.php"; //NOSONAR
			include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php"; //NOSONAR
			exit();
		}
	}
	
	if(!$loged_in)
	{
		include_once dirname(__FILE__)."/lib.inc/auth-admin.php";
		if(isset($_SESSION['admin_username']))
		{
			$username = $_SESSION['admin_username'];
		}
		
		if(isset($_SESSION['admin_password']))
		{
			$password = $_SESSION['admin_password'];
		}
		$adminLoggedIn = new \Pico\AuthAdmin($database, $username, $password, false);
		$adminLoggedIn->login();
		$admin_id = "";
		$school_id = "";
		$real_school_id = "";
		$use_token = 0;
		if($adminLoggedIn->admin_id)
		{
			$admin_id = $auth_admin_id = $adminLoggedIn->admin_id;
			$school_id = $auth_school_id = $adminLoggedIn->school_id;
			$real_school_id = $auth_school_id = $adminLoggedIn->real_school_id;
			$school_name = $adminLoggedIn->school_name;
			$school_code = $adminLoggedIn->school_code;
			$use_token = $adminLoggedIn->use_token;
		}
		if(!empty($admin_id))
		{
			include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php"; //NOSONAR
			?>
			<div class="card-container row container container-fluid d-flex justify-content-between">
			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/kelas.php"><div><img alt="img" src="lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/siswa.php"><div><img alt="img" src="lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/guru.php"><div><img alt="img" src="lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/artikel.php"><div><img alt="img" src="lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a>
					</div>
				</div>
			</div>

			</div>

			<div class="card-container row container container-fluid">

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/informasi.php"><div><img alt="img" src="lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a>
					</div>
				</div>
			</div>


			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/ujian.php"><div><img alt="img" src="lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/profil.php"><div><img alt="img" src="lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6">
				<div class="card">
					<div class="card-body card-center">
					<a href="admin/logout.php"><div><img alt="img" src="lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a>
					</div>
				</div>
			</div>

		</div>
			<?php
			include_once dirname(__FILE__)."/lib.inc/inc-informasi.php"; //NOSONAR
			include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php"; //NOSONAR
			exit();
		}
	}
	
	if(!$loged_in)
	{
		include_once dirname(__FILE__)."/login-form.php";
		exit();
	}
}
?>