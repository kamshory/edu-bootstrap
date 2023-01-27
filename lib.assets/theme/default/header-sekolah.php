<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<base href="<?php echo $cfg->base_url;?>">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/school.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/school.css">
<meta property="og:title" content="<?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?>" />
<meta property="og:type" content="website" />
<meta name="og:description" content="<?php echo $cfg->meta_description;?>">
<meta name="description" content="<?php echo $cfg->meta_description;?>">
<title><?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?></title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="all">
	<div class="top-bar">
    	
    </div>
    
  <div class="main-wrapper">
        <div class="header">
            <div class="banner">
                <img src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/banner-school.jpg">
            </div>
            <div class="main-menu">
            	<div class="mobile-menu-trigger"><a href="#">Menu</a></div>
            	<ul data-mobile-display="false">
                	<?php
					if(@$page_school_id == @$auth_school_id && @$auth_student_id)
					{
					?>
                	<li data-selected="<?php if($page_tab=='') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/">Depan</a></li>
                	<li data-selected="<?php if($page_tab=='about') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=about">Tentang</a></li>
                	<li data-selected="<?php if($page_tab=='student') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=student">Siswa</a></li>
                	<li data-selected="<?php if($page_tab=='teacher') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=teacher">Guru</a></li>
                	<li data-selected="<?php if($page_tab=='article') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=article">Artikel</a></li>
                	<li><a href="<?php echo $school_code;?>/ujian.php">Ujian</a></li>
                	<li data-selected="<?php if($page_tab=='profile') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=profile">Profil</a></li>
                	<li><a href="./"><?php echo $cfg->app_name;?></a></li>
                    <?php
					}
					else
					{
					?>
                	<li data-selected="<?php if($page_tab=='') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/">Depan</a></li>
                	<li data-selected="<?php if($page_tab=='about') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=about">Tentang</a></li>
                	<li data-selected="<?php if($page_tab=='student') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=student">Siswa</a></li>
                	<li data-selected="<?php if($page_tab=='teacher') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=teacher">Guru</a></li>
                	<li data-selected="<?php if($page_tab=='article') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=article">Artikel</a></li>
                	<li data-selected="<?php if($page_tab=='test') echo 'true'; else echo 'false';?>"><a href="<?php echo $school_code;?>/?tab=test">Ujian</a></li>
                	<li><a href="./"><?php echo $cfg->app_name;?></a></li>
                    <?php
					}
					?>
                </ul>
            </div>
      </div>
      
      <div class="main-content">
 