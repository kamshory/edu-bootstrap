<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
$cfg->module_title = "Sekolah";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<ul class="list-rounded">
  <li><a href="sekolah-profil.php">Profil Sekolah</a></li>
  <li><a href="sekolah-deskripsi.php">Keterangan Sekolah</a></li>
  <li><a href="ganti-sekolah.php">Pilih Sekolah</a></li>
  <li><a href="impor-data.php">Impor Data Sekolah</a></li>
  <li><a href="update.php">Update Aplikasi</a></li>
  <li><a href="peralatan.php">Peralatan</a></li>
</ul>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
