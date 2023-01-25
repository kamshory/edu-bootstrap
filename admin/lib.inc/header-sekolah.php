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
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/school.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/school.css">
<meta property="og:title" content="<?php if(isset($cfg->page_title)) echo ltrim($cfg->page_title. ' - ', ' - ');?><?php echo $cfg->app_name;?>" />
<meta property="og:type" content="website" />
<meta name="og:description" content="<?php echo $cfg->meta_description;?>">
<meta name="description" content="<?php echo $cfg->meta_description;?>">
<title><?php echo $cfg->app_name;?></title>
<?php
if(@!$mobile_browser)
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.datetimepicker.addon.min.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/init-datetime.js"></script>
<?php
}
?>
<?php
if(date('Y') < 2017)
{
if(!isset($_SESSION['set_time_token']))
{
	$setTimeToken = md5($_SERVER['REMOTE_ADDR'].'-'.time().'-'.mt_rand(111111, 999999));
	$_SESSION['set_time_token'] = $setTimeToken;
	
}
$setTimeToken = $_SESSION['set_time_token'];
?>
<script type="text/javascript">
var setTimeToken = '<?php echo $setTimeToken;?>';
</script>
<script type="text/javascript" src="lib.assets/script/time-sync.min.js">
</script>
<?php
}
?>
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
                    <li><a href="../">Depan</a></li>
                    <li data-selected="<?php if(basename($_SERVER['PHP_SELF'])=='sekolah-profil.php') echo 'true'; else echo 'false';?>"><a href="sekolah-profil.php">Sekolah</a></li>
                    <?php
                    if(@$school_id != 0)
                    {
                    ?>
                    <li data-selected="<?php if (basename($_SERVER['PHP_SELF']) == 'sekolah-deskripsi.php') {
                        echo 'true';
                    } else {
                        echo 'false';
                        }?>"><a href="sekolah-deskripsi.php">Keterangan</a></li>
                    <li data-selected="<?php if (basename($_SERVER['PHP_SELF']) == 'impor-data.php') {
                        echo 'true';
                    } else {
                        echo 'false';
                    }?>"><a href="impor-data.php">Impor Data</a></li>
                    <li><a href="kelas.php">Kelas</a></li>
                    <li><a href="siswa.php">Siswa</a></li>
                    <li><a href="guru.php">Guru</a></li>
                    <li><a href="artikel.php">Artikel</a></li>
                    <li><a href="ujian.php">Ujian</a></li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
      </div>
      
      <div class="main-content">
 