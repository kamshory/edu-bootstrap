<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <base href="<?php echo $cfg->base_assets;?>siswa">
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
</head>

<body class="text-center">
    <form class="form-signin" method="post" action="siswa/token.php">
        <img class="mb-4" src="<?php echo $cfg->base_assets;?>lib.assets/images/logo-120.png" alt="" width="90" height="90">
        <h1 class="h3 mb-3 font-weight-normal">Masukkan Token</h1>
        <label for="inputEmail" class="sr-only">Token</label>
        <input type="text" id="inputEmail" name="token" class="form-control" placeholder="Token" required autofocus>
        <button class="btn btn-lg btn-success btn-block" type="submit">Masukkan</button>
        <button class="btn btn-lg btn-primary btn-block" type="button" onclick="window.location='./'">Depan</button>
        <p class="mt-5 mb-3 text-muted">&copy; 2008-<?php echo date('Y'); ?></p>
        <input type="hidden" name="ref" value="<?php echo $_SERVER['REQUEST_URI'];?>">
    </form>
</body>
</html>
