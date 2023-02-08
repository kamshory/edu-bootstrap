<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
if(!empty($school_id))
{
	$basename = "ujian-soal.php";
	if(isset($_POST['question_text']) && isset($_POST['test_id']) && @$_POST['option']=='add')
	{
		$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
		$edit_mode = kh_filter_input(INPUT_POST, "edit_mode", FILTER_SANITIZE_NUMBER_UINT);
		$sql = "SELECT * FROM `edu_test` WHERE `test_id` = '$test_id' AND `school_id` = '$school_id' AND `teacher_id` = '$teacher_id' ";
		$stmt0 = $database->executeQuery($sql);
		if($stmt0->rowCount() > 0)
		{
			// Format Plain
			$picoEdu->sortQuestion($test_id);
			$sql = "SELECT `edu_test`.*, 
			(SELECT `edu_question`.`sort_order` FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` DESC LIMIT 0, 1) AS `sort_order`
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
				$raw_txt_data = kh_filter_input(INPUT_POST, "question_text", FILTER_DEFAULT);
				$clear_data = parseRawQuestion($raw_txt_data);
				
				$base_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
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
						$content = fixing_table(nl2br(UTF8ToEntities(filter_html(addImages(@$object['question'], $base_dir, $base_src)))));
						$content = addslashes($picoEdu->brToNewLineEncoded($content));
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
						if($stmt1->rowCount())
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
									$content_option = fixing_table(nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $base_dir, $base_src)))));
									$content_option = addslashes($picoEdu->brToNewLineEncoded($content_option));
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
									$stmt2 = $database->executeInsert($sql2, true);
									if($stmt2->rowCount() == 0)
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
				$sql = "SELECT * FROM `edu_question` WHERE `test_id` = '$test_id' ";
				$stmt = $database->executeQuery($sql);
				if($stmt->rowCount() > 0)
				{
				?>
				<ol class="question-ol">
				<?php
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($rows as $data)
				{
				$question_id = $data['question_id'];
				?>
				<li><span><?php echo $data['content'];?><?php if($edit_mode){?><span class="edit-question-ctrl"><a href="<?php echo $basename;?>?option=edit&question_id=<?php echo $question_id;?>" target="_blank"><span></span></a></span><?php }?></span>
					<ol class="option-ol" style="list-style-type:<?php echo $data['numbering'];?>">
						<?php
						$sql2 = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
						$stmt2 = $database->executeQuery($sql2);
						if($stmt2->rowCount() > 0)
						{
							$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							foreach($rows2 as $data2)
							{
								?><li class="option-li"><?php if($data2['score']>0){?><span class="score"></span><?php } ?><?php echo $data2['content'];?></li>
								<?php
							}
						}
						?>
					</ol>
				</li>
				<?php
				}
				?>
				</ol>
				<?php
				}				
			}
		}
	}
}
?>