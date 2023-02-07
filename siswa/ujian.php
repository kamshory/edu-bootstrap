<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}
$cfg->page_title = "Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(!empty(@$auth_student_id) && !empty(@$auth_school_id))
{
if(@$_GET['option'] == 'answer' && isset($_GET['answer_id']))
{
$answer_id = kh_filter_input(INPUT_GET, "answer_id", FILTER_SANITIZE_STRING_NEW);
$now = $picoEdu->getLocalDateTime();
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

$sql = "SELECT `edu_test`.* , `edu_answer`.`final_score`, `edu_answer`.`percent`, 
`edu_answer`.`start`, `edu_answer`.`end`, `edu_answer`.`competence_score`
FROM `edu_answer`
INNER JOIN (`edu_test`) on (`edu_test`.`test_id` = `edu_answer`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$answer_id' AND `edu_answer`.`student_id` = '$auth_student_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
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
        </td><td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['start'])));?> </td>
        </tr>
        <tr>
        <td>Selesai Ujian
        </td><td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['end'])));?> </td>
        </tr>
        <tr>
        <td>Nilai Akhir
        </td><td><?php echo ($data['final_score']);?> </td>
        </tr>
        <tr>
        <td>Persen
        </td><td><?php echo $picoEdu->numberFormatTrans($data['percent']);?> </td>
        </tr>
    </table>
</div>
<?php
$random_distribution = array();
if(strlen($data['random_distribution']) > 5)
{
	$bca = json_decode($data['random_distribution'], true);
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
		$random_distribution[] = array($val[0], $val[1]);
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
		$rowsx = $stmtx->fetchAll(PDO::FETCH_ASSOC);
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
		$random_distribution[] = array($val['basic_competence'], $val['num_question']);
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
		set `competence_score` = '".addslashes(json_encode($bc_score))."' 
		WHERE `answer_id` = '".$data['answer_id']."' AND `student_id` = '".$data['student_id']."' 
		";
		$database->execute($sql);
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
		foreach($random_distribution as $key=>$value)
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
		foreach($random_distribution as $key=>$value)
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
		foreach($random_distribution as $key=>$value)
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
LEFT JOIN (`edu_answer`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
LEFT JOIN (`edu_test`) on (`edu_test`.`test_id` = `edu_question`.`test_id`)
WHERE `edu_answer`.`answer_id` = '$answer_id' AND `edu_answer`.`student_id` = '$auth_student_id'
GROUP BY `edu_question`.`question_id` 
ORDER BY `pos` asc ";

$stmt1 = $database->executeQuery($sql);

if($stmt1->rowCount() > 0)
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
<ol class="test-question">
<?php
$i=0;
$no = $pagination->offset;
$rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
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
	where  `edu_option`.`question_id` = '$qid' group by  `edu_option`.`option_id` sort_order by  `edu_option`.`sort_order` ASC ";
	$stmt2 = $database->executeQuery($sql);

	if($stmt2->rowCount() > 0)
	{
	?>
	<div class="option">
	<ol class="listoption" style="list-style-type:<?php echo $data['numbering'];?>">
	<?php
	$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows2 as $data2)
	{
	?>
	<li>
	<?php
	if($data['publish_answer'] && $data['time_answer_publication'] <= $now)
	{
	?>
	<span class="option-circle<?php if($data2['score']) echo ' option-circle-selected';?>"><?php
	echo $data2['score']*1;
	?></span>
	<?php
	}
	?>
	<div class="list-option-item<?php echo ($data2['my_answer'])?' list-option-item-selected':'';?>">
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
	<input type="button" value="Tampilkan Semua Ujian" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
<?php
}
}
else
{
?>
<div class="warning">
Ujian tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali</a>.
</div>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'history' && isset($_GET['test_id']))
{
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

$sql = "SELECT `edu_test`.* 
FROM `edu_test`
INNER JOIN(`edu_answer`) on (`edu_answer`.`test_id` = `edu_test`.`test_id`)
WHERE `edu_test`.`test_id` = '$test_id' AND `edu_answer`.`student_id` = '$auth_student_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
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
ORDER BY `edu_answer`.`start` asc
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
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <td><?php echo $data['true'];?> </td>
      <td><?php echo $data['false'];?> </td>
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
<div class="warning">
Ujian tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali</a>.
</div>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'detail' && isset($_GET['test_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
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
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$array_class = $picoEdu->getArrayClass($school_id);
?>
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Ujian</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Kelas
		</td><td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
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
        <?php
		if(!$use_token)
		{
		?>
        <input type="button" name="join" id="join" class="btn com-button btn-success" value="Ikuti Ujian" onclick="window.location='ujian/?option=login&test_id=<?php echo $data['test_id'];?>'" /> 
        <?php
		}
		?>
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='ujian.php'" /></td>
		</tr>
	</table>
    <?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'enter-token')
{
include_once dirname(__FILE__)."/ujian-token.php";
}
else
{
if(!$use_token || @$_GET['option'] == 'list')
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$array_class = $picoEdu->getArrayClass($school_id);
?>
<style type="text/css">
.menu-control{
margin:0;
padding:2px 0;
position:absolute;
z-index:100;
left:30px;
top:100px;
background-color:#FFFFFF;
border:1px solid #DDDDDD;
box-shadow:0 0 3px #E5E5E5;
display:none;
}
.menu-control::before{
content:"";
width:10px;
height:0px;
border:10px solid transparent;
border-right:10px solid #DDDDDD;
position:absolute;
margin-left:-30px;
margin-top:30px;
}
.menu-control li{
list-style-type:none;
margin:0;
padding:0 2px;
}
.menu-control > li:first-child::before{
content:"";
width:9px;
height:0px;
border:9px solid transparent;
border-right:9px solid #FFFFFF;
position:absolute;
margin-left:-28px;
margin-top:31px;
}
.menu-control li a{
background-color:#FEFEFE;
display:block;
padding:5px 16px;
border-bottom:1px solid #EEEEEE;
}
.menu-control li a:hover{
background-color:#428AB7;
color:#FFFFFF;
}
.menu-control li:last-child a{
border-bottom:none;
}
</style>
<script type="text/javascript">
var use_token = <?php echo $use_token;?>;
window.onload = function()
{
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
	$(document).on('click', '.show-controls', function(e){
		var obj = $(this);
		if(obj.hasClass('menu-show'))
		{
			$('.show-controls').each(function(index, element) {
				$(this).removeClass('menu-show');
			});
			$('.menu-control').css({display:'none'});
		}
		else
		{
			$('.show-controls').each(function(index, element) {
				$(this).removeClass('menu-show');
			});
			var left = obj.offset().left + 40;
			var top = obj.offset().top - 34;
			var id = obj.attr('data-test-id');
			obj.addClass('menu-show');
			$('.menu-control').empty().append(buildMenu(id)).css({left:left, top:top, display:'block'});
		}
		e.preventDefault();
	});
}
function buildMenu(id)
{
	var html = 
	'<li><a href="ujian.php?option=detail&test_id='+id+'">Informasi Ujian</a></li>\r\n'+
	'<li><a href="ujian.php?option=history&test_id='+id+'">Riwayat Ujian</a></li>\r\n'+
	((use_token)?'':'<li><a href="ujian/?option=login&test_id='+id+'">Ikuti Ujian</a></li>\r\n')
	;
	return html;
}

</script>
<div class="search-control">  
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
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
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql . $pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = $picoEdu->createPaginationHtml($pagination);
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
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><i class="fas fa-pencil"></i></td>
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
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr>
      <td><a class="show-controls" data-test-id="<?php echo $data['test_id'];?>" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['school_program']);?></a></td>
      <td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><?php if($data['ntest']){?><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['ntest']);?> &times;</a><?php } else echo '-';?> </td>
      </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
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
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if($use_token)
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<ul class="list-rounded">
  <li><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=enter-token">Masuk Ujian</a></li>
  <li><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=list">Daftar Ujian</a></li>
</ul>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
}
}
?>