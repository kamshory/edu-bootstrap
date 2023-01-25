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
<base href="<?php echo $cfg->base_url;?>siswa/">
<title><?php if(isset($cfg->module_title)) echo ltrim($cfg->module_title.' - ', ' - ');?><?php echo $cfg->app_name;?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/css.min.css">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/js.js"></script>
</head>

<body>
<div class="all">
	<div class="header">
    	<div class="mobile-assets">
            <a class="mobile-menu-trigger mobile-menu-trigger-right" href="#"></a>
            <a class="mobile-menu-trigger mobile-menu-trigger-left" href="#"></a>
            <h1><?php if(isset($cfg->module_title)) echo $cfg->module_title; else echo $cfg->app_name;?></h1>
        </div>    
    	<div class="menu menu-left">
    	  <ul>
    	    <li><a href="../">Depan</a></li>
    	    <li><a href="sekolah.php">Sekolah</a></li>
    	    <li><a href="kelas.php">Kelas</a></li>
    	    <li><a href="siswa.php">Siswa</a></li>
    	    <li><a href="guru.php">Guru</a></li>
    	    <li><a href="artikel.php">Artikel</a></li>
    	    <li><a href="ujian.php">Ujian</a></li>
  	      </ul>
    	</div>
    	<div class="menu menu-right">
    	  <ul>
    	    <li><a href="profil.php">Profil</a></li>
    	    <li><a href="../logout.php">Keluar</a></li>
  	      </ul>
    	</div>
    </div>
    
    <div class="content">
   	  <!-- content begin -->
      