<?php
include_once dirname(__FILE__)."/lib.inc/auth.php";
$cfg->page_title = "Unduh";
include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
?>
    <div class="main-content">
    	<div class="main-content-wrapper">
    	<h1>Unduh Software <?php echo $cfg->app_name;?></h1>
        <p>Software <?php echo $cfg->app_name;?> berikut bebas diunduh dan disebarkan untuk kepentingan pendidikan. Software yang dapat Anda unduh adalah sebagai berikut:</p>
        <ul>
          <li><a href="admin/planetedu.pptx">Slide pengenalan <?php echo $cfg->app_name;?></a></li>
          <li><a href="admin/planetedu.xlsx">Format data sekolah, kelas, siswa dan guru</a></li>
          <li><a href="admin/planetedu.docx">Panduan Pengisian data sekolah, kelas, siswa dan guru</a></li>
          <li><a href="admin/panduan-membuat-akun-sekolah.pptx">Panduan Membuat Akun Sekolah</a></li>
          <li><a href="admin/modul-administrator.pptx">Panduan Administrator</a></li>
          <li><a href="admin/test-maker.zip">Editor Soal Ujian (ZIP)</a></li>
          <li><a href="admin/test-maker.exe">Editor Soal Ujian (EXE - Windows Installer)</a></li>
          <li><a href="admin/contoh-soal.txt">Contoh Soal Ujian</a></li>
        </ul>
    </div>
    </div>
<?php
include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
?>

