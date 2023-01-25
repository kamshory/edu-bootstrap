<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(@$school_id != 0)
{
$basename = "ujian-soal.php";
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "select * from `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$edit_mode = kh_filter_input(INPUT_GET, 'edit_mode', FILTER_SANITIZE_NUMBER_UINT);
$sql = "select * from `edu_question` where `test_id` = '$test_id' ";
$stmt = $database->executeQuery($sql);
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
        $sql2 = "select * from `edu_option` where `question_id` = '$question_id' ";
        $stmt2 = $database->executeQuery($sql2);
        if($stmt2->rowCount() > 0)
        {
            $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows2 as $data2)
            {
                ?><li class="option-li"><?php if($data2['score']>0){?><span class="score"></span><?php } ?><span><?php echo $data2['content'];?></span></li>
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
?>