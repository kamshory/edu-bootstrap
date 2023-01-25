<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$admin_id = $admin_login->admin_id;
$cfg->module_title = "Ujian";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<ul class="list-rounded">
  <li><a href="ujian-daftar.php">Daftar Ujian</a></li>
  <li><a href="ujian-soal.php">Soal Ujian</a></li>
  <li><a href="ujian-token.php">Token Ujian</a></li>
  <li><a href="ujian-ekspor.php">Ekspor Soal Ujian</a></li>
  <li><a href="ujian-laporan.php">Laporan Hasil Ujian</a></li>
</ul>
  <?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
