<?php
include_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";

if (@$school_id != 0) {
    include_once dirname(__FILE__) . "/lib.inc/header.php";
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
    include_once dirname(__FILE__) . "/lib.inc/footer.php";
} else if ($admin_id) {
    include_once dirname(__FILE__) . "/lib.inc/header.php";
?>
    <h1>Administrator Sekolah</h1>
    <p>Ini merupakan halaman Administrator yang dapat Anda gunakan untuk mengelola data sekolah.</p>
    <p>Anda pernah terdaftar sebagai administrator sekolah namun saat ini Anda tidak sedang mengelola sebuah sekolah. Silakan <a href="ganti-sekolah.php">pilih sekolah</a> atau <a href="impor-data.php">buat sebuah akun baru untuk sekolah Anda</a>.</p>
<?php
    include_once dirname(__FILE__) . "/lib.inc/footer.php";
} else {
    include_once dirname(__FILE__) . "/login-form.php";
}
?>