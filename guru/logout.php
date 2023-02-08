<?php
require_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
require_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
if(isset($_GET['confirm-logout']))
{
    unset($_SESSION['teacher_username']);
    unset($_SESSION['teacher_password']);
    header("Location: index.php");
}
?><!doctype html>
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
    <!-- Custom styles for this template -->
    <link href="lib.style/signin.css" rel="stylesheet">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="favs/manifest.json">
    <link rel="mask-icon" href="favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="favs/favicon.ico">
    <meta name="msapplication-config" content="favs/browserconfig.xml">
    <meta name="theme-color" content="#563d7c">

</head>

<body class="text-center">
    <form class="form-signin" method="post" action="">
        <img class="mb-4" src="lib.style/images/logo-120.png" alt="" width="90" height="90">
        <h1 class="h3 mb-3 font-weight-normal">Keluar</h1>
        <a class="btn btn-success" href="logout.php?confirm-logout=yes">Ya</a>
        <a class="btn btn-primary" href="index.php">Tidak</a>
        <p class="mt-5 mb-3 text-muted">&copy; 2008-<?php echo date('Y'); ?></p>
    </form>
</body>
</html>

