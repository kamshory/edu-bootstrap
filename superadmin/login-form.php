<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
include_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
?><!DOCTYPE html>
<html lang="en">
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
<div class="header">
<h1><?php echo $cfg->app_name;?></h1>
</div>
<div class="content">
<h3>Masuk Sebagai Super Administrator</h3>
<form name="form1" method="post" action="login.php">
  <div class="input-label">Username</div> 
  <div class="input-control"><input type="text" name="username" id="username" required></div>
  <div class="input-label">Password</div> 
  <div class="input-control"><input type="password" name="password" id="password" required></div>
  <div class="input-control">
  	<input class="button-gradient" type="submit" name="login" id="login" value="Login">
  </div>
</form>
<p><a href="../">Kembali ke depan</a></p>
</div>
</div>
</body>
</html>