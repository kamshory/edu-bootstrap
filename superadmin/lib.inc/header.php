<?php
if(!isset($cfg))
{
  exit();
}
?><!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo $picoEdu->printPageTitle($pageTitle, $cfg->app_name);?></title>

<link rel="stylesheet" href="<?php echo $cfg->base_url;?>lib.vendors/fontawesome/css/all.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">

<!-- Bootstrap core CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/css/bootstrap.min.css">

<!-- Favicons -->
<link rel="apple-touch-icon" href="<?php echo $cfg->base_assets;?>lib.favs/apple-touch-icon.png" sizes="180x180">
<link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="manifest" href="<?php echo $cfg->base_assets;?>lib.favs/manifest.json">
<link rel="mask-icon" href="<?php echo $cfg->base_assets;?>lib.favs/safari-pinned-tab.svg" color="#563d7c">
<link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon.ico">
<meta name="msapplication-config" content="<?php echo $cfg->base_assets;?>lib.favs/browserconfig.xml">
<meta name="theme-color" content="#3558BE">

<!-- Custom styles for this template -->
<link href="<?php echo $cfg->base_url;?>lib.vendors/dashboard/dashboard.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_url;?>lib.style/style.css">
<script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.vendors/jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function(){
    $(document).on('change', 'input[type="checkbox"].checkbox-selector', function(e2){
      let target = $($(this).attr('data-target'));
      let checked = $(this)[0].checked;
      target.each(function(e3){
        $(this)[0].checked = checked;
      });
    });
  }
    
  );
</script>
</head>

<body>
	<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="../"><?php echo $cfg->app_name;?></a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse"
      data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <?php
	  require_once dirname(__FILE__) . '/menu.php';
      $phpSelf = $picoEdu->gateBaseSelfName();
	  
      ?>

      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="chartjs-size-monitor">
          <div class="chartjs-size-monitor-expand">
            <div class=""></div>
          </div>
          <div class="chartjs-size-monitor-shrink">
            <div class=""></div>
          </div>
        </div>

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2"><?php echo $pageTitle;?></h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group mr-2">
              <?php 
              if($cfg->sync_data_enable)
              {
              ?>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location='sinkronisasi.php'">Sinkronkan</button>
              <?php
              }
              ?>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location='informasi.php'">Informasi</button>
            </div>
            <div class="dropdown btn-group">         
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo $adminLoggedIn->name;?>
              </button>
              <div class="dropdown-menu  dropdown-menu-right">
                <a class="dropdown-item" href="profil.php">Profil Admin</a>
                <a class="dropdown-item" href="foto.php">Foto Admin</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="pengaturan.php">Pengaturan</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">Keluar </a>
              </div>
            </div>    
          </div>
        </div>


