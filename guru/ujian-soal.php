<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/bukan-guru.php";
	exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
$cfg->page_title = "Soal Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
$time_create = $time_edit = $picoEdu->getLocalDateTime();
$member_create = $member_edit = $teacher_id;

if(@$_GET['option'] == 'delete')
{
	$question_id = kh_filter_input(INPUT_GET, "question_id", FILTER_SANITIZE_STRING_NEW);
	$digest = kh_filter_input(INPUT_GET, "digest", FILTER_SANITIZE_STRING_NEW_BASE64);
	$sql = "SELECT * FROM `edu_question` WHERE `question_id` = '$question_id' and `digest` = '$digest' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$dt = $stmt->fetch(PDO::FETCH_ASSOC);
		$id = $dt['question_id'];
		$test_id = $dt['test_id'];
		$sql = "DELETE FROM `edu_option` WHERE `question_id` = '$id' ";
		$database->executeDelete($sql, true);
		$sql = "DELETE FROM `edu_question` WHERE `question_id` = '$id' ";
		$database->executeDelete($sql, true);
		header("Location: ".basename($_SERVER['PHP_SELF'])."?test_id=$test_id"); //NOSONAR
	}
}

if(isset($_POST['savetext']) && @$_GET['option'] == 'add')
{
	// Format Plain
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$picoEdu->sortQuestion($test_id);
	$sql = "SELECT `edu_test`.*, 
	(select `edu_question`.`sort_order` FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` desc limit 0,1) as `sort_order`
	FROM `edu_test`
	WHERE `edu_test`.`test_id` = '$test_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$time_create = $picoEdu->getLocalDateTime();
		$time_edit = $picoEdu->getLocalDateTime();
	
		$random = ((int) $data['random']);
		$sort_order = ((int) $data['sort_order']);
		$score_standar = $data['standard_score'];
		
		$xml_data = kh_filter_input(INPUT_POST, "question_text", FILTER_DEFAULT);
		$clear_data = parseRawQuestion($xml_data);

		
		$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
		$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
		$dirBase = dirname(dirname(__FILE__));
		$permission = 0755;
		$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);
		
		$base_src = "media.edu/school/$school_id/test/$test_id";
		$database->executeTransaction("start transaction", true);
		$oke = 1;
		
		foreach($clear_data as $question_no=>$question)
		{
			$object = parseQuestion($question);
			if(isset($object['question']) && isset($object['numbering']) && isset($object['option']))
			{
				$content = addslashes(nl2br(UTF8ToEntities(filter_html(addImages(@$object['question'], $base_dir, $base_src)))));
				$content = $picoEdu->brToNewLineEncoded($content);
				$numbering = addslashes($object['numbering']);
				$digest = md5($object['question']);
				$sort_order++;
				
				$sql1 = "INSERT INTO `edu_question` 
				(`content`, `test_id`, `sort_order`, `multiple_choice`, `random`, `numbering`, `digest`, 
				`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
				('$content', '$test_id', '$sort_order', true, '$random', '$numbering', '$digest', 
				'$time_create', '$member_create', '$time_edit', '$member_edit', true)
				";
				$stmt1 = $database->executeInsert($sql1, true);
				if($stmt1->rowCount() == 0)
				{
					$oke = $oke * 0;
				}
				else
				{
					$question_id = $database->getDatabaseConnection()->lastInsertId();
					if(@is_array($object['option']) && count($object['option']))
					{
						foreach($object['option'] as $option_no=>$option)
						{
							$content_option = addslashes(nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $base_dir, $base_src)))));
							$content_option = $picoEdu->brToNewLineEncoded($content_option);
							$order_option = $option_no+1;
							$score_option = addslashes(@$option['value']*$score_standar); 
							if($score_option == 0) 
							{
								$score_option = addslashes(@$option['score']*$score_standar);
							}
							
							$sql2 = "INSERT INTO `edu_option` 
							(`question_id`, `content`, `sort_order`, `score`, 
							`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
							('$question_id', '$content_option', '$order_option', '$score_option', 
							'$time_create', '$member_create', '$time_edit', '$member_edit', true)
							";
							$stmt2 = $database->executeInsert($sql2);
							if($stmt2->rowCount() > 0)
							{
								$oke = $oke * 0;
							}
						}
					}
				}
			}
		}
		if($oke)
		{
			$database->executeTransaction("commit", true);
		}
		else
		{
			$database->executeTransaction("rollback", true);
		}
		header("Location: ".$_SERVER['REQUEST_URI']);
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
	$number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
	$numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
	$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
	
	$direktori = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
	$prefiks = "media.edu/school/$school_id/test/$test_id";

	$question = kh_filter_input(INPUT_POST, "question");
	$question = UTF8ToEntities($question);
	$question = addslashes(removeparagraphtag(extractImageData($question, $direktori, $prefiks, $fileSync))); 	
	$question = $picoEdu->brToNewLineEncoded($question);
	$picoEdu->sortQuestion($test_id);
	$sql1 = "SELECT `edu_test`.*, 
	(select `edu_question`.`sort_order` FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` desc limit 0,1) as `sort_order`
	FROM `edu_test`
	WHERE `edu_test`.`test_id` = '$test_id'
	";
	$stmt1 = $database->executeQuery($sql1);

	if($stmt1->rowCount() > 0)
	{
		$data1 = $stmt1->fetch(PDO::FETCH_ASSOC);
		$sort_order = $data1['sort_order'] + 1;
		$time_create = $picoEdu->getLocalDateTime();
		$time_edit = $picoEdu->getLocalDateTime();
		
		$digest = md5($question);
		$sql = "SELECT * FROM `edu_question` WHERE `digest` = '$digest' and `test_id` = '$test_id' ";
		$stmt2 = $database->executeQuery($sql);
		if($stmt2->rowCount() == 0)
		{
			$database->executeTransaction("start transaction", true);

			$question_id = $database->generateNewId();

			$sql = "INSERT INTO `edu_question` 
			(`question_id`, `content`, `test_id`, `multiple_choice`, `random`, `numbering`, `digest`, `sort_order`,
			`time_create`, `member_create`, `time_edit`, `member_edit`) values
			('$question_id', '$question', '$test_id', true, '$random', '$numbering', '$digest', '$sort_order',
			'$time_create', '$member_create', '$time_edit', '$member_edit')";
			$database->executeInsert($sql, true);
			$sort_order = 0;
			$oke = 1;
			for($i=1; $i <= $number_of_option; $i++)
			{
				$sort_order++;
				$id2 = $i;
					
				$option = kh_filter_input(INPUT_POST, "option_".$id2);
				$option = UTF8ToEntities($option);
				$option = addslashes(removeparagraphtag(extractImageData($option, $direktori, $prefiks, $fileSync)));
				$option = $picoEdu->brToNewLineEncoded($option);
						
				$score = kh_filter_input(INPUT_POST, "score_".$id2, FILTER_SANITIZE_NUMBER_FLOAT);
				$option_id = $database->generateNewId();
				$sql = "INSERT INTO `edu_option` 
				(`option_id`, `question_id`, `content`, `sort_order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
				('$option_id', '$question_id', '$option', '$sort_order', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit');";
				$stmt3 = $database->executeInsert($sql, true);
				if($stmt3->rowCount() > 0)
				{
					$oke = $oke*1;
				}
				else
				{
					$oke = 0;
				}
			}
			if($oke)
			{
				$database->executeTransaction("commit", true);
			}
			else
			{
				$database->executeTransaction("rollback", true);
			}
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$question_id = kh_filter_input(INPUT_POST, "question_id", FILTER_SANITIZE_STRING_NEW);
	$numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
	$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
	$basic_competence = trim(kh_filter_input(INPUT_POST, "basic_competence", FILTER_SANITIZE_STRING_NEW));
	$basic_competence = preg_replace("/[^0-9]/i", ".", $basic_competence); // NOSONAR
	$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");

	$sql = "SELECT `test_id` FROM `edu_question` WHERE `question_id` = '$question_id' ";
	$stmt1 = $database->executeQuery($sql);
	if($stmt1->rowCount() > 0)
	{
		$dt = $stmt1->fetch(PDO::FETCH_ASSOC);
		$test_id = $dt['test_id'];
		
		$direktori = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
		$prefiks = "media.edu/school/$school_id/test/$test_id";
	
		$question = kh_filter_input(INPUT_POST, "question");
		$question = UTF8ToEntities($question);
		$question = addslashes(removeparagraphtag(extractImageData($question, $direktori, $prefiks, $fileSync))); 	
		$question = $picoEdu->brToNewLineEncoded($question);
		
		$sql = "UPDATE `edu_question` 
		set `content` = '$question', `random` = '$random', `numbering` = '$numbering', `basic_competence` = '$basic_competence' 
		WHERE `question_id` = '$question_id'";
		$stmt2 = $database->executeQuery($sql);
		if($stmt2->rowCount() > 0)
		{
			$sql = "UPDATE `edu_question` SET `time_edit` = '$time_edit', `member_edit` = '$member_edit' WHERE `question_id` = '$question_id'";
			$database->executeUpdate($sql, true);			
		}
		
		$sql3 = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
		$stmt3 = $database->executeQuery($sql3);
		if ($stmt3->rowCount() > 0) {
			$rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows3 as $dt) {
				$id2 = $dt['option_id'];

				$option = kh_filter_input(INPUT_POST, "option_" . $id2);
				$option = UTF8ToEntities($option);
				$option = addslashes(removeparagraphtag(extractImageData($option, $direktori, $prefiks, $fileSync)));
				$option = $picoEdu->brToNewLineEncoded($option);

				$score = kh_filter_input(INPUT_POST, "score_" . $id2, FILTER_SANITIZE_NUMBER_FLOAT);
				$sql = "UPDATE `edu_option` SET `content` = '$option', `score` = '$score' WHERE `question_id` = '$question_id' and `option_id` = '$id2'";
				$stmt4 = $database->executeQuery($sql);
				if ($stmt4->rowCount() > 0) 
				{
					$sql = "UPDATE `edu_option` SET `time_edit` = '$time_edit', `member_edit` = '$member_edit' 
					WHERE `question_id` = '$question_id' and `option_id` = '$id2'";
					$database->executeUpdate($sql, true);
				}
			}
		}
		if(@$_GET['ref'])
		{
			$ref = base64_decode($_GET['ref']);
			if($ref)
			{
				header("Location: $ref");
			}
		}
		else
		{
			header("Location: ".basename($_SERVER['PHP_SELF'])."?test_id=$test_id");
		}
	}
}

include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option'] == 'add')
{
	if(@$_GET['format']=='text')
	{
		include_once dirname(__FILE__)."/test-editor.php";
		exit();
	}
	include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test`.* ,
	(select count(distinct `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) as `collection`
	FROM `edu_test` WHERE `test_id` = '$test_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
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
			<td><?php echo $data['number_of_question'];?> soal</td>
		</tr>
		<tr>
			<td>Jumlah Pilihan</td>
			<td><?php echo $data['number_of_option'];?> pilihan</td>
		</tr>
		<tr>
			<td>Koleksi Soal</td>
			<td><span id="total_collection"><?php echo $data['collection'];?></span> soal <a href="ujian-soal.php?test_id=<?php echo $data['test_id'];?>">Lihat</a></td>
		</tr>
			<tr>
			<td>Pengacakan Soal</td>
			<td><?php echo ($data['random'])?'Diacak':'Tidak Diacak';?> </td>
			</tr>
		</table>
		</div>
		<?php
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
		<form id="form2" name="form2" method="post" action="" >
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript">
		var base_assets = '<?php echo $cfg->base_assets;?>';
		var numbering = <?php echo json_encode($cfg->numbering);?>;
		var test_id = '<?php echo $test_id;?>';
		var maxScore = '<?php echo $data['standard_score'];?>';
		var baseTestURLLength = <?php echo strlen("media.edu/school/$school_id/test/$test_id/");?>;	
		</script>
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/test-editor.js"></script>
		<div class="question-area">
		<?php
		$numbering = 'upper-alpha';
		?>
		<fieldset>
		<legend>Soal Ujian</legend>
		<div class="question-prop">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="160">Kompetensi Dasar</td>
			<td><input type="text" class="input-text input-text-short" name="basic_competence" id="basic_competence" value="" /></td>
		</tr>
		<tr>
			<td>Tipe Pilihan</td>
			<td><select name="numbering" id="numbering" data-required="true" required="required">
			<option value="upper-alpha">A, B, C, D, ...</option>
			<option value="lower-alpha">a, b, c, d, ...</option>
			<option value="upper-roman">I, II, III, IV, ...</option>
			<option value="lower-roman">i, ii, iii, iv, ...</option>
			<option value="decimal">1, 2, 3, 4, ...</option>
			<option value="decimal-leading-zero">01, 02, 03, 04, ...</option>
			</select></td>
		</tr>
		<tr>
			<td>Pengacakan Pilihan</td>
			<td><label><input type="checkbox" name="random" id="random" value="1"<?php
			if ($data['random']) {
				echo PicoConst::INPUT_CHECKBOX_CHECKED;
			}
			?> /> Diacak</label></td>
		</tr>
		</table>
		</div>
		<div class="question-editor">
		<textarea spellcheck="false" class="htmleditor" name="question" id="question" style="width:100%;"></textarea>
		<input type="hidden" name="test_id" id="test_id" value="<?php echo $test_id;?>" />
		<input type="hidden" name="number_of_option" id="number_of_option" value="<?php echo $data['number_of_option'];?>" />
		</div>
		</fieldset>
		</div>

		<div class="option-area">
		<fieldset>
		<legend>Pilihan Jawaban</legend>

		<?php
		for($i=1;$i<=$data['number_of_option']; $i++)
		{
			?>
			<div class="option-item" data-index="<?php echo $i-1;?>">
			<div class="option-score">Pilihan <span class="option-label"><?php echo $cfg->numbering[$numbering][$i-1];?></span> | Nilai <input type="number" min="0" max="<?php echo $data['standard_score'];?>" class="input-text input-text-short score" name="score_<?php echo $i;?>" id="score_<?php echo $i;?>" autocomplete="off" /> (Nilai Maksimum <?php echo $data['standard_score'];?>)</div>
			<div class="option-editor">
			<textarea spellcheck="false" class="htmleditor" name="option_<?php echo $i;?>" id="option_<?php echo $i;?>" style="width:100%;"></textarea>
			</div>
			</div>
			<?php
		}
		?>
		</fieldset>
		</div>
		<div class="button-area">
		<input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" />
		<input type="button" name="showall" id="showall" class="btn com-button btn-success" value="Tampilkan Semua Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?test_id=<?php echo $test_id;?>'" />
		</div>

		</form>
		<?php
		
	}
	else
	{
		?>
		<div class="warning">Ujian tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>
		<?php
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'edit')
{
	include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$question_id = kh_filter_input(INPUT_GET, "question_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT * FROM `edu_question` WHERE `question_id` = '$question_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) 
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$test_id = $data['test_id'];

		$sql = "SELECT `edu_test`.* ,
			(select count(distinct `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) as `collection`
			FROM `edu_test` WHERE `test_id` = '$test_id' ";

		$stmt3 = $database->executeQuery($sql);

		if ($stmt3->rowCount() > 0) {
			$data3 = $stmt3->fetch(PDO::FETCH_ASSOC);

			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/test.css" />
			<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
			<script type="text/javascript">
			var base_assets = '<?php echo $cfg->base_assets; ?>';
			var numbering = <?php echo json_encode($cfg->numbering); ?>;
			var test_id = '<?php echo $data['test_id']; ?>';
			var baseTestURLLength = <?php echo strlen("media.edu/school/$school_id/test/$test_id/"); ?>;	
			</script>
			<script type="text/javascript" src="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/js/test-editor.js"></script>

			<div class="dialogs">
				<div id="split-dialog">
					<div id="split-dialog-inner">
						<div class="content-editable" contenteditable="true">
						</div>
					</div>
				</div>
			</div>


			<form id="form2" name="form2" method="post" action="">
			<div class="test-info">
			<table width="100%" border="0">
			<tr>
				<td width="160">Nama Ujian</td>
				<td><?php echo $data3['name']; ?> </td>
			</tr>
			<tr>
				<td>Mata Pelajaran</td>
				<td><?php echo $data3['subject']; ?> </td>
			</tr>
			<tr>
				<td>Jumlah Soal</td>
				<td><?php echo $data3['number_of_question']; ?> soal</td>
			</tr>
			<tr>
				<td>Jumlah Pilihan</td>
				<td><?php echo $data3['number_of_option']; ?> pilihan</td>
			</tr>
			<tr>
				<td>Koleksi Soal</td>
				<td><?php echo $data3['collection']; ?> soal <a href="ujian-soal.php?test_id=<?php echo $data3['test_id']; ?>">Lihat</a></td>
			</tr>
			</table>
			</div>
			<div class="question-area">
			<fieldset>
			<legend>Soal Ujian</legend>
			<div class="question-prop">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td width="160">Kompetensi Dasar</td>
				<td><input type="text" class="input-text input-text-short" name="basic_competence" id="basic_competence" value="<?php echo $data['basic_competence']; ?>" /></td>
			</tr>
			<tr>
				<td>Tipe Pilihan</td>
				<td><select name="numbering" id="numbering" data-required="true" required="required">
				<option value="upper-alpha"<?php if ($data['numbering'] == 'upper-alpha')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>A, B, C, D, ...</option>
				<option value="lower-alpha"<?php if ($data['numbering'] == 'lower-alpha')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>a, b, c, d, ...</option>
				<option value="upper-roman"<?php if ($data['numbering'] == 'upper-roman')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>I, II, III, IV, ...</option>
				<option value="lower-roman"<?php if ($data['numbering'] == 'lower-roman')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>i, ii, iii, iv, ...</option>
				<option value="decimal"<?php if ($data['numbering'] == 'decimal')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>1, 2, 3, 4, ...</option>
				<option value="decimal-leading-zero"<?php if ($data['numbering'] == 'decimal-leading-zero')
								echo PicoConst::SELECT_OPTION_SELECTED; ?>>01, 02, 03, 04, ...</option>
				</select></td>
			</tr>
			<tr>
				<td>Pengacakan Pilihan</td>
				<td><label><input type="checkbox" name="random" id="random" value="1"<?php echo $picoEdu->ifMatch($data['random'], 1, PicoConst::INPUT_CHECKBOX_CHECKED); ?> /> Diacak</label></td>
			</tr>
			</table>
			</div>
			<div class="question-editor">
			<textarea spellcheck="false" class="htmleditor" name="question" id="question" style="width:100%;"><?php echo htmlspecialchars(($data['content'])); ?></textarea>
			<input type="hidden" name="question_id" id="question_id" value="<?php echo $question_id; ?>" />
			</div>
			</fieldset>
			</div>

			<div class="option-area">
			<fieldset>
			<legend>Pilihan Jawaban</legend>

			<?php
			$numbering = $data['numbering'];
			$sql2 = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
			$i = 0;
			$stmt2 = $database->executeQuery($sql2);
			if ($stmt2->rowCount() > 0) {
				$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
				?>
				<div class="option-item" data-index="<?php echo $i; ?>">
				<div class="option-score">Pilihan <span class="option-label"><?php echo $cfg->numbering[$numbering][$i]; ?></span> | Nilai <input type="number" min="0" max="<?php echo $data3['standard_score']; ?>" class="input-text input-text-short" name="score_<?php echo $data2['option_id']; ?>" id="score_<?php echo $data2['option_id']; ?>" value="<?php echo $data2['score']; ?>" autocomplete="off" /> (Nilai Maksimum <?php echo $data3['standard_score']; ?>)</div>
				<div class="option-editor">
				<textarea spellcheck="false" class="htmleditor" name="option_<?php echo $data2['option_id']; ?>" id="option_<?php echo $data2['option_id']; ?>" style="width:100%;"><?php echo htmlspecialchars(($data2['content'])); ?></textarea>
				</div>
				</div>
			<?php
			$i++;
			}
			}
			?>
		</fieldset>
		</div>


		<div class="button-area">
		<input type="submit" name="save" id="save" class="btn com-button btn-success" value="Simpan" />
		<input type="button" name="add" id="add" class="btn com-button btn-success" value="Tambah Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=add&test_id=<?php echo $test_id; ?>'" />
		<input type="button" name="showall" id="showall" class="btn com-button btn-success" value="Tampilkan Semua Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?test_id=<?php echo $test_id; ?>'" />
		</div>

		</form>
		<?php
		}
	}
	else
	{
		?>
		<div class="warning">Ujian tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>
		<?php
	}

	include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(isset($_GET['test_id']))
{
	include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test`.* ,
	(select count(distinct `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) as `collection`
	FROM `edu_test` WHERE `test_id` = '$test_id' 
	";

	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
		
		
		<link rel="stylesheet" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/test.css">
		<script type="text/javascript">
		var test_name = '<?php echo addslashes($data['name']); ?>';
		</script>
		<form name="form1" method="post" action="" enctype="multipart/form-data">
		<div class="test-info">
		<table width="100%" border="0">
		<tr>
			<td width="160">Nama Ujian</td>
			<td><?php echo $data['name']; ?> </td>
		</tr>
		<tr>
			<td>Mata Pelajaran</td>
			<td><?php echo $data['subject']; ?> </td>
		</tr>
		<tr>
			<td>Jumlah Soal</td>
			<td><?php echo $data['number_of_question']; ?> soal</td>
		</tr>
		<tr>
			<td>Jumlah Pilihan</td>
			<td><?php echo $data['number_of_option']; ?> pilihan</td>
		</tr>
		<tr>
			<td>Koleksi Soal</td>
			<td><span id="total_collection"><?php echo $data['collection']; ?></span> soal <a href="ujian-soal.php?test_id=<?php echo $data['test_id']; ?>">Lihat</a></td>
		</tr>
		</table>
		</div>
		<?php

		$number_of_option = $data['number_of_option'];
		$caption_option = array();
		for ($i = 0; $i < $number_of_option; $i++) {
			$caption_option[$i] = chr(65 + $i);
		}

		if (@$_GET['option'] == 'analys') {
			$sql = "SELECT * FROM `edu_question` WHERE `test_id` = '$test_id' ORDER BY `sort_order` asc ";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				?>
		<table width="100%" border="1" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
		<thead>
		<tr>
			<td width="20">No</td>
			<td width="30">Lihat</td>
			<td>Potongan Soal</td>
			<td align="center" width="50">Jawaban</td>
			<?php
									for ($i = 0; $i < $number_of_option; $i++) {
										?>
			<td align="center" width="20"><?php echo $caption_option[$i]; ?> </td>
			<?php
									}
									?>
			<td align="right" width="50">Menjawab</td>
			<td align="right" width="40">Benar</td>
			<td align="right" width="40">Salah</td>
			<td align="right" width="50">%Benar</td>
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
			foreach($rows as $data) {
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
				FROM `edu_answer` 
				WHERE `edu_answer`.`answer` like concat('%,',`edu_option`.`option_id`,']%')
				group by `edu_answer`.`test_id`
				limit 0,1
				) as `pilih`
				FROM `edu_option`
				WHERE `edu_option`.`question_id` = '$question_id' ";
				$answer = '';
				$option = array();
				$j = 0;
				$score = 0;
				$menjawab = 0;
				$stmt2 = $database->executeQuery($sql);
				if ($stmt2->rowCount() > 0) {
					$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows2 as $data2) {
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
			<td align="right"><?php echo $no; ?> </td>
			<td><a href="#" class="show-question" data-number="<?php echo $no; ?>" data-question-id="<?php echo $question_id; ?>">Lihat</a></td>
			<td><?php echo substr($content, 0, 70); ?>...</td>
			<td align="center"><?php echo @$caption_option[$answer]; ?> </td>
			<?php
											for ($i = 0; $i < $number_of_option; $i++) {
												?>
			<td align="right"><?php echo @$option[$i]; ?> </td>
			<?php
											}
											?>
			<td align="right"><?php echo $menjawab; ?> </td>
			<td align="right"><?php echo @$option[$answer] + 0; ?> </td>
			<td align="right"><?php echo $menjawab - @$option[$answer]; ?> </td>
			<td align="right"><?php if ($menjawab != 0) {
				echo $picoEdu->numberFormatTrans(100 * (@$option[$answer] + 0) / $menjawab, true);
			} ?> </td>
		</tr>
		<?php
		$total_menjawab += $menjawab;
		$total_benar += @$option[$answer];
		$total_salah += $menjawab - @$option[$answer];
		}
		if ($total_menjawab != 0) {
		$total_persen = 100 * $total_benar / $total_menjawab;
		} else {
		$total_persen = 0;
		}
		?>
		</tbody>

		<tfoot>
		<tr>
			<td colspan="<?php echo $number_of_option + 4; ?>">Total</td>
			<td align="right"><?php echo $total_menjawab; ?> </td>
			<td align="right"><?php echo $total_benar; ?> </td>
			<td align="right"><?php echo $total_salah; ?> </td>
			<td align="right"><?php echo $picoEdu->numberFormatTrans($total_persen, true); ?> </td>
		</tr>
		</tfoot>
		</table>

		<div class="button-area">
			<input type="button" class="btn com-button btn-success" name="export" id="export" value="Ekspor" onclick="window.open('ujian-analisa.php?test_id=<?php echo $test_id; ?>');" />
		</div>

		<div class="dialogs" style="display:none;">
			<div class="dialog-question" title="Soal Ujian">
				<div class="dialog-question-inner"></div>
			</div>
		</div>

		<script type="text/javascript">
		$(document).ready(function(e) {
			$(document).on('click', '.show-question', function(e){
				var question_id = $(this).attr('data-question-id');
				var number = $(this).attr('data-number');
				$('.dialog-question-inner').html('');
				$('.dialog-question').dialog({
					modal:true,
					title:'Soal Ujian',
					width:720,
					height:400
				});
				$.get('ajax-load-question.php', {question_id:question_id, number:number}, function(answer){
					$('.dialog-question-inner').html(answer);
				});
				e.preventDefault();
			});
		});
		</script>

		<?php
	}
	} else {
	?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/test.css" />


<script type="text/javascript" src="<?php echo $cfg->base_assets; ?>lib.assets/script/jquery.ui.touch-punch.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets; ?>lib.assets/script/FileSaver.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets; ?>lib.assets/script/html-docx.js"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
	function setNoiseBackground(el, width, height, opacity) {
		var canvas = document.createElement("canvas");
		var context = canvas.getContext("2d");
		canvas.width = width;
		canvas.height = height;
		for (var i = 0; i < width; i++) {
			for (var j = 0; j < height; j++) {
				var val = Math.floor(Math.random() * 255);
				context.fillStyle = "rgba(" + val + "," + val + "," + val + "," + opacity + ")";
				context.fillRect(i, j, 1, 1);
			}
		}
		el.style.background = "url(" + canvas.toDataURL("image/png") + ")";
	}
	setNoiseBackground(document.getElementsByTagName('body')[0], 50, 50, 0.02);
}, false);

function activateSortOrder()
{
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		forcePlaceholderSize: true,
		revert: true,
		stop: function(event, ui)
		{
			var array_question = [];
			$("#sortable > li").each(function(index, element) {
			array_question.push($(this).attr('data-question-id'));
			});
			$.post('ajax-sort-question.php', 
			{
				array_question:array_question.join(','), 
				sort:'yes'
			}, 
			function(answer){

			});
		}
	});
	$("#sortable").disableSelection();
}
window.onload=function(){
	$('.deletequestion').click(function(){
		return confirm('Apakah Anda akan menghapus soal ini beserta dengan seluruh pilihannya?');
	});
	$(document).on('click', '.kd-ctrl a', function(e){
		var question_id = $(this).attr('data-question-id');
		var obj = $(this).find('span');
		var bc = obj.text().trim();
		obj.replaceWith('<input type="text" value="'+bc+'" data-question-id="'+question_id+'" class="kd-ctrl-input">');
		$(this).find('input').select();
		$(this).closest('.kd-ctrl').attr('data-focus', 'true');
		e.preventDefault();
	});
	$(document).on('blur', '.kd-ctrl-input', function(e){
		save_competence($(this));
	});
	$(document).on('submit', 'form[name="form1"]', function(e){
		e.preventDefault();
		save_competence($(this).find('.kd-ctrl-input'));
	});
	$(document).on('submit', 'form[name="formrandom"]', function(e){
		var data = {};
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			var tr = $(this);
			var bc = 'bc'+tr.find('.take').attr('data-basic-competence');
			var col = tr.find('.take').val();
			data[bc] = col;
        });
		var test_id = $('form[name="formrandom"] input[name="test_id"]').val(); 
		$.post('ajax-question-distribution.php', {'save':'save', test_id:test_id, data:JSON.stringify(data)}, function(answer){
		})
		closeOverlayDialog();
		e.preventDefault();
	});
	$(document).on('change blur keyup', 'form[name="formrandom"] .take', function(e){
		var total = 0;
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			var tr = $(this);
			var col = parseInt(tr.find('.take').val());
			total += col;
        });
		$('form[name="formrandom"] .take_total').val(total);
	});
}
function save_competence(obj)
{
	var question_id = obj.attr('data-question-id');
	var value = obj.val();
	var parent = obj.closest('.kd-ctrl');
	obj.replaceWith('<span data-question-id"'+question_id+'">'+value+'</span>');
	parent.attr('data-focus', 'false');
	$.post('ajax-update-competence.php', {save:'save', question_id:question_id, value:value}, function(answer){
	});
}
function downloadInWord()
{
	var source = $('.test-question')[0].outerHTML;
	var doc = $(source);
	$(doc).find(' > li').each(function(index, element) {
        $(this).append("<br>");
    });
	$(doc).find('.question-edit-ctrl').remove();
	$(doc).find('.option-circle').remove();
	doc = convertImagesToBase64(doc);
	var content = doc[0].outerHTML;
	var converted = htmlDocx.asBlob('<!DOCTYPE html>'+content, {orientation: 'portrait'});
	saveAs(converted, test_name+'.docx');
}
function convertImagesToBase64 (doc) {
	var regularImages = doc.find('img');
	var canvas = document.createElement('canvas');
	var ctx = canvas.getContext('2d');
	[].forEach.call(regularImages, function (obj) {
		var imgElement = obj;
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		canvas.width = imgElement.width;
		canvas.height = imgElement.height;
		ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
		var dataURL = canvas.toDataURL();
		imgElement.setAttribute('src', dataURL);
	});
	canvas.remove();
	return doc;
}
function distribution(test_id)
{
	$.get('ajax-question-distribution.php', {test_id:test_id}, function(answer){
		var obj = $(answer);
		obj.css({'opacity':0});
		obj.css({'width':'300px'});
		$('body').append(obj);
		var heigth = obj.height();
		var html = '<div id="basic-competence-distribution"></div>';
		overlayDialog(html, 300, heigth);
		$('#basic-competence-distribution').empty().append(obj);
		obj.css({'opacity':1});
	});
}

</script>
<style type="text/css">
.dialog-distribution .dialog-kd{
	border-collapse:collapse;
}
.dialog-distribution .dialog-kd thead td{
	padding: 5px 5px;
	background-color: #305A99;
	color: #F6F6F6;
	font-weight: bold;
}
.dialog-distribution .dialog-kd td{
	padding:5px 5px;
	text-align:center;
}
.dialog-distribution .dialog-kd tbody tr:nth-child(odd) td{
	background-color:#FAFAFA;
}
.dialog-distribution .dialog-kd tbody tr:nth-child(even) td{
	background-color:#F5F5F5;
}
.dialog-distribution .dialog-kd td input[type="number"]{
	width:100%;
	box-sizing:border-box;
	text-align:center;
}
.dialog-distribution .button-area{
	text-align:center;
	padding:7px 0 1px 0;
}
.dialog-distribution .button-area input[type="button"],
.dialog-distribution .button-area input[type="submit"],
.dialog-distribution .button-area input[type="reset"]{
	width:85px;
	box-sizing:border-box;
}
.dialog-distribution .dialog-kd tfoot td{
	background-color: #AAF89B;
}

</style>
<?php
$sql = "SELECT * 
FROM `edu_question` WHERE `test_id` = '$test_id' 
ORDER BY `sort_order` asc, `question_id` asc
";

$stmt = $database->executeQuery($sql);

if($stmt->rowCount() > 0)
{
?>
<ol id="sortable" class="test-question">
<?php
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $data) {
?>
<li data-question-id="<?php echo $data['question_id']; ?>">
<div class="kd-ctrl"><a href="#" data-question-id="<?php echo $data['question_id']; ?>"><span><?php echo $data['basic_competence']; ?></span></a></div>
<div class="question-edit-ctrl">
<a class="btn btn-primary" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=edit&question_id=<?php echo $data['question_id']; ?>&ref=<?php echo base64_encode($_SERVER['REQUEST_URI']); ?>">Ubah Soal</a> 
<a class="btn btn-danger deletequestion" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=delete&question_id=<?php echo $data['question_id']; ?>&digest=<?php echo $data['digest']; ?>">Hapus Soal</a> </div>
<div class="question">
<?php
echo $data['content'];
?>
<div class="option">
<ol class="listoption" style="list-style-type:<?php echo $data['numbering']; ?>">
<?php
$question_id = $data['question_id'];
$sql = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
$stmt2 = $database->executeQuery($sql);
if ($stmt2->rowCount() > 0) {
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $data2) {
?>
<li>
<span class="option-circle<?php 
if ($data2['score'])
{
echo ' option-circle-selected';
} ?>"><?php
echo $data2['score'] * 1;
?></span>
<div class="list-option-item">
<div class="option-content">
<?php
echo $data2['content'];
?>
</div>
</div>
</li>
<?php
}
}
?>
</ol>
<?php
								}
?>

</div>
</div>


</li>
<?php
						}
						?>
</ol>
<div class="button-area">
<input type="button" name="urutkan_soal" id="urutkan_soal" class="btn com-button btn-success" value="Urutkan Soal" onclick="activateSortOrder()" />
<input type="button" name="distribusi_soal" id="distribusi_soal" class="btn com-button btn-success" value="Distribusi KD" onclick="distribution(<?php echo $test_id; ?>)" />
<input type="button" name="export" id="export" class="btn com-button btn-success" value="Ekspor Soal" onclick="window.location='ujian-ekspor.php?test_id=<?php echo $test_id; ?>'" />
<input type="button" name="import" id="import" class="btn com-button btn-success" value="Impor Soal" onclick="window.location='ujian-impor.php?test_id=<?php echo $test_id; ?>'" />
<input type="button" name="collection" id="collection" class="btn com-button btn-success" value="Bank Soal" onclick="window.location='ujian-bank-soal.php'" />
<input type="button" name="analys" id="analys" class="btn com-button btn-success" value="Analisa Butir Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=analys&test_id=<?php echo $test_id; ?>'" />
<input type="button" name="add" id="add" class="btn com-button btn-success" value="Tambah Soal (HTML)" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=add&test_id=<?php echo $test_id; ?>'" />
<input type="button" name="add" id="add" class="btn com-button btn-success" value="Tambah Soal (Teks)" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=add&format=text&test_id=<?php echo $test_id; ?>'" />
<input type="button" name="download-word" id="download-word" class="btn com-button btn-success" value="Download Format Word" onclick="downloadInWord()" />
<input type="button" name="show" id="show" class="btn com-button btn-success" value="Tampilkan Informasi Ujian" onclick="window.location='ujian.php?option=detail&test_id=<?php echo $test_id; ?>'" />
<input type="button" name="edit" id="edit" class="btn com-button btn-success" value="Ubah Informasi Ujian" onclick="window.location='ujian.php?option=edit&test_id=<?php echo $test_id; ?>'" />
</div>
<?php
				}
				?>
</form>
<?php
	}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
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
	'<li><a href="ujian-soal.php?option=add&format=text&test_id='+id+'">Tambah Soal Teks</a></li>\r\n'+
	'<li><a href="ujian-soal.php?option=add&test_id='+id+'">Tambah Soal HTML</a></li>\r\n'+
	'<li><a href="ujian-soal.php?test_id='+id+'">Tampilkan Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-impor.php?test_id='+id+'">Impor Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-ekspor.php?test_id='+id+'">Ekspor Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-soal.php?option=analys&test_id='+id+'">Analisa Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-laporan.php?option=detail&test_id='+id+'">Laporan Hasil Ujian</a></li>\r\n'+
	'<li><a href="ujian.php?option=edit&test_id='+id+'">Ubah Informasi Ujian</a></li>\r\n'
	;
	return html;
}
</script>

<ul class="menu-control">
</ul>

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
				'delimiter'=>PicoEdu::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
    </select>
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

if($class_id != '')
{
	$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
	$pagination->array_get[] = 'class_id';
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
(select count(distinct `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`)*1 as `number_of_question`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
ORDER BY `edu_test`.`test_id` desc
";
$sql_test = "SELECT `edu_test`.`test_id`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
";

$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql . $pagination->limit_sql);

$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit > 0)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = $picoEdu->createPaginationHtml($pagination);
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8)
	{
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5)
	{
		display:none;
	}
}
</style>

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></td>
      <td width="25">No</td>
      <td>Nama Ujian</td>
      <td>Pelajaran</td>
      <td>Kelas</td>
      <td>Guru</td>
      <td>Sifat</td>
      <td>Soal</td>
      <td>Aktif</td>
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
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td><a class="show-controls" data-test-id="<?php echo $data['test_id'];?>" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><?php
	   $class = $picoEdu->textClass($array_class, $data['class']); 
	   $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);
	   ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['teacher'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Terbuka':'Tertutup';?></a></td>
      <td><?php if($data['number_of_question']){ ?><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?> soal</a><?php } else { echo '-';} ?> </td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
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
<div class="warning">Data tidak ditemukan. <a href="ujian.php?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>