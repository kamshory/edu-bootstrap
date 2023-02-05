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
    <title>Pico Edu</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $cfg->base_assets;?>lib.vendors/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- Favicons -->
    <link rel="apple-touch-icon" href="favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="favs/manifest.json">
    <link rel="mask-icon" href="favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="favs/favicon.ico">
    <meta name="msapplication-config" content="favs/browserconfig.xml">
    <meta name="theme-color" content="#563d7c">

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

<body>
<p>Anda tidak terdaftar sebagai siswa, guru, ataupun administrator di <?php echo $cfg->app_name;?>. Jika sekolah Anda belum bergabung dengan <?php echo $cfg->app_name;?>, silakan ajukan kepada kepala sekolah Anda untuk bergabung di <?php echo $cfg->app_name;?>. Semua layanan <?php echo $cfg->app_name;?> adalah gratis untuk selamanya.</p>
<p><a href="about.php">Klik di sini untuk informasi lebih lanjut</a></p>
<p><a href="./">Klik di sini untuk informasi kembali ke depan</a></p>
</body>
</html>
