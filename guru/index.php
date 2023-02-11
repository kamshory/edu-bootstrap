<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$pageTitle = "Halaman Depan Guru";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	?>

	<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>