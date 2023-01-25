<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
exit();
}
?>
<style type="text/css">
.two-side-table{
	margin-bottom:10px;
}
.two-side-table td{
	padding:2px 0;
	vertical-align:top;
}
.two-side-table tr td:first-child::after{
	content:":";
	float:right;
	padding-right:10px;
	margin-right: -7px;
	color:#555555;
}
.test-info{
	height:inherit; padding:10px; box-sizing:border-box; overflow:auto;
}
</style>
<div class="test-info">
<?php
if(isset($_GET['test_id']))
{
	$array_class = $picoEdu->getArrayClass($school_id);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_test`.* $nt,
	(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher_id`,
	(select `member`.`name` from `member` where `member`.`member_id` = `edu_test`.`member_create`) as `member_create`,
	(select `member`.`name` from `member` where `member`.`member_id` = `edu_test`.`member_edit`) as `member_edit`
	from `edu_test` 
	where 1
	and `edu_test`.`test_id` = '$test_id' and `edu_test`.`school_id` = '$school_id' 
	";
	$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<form name="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td width="180">Nama Ujian</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Kelas
		</td><td><?php $class = $picoEdu->textClass($array_class, $data['class']); 
		$class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><?php echo $data['subject'];?></td>
		</tr>
		<tr>
		<td>Guru
		</td><td><?php echo $data['teacher_id'];?></td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><?php echo $data['description'];?></td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><?php echo $data['guidance'];?></td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><?php echo $data['open']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><?php echo $data['has_limits']?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['has_limits'])
		{
		?>
		<tr>
		<td>Batas Percobaan</td>
		<td><?php echo $data['trial_limits'];?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Nilai Kelulusan
		</td><td><?php echo $data['threshold'];?></td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><?php if ($data['assessment_methods'] == 'H') {
			echo "Nilai Tertinggi";
		}
		if ($data['assessment_methods'] == 'N') {
			echo "Nilai Terbaru";
			}?></td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?></td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?></td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><?php echo $data['question_per_page'];?></td>
		</tr>
		<tr>
		<td>Acak
		</td><td><?php echo $data['random']?"Ya":"Tidak";?></td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><?php echo date('H:i:s', $data['duration']);?></td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><?php echo $data['has_alert']?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['has_alert'])
		{
		?>
		<tr>
		<td>Waktu Peringatan</td>
		<td><?php echo sprintf("%d", $data['alert_time']/60);?> menit</td>
		</tr>
		<tr>
		<td>Pesan Peringatan</td>
		<td><?php echo $data['alert_message'];?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo $data['autosubmit']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Nilai Standard</td>
		<td><?php echo $data['standard_score'];?></td>
		</tr>
		<tr>
		<td>Penalti
		</td><td><?php echo $data['penalty'];?></td>
		</tr>
		<tr>
		<td>Notifikasi Nilai</td>
		<td><?php echo $data['score_notification']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><?php echo $data['publish_answer']?'Ya':'Tidak';?></td>
		</tr>
        <?php
		if($data['publish_answer'])
		{
		?>
		<tr>
		<td>Pengumuman Kunci Jawaban</td>
		<td><?php echo $data['time_answer_publication'];?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><?php 
		if($data['test_availability'] == 'F') 
		{echo 'Selamanya';} 
		if($data['test_availability'] == 'L') 
		{echo 'Terbatas';}
		?></td>
		</tr>
        <?php
		if($data['test_availability'] == 'L')
		{
		?>
		<tr>
		<td>Tersedia Mulai</td>
		<td><?php echo $data['available_from'];?></td>
		</tr>
		<tr>
		<td>Tersedia Hingga</td>
		<td><?php echo $data['available_to'];?></td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['member_create'];?> (<?php echo $data['role_create'];?>)</td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['member_edit'];?> (<?php echo $data['role_edit'];?>)</td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><?php echo $data['active']?'Ya':'Tidak';?></td>
		</tr>
	</table>
</form>
<?php
}
}
?>
</div>