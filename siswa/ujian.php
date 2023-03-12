<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$pageTitle = "Ujian";
$pagination = new \Pico\PicoPagination();

if(!empty(@$auth_student_id) && !empty(@$auth_school_id))
{
if(@$_GET['option'] == 'answer' && isset($_GET['answer_id']))
{
$answer_id = kh_filter_input(INPUT_GET, "answer_id", FILTER_SANITIZE_STRING_NEW);
$now = $database->getLocalDateTime();
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

$sql = "SELECT `edu_test`.* , `edu_answer`.`final_score`, `edu_answer`.`percent`, 
`edu_answer`.`start`, `edu_answer`.`end`, `edu_answer`.`competence_score`
FROM `edu_answer`
INNER JOIN (`edu_test`) ON (`edu_test`.`test_id` = `edu_answer`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$answer_id' AND `edu_answer`.`student_id` = '$auth_student_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
$test_id = $data['test_id'];
?>
<style type="text/css">
.test-info{
	padding:10px;
	border:1px solid #DDDDDD;
	background-color:#FAFAFA;
	margin-bottom:10px;
}
</style>
<div class="test-info">
      <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
        <tr>
        <td>Ujian</td><td><?php echo $data['name'];?> </td>
        </tr>
        <tr>
        <td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
        </tr>
        <tr>
        <td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
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
        <td>Mulai Ujian
        </td><td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['start'])));?> </td>
        </tr>
        <tr>
        <td>Selesai Ujian
        </td><td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['end'])));?> </td>
        </tr>
        <tr>
        <td>Nilai Akhir
        </td><td><?php echo $data['final_score'];?> </td>
        </tr>
        <tr>
        <td>Persen
        </td><td><?php echo $picoEdu->numberFormatTrans($data['percent']);?> </td>
        </tr>
    </table>
</div>
<?php
$competence_distribution = array();
if(strlen($data['competence_distribution']) > 5)
{
	$bca = json_decode($data['competence_distribution'], true);
	$bc_array = array();
	foreach($bca as $key=>$val)
	{
		$bc = str_replace("bc", "", $key);
		$bc = str_replace("_", ".", $bc);
		$arr = explode(".", $bc, 2);
		$major = @$arr[0]*1;
		$minor = str_replace(".", "", @$arr[1]) * 1;
		$idx = $major * 100 + $minor;  
		$bc_array[$idx] = array($bc, $val);
	}
	ksort($bc_array);
	foreach($bc_array as $key=>$val)
	{
		$competence_distribution[] = array($val[0], $val[1]);
	}
}
else
{
	$bc_array = array();
	$sql = "SELECT `edu_question`.`basic_competence`, count(distinct `edu_question`.`question_id`) AS `num_question`
	FROM `edu_question`
	WHERE `edu_question`.`test_id` = '$test_id' 
	GROUP BY `edu_question`.`basic_competence`
	";
	$stmtx = $database->executeQuery($sql);
	if ($stmtx->rowCount() > 0) {
		$rowsx = $stmtx->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rowsx as $dt) {
			$bc = $dt['basic_competence'];
			if (strlen($bc) > 0) {
				$arr = explode(".", $bc, 2);
				$major = @$arr[0] * 1;
				$minor = str_replace(".", "", @$arr[1]) * 1;
				$idx = $major * 100 + $minor;
				$bc_array[$idx] = $dt;
			}
		}
	}
	ksort($bc_array);
	foreach($bc_array as $key=>$val)
	{
		$competence_distribution[] = array($val['basic_competence'], $val['num_question']);
	}
	
}
if(strlen($data['competence_score']) > 5)
{
	$bc_score = json_decode($data['competence_score'], true);
}
else
{
	$bc_score = $picoEdu->getTextScore($answer_id, true);
	if(isset($data['answer_id']) && isset($data['student_id']))
	{
		$sql = "UPDATE `edu_answer` 
		SET `competence_score` = '".addslashes(json_encode($bc_score))."' 
		WHERE `answer_id` = '".$data['answer_id']."' AND `student_id` = '".$data['student_id']."' 
		";
		$database->executeUpdate($sql, true);
	}
}
if(count($bc_score))
{
$bc_score_value = array();
foreach($bc_score as $key=>$val)
{
	$bc_score_value[$val[0]] = $val[1];
}
	?>
<div class="basic-competence-score">
    <table border="1" cellpadding="0" cellspacing="0">
    <thead>
    	<tr>
        <td>Kompetensi Dasar</td>
        <?php
		foreach($competence_distribution as $key=>$value)
		{
			?>
            <td align="right"><?php echo $value[0];?> </td>
            <?php
		}
		?>
        </tr>
     </thead>
     <tbody>   
    	<tr>
        <td>Jumlah Soal</td>
        <?php
		foreach($competence_distribution as $key=>$value)
		{
			?>
            <td align="right"><?php echo $value[1];?> </td>
            <?php
		}
		?>
        </tr>
    	<tr>
        <td>Nilai</td>
        <?php
		foreach($competence_distribution as $key=>$value)
		{
			$score = @$bc_score_value[$value[0]] * 1;
			?>
            <td align="right"><?php echo $picoEdu->numberFormatTrans($score, true);?> </td>
            <?php
		}
		?>
        </tr>
   	</tbody>
    </table>
</div>
    <?php
}
?>

<?php

$sql = "SELECT `edu_question`.* , `edu_answer`.`answer` AS `answer` , instr(`edu_answer`.`answer`,`edu_question`.`question_id`) AS `pos`,
`edu_test`.`publish_answer`, `edu_test`.`time_answer_publication`
FROM `edu_question` 
LEFT JOIN (`edu_answer`) ON (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
LEFT JOIN (`edu_test`) ON (`edu_test`.`test_id` = `edu_question`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$answer_id' AND `edu_answer`.`student_id` = '$auth_student_id'
GROUP BY `edu_question`.`question_id` 
ORDER BY `pos` ASC ";

$stmt1 = $database->executeQuery($sql);

if($stmt1->rowCount() > 0)
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
<ol class="test-question">
<?php
$i=0;
$no = $pagination->getOffset();
$rows1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
foreach($rows1 as $data)
{
	$j=$i%2;
	$no++;
	$qid = $data['question_id'];
	$answer = $data['answer'];
	?>
	<li value="<?php echo $no;?>">
	<div class="kd-ctrl"><a href="javascript:;"><span><?php echo $data['basic_competence'];?></span></a></div>
	<div class="question">
	<?php echo $data['content'];?>
	<?php
	$sql2 = "SELECT `edu_option`.* , '$answer' like concat('%,',`edu_option`.`option_id`,']%') AS `my_answer`
	FROM `edu_option` 
	where  `edu_option`.`question_id` = '$qid' group by  `edu_option`.`option_id` ORDER BY  `edu_option`.`sort_order` ASC ";
	$stmt2 = $database->executeQuery($sql);

	if($stmt2->rowCount() > 0)
	{
	?>
	<div class="option">
	<ol class="listoption" style="list-style-type:<?php echo $data['numbering'];?>">
	<?php
	$rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows2 as $data2)
	{
	?>
	<li>
	<?php
	if($data['publish_answer'] && $data['time_answer_publication'] <= $now)
	{
	?>
	<span class="option-circle<?php echo $picoEdu->trueFalse($data2['score'], ' option-circle-selected', '');?>"><?php
	echo $data2['score']*1;
	?></span>
	<?php
	}
	?>
	<div class="list-option-item<?php echo $picoEdu->trueFalse($data2['my_answer'], ' list-option-item-selected', '');?>">
	<div class="option-content">
	<?php
	echo $data2['content'];
	?>
	</div>
	</div>
	</li>
	<?php
	}
	?>
	</ol>
	</div>
	<?php
	}
	?>
	</div>
	</li>
	<?php
	$i++;
}
?>
</ol>

<div class="button-area">
	<input type="button" value="Tampilkan Semua Ujian" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" />
</div>
<?php
}
}
else
{
?>
<div class="alert alert-warning">
Ujian tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali</a>.
</div>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'history' && isset($_GET['test_id']))
{
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

$sql = "SELECT `edu_test`.* 
FROM `edu_test`
INNER JOIN (`edu_answer`) ON (`edu_answer`.`test_id` = `edu_test`.`test_id`)
WHERE `edu_test`.`test_id` = '$test_id' AND `edu_answer`.`student_id` = '$auth_student_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<style type="text/css">
.test-info{
	padding:10px;
	border:1px solid #DDDDDD;
	background-color:#FAFAFA;
	margin-bottom:10px;
}
</style>
<div class="test-info">
      <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
        <tr>
        <td>Ujian</td><td><?php echo $data['name'];?> </td>
        </tr>
        <tr>
        <td>Jumlah Soal</td><td><?php echo $data['number_of_question'];?> </td>
        </tr>
        <tr>
        <td>Jumlah Pilihan</td><td><?php echo $data['number_of_option'];?> </td>
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
		<td>Otomatis Kirim Jawaban</td>
		<td><?php echo $picoEdu->trueFalse($data['autosubmit'], 'Ya', 'Tidak');?> </td>
		</tr>
    </table>
</div>
<?php

$sql = "SELECT `edu_answer`.*
FROM `edu_answer`
WHERE `edu_answer`.`student_id` = '$auth_student_id' AND `edu_answer`.`test_id` = '$test_id' 
ORDER BY `edu_answer`.`start` ASC
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
<style type="text/css">
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(3), .hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
</style>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>Pelaksanaan Ujian</td>
      <td>Durasi</td>
      <td>Soal</td>
      <td>Benar</td>
      <td>Salah</td>
      <td>N.Awal</td>
      <td>Penalti</td>
      <td>N.Akhir</td>
      <td>Persen</td>
    </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	$data['number_of_question'] = substr_count($data['answer'], "]");
	?>
    <tr>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="ujian.php?option=answer&answer_id=<?php echo $data['answer_id'];?>"><?php echo translateDate(date('d M Y H:i:s', strtotime($data['start'])));?></a></td>
      <td><?php echo gmdate('H:i:s', strtotime($data['end']) - strtotime($data['start']));?> </td>
      <td><?php if($data['number_of_question']){ echo $data['number_of_question']; } else { echo '-';} ?> </td>
      <td><?php echo $data['answer_true'];?> </td>
      <td><?php echo $data['answer_false'];?> </td>
      <td><?php echo $data['initial_score'];?> </td>
      <td><?php echo $data['penalty'];?> </td>
      <td><?php echo $data['final_score'];?> </td>
      <td><?php echo $picoEdu->numberFormatTrans($data['percent'], true);?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>
<?php
}
}
else
{
?>
<div class="alert alert-warning">
Ujian tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali</a>.
</div>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'detail' && isset($_GET['test_id']))
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_test`.* $nt,
	(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher_id`
	FROM `edu_test` 
	WHERE `edu_test`.`test_id` = '$test_id' AND `edu_test`.`school_id` = '$school_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	$array_class = $picoEdu->getArrayClass($school_id);
	?>

	<script>
		let testId = '<?php echo $test_id;?>';
		let websocketURL = '<?php echo $picoEdu->getWebsocketHost();?>/?module=test&test_id='+testId;
	</script>
	<script src="../lib.assets/script/test-ws.js"></script>
	<script src="../lib.assets/script/test-ws-student.js"></script>

	<div class="modal fade" id="test-alert" tabindex="-1" role="dialog" aria-labelledby="test-alert-title" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="test-alert-title">Pesan Pengawas</h5>
		</div>
			<div class="modal-body">
			</div>
		</div>
	</div>
	</div>



	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td>Nama Ujian</td>
			<td><?php echo $data['name'];?> </td>
			</tr>
			<tr>
			<td>Kelas
			</td><td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
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
			<td>Durasi
			</td><td><?php echo gmdate('H:i:s', $data['duration']);?> </td>
			</tr>
			<?php
			if($data['has_alert'])
			{
			?>
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
		</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td></td>
			<td>
			<input type="button" name="join" id="join" class="btn btn-success" value="Ikuti Ujian" onclick="window.location='ujian/<?php echo $data['test_id'];?>'" /> 
			<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='ujian.php'" /></td>
			</tr>
		</table>
		<?php
	}
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'enter-token')
{
	require_once dirname(__FILE__)."/ujian-token.php";
}
else
{
if(!$use_token || @$_GET['option'] == 'list')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$teacher_id = kh_filter_input(INPUT_GET, "teacher_id", FILTER_SANITIZE_STRING_NEW);

$array_class = $picoEdu->getArrayClass($school_id);
?>

<script type="text/javascript">
var use_token = <?php echo $use_token;?>;
window.onload = function()
{
	$(document).on('change', '#searchform select', function(e){
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
	$sql2 = "SELECT * FROM `edu_teacher` WHERE `school_id` = '$school_id' AND `active` = true ORDER BY `name` ASC ";	
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
					'reg_number',
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
$sql_filter .= " 
	and (
	`edu_test`.`open` = '1'
	or `edu_test`.`class` = ''
	or concat(',', `edu_test`.`class`, ',') like '%,$class_id,%'
	)
";

$nt = '';

$sql = "SELECT `edu_test`.* $nt,
(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_test`.`school_program_id`) AS `school_program`,
(SELECT COUNT(DISTINCT `edu_answer`.`answer_id`) FROM `edu_answer` 
WHERE `edu_answer`.`test_id` = `edu_test`.`test_id` AND `edu_answer`.`student_id` = '$auth_student_id') AS `ntest`
FROM `edu_test`
WHERE `edu_test`.`active` = true AND `edu_test`.`school_id` = '$school_id' $sql_filter
ORDER BY `edu_test`.`test_id` DESC
";
$sql_test = "SELECT `edu_test`.*
FROM `edu_test`
WHERE `edu_test`.`active` = true AND `edu_test`.`school_id` = '$school_id' $sql_filter
";

$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
$paginationHTML = $pagination->buildHTML();
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(5)
	{
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4)
	{
		display:none;
	}
}
</style>
<ul class="menu-control">
</ul>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><i class="fas fa-list"></i></td>
      <td width="25">No</td>
      <td>Ujian</td>
      <td>Kelas</td>
      <td>Jurusan</td>
      <td>Pelajaran</td>
      <td>Ikut</td>
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
    <tr>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><i class="fas fa-list"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['school_program'];?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><?php if($data['ntest']){?><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['ntest'];?> &times;</a><?php } else {echo '-';}?> </td>
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
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if($use_token)
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<ul class="list-rounded">
  <li><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=enter-token">Masuk Ujian</a></li>
  <li><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=list">Daftar Ujian</a></li>
</ul>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
}
}
?>