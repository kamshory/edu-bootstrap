<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
$cfg->page_title = "Halaman Depan Siswa";
if(!empty(@$school_id))
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	?>
    
    <?php
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
	require_once dirname(__FILE__)."/login-form.php";
}

?>