<?php
if(!defined('DB_NAME'))
{
	exit();
}
$dur_obj = $picoEdu->secondsToTime($data['duration']);
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?php echo rtrim($cfg->base_url, "/");?>/">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/jqu/assets/font/open-sans/index.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-student.min.css" />
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/lib.assets/theme/default/css/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/jqu/assets/css/style.min.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/jqu/assets/font/icon.css" />
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/jqu/assets/js/jquery.unand.min.js"></script>
<script type="application/javascript">
var mui;
$(document).ready(function(e) {
	mui = new mobileUI('*');
});
</script>
<title><?php echo $data['name'];?> - <?php echo $cfg->app_name;?></title>
</head>
<body>
<div class="all" data-has_alert="false">
<div class="wrapper">
<?php

?>
<div class="header-container">
<div class="header">
	<div class="cop">
    <h1><?php echo $data['name'];?></h1>
    <h2><?php echo $student_name;?></h2>
    </div>
    <table width="100%" border="0" cellpadding="0">
    <?php
	if($data['test_availability'] != 'F')
	{
	?>
      <tr>
        <td width="48%" align="right">Batas Akhir Ujian</td>
        <td width="52%">: <?php echo date('j F Y H:i', strtotime($data['available_to']));?></td>
      </tr>
      <?php
	}
	?>
      <tr>
        <td width="48%" align="right">Jumlah Soal</td>
        <td width="52%">: <?php echo $data['number_of_question'];?> Soal</td>
      </tr>
      <tr>
        <td align="right">Durasi Ujian</td>
        <td>: <?php echo implode(':',$dur_obj);?></td>
      </tr>
      <tr>
        <td align="right">Sisa Waktu</td>
        <td>: <span id="sisa-waktu"></span></td>
      </tr>
    </table>
    </div>
    </div>
    
    <div id="countdown-element">
    	<div id="countdown-element-inner"></div>
    </div>
    <?php
	if($has_alert && $alert_message != '')
	{
	?>
    <div id="has_alert"><?php echo $alert_message;?></div>
    <?php
	}
	?>
<script type="text/javascript">
var alert_time = <?php echo $alert_time;?>;
var test = <?php echo $test_id;?>;
var autosubmit = <?php echo $autosubmit*1;?>;
var due_time = <?php echo (@$_SESSION['session_test'][$student_id][$test_id]['due_time']-time());?>;
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/test-us.min.js"></script>
<?php	

$question_package = addslashes($question_package);
$sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) as `order`
from `edu_question`
where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
order by `order`
";
$res = mysql_query($sql);

$number_of_question = mysql_num_rows($res);;
$no_halaman_awal = 0;
$no_halaman_akhir = 0;

if($number_of_question)
{
$offset_maksimum = floor($number_of_question/$question_per_page);
$jumlah_halaman = floor($number_of_question/$question_per_page);
if($offset_maksimum == $number_of_question/$question_per_page)
{
	$offset_maksimum = ($number_of_question/$question_per_page) - 1;
}
$question_per_page = $question_per_page * 1;

$sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) as `order`
from `edu_question`
where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
order by `order`
";
$res = mysql_query($sql);

if($guidance_text)
{
?>
<div class="guidance">
<?php echo nl2br($guidance_text);?>
</div>
<?php
}

$jumlah_halaman = 1;
if($question_per_page < $number_of_question)
{
	if($question_per_page > 0)
	{
		$jumlah_halaman = ceil($number_of_question / $question_per_page);
	}
	else
	{
		$jumlah_halaman = 1;
	}
}
else
{
	$dibagi = 0;
	$jumlah_halaman = 1;
}
if($jumlah_halaman > 1)
{
	$dibagi = 1;
}
else
{
	$dibagi = 0;
}

?>
<div id="question-test-wrapper" data-dibagi="<?php echo $dibagi;?>">
<form name="testfrm" id="testfrm" method="post" action="<?php echo $cfg->base_url."siswa/ujian/";?>" enctype="multipart/form-data">
<ol id="test-question">
<?php
$segmen = 0;
$no = $offset;
$inc = 0;
$arr_soal = array();
while(($data = mysql_fetch_assoc($res)))
{
	$soal = $data['question_id'];
	$no++;
	$segmen = floor($inc/$question_per_page);
	if($segmen == 0) $display = "";
	else $display = "none";
	$arr_soal[$no] = $soal;
	?>
    <li data-segmen="<?php echo $segmen;?>" value="<?php echo $no;?>" data-number="<?php echo $no;?>" data-question="<?php echo $soal;?>" style="display:<?php echo $display;?>">
    <div class="question-item">
    <div class="test-content">
    	<?php echo $data['content'];?>
    </div>
    <div class="option-area">
    <?php
	if($data['random'])
	{
	$sql2 = "SELECT `edu_option`.* , rand() as `rand`
	from `edu_option`
	where `edu_option`.`question_id` = '$soal'
	order by `rand` asc
	";
	}
	else
	{
	$sql2 = "SELECT `edu_option`.* , rand() as `rand`
	from `edu_option`
	where `edu_option`.`question_id` = '$soal'
	order by `order` asc
	";
	}
	$i=1;
	$stmt2 = $database->executeQuery($sql2);
			if ($stmt2->rowCount() > 0) {
				$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
					$answer = @$_SESSION['answer_tmp'][$student_id][$test_id]['answer_' . $data2['question_id']];
					?>
    	<div class="option-item">
        	<div class="option-ctrl">
            	<label><input type="radio" data-test="<?php echo $test_id; ?>" data-question="<?php echo $soal; ?>" name="answer_<?php echo $data['question_id']; ?>" id="answer_<?php echo $data['question_id']; ?>" class="radio_answer" value="<?php echo $data2['option_id']; ?>">
                <?php echo liststyle($data['numbering'], $i); ?>
                </label>
            </div>
            <div class="option-content">
            	<?php echo ($data2['content']); ?>
            </div>
            <div class="clear"></div>
        </div>
        <?php
						$i++;
				}
			}
	?>
    	<div class="option-item">
        	<div class="option-ctrl">
            	<label><input type="radio" data-test="<?php echo $test_id;?>" data-question="<?php echo $soal;?>" name="answer_<?php echo $data['question_id'];?>" id="answer_<?php echo $data['question_id'];?>" class="radio_answer" value=""<?php if($answer=='') echo ' checked="checked"';?>>
                <?php echo liststyle($data['numbering'], $i);?>
                </label>
            </div>
            <div class="option-content">
            	Tidak menjawab
            </div>
            <div class="clear"></div>
        </div>
    </div>
    </div>
    </li>
    <?php
	$inc++;
}
?>
</ol>
<?php
}
?>
<div class="pagination">
<ul>
<?php
for($i = 0, $j = 0, $k = 1; $i<$number_of_question; $i += $question_per_page, $j++, $k++)
{
if($j == 0)
{
	$pgs = "page-selected";
}
else
{
	$pgs = "page-not-selected";
}
?>
<li><a href="#" data-segmen="<?php echo $j?>" class="<?php echo $pgs;?>"><?php echo $k?></a></li>
<?php
}
?>
</ul>
</div>

<div class="button-area" style="margin-bottom:10px;">
<?php
$start = @$_SESSION['session_test'][$student_id][$test_id]['start'];
if($start == '' || $start == '0000-00-00 00:00:00')
{
	$start = $picoEdu->getLocalDateTime();
	$_SESSION['session_test'][$student_id][$test_id]['start'] = $start;
}
?>
<input type="button" name="check" id="check" class="com-button submit-test-button" href="#check-answer" data-rel="popup" value="Periksa Jawaban" />
<input type="button" name="save" id="save" class="com-button submit-test-button" value="Kirim Jawaban" onclick="submitTest($(this).closest('form'))"/>
<input type="hidden" name="submit_test" value="Kirim">
<input type="hidden" name="time_start" value="<?php echo $start;?>">
<input type="hidden" name="test_id" value="<?php echo $test_id;?>">
</div>
</form>
</div>
</div>
</div>

<div style="display:none;">
	<div class="dialog-loggedout" title="Keluar Ujian">
    	<div class="dialog-loggedout-inner">
        <p>Anda telah keluar dari test ini. Silakan masuk kembali dengan ID dan password Anda.</p>
        </div>
    </div>
</div>
<div data-role="popup" data-section="check-answer" data-modal="true" data-title="Periksa Jawaban" data-close-icon="true">
    <div class="dialog-check-inner">
        <div class="circle-answer-area">
            <?php
            for($i = 0, $j=1; $i<$number_of_question; $i++, $j++)
            {
                $segmen = floor($i/$question_per_page);
            ?>
            <a data-answered="false" data-number="<?php echo $j?>" data-question="<?php echo $arr_soal[$j];?>" class="tigger-check-answer" data-segmen="<?php echo $segmen;?>" href="#"><span class="circle circle-32" data-answered="false"><?php echo $j?></span></a>
            <?php
            }
            ?>
        </div>
        <a href="#"><span class="circle circle-32" data-answered="false"></span></a> <span style="line-height:24px; vertical-align:top;"> = belum dijawab</span>        
    </div>
</div>

</body>
</html>