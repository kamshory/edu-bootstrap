<?php
require_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
require_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
include_once dirname(dirname(__FILE__))."/lib.inc/header-bootstrap.php";
?>
    <p>Anda tidak terdaftar sebagai administrator di <?php echo $cfg->app_name;?>. </p>
    <p><a href="ganti-sekolah.php">Klik di sini untuk memilih sekolah</a></p>
    <p><a href="../about.php">Klik di sini untuk informasi lebih lanjut</a></p>

<?php
include_once dirname(dirname(__FILE__))."/lib.inc/footer-bootstrap.php";
exit();
?>
				