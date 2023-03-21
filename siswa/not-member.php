<?php
require_once dirname(__DIR__)."/lib.inc/auth-siswa.php";
$pageTitle = "Pengguna Tidak Terdaftar";
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
?>

<p>Anda tidak terdaftar sebagai siswa di <?php echo $cfg->app_name;?>. Silakan menghubungi sekolah Anda agar Anda didaftarkan sebagai siswa. Jika sekolah Anda belum bergabung dengan <?php echo $cfg->app_name;?>, silakan ajukan kepada kepala sekolah Anda untuk bergabung di <?php echo $cfg->app_name;?>. Semua layanan <?php echo $cfg->app_name;?> adalah gratis untuk selamanya.</p>
<p><a href="../about.php">Klik di sini untuk informasi lebih lanjut</a></p>
<p><a href="../">Klik di sini untuk informasi kembali ke depan</a></p>
<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
?>
