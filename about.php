<?php
include_once dirname(__FILE__)."/lib.inc/auth.php";
$cfg->page_title = "Tentang";
include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
?>

    <div class="main-content">
    	<div class="main-content-wrapper">
            <h1>Tentang <?php echo $cfg->app_name;?></h1>
            
<p><?php echo $cfg->app_name;?> adalah perangkat server mini untuk aplikasi pendidikan. <?php echo $cfg->app_name;?> didesain

  agar dapat berjalan secara offline (luring) sehingga sekolah tidak perlu menyediakan sambungan internet. Selain itu, biaya operasionalnya menjadi sangat murah.</p>
<p><?php echo $cfg->app_name;?> adalah jawaban atas permintaan banyak sekolah agar aplikasi pendidikan Planet Edu dapat berjalan secara offline mengingat banyak sekolah yang masih belum memiliki fasilitas internet yang memadai untuk kebutuhan siswa melaksanakan uji coba UNBK.</p>
<p>Perangkat server didesain sesederhana mungkin agar terjangkau oleh semua sekolah. Dengan konsumsi daya yang sangat rendah, perangkat server dapat dihidupkan dengan menggunakan <em>power bank</em> dalam keadaan darurat.</p>
   	  </div>

</div>

<?php
include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
?>
