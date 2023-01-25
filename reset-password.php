<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
$err = '';
$link_ok = false;
if(isset($_GET['username']) && isset($_GET['auth']))
{
	$username = kh_filter_input(INPUT_GET, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$auth = kh_filter_input(INPUT_GET, 'auth', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$sql = "select `username`, `member_id`, `email`, `auth`
	from `member`
	where (`username` like '$username' and `auth` like '$auth') 
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$un = $data['username'];
		$link_ok = true;
		if(isset($_POST['reset-password']))
		{
			$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
			$password2 = kh_filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_PASSWORD);
			if($password == $password2 && strlen($password)>3)
			{
				$newauth = md5(mt_rand(111111,999999));
				$sql = "update `member`
				set `password` = md5(md5('$password')),
				`auth` = '$newauth'
				where (`username` like '$un' and `auth` like '$auth') 
				";
				$database->execute($sql);
				$_SESSION['username'] = $un;
				$_SESSION['password'] = md5($password);
				header('Location: index.php');
			}
		}
	}
	
	
	
	
}
if(isset($_POST['username']) && isset($_POST['send']))
{
	$username = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
	$password = md5(kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD));
	
	$auth = md5($_SERVER['REMOTE_ADDR'].date('Y-m-d'));
	$sql = "update `member` set `auth` = '$auth'
	where ((`email` like '$email' and '$email' != '') or `username` like '$username' or (`phone` like '$phone' and '$phone' != '')) 
	";
	$database->execute($sql);
	
	$sql = "select `username`, `member_id`, `email`, `auth`, `name`
	from `member`
	where ((`email` like '$email' and '$email' != '') or `username` like '$username' or (`phone` like '$phone' and '$phone' != '')) 
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$receiver_name = $data['name'];
		$email = $data['email'];
		$username = $data['username'];
		$auth = $data['auth'];
		$link = $cfg->base_url."/reset-password.php?username=$username&auth=$auth";
		
$message = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Reset Password - '.$cfg->app_name.'</title>
</head>

<body>
	<p>Seseorang, mungkin Anda, telah meminta link reset password untuk dikirimkan ke email Anda. </p>
	<p>Berikut ini merupakan link reset password:</p>
	<p><a href="__LINK__">__LINK__</a></p>
	<p>Buka link di atas dengan menggunakan browser kemudian masukkan password yang baru.</p>
	<p>&nbsp;</p>
	<p>Tim '.$cfg->app_name.'</p>
	</body>
</html>
';	
		$message = str_replace('__LINK__', $link, $message);

		$targetmail = $data['email'];
		
		$subject = "Reset Password ".$cfg->app_name."";
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=utf-8" . "\r\n";
		// Additional headers
		$headers .= "From: Reset Password ".$cfg->app_name." <".$cfg->mail_reset_password.">\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();
		
		$message = wordwrap($message, 70);
		$err = '';
		
		date_default_timezone_set('Asia/Jakarta');
		
		require_once dirname(__FILE__)."/lib.inc/PHPMailer/PHPMailerAutoload.php";
		
		//Create a new PHPMailer instance
		$mail = new PHPMailer;
		
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
		
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		
		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;
		
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';
		
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = "planetbiru.id@gmail.com";
		
		//Password to use for SMTP authentication
		$mail->Password = "BanyakBacot2014";
		
		//Set who the message is to be sent from
		$mail->setFrom('noreply@beta.planetbiru.net', 'Reset Passowrd Planetbiru');
		
		//Set an alternative reply-to address
		$mail->addReplyTo('noreply@beta.planetbiru.net', 'Reset Passowrd Planetbiru');
		
		//Set who the message is to be sent to
		$mail->addAddress($targetmail, $receiver_name);
		
		//Set the subject line
		$mail->Subject = $subject;
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML($message);
		
		//Replace the plain text body with one created manually
		$mail->AltBody = strip_tags($message);
		
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		
		//send the message, check for errors
		if (!$mail->send()) {
			$err = 2;
		} else {
			$err = 1;
		}		
	
	}
	else
	{
		$err = 3;
		// tidak terdaftar
	}
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<title>Reset Password - Planetedu</title>
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
	if($link_ok)
	{
	?>
	<form name="loginform" action="" method="post" enctype="multipart/form-data" onSubmit="return (document.getElementById('password').value == document.getElementById('password2').value && document.getElementById('password').value.length > 3)">
	<div class="input-label">Password Baru</div>
	<div class="input-control"><input data-full-width="true" type="password" name="password" id="password" class="input-login" autocomplete="off" required /></div>
	<div class="input-label">Ulangi</div>
	<div class="input-label"><input data-full-width="true" type="password" name="password2" id="password2" class="input-login" autocomplete="off" required /></div>
    <div data-role="button-set">
    <input class="button-gradient" type="submit" name="reset-password" id="reset-password" value="Simpan Password" />
    </div>
    </form>
	<?php
	}
	else if(isset($_POST['send']))
	{
		if($err == 3)
		{
			// not registered
			?>
			<div class="message"><p>Username tidak terdaftar.</p></div>
			<div class="backlink"><a href="reset-password.php"><?php echo $language_res[$language_id]['txt_back'];?></a></div>
			<?php
		}
		else if($err == 2)
		{
			?>
			<div class="message"><p>Gagal mengirim email.</p></div>
			<div class="backlink"><a href="login.php">Masuk</a></div>
			<?php
			// fail to sent
		}
		else if($err == 1)
		{
			// send
			?>
			<div class="message"><p>Email telah dikirim.</p></div>
			<div class="backlink"><a href="reset-password.php">Kembali</a></div>
			<?php
		}
		else 
		{
			// unknown error
			?>
			<div class="message"><p>Kesalahan tak terdefinisikan.</p></div>
			<div class="backlink"><a href="reset-password.php">Kembali</a></div>
			<?php
		}
	}
else
	{
	?>
    <h3>Reset Password</h3>
        <form id="form1" name="form1" method="post" action="reset-password.php">
        <div class="input-label">Username</div>
        <div class="input-control"><input type="text" name="username" id="username" placeholder="Username" data-full-width="true" /></div>
        <div class="input-control"><input type="hidden" name="ref" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']);?>">
        <input class="button-gradient" type="submit" name="send" id="send" value="Kirim" /></td>
        <input class="button-gradient2" type="button" name="login" id="login" value="Masuk" onclick="window.location='login.php'" />
        </div>
        </form>
	<?php
	}
	?>
<p><a href="http://www.planetbiru.com/register.php" target="_blank">Klik di sini jika belum terdaftar</a></p>
<p><a href="./">Kembali ke depan</a></p>
</div>
</div>
</body>
</html>