	<div class="starting">
        <div class="shortcut">
          	<div class="flexbox-container">
            	<div class="box-student">
                	<div class="shortcut-detail">
                    	<h3>Fitur untuk Siswa</h3>
                        <div class="detail-description">
                        <p>Siswa dapat mengikuti materi pelajaran yang dibuat oleh guru serta dapat mengikuti ujian online yang diberikan oleh guru. Siswa juga dapat langsung melihat hasil ujiannya jika guru menghendaki.</p>
                        </div>
                        <div class="detail-go"><a href="siswa">Mulai</a></div>
                    </div>
					<div class="box-wrapper">
                        <div class="box-wrapper-body"></div>
                    	<h3>Siswa</h3>
                        <p class="total-member">106.432</p>
                    </div>
                </div>
            	<div class="box-teacher">
                	<div class="shortcut-detail">
                    	<h3>Fitur untuk Guru</h3>
                        <div class="detail-description">
                        <p>Guru dapat membuat materi pelajaran berupa artikel yang dapat dibaca oleh semua siswa dan guru. Guru juga dapat membuat ujian online yang dapat diikuti oleh siswa. Nilai ujian langsung dibuat oleh sistem secara otomatis. </p>
                        </div>
                        <div class="detail-go"><a href="guru">Mulai</a></div>
                    </div>
					<div class="box-wrapper">
                        <div class="box-wrapper-body"></div>
                    	<h3>Guru</h3>
                        <p class="total-member">34.241</p>
                    </div>
                </div>
            	<div class="box-school">
                	<div class="shortcut-detail">
                    	<h3>Fitur untuk Sekolah</h3>
                        <div class="detail-description">
                        <p>Sekolah dapat mengelola nilai ujian siswa, memantau aktivitas siswa dan guru serta dapat melihat kinerja guru. Administrator juga bisa menulis artikel yang dapat dibaca oleh siswa dan guru serta membuat ujian yang dapat diikuti oleh siswa.</p>
                        </div>
                        <div class="detail-go"><a href="admin">Mulai</a></div>
                    </div>
					<div class="box-wrapper">
                        <div class="box-wrapper-body"></div>
                    	<h3>Sekolah</h3>
                        <p class="total-member">239</p>
                    </div>
                </div>
            </div>
          </div>
       </div>
       
       
       <div class="article-shortcut">
       <div class="article-link-list">
       <h3>Informasi <?php echo $cfg->app_name;?></h3>
       <?php
		$sql = "SELECT `edu_info`.* 
		from `edu_info` 
		where `edu_info`.`active` = '1'
		order by `edu_info`.`info_id` desc
		limit 0, 10
		";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
		?>
       <ul>
       <?php
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	   foreach($rows as $data)
	   {
		   ?>
       		<li><a href="info.php?info_id=<?php echo $data['info_id'];?>"><?php echo $data['name'];?></a></li>
            <?php
	   }
	   ?>
       </ul>
       <?php
		}
		?>
       </div>
       <div class="article-archive">
       <h3>Arsip Informasi</h3>
       <?php
		$sql = "SELECT `edu_info`.`time_create` , left(`edu_info`.`time_create`, 7) as `month`, count(*) as `count`
		from `edu_info` 
		where `edu_info`.`active` = '1'
		group by `month`
		order by `edu_info`.`info_id` desc
		";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
		?>
       <ul>
       <?php
	   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	   foreach($rows as $data)
	   {
			$period = translateDate(date('F Y', strtotime($data['month'].'-01')));
		   ?>
       		<li><a href="info.php?option=archive&period=<?php echo $data['month'];?>"><?php echo $period;?> (<?php echo $data['count'];?>)</a></li>
            <?php
	   }
	   ?>
       </ul>
       <?php
		}
		?>
        <p><a href="about.php">Tentang Planet Edu</a></p>
        <p><a href="response.php">Tanggapan Sekolah</a></p>
        <p><a href="usermanual/">Panduan Menggunakan Planet Edu</a></p>
       </div>
       </div>
       
       
       <div class="footer">
       	&copy; <a href="http://www.planetbiru.com/">Planetbiru</a> 2008-<?php echo date('Y');?>. All rights reserved.
       </div>
       
</div>

</html>