<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

$pageTitle = "Bank Soal";
$pagination = new \Pico\PicoPagination();
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

?>

<ul class="list-rounded">
  <li><a href="ujian-daftar-paket.php">Daftar Paket</a></li>
  <li><a href="ujian-paket-soal.php">Paket Soal</a></li>
</ul>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>