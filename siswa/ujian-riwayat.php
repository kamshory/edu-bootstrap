<?php
require_once dirname(__DIR__)."/lib.inc/auth-siswa.php";
if(empty($school_id))
{
	require_once __DIR__."/bukan-siswa.php";
	exit();
}

$pageTitle = "Riwayat Ujian";
$pagination = new \Pico\PicoPagination();

if(@$_GET['option'] == 'kick-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_peserta_test`.* FROM `edu_peserta_test` WHERE `id` = '$id' AND `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$waktu = $database->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$sql = "DELETE FROM `sessions` WHERE `id` = '$sessions_id' ";
		$database->executeDelete($sql, true);
		$sql = "UPDATE `edu_peserta_test` SET `waktu_keluar` = '$waktu', `ip_keluar` = '$ip', `login_edit` = '$admin_id', `status` = '3' WHERE `id` = '$id'";	
		$database->executeUpdate($sql, true);
		header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&test_id=$test_id");
	}
}
if(@$_GET['option'] == 'block-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_peserta_test`.* FROM `edu_peserta_test` WHERE `id` = '$id' AND `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$waktu = $database->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$siswa_id = $data['siswa_id'];
		$sql = "DELETE FROM `sessions` WHERE `id` = '$sessions_id' ";
		$database->executeDelete($sql, true);
		$sql = "UPDATE `edu_peserta_test` SET `waktu_keluar` = '$waktu', `ip_keluar` = '$ip', `login_edit` = '$admin_id', `status` = '4' WHERE `id` = '$id'";	
		$database->executeUpdate($sql, true);
		$sql = "UPDATE `siswa` SET `blokir` = '1' WHERE `siswa_id` = '$siswa_id' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
}

if(@$_GET['option'] == 'detail')
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR

$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$status = kh_filter_input(INPUT_GET, "status", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.* ,
(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) AS `number_of_real_question`
FROM `edu_test` WHERE `test_id` = '$test_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="160">Nama Ujian</td>
    <td><?php echo $data['name'];?> </td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $data['subject'];?> </td>
  </tr>
  <tr>
    <td>Jumlah Soal</td>
    <td><?php echo $data['number_of_question'];?> </td>
  </tr>
  <tr>
    <td>Jumlah Pilihan</td>
    <td><?php echo $data['number_of_option'];?> </td>
  </tr>
  <tr>
    <td>Koleksi Soal</td>
    <td><span id="jumlahkoleksi"><?php echo $data['number_of_real_question'];?></span></td>
  </tr>
    <tr>
    <td>Pengacakan Soal</td>
    <td><?php echo ($data['random'])?'Diacak':'Tidak Diacak';?> </td>
    </tr>
    <tr>
    <td>Durasi Ujian</td>
    <td><?php echo gmdate('H:i:s', $data['duration']);?> </td>
    </tr>
    <tr>
    <td>Otomatis Kirim Jawaban</td>
    <td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
    </tr>
</table>
 </div>

<form name="searchform" id="searchform" method="get" action="">
<input type="hidden" name="option" value="detail" />
<input type="hidden" name="test_id" value="<?php echo $test_id;?>" />
<div style="padding-bottom:5px;">
Status <select name="status" id="status">
	<option value="">Semua</option>
	<option value="1"<?php echo $picoEdu->ifMatch($status, '1', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Ujian</option>
	<option value="2"<?php echo $picoEdu->ifMatch($status, '2', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Selesai</option>
	<option value="3"<?php echo $picoEdu->ifMatch($status, '3', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Dikeluarkan</option>
	<option value="4"<?php echo $picoEdu->ifMatch($status, '4', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Diblokir</option>
</select>
<input type="submit" id="show" class="btn btn-primary" value="Tampilkan" />
</div>
</form>

<style type="text/css">
.tabel-peserta-test tbody tr.duplicated td{
	animation-iteration-count:infinite;
	animation-name:kedip;
	animation-timing-function:ease-in-out;
	animation-duration:1s;
}
@keyframes kedip{
	0%{
		background-color:#E087A5;
	}
	50%{
		background-color:#D2E1FF;
	}
	100%{
		background-color:#E087A5;
	}
}
</style>

<script type="text/javascript">
$(window).ready(function(e) {
	setInterval(function(){
		var url = $('#tabel-monitoring').attr('data-url');
		var test_id = $('#tabel-monitoring').attr('data-test-id');
		var status = $('#tabel-monitoring').attr('data-status');
		$.get(url, {test_id:test_id, status:status}, function(answer){
			$('#tabel-monitoring').html(answer);
		});
	}, 5000);
	
	$(document).on('click', '.kick-student', function(e){
		var name_siswa = $(this).attr('data-name-siswa');
		if(confirm('Apakah Anda akan mengeluarkan '+name_siswa+' dari test?'))
		{
			var url = $('#tabel-monitoring').attr('data-url');
			var test_id = $('#tabel-monitoring').attr('data-test-id');
			var status = $('#tabel-monitoring').attr('data-status');
			var id = $(this).attr('data-id');
			$.get(url, {option:'kick-student', id:id, test_id:test_id, status:status}, function(answer){
				$('#tabel-monitoring').html(answer);
			});
		}
		e.preventDefault();
	});
	$(document).on('click', '.block-student', function(e){
		var name_siswa = $(this).attr('data-name-siswa');
		if(confirm('Apakah Anda akan mengeluarkan '+name_siswa+' dari test serta memblokir akunnya?'))
		{
			var url = $('#tabel-monitoring').attr('data-url');
			var test_id = $('#tabel-monitoring').attr('data-test-id');
			var status = $('#tabel-monitoring').attr('data-status');
			var id = $(this).attr('data-id');
			$.get(url, {option:'block-student', id:id, test_id:test_id, status:status}, function(answer){
				$('#tabel-monitoring').html(answer);
			});
		}
		e.preventDefault();
	});
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('#searchform').submit();
	});
});
</script>

<div id="tabel-monitoring" data-test-id="<?php echo $test_id;?>" data-status="<?php echo $status;?>" data-url="ajax-test-history.php">
<?php
require_once __DIR__."/ajax-test-history.php";
?>
</div>
<div class="button-area">
<input type="button" name="show-all" id="show-all" value="Semua Ujian" class="btn btn-success" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" />
</div>
<?php
}
}
else
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$teacher_id = kh_filter_input(INPUT_GET, "teacher_id", FILTER_SANITIZE_STRING_NEW);
?>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Guru</span>
    <select class="form-control input-select" name="teacher_id" id="teacher_id">
    <option value="">- Pilih Guru -</option>
    <?php 
	$sql2 = "SELECT * FROM `edu_teacher` WHERE `school_id` = '$school_id' AND `active` = true";
	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'teacher_id')
			),
			'selectCondition'=>array(
				'source'=>'teacher_id',
				'value'=>$teacher_id
			),
			'caption'=>array(
				'delimiter'=>\Pico\PicoConst::RAQUO,
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

if(!empty($teacher_id))
{
	$sql_filter .= " AND `edu_test`.`teacher_id` = '$teacher_id' ";
	$pagination->appendQueryName('teacher_id');
}
$sql_filter .= " AND `edu_test`.`teacher_id` = '$teacher_id' ";

$nt = '';


$sql = "SELECT `edu_test`.* $nt, 
(SELECT COUNT(DISTINCT `edu_test_member`.`student_id`) FROM `edu_test_member` WHERE `edu_test_member`.`test_id` = `edu_test`.`test_id`) AS `student`,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher`,
(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` GROUP BY `edu_question`.`test_id`)*1 AS `number_of_question`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' $sql_filter
HAVING 1 AND `student` > 0
ORDER BY `edu_test`.`test_id` DESC
";
$sql_test = "SELECT `edu_test`.* $nt, 
(SELECT COUNT(DISTINCT `edu_test_member`.`student_id`) FROM `edu_test_member` WHERE `edu_test_member`.`test_id` = `edu_test`.`test_id`) AS `student`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' $sql_filter
HAVING 1 AND `student` > 0
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());

$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{

$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
$paginationHTML = $pagination->buildHTML();
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="">

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>Nama Ujian</td>
      <td>Kelas</td>
      <td>Pelajaran</td>
      <td>Guru</td>
      <td>Durasi</td>
      <td>Sifat</td>
      <td>Soal</td>
      <td>Peserta</td>
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
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['teacher'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo gmdate('H:i:s', $data['duration']);?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Terbuka':'Tertutup';?></a></td>
      <td><?php if($data['number_of_question']){ ?><a href="data-question-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?> soal</a><?php } else { echo '-';} ?> </td>
      <td><?php if($data['student']){ ?><a href="data-question-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['student'];?> orang</a><?php } else { echo '-';} ?> </td>
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

</form>
<?php
}
else if(@$_GET['q'] != '')
{
?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
?>