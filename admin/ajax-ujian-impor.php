<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
	exit();
}
if(isset($_POST['from']) && isset($_POST['to']))
{
	include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
	include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
	$id = kh_filter_input(INPUT_POST, 'from', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_POST, 'to', FILTER_SANITIZE_NUMBER_UINT);
	$selection = kh_filter_input(INPUT_POST, 'selection', FILTER_SANITIZE_STRING_NEW);
	$selection_index = json_decode($selection);
	
	$time_create = $time_edit = $picoEdu->getLocalDateTime();	
	$member_create = $member_edit = $admin_id;
	
	
	$sql = "select * from `edu_test_collection` where `test_collection_id` = '$id' and `active` = '1' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$basename = $data['file_path'];
		$file_path = dirname(dirname(__FILE__))."/media.edu/question-collection/data/".$basename;
		if(file_exists($file_path))
		{

			$sql = "SELECT `edu_test`.*, 
			(select `edu_question`.`order` from `edu_question` 
				where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
			from `edu_test`
			where `edu_test`.`test_id` = '$test_id'
			";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0)
			{
				$data = $stmt->fetch(PDO::FETCH_ASSOC);		
				$random = $data['random'];
				$order = $data['order'];
				$score_standar = $data['standard_score'];

				$test_dir = dirname(dirname(__FILE__))."/media.edu/school";
				if(!file_exists($test_dir))
				{
					mkdir($test_dir);
				}
				$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id";
				if(!file_exists($test_dir))
				{
					mkdir($test_dir);
				}
				$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test";
				if(!file_exists($test_dir))
				{
					mkdir($test_dir);
				}
				$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
				if(!file_exists($test_dir))
				{
					mkdir($test_dir);
				}
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
						if(($selection_index[$idx] == 1 || $selection == ""  || $selection == "[]"))
						{
							$text_pertanyaan = trim(@$question->question->text);
							$random = trim(@$question->question->random)*1;
							$numbering = addslashes(trim(@$question->question->numbering));
							$competence = addslashes(trim(@$question->question->competence));
							$order++;
							if(count(@$question->question->file))
							{
								foreach($question->question->file as $index_file_question => $file)
								{
									$name_file = trim(@$file->name, " \r\n\t ");
									$type_file = trim(@$file->type, " \r\n\t ");
									$encoding_file = trim(@$file->encoding, " \r\n\t ");
									$data_file = trim(@$file->data, " \r\n\t ");
									if(stripos($encoding_file, "base64") !== false)
									{
										$data_file = base64_decode($data_file);
									}
									// create file
									file_put_contents($test_dir."/".$name_file, $data_file);
									// check image
									/*
									Commented
									list($width, $height, $type, $attr) = getimagesize($test_dir."/".$name_file);
									if(($width * $height) == 0)
									{
										@unlink($test_dir."/".$name_file);
									}
									*/
								}
							}
							$pertanyaan = htmlspecialchars_decode(htmlentities(replaceBase($text_pertanyaan, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
							$digest = md5($pertanyaan);
							$pertanyaan = addslashes($pertanyaan);

							$question_id = $database->generateNewId();
							
							$sql1 = "INSERT INTO `edu_question` 
							(`question_id`, `content`, `test_id`, `multiple_choice`, `order`, `random`, `numbering`, `digest`, `basic_competence`,
							`time_create`, `member_create`, `time_edit`, `member_edit`) values
							('$question_id', '$pertanyaan', '$test_id', '1', '$order', '$random', '$numbering', '$digest', '$competence',
							'$time_create', '$member_create', '$time_edit', '$member_edit'); 
							";
							$stmt = $database->executeQuery($sql1);
							
							if($stmt->rowCount() > 0 && count(@$question->answer->option) > 0)
							{
								foreach($question->answer->option as $index_option => $option)
								{
									$text_option = trim(@$option->text);
									$score = trim(@$option->value)*1;
									if(count(@$option->file))
									{
										foreach($option->file as $index_file_question => $file)
										{
											$name_file = trim(@$file->name, " \r\n\t ");
											$type_file = trim(@$file->type, " \r\n\t ");
											$encoding_file = trim(@$file->encoding, " \r\n\t ");
											$data_file = trim(@$file->data, " \r\n\t ");
											
											if(stripos($encoding_file, "base64") !== false)
											{
												$data_file = base64_decode($data_file);
											}
											// create file
											file_put_contents($test_dir."/".$name_file, $data_file);
											// check image
											/*
											Commented
											list($width, $height, $type, $attr) = getimagesize($test_dir."/".$name_file);
											if(($width * $height) == 0)
											{
												@unlink($test_dir."/".$name_file);
											}
											*/
										}
									}
									$option = htmlspecialchars_decode(htmlentities(replaceBase($text_option, $base_src."/"), ENT_QUOTES, "UTF-8"), ENT_QUOTES);
									$digest = md5($option);
									$option = addslashes($option);
									
									$order2 = $index_option + 1;

									$option_id = $database->generateNewId();
									
									$sql2 = "INSERT INTO `edu_option` 
									(`option_id`, `question_id`, `content`, `order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
									('$option_id', '$question_id', '$option', '$order2', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit'); 
									";
									
									$database->executeInsert($sql2);
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
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `question`
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' 
order by `edu_test`.`test_id` desc
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
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
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['class'];?></a></td>
	  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
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