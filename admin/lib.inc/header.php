<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
<meta name="generator" content="Hugo 0.101.0">
<title><?php if (isset($cfg->page_title)) {
  echo ltrim($cfg->page_title.' - ', ' - ');
}?><?php echo $cfg->app_name;?></title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">

<!-- Bootstrap core CSS -->
<link href="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/bootstrap.min.css" rel="stylesheet">

<!-- Favicons -->
<link rel="apple-touch-icon" href="<?php echo $cfg->base_assets;?>favs/apple-touch-icon.png" sizes="180x180">
<link rel="icon" href="<?php echo $cfg->base_assets;?>favs/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="<?php echo $cfg->base_assets;?>favs/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="manifest" href="<?php echo $cfg->base_assets;?>favs/manifest.json">
<link rel="mask-icon" href="<?php echo $cfg->base_assets;?>favs/safari-pinned-tab.svg" color="#563d7c">
<link rel="icon" href="<?php echo $cfg->base_assets;?>favs/favicon.ico">
<meta name="msapplication-config" content="<?php echo $cfg->base_assets;?>favs/browserconfig.xml">
<meta name="theme-color" content="#563d7c">

<!-- Custom styles for this template -->
<link href="<?php echo $cfg->base_url;?>lib.vendors/dashboard/dashboard.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $cfg->base_url;?>lib.style/style.css">
<script src="<?php echo $cfg->base_url;?>lib.vendors/jquery/jquery.min.js"></script>
<script src="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/bootstrap.bundle.min.js"></script>

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
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">Pico Edu</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse"
      data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <?php
	  include_once dirname(__FILE__) . '/menu.php';
      $phpSelf = basename($_SERVER['PHP_SELF']);
	  
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
          <h1 class="h2"><?php echo $cfg->page_title;?></h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group mr-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
              <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-calendar">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              This week
            </button>
          </div>
        </div>


