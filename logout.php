<?php
include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
include_once dirname(__FILE__) . "/lib.inc/sessions.php";
if (isset($_GET['confirm-logout'])) {
	include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
	include_once dirname(__FILE__) . "/lib.inc/sessions.php";
	unset($_SESSION['student_username']);
	unset($_SESSION['student_password']);
	unset($_SESSION['teacher_username']);
	unset($_SESSION['teacher_password']);
	unset($_SESSION['admin_username']);
	unset($_SESSION['admin_password']);
	session_destroy();
	header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="msapplication-navbutton-color" content="#3558BE">
	<meta name="theme-color" content="#3558BE">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
	<title>Keluar <?php echo $cfg->app_name; ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/no-data.css">
	<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/images/favicon.png" />
</head>

<body>
	<div class="all">
		<div class="header">
			<h1><?php echo $cfg->app_name; ?></h1>
		</div>
		<div class="content">
			<p>Apakah Anda yakin akan keluar?</p>
			<p>
				<a class="button-gradient" href="logout.php?confirm-logout=yes">Ya</a>
				<a class="button-gradient2" href="index.php">Tidak</a>
			</p>
		</div>
	</div>
</body>

</html>