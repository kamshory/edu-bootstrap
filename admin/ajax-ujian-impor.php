<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
	exit();
}
if(isset($_POST['from']) && isset($_POST['to']))
{
	$school_id = @$school_id . '';
	include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
	include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
	$id = kh_filter_input(INPUT_POST, 'from', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_POST, 'to', FILTER_SANITIZE_NUMBER_UINT);
	$selection = kh_filter_input(INPUT_POST, 'selection', FILTER_SANITIZE_STRING_NEW);
	$selection_index = json_decode($selection);
	
	$time_create = $time_edit = $picoEdu->getLocalDateTime();	
	$member_create = $member_edit = $admin_id;
	
	
	$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$id' and `active` = true ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$basename = $data['file_path'];
		$file_path = dirname(dirname(__FILE__))."/media.edu/question-collection/data/".$basename;
		if(file_exists($file_path))
		{

			$sql = "SELECT `edu_test`.*, 
			(select `edu_question`.`sort_order` FROM `edu_question` 
				WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` desc limit 0,1) as `sort_order`
			FROM `edu_test`
			WHERE `edu_test`.`test_id` = '$test_id'
			";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
				$data = $stmt->fetch(PDO::FETCH_ASSOC);		
				$random = ((int) $data['random']);
				$sort_order = ((int) $data['sort_order']);
				$score_standar = $data['standard_score'];

				
				$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
				$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
				$dirBase = dirname(dirname(__FILE__));
				$permission = 0755;
				$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);
				
				$base_src = "media.edu/school/$school_id/test/$test_id";
				
				$temp_dir = $test_dir;
				
				$basename = md5($_SERVER['REMOTE_ADDR'].'.'.time().'.'.mt_rand(111111, 999999));		
	
				$xml_data = file_get_contents($file_path);
				$test_data = simplexml_load_string($xml_data);
				$number_of_question = count(@$test_data->item);
	
				if($number_of_question)
				{
					$idx = 0;
					foreach($test_data->item as $index_question => $question)
					{
						// petanyaan
						if($selection_index[$idx] == 1 || $selection == ""  || $selection == "[]")
						{
							$text_pertanyaan = trim(@$question->question->text);
							$random = ((int) @$question->question->random);
							$numbering = addslashes(trim(@$question->question->numbering));
							$competence = addslashes(trim(@$question->question->competence));
							$sort_order++;
							if(count(@$question->question->file))
							{
								foreach($question->question->file as $index_file_question => $file)
								{
									$name_file = $picoEdu->trimWhitespace(@$file->name);
									$type_file = $picoEdu->trimWhitespace(@$file->type);
									$encoding_file = $picoEdu->trimWhitespace(@$file->encoding);
									$data_file = $picoEdu->trimWhitespace(@$file->data);
									if(stripos($encoding_file, "base64") !== false)
									{
										$data_file = base64_decode($data_file);
									}
									// create file
									$fileSync->createFileWithContent($test_dir."/".$name_file, $data_file, true);
								}
							}
							$pertanyaan = htmlspecialchars_decode(htmlentities(replaceBase($text_pertanyaan, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
							$digest = md5($pertanyaan);
							$pertanyaan = addslashes($pertanyaan);

							$question_id = $database->generateNewId();
							
							$sql1 = "INSERT INTO `edu_question` 
							(`question_id`, `content`, `test_id`, `multiple_choice`, `sort_order`, `random`, `numbering`, `digest`, `basic_competence`,
							`time_create`, `member_create`, `time_edit`, `member_edit`) values
							('$question_id', '$pertanyaan', '$test_id', '1', '$sort_order', '$random', '$numbering', '$digest', '$competence',
							'$time_create', '$member_create', '$time_edit', '$member_edit')
							";
							$stmt = $database->executeInsert($sql1, true);;
							
							if($stmt->rowCount() > 0 && count(@$question->answer->option) > 0)
							{
								foreach($question->answer->option as $index_option => $option)
								{
									$text_option = trim(@$option->text);
									$score = ((int) (@$option->value));
									if(count(@$option->file) > 0)
									{
										foreach($option->file as $index_file_question => $file)
										{
											$name_file = $picoEdu->trimWhitespace(@$file->name);
											$type_file = $picoEdu->trimWhitespace(@$file->type);
											$encoding_file = $picoEdu->trimWhitespace(@$file->encoding);
											$data_file = $picoEdu->trimWhitespace(@$file->data);
											
											if(stripos($encoding_file, "base64") !== false)
											{
												$data_file = base64_decode($data_file);
											}
											// create file
											$fileSync->createFileWithContent($test_dir."/".$name_file, $data_file, true);
										}
									}
									$option = htmlspecialchars_decode(htmlentities(replaceBase($text_option, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
									$digest = md5($option);
									$option = addslashes($option);
									
									$order2 = $index_option + 1;

									$option_id = $database->generateNewId();
									
									$sql2 = "INSERT INTO `edu_option` 
									(`option_id`, `question_id`, `content`, `sort_order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
									('$option_id', '$question_id', '$option', '$order2', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit')
									";
									
									$database->executeInsert($sql2, true);
								}
							}
						}
						$idx++;
					}
				}
			}
		}
	}
}



$sql = "SELECT `edu_test`.*,
(select `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
(select count(distinct `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) as `question`
FROM `edu_test`
WHERE `edu_test`.`school_id` = '$school_id' 
ORDER BY `edu_test`.`test_id` desc
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
  <thead>
	<tr>
	  <td>Ujian</td>
	  <td>Kelas</td>
	  <td>Pelajaran</td>
	  <td>Soal</td>
	 </tr>
	</thead>
	<tbody>
	<?php
	$no=0;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
	<tr>
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['class'];?></a></td>
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
	  <td><a target="_blank" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['question'];?></a></td>
	 </tr>
	<?php
	}
	?>
	</tbody>
  </table>
<?php
}

?>