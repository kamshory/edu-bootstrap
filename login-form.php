<?php
include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
include_once dirname(__FILE__) . "/lib.inc/sessions.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Planetbiru">
    <meta name="generator" content="Planetbiru">
    <title><?php echo $cfg->app_name;?></title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.vendors/bootstrap/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.style/signin.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?php echo $cfg->base_assets;?>lib.favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="<?php echo $cfg->base_assets;?>lib.favs/manifest.json">
    <link rel="mask-icon" href="<?php echo $cfg->base_assets;?>lib.favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon.ico">
    <meta name="msapplication-config" content="<?php echo $cfg->base_assets;?>lib.favs/browserconfig.xml">
    <meta name="theme-color" content="#3558BE">

    <?php
  if (date('Y') < 2017) {
    if (!isset($_SESSION['set_time_token'])) {
      $setTimeToken = md5($_SERVER['REMOTE_ADDR'] . '-' . time() . '-' . mt_rand(111111, 999999));
      $_SESSION['set_time_token'] = $setTimeToken;
    }
    $setTimeToken = $_SESSION['set_time_token'];
  ?>
    <script type="text/javascript">
      var setTimeToken = '<?php echo $setTimeToken; ?>';
    </script>
    <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/time-sync.min.js">
    </script>
  <?php
  }
  ?>

</head>

<body class="text-center">
    <form class="form-signin" method="post" action="">
        <img class="mb-4" src="<?php echo $cfg->base_assets;?>lib.assets/images/logo-120.png" alt="" width="90" height="90">
        <h1 class="h3 mb-3 font-weight-normal">Masuk Sebagai Siswa</h1>
        <label for="inputEmail" class="sr-only">Username</label>
        <input type="text" id="inputEmail" name="username" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        <p class="mt-5 mb-3 text-muted">&copy; 2008-<?php echo date('Y'); ?></p>
    </form>
</body>
</html>

