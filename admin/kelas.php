<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-admin.php";
exit();
}
$cfg->module_title = "Kelas";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<ul class="list-rounded">
  <li><a href="kelas-daftar.php">Daftar Kelas</a></li>
  <li><a href="jurusan.php">Daftar Jurusan</a></li>
  <li><a href="kelas-siswa-ubah.php">Perubahan Kelas Siswa</a></li>
</ul>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
