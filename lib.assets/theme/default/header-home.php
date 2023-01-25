<?php
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<base href="<?php echo $cfg->base_url;?>">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/home.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/home.min.css">
<?php
/*
<meta property="og:title" content="<?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?>" />
<meta property="og:type" content="website" />
<meta name="og:description" content="<?php echo $cfg->meta_description;?>">
<meta name="description" content="<?php echo $cfg->meta_description;?>">
*/
?>
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
            	<li><a href="about.php">Tentang</a></li>
            	<li><a href="features.php">Fitur</a></li>
            	<li><a href="usermanual">Panduan</a></li>
            </ul>
        </div>
    	<div class="menu menu-right">
        	<ul>
            	<?php
				if(@$member_id)
				{
				?>
            	<li><a href="siswa">Siswa</a></li>
            	<li><a href="guru">Guru</a></li>
            	<li><a href="admin">Sekolah</a></li>
            	<li><a href="profil.php">Profil</a></li>
            	<li><a href="logout.php">Keluar</a></li>
                <?php
				}
				else
				{
				?>
            	<li><a href="siswa">Siswa</a></li>
            	<li><a href="guru">Guru</a></li>
            	<li><a href="admin">Sekolah</a></li>
                <?php
				}
				?>
            </ul>
        </div>
        </div>
    	</div>
    	<div class="banner">
        	<img src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/banner-2.jpg">
        </div>
    </div>
    