<?php
require_once dirname(__DIR__)."/lib.inc/auth-siswa.php";
$pageTitle = "Halaman Depan Siswa";
if(isset($school_id) && !empty($school_id))
{
	require_once __DIR__."/lib.inc/header.php"; //NOSONAR
	?>
    <p>Selamat datang <?php echo $studentLoggedIn->name ?></p>
    <?php
	require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
else
{
	require_once __DIR__."/login-form.php";
}
