<?php
require_once dirname(__DIR__)."/lib.inc/auth-guru.php";
if(!empty($school_id))
{
$basename = "ujian-soal.php";
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT * FROM `edu_test` WHERE `test_id` = '$test_id' AND `school_id` = '$school_id' AND `teacher_id` = '$teacher_id'";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$edit_mode = kh_filter_input(INPUT_GET, "edit_mode", FILTER_SANITIZE_NUMBER_UINT);
$sql = "SELECT * FROM `edu_question` WHERE `test_id` = '$test_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
<ol class="question-ol">
<?php
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
foreach($rows as $data)
{
$question_id = $data['question_id'];
?>
<li><span><?php echo $data['content'];?><?php echo $picoEdu->trueFalse($edit_mode, '<span class="edit-question-ctrl"><a href="'.$basename.'?option=edit&question_id='.$question_id.'" target="_blank"><span></span></a></span>', '');?></span>
    <ol class="option-ol" style="list-style-type:<?php echo $data['numbering'];?>">
        <?php
        $sql2 = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
        $stmt2 = $database->executeQuery($sql2);
        if($stmt2->rowCount() > 0)
        {
            $rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            foreach($rows2 as $data2)
            {
                ?><li class="option-li"><?php echo $picoEdu->trueFalse($data2['score'] > 0, '<span class="score"></span>', '');?><span><?php echo $data2['content'];?></span></li>
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
?>