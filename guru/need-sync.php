<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
include_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";

if(isset($_POST['sync']))
{
	$teacher_id = kh_filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_UINT);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$auth = kh_filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_STRING_NEW);
	$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
	if($password != '')
	{
		$sql = "SELECT `edu_teacher`.*
		from `edu_teacher` 
		where `edu_teacher`.`teacher_id` = '$teacher_id' and `edu_teacher`.`email` like '$email' and `edu_teacher`.`auth` like '$auth' ";
		$res = mysql_query($sql);
		if(mysql_num_rows($res))
		{
			$sql = "select * from `member` where `email` like '$email' and `member_id` != '$teacher_id' ";
			$res = mysql_query($sql);
			if(!mysql_num_rows($res))
			{
			
				$sql = "update `member` set `email` = '$email', `password` = md5(md5('$password')), `active` = '1', `blocked` = '0' 
				where `member_id` = '$teacher_id' ";
				mysql_query($sql);
				$sql = "SELECT `username`, `member_id`
				from `member`
				where `email` like '$email' and `password` like md5(md5('$password'))
				";
				$res = mysql_query($sql);
				if(mysql_num_rows($res))
				{
					$data = mysql_fetch_assoc($res);
					$_SESSION['username'] = $data['username'];
					$_SESSION['password'] = md5($password);
					header("Location: profil.php");
				}
			}
			else
			{
				// someone take email
				header("Location: need-sync.php?option=email-taken&id=$teacher_id&email=$email&auth=$auth");
			}
		}
	}
}

$teacher_id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
$email = kh_filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
$auth = kh_filter_input(INPUT_GET, 'auth', FILTER_SANITIZE_STRING_NEW);

$sql = "SELECT `edu_teacher`.*
from `edu_teacher` 
where `edu_teacher`.`teacher_id` = '$teacher_id' and `edu_teacher`.`email` like '$email' and `edu_teacher`.`auth` like '$auth' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<title>Memerlukan Sinkronisasi</title>
<link type="text/css" rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/login.css">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
</head>
<body>
<div class="all">
<div class="header">
<h1><?php echo $cfg->app_name;?></h1>
</div>
<div class="content">
<?php
if(@$_GET['option'] == 'email-taken')
{
?>
<h3>Sinkronisasi Gagal</h3>
<p>Mohon maaf <strong><?php echo $data['name'];?></strong></p>
<p>Email yang Anda gunakan di <?php echo $cfg->app_name;?> telah digunakan oleh pengguna lain di Planetbiru. Kami tidak melakukan perubahan apapun kecuali:</p>
<ol>
  <li>Anda meminta kepada sekolah untuk mengganti alamat email dengan yang baru yang Anda yakin milik Anda dan tidak digunakan oleh orang lain.</li>
  <li>Anda melakukan <a href="http://www.planetbiru.com/reset-password.php" target="_blank">reset password dari Planetbiru</a>.</li>
</ol>
<p><a href="http://www.planetbiru.com/reset-password.php" target="_blank">Klik di sini untuk reset password</a></p>
<p><a href="../">Kembali ke depan</a></p>
<?php
}
else
{
?>
<h3>Memerlukan Sinkronisasi</h3>
<p>Selamat Datang <strong><?php echo $data['name'];?></strong></p>
<p>Password <?php echo $cfg->app_name;?> Anda tidak sesuai dengan password Planetbiru Anda. Anda harus menyamakannya dengan cara melakukan sinkronisasi. Setelah proses sinkronisasi, password <?php echo $cfg->app_name;?> akan sama dengan password Planetbiru dan Anda dapat masuk ke dalam dua akun sekaligus dengan menggunakan satu password saja.</p>
<form name="form1" method="post" action="need-sync.php">
  <div class="input-label">Email Saat Ini</div> 
  <div class="input-control"><input type="email" name="email" id="email" value="<?php echo $data['email'];?>" readonly></div>
  <div class="input-label">Password Saat Ini</div> 
  <div class="input-control"><input type="password" name="password" id="password"></div>
  <div class="input-control">
  	<input class="button-gradient" type="submit" name="sync" id="sync" value="Sinkronisasi">
  	<input class="button-gradient2" type="button" name="reset" id="reset" value="Reset Password" onclick="window.location='../reset-password.php'">
  </div>
  <input type="hidden" name="id" value="<?php echo $data['teacher_id'];?>">
  <input type="hidden" name="email" value="<?php echo $data['email'];?>">
  <input type="hidden" name="auth" value="<?php echo $data['auth'];?>">
</form>

<p><a href="http://www.planetbiru.com/register.php" target="_blank">Klik di sini jika belum terdaftar</a></p>
<p><a href="../">Kembali ke depan</a></p>
<?php
}
?>
</div>
</div>
</body>
</html>
<?php
}
?>