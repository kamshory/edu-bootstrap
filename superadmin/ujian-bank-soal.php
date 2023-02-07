<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$cfg->page_title = "Bank Soal";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

?>

<ul class="list-rounded">
  <li><a href="ujian-daftar-paket.php">Daftar Paket</a></li>
  <li><a href="ujian-paket-soal.php">Paket Soal</a></li>
</ul>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>