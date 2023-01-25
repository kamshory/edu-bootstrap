<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Ujian";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<ul class="list-rounded">
    <li><a href="ujian-daftar.php">Daftar Ujian</a></li>
    <li><a href="ujian-soal.php">Soal Ujian</a></li>
    <?php
    if($use_token)
    {
    ?>
    <li><a href="ujian-token.php">Token Ujian</a></li>
    <?php
	}
	?>
    <li><a href="ujian-impor.php">Impor Soal Ujian</a></li>
    <li><a href="ujian-ekspor.php">Ekspor Soal Ujian</a></li>
    <li><a href="ujian-bank-soal.php">Bank Soal</a></li>
    <li><a href="ujian-monitoring.php">Monitoring Ujian</a></li>
    <li><a href="ujian-riwayat.php">Riwayat Ujian</a></li>
    <li><a href="ujian-laporan.php">Laporan Hasil Ujian</a></li>
</ul>
  <?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
