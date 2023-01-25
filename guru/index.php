<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}
include_once dirname(__FILE__)."/lib.inc/header.php";
	?>
    <ul class="shortcut-image-80">
    	<li><a href="../"><div><img alt="" src="../lib.assets/theme/default/css/images/home-80.png" /></div><div class="menu-label">Depan</div></a></li>
    	<li><a href="kelas.php"><div><img alt="" src="../lib.assets/theme/default/css/images/class-80.png" /></div><div class="menu-label">Kelas</div></a></li>
    	<li><a href="siswa.php"><div><img alt="" src="../lib.assets/theme/default/css/images/students-80.png" /></div><div class="menu-label">Siswa</div></a></li>
    	<li><a href="guru.php"><div><img alt="" src="../lib.assets/theme/default/css/images/teachers-80.png" /></div><div class="menu-label">Guru</div></a></li>
    	<li><a href="artikel.php"><div><img alt="" src="../lib.assets/theme/default/css/images/article-80.png" /></div><div class="menu-label">Artikel</div></a></li>
    	<li><a href="info.php"><div><img alt="" src="../lib.assets/theme/default/css/images/news-80.png" /></div><div class="menu-label">Info</div></a></li>
    	<li><a href="ujian.php"><div><img alt="" src="../lib.assets/theme/default/css/images/exam-80.png" /></div><div class="menu-label">Ujian</div></a></li>
    	<li><a href="profil.php"><div><img alt="" src="../lib.assets/theme/default/css/images/profile-80.png" /></div><div class="menu-label">Profil</div></a></li>
    	<li><a href="logout.php"><div><img alt="" src="../lib.assets/theme/default/css/images/logout-80.png" /></div><div class="menu-label">Logout</div></a></li>
    </ul>
    <?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>