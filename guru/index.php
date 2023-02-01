<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}
$cfg->page_title = "Halaman Depan Guru";
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	?>

	<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>