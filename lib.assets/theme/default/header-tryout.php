<?php
if(!defined('DB_NAME')) exit();
$cfg->app_name = "Try Out Gratis Ujian Nasional ".$cfg->app_name;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<base href="<?php echo $cfg->base_url;?>tryout/">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/home.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/home.min.css">
<meta property="og:title" content="<?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?>" />
<meta property="og:type" content="website" />
<meta name="og:description" content="<?php echo $cfg->meta_description;?>">
<meta name="description" content="<?php echo $cfg->meta_description;?>">
<title><?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?></title>
</head>

<body>

<div class="all">
	<div class="top-content">
    	<div class="mobile-assets">
            <a class="mobile-menu-trigger mobile-menu-trigger-right" href="#"></a>
            <a class="mobile-menu-trigger mobile-menu-trigger-left" href="#"></a>
            <h1><?php echo $cfg->app_name;?></h1>
        </div>
        <div class="menu-container">
        <div class="menu-wrapper">    
    	<div class="menu menu-left">
        	<ul>
            	<li><a href="./">Depan</a></li>
            	<li><a href="schedule.php">Jadwal</a></li>
            	<li><a href="register.php">Mendaftar</a></li>
            </ul>
        </div>
    	<div class="menu menu-right">
        	<ul>
            	<?php
				if(@$member_id)
				{
				?>
            	<li><a href="ujian.php">Ujian</a></li>
            	<li><a href="profil.php">Profil</a></li>
            	<li><a href="../logout.php">Keluar</a></li>
                <?php
				}
				else
				{
				?>
            	<li><a href="login.php">Masuk</a></li>
            	<li><a href="register.php">Daftar</a></li>
                <?php
				}
				?>
            	<li><a href="../"><?php echo $cfg->app_name;?></a></li>
            </ul>
        </div>
        </div>
    	</div>
    	<div class="banner">
        	<?php
			if(basename($_SERVER['PHP_SELF']) == 'index.php')
			{
			?>
        	<img src="<?php echo $cfg->base_assets;?>tryout/images/banner.jpg">
            <?php
			}
			else
			{
			?>
        	<img src="<?php echo $cfg->base_assets;?>tryout/images/banner-2.jpg">
            <?php
			}
			?>
        </div>
    </div>
    <div class="main-content">
        <div class="main-content-wrapper">
