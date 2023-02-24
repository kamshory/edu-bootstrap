<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-guru.php";
	exit();
}

$pageTitle = "Ujian";
require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(@$_GET['option'] == 'execution')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$array_class = $picoEdu->getArrayClass($school_id);
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher_id`,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_test`.`school_program_id`) AS `school_program_id`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_create`) AS `member_create`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_edit`) AS `member_edit`
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$test_id' AND `edu_test`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>

<style>
    .row.test-member .card{
        margin-bottom: 20px;
    }

	.btn-test-info {

		padding: 2px 2px;
		width: 22px;
		height: 22px;
		line-height: 1;
		vertical-align: middle;
		border-radius: 50%;
		margin-top: -4px
	}

</style>
<h4><?php echo $data['name'];?> <button type="button" class="btn btn-sm btn-secondary btn-test-info" data-toggle="modal" data-target="#detail-test-modal">
  <i class="fas fa-info"></i>
</button></h4>

<div class="row test-member">


</div>
<style>
    .row.test-member .card{
        margin-bottom: 20px;
    }

	.btn-test-info {

		padding: 2px 2px;
		width: 22px;
		height: 22px;
		line-height: 1;
		vertical-align: middle;
		border-radius: 50%;
		margin-top: -4px
	}

	.test-member .card-title{
		width:calc(100% - 90px);
		margin-left:10px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.1rem;
	}
	.test-member .card-body{
		padding:1.6rem;
		margin: -1rem;
	}


	.img-300x300 img{
		width:80px;
		height: 80px;
	}
	.img-300x300{
		width:80px;
	}
	
</style>

<script>
    let testId = '<?php echo $test_id;?>';
    let websocketURL = '<?php echo $picoEdu->getWebsocketHost();?>/?test_id='+testId;
</script>
<script src="../lib.assets/script/test-ws.js"></script>
<script src="../lib.assets/script/test-ws-supervisor.js"></script>



<div class="modal fade" id="detail-test-modal" tabindex="-1" role="dialog" aria-labelledby="detail-test-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detail-test-modal-title">Informasi Ujian</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      

<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
    <td>Nama Ujian</td>
    <td><?php echo $data['name'];?> </td>
    </tr>
    <tr>
    <td>Jurusan
    </td><td><?php echo $data['school_program_id'];?> </td>
    </tr>
    <tr>
    <td>Kelas
    </td>
    <td><?php 
    $class = $picoEdu->textClass($array_class, $data['class']); 
    $class_sort = $picoEdu->textClass($array_class, $data['class'], 5);
    ?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
    </tr>
    <tr>
    <td>Mata Pelajaran
    </td><td><?php echo $data['subject'];?> </td>
    </tr>
    <tr>
    <td>Guru
    </td><td><?php echo $data['teacher_id'];?> </td>
    </tr>
    <tr>
    <td>Keterangan
    </td><td><?php echo $data['description'];?> </td>
    </tr>
    <tr>
    <td>Petunjuk
    </td><td><?php echo $data['guidance'];?> </td>
    </tr>
    <tr>
    <td>Terbuka
    </td><td><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?> </td>
    </tr>
    <tr>
    <td>Dibatasi</td>
    <td><?php echo $picoEdu->trueFalse($data['has_limits'], 'Ya', 'Tidak');?> </td>
    </tr>
    <?php
    if($data['has_limits'])
    {
    ?>
    <tr>
    <td>Batas Percobaan</td>
    <td><?php echo $data['trial_limits'];?> </td>
    </tr>
    <?php
    }
    ?>
    <tr>
    <td>Nilai Kelulusan
    </td><td><?php echo $data['threshold'];?> </td>
    </tr>
    <tr>
    <td>Metode Penilaian</td>
    <td><?php echo $picoEdu->selectFromMap($data['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
    </tr>
    <tr>
    <td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
    </tr>
    <tr>
    <td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
    </tr>
    <tr>
    <td>Soal Perhalaman</td>
    <td><?php echo $data['question_per_page'];?> </td>
    </tr>
    <tr>
    <td>Soal Diacak
    </td><td><?php echo $picoEdu->trueFalse($data['random'], 'Ya', 'Tidak');?> </td>
    </tr>
    <tr>
    <td>Durasi
    </td><td><?php echo gmdate('H:i:s', $data['duration']);?> </td>
    </tr>
    <tr>
    <td>Beri Peringatan</td>
    <td><?php echo $picoEdu->trueFalse($data['has_alert'], 'Ya', 'Tidak');?> </td>
    </tr>
    <?php
    if($data['has_alert'])
    {
    ?>
    <tr>
    <td>Waktu Peringatan</td>
    <td><?php echo (int) ($data['alert_time']/60);?> menit</td>
    </tr>
    <tr>
    <td>Pesan Peringatan</td>
    <td><?php echo $data['alert_message'];?> </td>
    </tr>
    <?php
    }
    ?>
    <tr>
    <td>Otomatis Kirim Jawaban</td>
    <td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
    </tr>
    <tr>
    <td>Nilai Standard</td>
    <td><?php echo $data['standard_score'];?> </td>
    </tr>
    <tr>
    <td>Penalti
    </td><td><?php echo $data['penalty'];?> </td>
    </tr>
    <tr>
    <td>Notifikasi Nilai</td>
    <td><?php echo ($data['score_notification'])?'Ya':'Tidak';?> </td>
    </tr>
    <tr>
    <td>Umumkan Kunci Jawaban</td>
    <td><?php echo ($data['publish_answer'])?'Ya':'Tidak';?> </td>
    </tr>
    <?php
    if($data['publish_answer'])
    {
    ?>
    <tr>
    <td>Pengumuman Kunci Jawaban</td>
    <td><?php echo $data['time_answer_publication'];?> </td>
    </tr>
    <?php
    }
    ?>
    <tr>
    <td>Ketersediaan Ujian
    </td><td><?php echo $picoEdu->selectFromMap($data['test_availability'], array('F'=>'Selamanya', 'L'=>'Terbatas'));?> </td>
    </tr>
    <?php
    if($data['test_availability'] == 'L')
    {
    ?>
    <tr>
    <td>Tersedia Mulai</td>
    <td><?php echo $data['available_from'];?> </td>
    </tr>
    <tr>
    <td>Tersedia Hingga</td>
    <td><?php echo $data['available_to'];?> </td>
    </tr>
    <?php
    }
    ?>
    <tr>
    <td>Dibuat</td>
    <td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
    </tr>
    <tr>
    <td>Diubah</td>
    <td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
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
    <td><?php echo $data['ip_create'];?> </td>
    </tr>
    <tr>
    <td>IP Ubah</td>
    <td><?php echo $data['ip_edit'];?> </td>
    </tr>
    <tr>
    <td>Aktif
    </td><td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
    </tr>
</table>

</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>



<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$array_class = $picoEdu->getArrayClass($school_id);
$edit_key = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher_id`,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_test`.`school_program_id`) AS `school_program_id`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_create`) AS `member_create`,
(SELECT `member`.`name` FROM `member` WHERE `member`.`member_id` = `edu_test`.`member_edit`) AS `member_edit`
FROM `edu_test` 
WHERE `edu_test`.`test_id` = '$edit_key' AND `edu_test`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_test" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Jurusan
		</td><td><?php echo $data['school_program_id'];?> </td>
		</tr>
		<tr>
		<td>Kelas
		</td>
		<td><?php 
		$class = $picoEdu->textClass($array_class, $data['class']); 
		$class_sort = $picoEdu->textClass($array_class, $data['class'], 5);
		?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
		</tr>
		<tr>
		<td>Mata Pelajaran
		</td><td><?php echo $data['subject'];?> </td>
		</tr>
		<tr>
		<td>Guru
		</td><td><?php echo $data['teacher_id'];?> </td>
		</tr>
		<tr>
		<td>Keterangan
		</td><td><?php echo $data['description'];?> </td>
		</tr>
		<tr>
		<td>Petunjuk
		</td><td><?php echo $data['guidance'];?> </td>
		</tr>
		<tr>
		<td>Terbuka
		</td><td><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Dibatasi</td>
		<td><?php echo $picoEdu->trueFalse($data['has_limits'], 'Ya', 'Tidak');?> </td>
		</tr>
        <?php
		if($data['has_limits'])
		{
		?>
		<tr>
		<td>Batas Percobaan</td>
		<td><?php echo $data['trial_limits'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Nilai Kelulusan
		</td><td><?php echo $data['threshold'];?> </td>
		</tr>
		<tr>
		<td>Metode Penilaian</td>
		<td><?php echo $picoEdu->selectFromMap($data['assessment_methods'], array('H'=>"Nilai Tertinggi", 'N'=>"Nilai Terbaru"));?> </td>
		</tr>
		<tr>
		<td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
		</tr>
		<tr>
		<td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
		</tr>
		<tr>
		<td>Soal Perhalaman</td>
		<td><?php echo $data['question_per_page'];?> </td>
		</tr>
		<tr>
		<td>Soal Diacak
		</td><td><?php echo $picoEdu->trueFalse($data['random'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Durasi
		</td><td><?php echo gmdate('H:i:s', $data['duration']);?> </td>
		</tr>
		<tr>
		<td>Beri Peringatan</td>
		<td><?php echo $picoEdu->trueFalse($data['has_alert'], 'Ya', 'Tidak');?> </td>
		</tr>
        <?php
		if($data['has_alert'])
		{
		?>
		<tr>
		<td>Waktu Peringatan</td>
		<td><?php echo (int) ($data['alert_time']/60);?> menit</td>
		</tr>
		<tr>
		<td>Pesan Peringatan</td>
		<td><?php echo $data['alert_message'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Nilai Standard</td>
		<td><?php echo $data['standard_score'];?> </td>
		</tr>
		<tr>
		<td>Penalti
		</td><td><?php echo $data['penalty'];?> </td>
		</tr>
		<tr>
		<td>Notifikasi Nilai</td>
		<td><?php echo ($data['score_notification'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Umumkan Kunci Jawaban</td>
		<td><?php echo ($data['publish_answer'])?'Ya':'Tidak';?> </td>
		</tr>
        <?php
		if($data['publish_answer'])
		{
		?>
		<tr>
		<td>Pengumuman Kunci Jawaban</td>
		<td><?php echo $data['time_answer_publication'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Ketersediaan Ujian
		</td><td><?php echo $picoEdu->selectFromMap($data['test_availability'], array('F'=>'Selamanya', 'L'=>'Terbatas'));?> </td>
		</tr>
        <?php
		if($data['test_availability'] == 'L')
		{
		?>
		<tr>
		<td>Tersedia Mulai</td>
		<td><?php echo $data['available_from'];?> </td>
		</tr>
		<tr>
		<td>Tersedia Hingga</td>
		<td><?php echo $data['available_to'];?> </td>
		</tr>
        <?php
		}
		?>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
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
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Aktif
		</td><td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$array_class = $picoEdu->getArrayClass($school_id);
?>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
	$(document).on('click', 'a[data-availability="L"]', function(e){
		var from = $(this).attr('data-from');
		var to = $(this).attr('data-to');
		alert('Tersedia antara '+from+' hingga '+to);
		e.preventDefault();
	});
	$(document).on('click', 'a[data-availability="F"]', function(e){
		alert('Tersedia selamanaya ');
		e.preventDefault();
	});
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Kelas</span> 
    <select class="form-control input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
	$sql2 = "SELECT * FROM `edu_class` WHERE `school_id` = '$school_id' ";
	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'class_id')
			),
			'selectCondition'=>array(
				'source'=>'class_id',
				'value'=>$class_id
			),
			'caption'=>array(
				'delimiter'=>\Pico\PicoEdu::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
    </select>
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_test`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}
if($class_id != '')
{
	$pagination->appendQueryName('class_id');
	$sql_filter .= " and (concat(',',`edu_test`.`class`,',') like '%,$class_id,%')";
}


$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' AND `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
ORDER BY `edu_test`.`test_id` DESC
";
$sql_test = "SELECT `edu_test`.*
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' AND `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination(basename($_SERVER['PHP_SELF']), true); 
$paginationHTML = $pagination->buildHTML();
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9)
	{
		display:none;
	}
}
</style>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
	  <td width="16"><i class="fas fa-graduation-cap"></i></td>
      <td width="25">No</td>
      <td>Ujian</td>
      <td>Kelas</td>
      <td>Pelajaran</td>
      <td>Guru</td>
      <td>Terbuka</td>
      <td>Soal</td>
      <td>Tersedia</td>
      <td>Aktif</td>
     </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
	  <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=execution&test_id=<?php echo $data['test_id'];?>"><i class="fas fa-graduation-cap"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['teacher'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?></a></td>
      <td><a href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>" data-availability="<?php echo $data['test_availability'];?>" data-from="<?php echo $data['available_from'];?>" data-to="<?php echo $data['available_to'];?>"><?php echo $picoEdu->selectFromMap($data['test_availability'], array('F'=>'Selamanya', 'L'=>'Terbatas'));?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
  </div>
</form>
<?php
}
else if(@$_GET['q'] != '')
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="ujian.php?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>