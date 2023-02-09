<?php
$cfg->page_title = "Admin Tidak Terdaftar";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<p>Anda tidak terdaftar sebagai administrator di <?php echo $cfg->app_name;?>. Jika Anda belum pernah terdaftar di <?php echo $cfg->app_name;?>, silakan bergabung di <?php echo $cfg->app_name;?> untuk membuat akun sekolah. Jika Anda sudah pernah terdaftar di <?php echo $cfg->app_name;?>, silakan ganti <a href="ganti-sekolah.php">pilih sekolah</a> dari daftar. Semua layanan <?php echo $cfg->app_name;?> adalah gratis untuk selamanya.</p>
<p><a href="ganti-sekolah.php">Klik di sini untuk memilih sekolah</a></p>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>