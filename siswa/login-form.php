<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
include_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<title>Masuk ke <?php echo $cfg->app_name;?></title>
<link type="text/css" rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/login.css">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
</head>
<body>
<div class="all">
<div class="content">
<div class="body">
<div class="logo-180"></div>
<h3>Masuk Sebagai Siswa</h3>
<form name="form1" method="post" action="login.php">
  <div class="input-control"><input type="text" name="username" id="username" placeholder="Username/NIS" required></div>
  <div class="input-control"><input type="password" name="password" id="password" placeholder="Password" required></div>
  <div class="input-control">
  	<input class="button-gradient" type="submit" name="login" id="login" value="Login">
  </div>
</form>
</div>
<div class="footer">
<a href="../">DEPAN</a>
</div>
</div>
</div>
</body>
</html>