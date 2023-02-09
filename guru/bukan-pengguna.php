<?php
$cfg->page_title = "Pengguna Tidak Terdaftar";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<p>Anda tidak terdaftar sebagai guru di <?php echo $cfg->app_name;?>. Jika Anda pernah terdaftar sebagai guru, <a href="ganti-sekolah.php">silakan pilih sekolah</a>. Jika Anda belum pernah terdaftar sebelumnya, silakan menghubungi sekolah Anda agar Anda didaftarkan sebagai guru. Jika sekolah Anda belum bergabung dengan <?php echo $cfg->app_name;?>, silakan ajukan kepada kepala sekolah Anda untuk bergabung di <?php echo $cfg->app_name;?>. Semua layanan <?php echo $cfg->app_name;?> adalah gratis untuk selamanya.</p>
<p><a href="ganti-sekolah.php">Klik di sini untuk memilih sekolah</a></p>
<p><a href="../">Klik di sini untuk informasi kembali ke depan</a></p>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>