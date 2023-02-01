<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/dom.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
	$reg_number = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$password = md5(kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD));
	$_SESSION['student_username'] = $username;
	$_SESSION['student_password'] = $password;
									 
	$sql = "SELECT `username`, `student_id`
	from `edu_student`
	where (
		(`email` like '$email' and `email` != '')
		or 
		(`reg_number` like '$reg_number' and `reg_number` != '')
		or 
		(`username` like '$username' and `username` != '')
		or 
		(`phone` like '$phone' and `phone` != '')
		) 
		and `password` like md5('$password')
		and `active` = '1'
		and `blocked` = '0'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$_SESSION['student_username'] = $data['username'];
		$_SESSION['student_password'] = $password;
		
		if(isset($_POST['ref']))
		{
			$ref = $_POST['ref'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref);
			}
			else
			{
				header('Location: index.php'); //NOSONAR
			}
		}
		else if(isset($_SERVER['HTTP_REFERER']))
		{
			$ref = $_SERVER['HTTP_REFERER'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref);
			}
			else
			{
				header('Location: index.php'); //NOSONAR
			}
		}
		else
		{
			header('Location: index.php'); //NOSONAR
		}
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
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
	
	$student_login = new StudenAuth($database, $username, $password, false);
	
	$student_id = 0;
	$school_id = 0;
	$class_id = 0;
	$auth_student_school_id = 0;
	$auth_school_id = 0;
	$use_token = 0;
	if($student_login->student_id)
	{
		$student_id = $auth_student_id = $student_login->student_id;
		$student_name = $student_login->name;
		$school_id = $auth_student_school_id = $auth_school_id = $student_login->school_id;
		$school_code = $student_login->school_code;
		$class_id = $student_login->class_id;
		$use_token = $student_login->use_token;
	}

	if(@$student_id)
	{
		include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php";
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


		<div class="card-container row container container-fluid d-flex justify-content-between">
			<?php

			$sql = "SELECT `edu_info`.* 
			from `edu_info` 
			where `edu_info`.`active` = '1'
			order by `edu_info`.`info_id` desc
			limit 0, 2
			";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
			?>
		<?php
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$content = "";
		foreach($rows as $data)
		{
			$pars = extractParagraph($data['content']);
			foreach($pars as $txt)
			{
				if(!empty($txt))
				{
					$content = $txt;
					$content = preg_replace('/[\s]+/', ' ', $content);
					if(strlen($content) > 100)
					{
						$content = substr($content, 0, 100)."&hellip;";
					}
				}
			}
			?>
			<div class="col-sm-6">
					<div class="card">
					<div class="card-body">
						<h5 class="card-title"><?php echo $data['name'];?></h5>
						<p class="card-text"><?php echo $content;?></p>
						<a href="informasi.php?info_id=<?php echo $data['info_id'];?>" class="btn btn-primary">Baca</a>
					</div>
					</div>
				</div>
				<?php
		}
		?>
		<?php
			}
			?>

				
			</div>

		<?php
		include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php";
		exit();
	}
	
	if(!$loged_in)
	{
		if(isset($_SESSION['teacher_username']))
		{
			$username = $_SESSION['teacher_username'];
		}
		
		if(isset($_SESSION['teacher_password']))
		{
			$password = $_SESSION['teacher_password'];
		}
		
		$teacher_login = new TeacherAuth($database, $username, $password, false);
		
		$teacher_id = 0;
		$school_id = 0;
		$auth_teacher_id = 0;
		$auth_school_id = 0;
		$auth_teacher_school_id = 0;
		$school_code = '';
		$use_token = 0;
		if($teacher_login->teacher_id)
		{
			$teacher_id = $auth_teacher_id = $teacher_login->teacher_id;
			$school_id = $auth_school_id = $auth_teacher_school_id = $teacher_login->school_id;
			$school_code = $teacher_login->school_code;
			$school_name = $teacher_login->school_name;
			$use_token = $teacher_login->use_token;
			$loged_in = true;
		}
		if(@$teacher_id)
		{
			include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php";
			?>
			<ul class="shortcut-image-80">
				<li><a href="./"><div><img alt="img" src="lib.assets/theme/default/css/images/home-80.png" /></div><div class="menu-label">Depan</div></a></li>
				<li><a href="guru/kelas.php"><div><img alt="img" src="lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a></li>
				<li><a href="guru/siswa.php"><div><img alt="img" src="lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a></li>
				<li><a href="guru/guru.php"><div><img alt="img" src="lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a></li>
				<li><a href="guru/artikel.php"><div><img alt="img" src="lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a></li>
				<li><a href="guru/informasi.php"><div><img alt="img" src="lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a></li>
				<li><a href="guru/ujian.php"><div><img alt="img" src="lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a></li>
				<li><a href="guru/profil.php"><div><img alt="img" src="lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a></li>
				<li><a href="guru/logout.php"><div><img alt="img" src="lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a></li>
			</ul>
			<?php
			include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php";
			exit();
		}
	}
	
	if(!$loged_in)
	{
		if(isset($_SESSION['admin_username']))
		{
			$username = $_SESSION['admin_username'];
		}
		
		if(isset($_SESSION['admin_password']))
		{
			$password = $_SESSION['admin_password'];
		}
		$admin_login = new AdminAuth($database, $username, $password, false);
		$admin_id = 0;
		$school_id = 0;
		$real_school_id = 0;
		$use_token = 0;
		if($admin_login->admin_id)
		{
			$admin_id = $auth_admin_id = $admin_login->admin_id;
			$school_id = $auth_school_id = $admin_login->school_id;
			$real_school_id = $auth_school_id = $admin_login->real_school_id;
			$school_name = $admin_login->school_name;
			$school_code = $admin_login->school_code;
			$use_token = $admin_login->use_token;
		}
		if(@$admin_id)
		{
			include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php";
			?>
			<ul class="shortcut-image-80">
				<li><a href="./"><div><img alt="img" src="lib.assets/theme/default/css/images/home-80.png" /></div><div class="menu-label">Depan</div></a></li>
				<li><a href="admin/kelas.php"><div><img alt="img" src="lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a></li>
				<li><a href="admin/siswa.php"><div><img alt="img" src="lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a></li>
				<li><a href="admin/guru.php"><div><img alt="img" src="lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a></li>
				<li><a href="admin/artikel.php"><div><img alt="img" src="lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a></li>
				<li><a href="admin/informasi.php"><div><img alt="img" src="lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a></li>
				<li><a href="admin/ujian.php"><div><img alt="img" src="lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a></li>
				<li><a href="admin/profil.php"><div><img alt="img" src="lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a></li>
				<li><a href="admin/logout.php"><div><img alt="img" src="lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a></li>
			</ul>
			<?php
			include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php";
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