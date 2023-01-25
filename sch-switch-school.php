<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
$cfg->module_title = "Pilih Sekolah";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<ul class="list-rounded">
  <li><a href="../guru/ganti-sekolah.php">Sebagai Guru</a></li>
  <li><a href="../siswa/ganti-sekolah.php">Sebagai Siswa</a></li>
</ul>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>