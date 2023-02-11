<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
$pageTitle = "Belum Ada Sekolah";
require_once dirname(__FILE__) . "/lib.inc/header.php";
?>
<p>Anda belum membuat sekolah. Silakan upload file untuk membuat sekolah.</p>
<p><a href="impor-data.php">Klik di sini untuk membuat sekolah</a></p>
<p><a href="../about.php">Klik di sini untuk informasi lebih lanjut</a></p>
<p><a href="index.php">Klik di sini untuk kembali</a></p>
<?php
require_once dirname(__FILE__) . "/lib.inc/footer.php";
?>