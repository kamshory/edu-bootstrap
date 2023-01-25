<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(@$school_id != 0)
{
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
	if (isset($_GET['test_id'])) {
		$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
		$sql = "SELECT `edu_test`.* ,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `koleksi`
from `edu_test` where `test_id` = '$test_id' 
";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			header("Content-Type: application/vnd.xls");
			header("Content-Disposition: attachment; filename=\"" . $data['name'] . ".xls\"");
			?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Analisa Soal Ujian - <?php echo $cfg->app_name; ?></title>
</head>

<body>
<h3>Analisa Butir Soal <?php echo $data['name']; ?></h3>
<?php

$number_of_option = $data['number_of_option'];
$caption_option = array();
for ($i = 0; $i < $number_of_option; $i++) {
	$caption_option[$i] = chr(65 + $i);
}

$sql = "select * from `edu_question` where `test_id` = '$test_id' order by `order` asc ";
$stmt = $database->executeQuery($sql);
if ($stmt->rowCount() > 0) {
{
?>
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table">
<thead>
  <tr>
    <td width="20">No</td>
    <td>Potongan Soal</td>
    <td width="70">Jawaban</td>
    <?php
							for ($i = 0; $i < $number_of_option; $i++) {
								?>
    <td width="20"><?php echo $caption_option[$i]; ?></td>
    <?php
							}
							?>
    <td width="70">Menjawab</td>
    <td width="60">Benar</td>
    <td width="60">Salah</td>
    <td width="70">%Benar</td>
  </tr>
</thead>

<tbody>
<?php
$no = 0;
$total_menjawab = 0;
$total_benar = 0;
$total_salah = 0;
$total_persen = 0;
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $data){
	$no++;
	$question_id = $data['question_id'];
	if (stripos($data['content'], "<p") === false) {
		$data['content'] = "<p>" . $data['content'] . "</p>";
	}
	$obj = parseHtmlData('<html><body>' . ($data['content']) . '</body></html>');
	$arrparno = array();
	$arrparlen = array();
	$cntmax = ""; // do not remove
	$content = ""; // do not remove
	$i = 0;
	$minlen = 10;

	if (isset($obj->p) && count($obj->p) > 0) {
		$max = 0;
		foreach ($obj->p as $parno => $par) {
			$arrparlen[$i] = strlen(trim(strip_tags($par), " \r\n\t&nbsp; "));
			if ($arrparlen[$i] > $max) {
				$max = $arrparlen[$i];
				$cntmax = $par;
			}
			if ($arrparlen[$i] >= $minlen) {
				$content = $par;
				break;
			}
		}
		if (!$content) {

			$content = $cntmax;
		}
	}

	$sql2 = "SELECT `edu_option`.*,
	(select count(distinct `edu_answer`.`answer_id`) 
		from `edu_answer` 
		where `edu_answer`.`answer` like concat('%,',`edu_option`.`option_id`,']%')
		group by `edu_answer`.`test_id`
		limit 0,1
		) as `pilih`
	from `edu_option`
	where `edu_option`.`question_id` = '$question_id' ";
	$answer = '';
	$option = array();
	$j = 0;
	$score = 0;
	$menjawab = 0;
	$stmt2 = $database->executeQuery($sql);
	if ($stmt2->rowCount() > 0) {
		foreach($rows2 as $data2) {
		$option[$j] = $data2['pilih'];
		if ($data2['score'] > $score) {
			$score = $data2['score'];
			$answer = $j;
		}
		$menjawab += $data2['pilih'];
		$j++;
	}
}

	?>
  <tr>
    <td align="right"><?php echo $no; ?></td>
    <td><?php echo substr($content, 0, 70); ?>...</td>
    <td><?php echo @$caption_option[$answer]; ?></td>
    <?php
	for ($i = 0; $i < $number_of_option; $i++) {
		?>
    <td><?php echo @$option[$i]; ?></td>
    <?php
	}
	?>
    <td><?php echo $menjawab; ?></td>
    <td><?php echo @$option[$answer] + 0; ?></td>
    <td><?php echo $menjawab - @$option[$answer]; ?></td>
    <td><?php if ($menjawab != 0) {
	echo $picoEdu->numberFormatTrans(100 * (@$option[$answer] + 0) / $menjawab, true);
} 
?></td>
</tr>
<?php
	$total_menjawab += $menjawab;
	$total_benar += @$option[$answer];
	$total_salah += $menjawab - @$option[$answer];
}
if ($total_menjawab != 0) 
{
	$total_persen = 100 * $total_benar / $total_menjawab;
} 
else 
{
	$total_persen = '';
}
?>
</tbody>
<?php
}
?>

<tfoot>
  <tr>
    <td colspan="<?php echo $number_of_option + 3; ?>">Total</td>
    <td><?php echo $total_menjawab; ?></td>
    <td><?php echo $total_benar; ?></td>
    <td><?php echo $total_salah; ?></td>
    <td><?php echo $picoEdu->numberFormatTrans($total_persen, true); ?></td>
  </tr>
</tfoot>
</table>
<?php
						}
						?>
</body>
</html>
<?php
		}
	}
}
?>