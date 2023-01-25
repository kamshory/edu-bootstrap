<?php
include_once dirname(__FILE__)."/lib.inc/auth.php";
$cfg->page_title = "Fitur";
include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
?>
    <div class="main-content">
    	<div class="main-content-wrapper">
            <h1>Fitur <?php echo $cfg->app_name;?></h1>
            <p><?php echo $cfg->app_name;?> dilengkapi dengan fitur yang sangat menunjang pendidikan, baik di sekolah maupun perguruan tinggi. Adapun fitur <?php echo $cfg->app_name;?> adala sebagai berikut:</p>
            <ul>
              <li>Materi Pelajaran Sekolah</li>
              <li>Quiz dan Ujian Online Sekolah</li>
              <li>Bank Soal dan Kunci Jawaban</li>
              <li>Video Tutorial</li>
            </ul>
		</div>
    </div>
<?php
include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
?>
