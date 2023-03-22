<?php
require_once dirname(__DIR__) . "/lib.inc/auth-admin.php";
$pageTitle = "Halaman Depan Administrator";

if(!empty(@$school_id)) {
    require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
?>
    <h1>Administrator Sekolah</h1>
    <?php
    if (@$school_name != "") {
    ?>
        <p>Saat ini Anda sedang mengelola <strong><?php echo $school_name; ?></strong></p>
    <?php
    }
    ?>
    <p>Ini merupakan halaman Administrator yang dapat Anda gunakan untuk mengelola data sekolah.</p>
    <p>Mohon untuk menjaga kerahasiaan akun Anda. Jangan meninggalkan komputer atau gadget dalam kondisi login di akun andministrator <?php echo $cfg->app_name; ?>.</p>
<?php
    require_once __DIR__ . "/lib.inc/footer.php"; //NOSONAR
} else if ($admin_id) {
    require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
?>
    <h1>Administrator Sekolah</h1>
    <p>Ini merupakan halaman Administrator yang dapat Anda gunakan untuk mengelola data sekolah.</p>
    <p>Anda pernah terdaftar sebagai administrator sekolah namun saat ini Anda tidak sedang mengelola sebuah sekolah. Silakan <a href="ganti-sekolah.php">pilih sekolah</a> atau <a href="impor-data.php">buat sebuah akun baru untuk sekolah Anda</a>.</p>
<?php
    require_once __DIR__ . "/lib.inc/footer.php"; //NOSONAR
} else {
    require_once __DIR__ . "/login-form.php";
}
?>