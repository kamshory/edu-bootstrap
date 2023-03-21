<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once __DIR__."/bukan-super-admin.php";
	exit();
}

$pageTitle = "Bank Soal";
$pagination = new \Pico\PicoPagination();
require_once __DIR__."/lib.inc/header.php"; //NOSONAR

?>

<ul class="list-rounded">
  <li><a href="ujian-daftar-paket.php">Daftar Paket</a></li>
  <li><a href="ujian-paket-soal.php">Paket Soal</a></li>
</ul>

<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
?>