<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
$cfg->page_title = "Halaman Depan Siswa";
if(@$school_id != 0)
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	?>
    
    <?php
	include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
	include_once dirname(__FILE__)."/login-form.php";
}

?>