<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Monitoring Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(@$_GET['option'] == 'kick-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_peserta_test`.* from `edu_peserta_test` where `id` = '$id' and `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$waktu = $picoEdu->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$sql = "DELETE FROM `sessions` where `id` = '$sessions_id' ";
		$database->executeDelete($sql);
		$sql = "update `edu_peserta_test` set `waktu_keluar` = '$waktu', `ip_keluar` = '$ip', `login_edit` = '$admin_id', `status` = '3' where `id` = '$id'";	
		$database->executeUpdate($sql);
		header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&test_id=$test_id");
	}
}
if(@$_GET['option'] == 'block-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_peserta_test`.* from `edu_peserta_test` where `id` = '$id' and `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$waktu = $picoEdu->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$siswa_id = $data['siswa_id'];
		$sql = "DELETE FROM `sessions` where `id` = '$sessions_id' ";
		$database->executeDelete($sql);
		$sql = "update `edu_peserta_test` set `waktu_keluar` = '$waktu', `ip_keluar` = '$ip', `login_edit` = '$admin_id', `status` = '4' where `id` = '$id'";	
		$database->executeUpdate($sql);
		$sql = "update `siswa` set `blokir` = '1' where `siswa_id` = '$siswa_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id' ";
		$database->executeUpdate($sql);
	}
}

if(@$_GET['option'] == 'detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";

$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$status = kh_filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.* ,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `number_of_real_question`
from `edu_test` where `test_id` = '$test_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="160">Nama Ujian</td>
    <td><?php echo $data['name'];?></td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $data['subject'];?></td>
  </tr>
  <tr>
    <td>Jumlah Soal</td>
    <td><?php echo $data['number_of_question'];?></td>
  </tr>
  <tr>
    <td>Jumlah Pilihan</td>
    <td><?php echo $data['number_of_option'];?></td>
  </tr>
  <tr>
    <td>Koleksi Soal</td>
    <td><span id="jumlahkoleksi"><?php echo $data['number_of_real_question'];?></span></td>
  </tr>
    <tr>
    <td>Pengacakan Soal</td>
    <td><?php echo ($data['random'])?'Diacak':'Tidak Diacak';?></td>
    </tr>
    <tr>
    <td>Durasi Ujian</td>
    <td><?php echo gmdate('H:i:s', $data['duration']);?></td>
    </tr>
		<tr>
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo ($data['autosubmit'])?'Ya':'Tidak';?></td>
		</tr>
</table>
 </div>

<form name="searchform" id="searchform" method="get" action="">
<input type="hidden" name="option" value="detail" />
<input type="hidden" name="test_id" value="<?php echo $test_id;?>" />
<div style="padding-bottom:5px;">
Status <select name="status" id="status">
	<option value="">Semua</option>
	<option value="1"<?php if($status == '1') echo ' selected="selected"';?>>Ujian</option>
	<option value="2"<?php if($status == '2') echo ' selected="selected"';?>>Selesai</option>
	<option value="3"<?php if($status == '3') echo ' selected="selected"';?>>Dikeluarkan</option>
	<option value="4"<?php if($status == '4') echo ' selected="selected"';?>>Diblokir</option>
</select>
<input type="submit" id="show" class="com-button" value="Tampilkan" />
</div>
</form>

<style type="text/css">
.tabel-peserta-test tbody tr.duplikat td{
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

<div id="tabel-monitoring" data-test-id="<?php echo $test_id;?>" data-status="<?php echo $status;?>" data-url="ajax-test-monitoring.php">
<?php
include_once dirname(__FILE__)."/ajax-test-monitoring.php";
?>
</div>
<div class="button-area">
<input type="button" name="show-all" id="show-all" value="Semua Ujian" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
<?php
}
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
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
    <span class="search-label">Kelas</span> 
    <select class="input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
	$sql2 = "select * from `edu_class` where `school_id` = '$school_id' ";
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
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
    </select>
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_test`.`name` like '%".addslashes($pagination->query)."%' )";
}

if($class_id != '')
{
	$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
	$pagination->array_get[] = 'class_id';
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(select count(distinct `edu_test_member`.`student_id`) from `edu_test_member` where `edu_test_member`.`test_id` = `edu_test`.`test_id`) as `student`,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`)*1 as `number_of_question`
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
having 1 and `student` > 0
order by `edu_test`.`test_id` desc
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);

$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit > 0)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = "";
foreach($pagination->result as $i=>$obj)
{
$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="">

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
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
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr<?php $rowclass=""; if(@$data['default']==1) $rowclass.=" data-default"; if(isset($data['active'])){if(@$data['active']==1) $rowclass.=" data-active"; if(@$data['active']==0) $rowclass.=" data-inactive";} $rowclass = trim($rowclass); if(strlen($rowclass)){echo " class=\"$rowclass\"";}?>>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['teacher_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo gmdate('H:i:s', $data['duration']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Terbuka':'Tertutup';?></a></td>
      <td><?php if($data['number_of_question']){ ?><a href="data-question-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?> soal</a><?php } else { echo '-';} ?></td>
      <td><?php if($data['student']){ ?><a href="data-question-ujian.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['student'];?> orang</a><?php } else { echo '-';} ?></td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

</form>
<?php
}
else if(@$_GET['q'])
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>