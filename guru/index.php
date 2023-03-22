<?php
require_once dirname(__DIR__)."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	require_once __DIR__."/login-form.php";
	exit();
}
$pageTitle = "Halaman Depan Guru";
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
	?>

	<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
?>