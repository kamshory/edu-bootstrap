<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(!empty($school_id))
{
    $question_id = kh_filter_input(INPUT_GET, "question_id", FILTER_SANITIZE_STRING_NEW);
    $number = kh_filter_input(INPUT_GET, "number", FILTER_SANITIZE_NUMBER_UINT);
    $sql = "SELECT * FROM `edu_question` WHERE `question_id` = '$question_id' ";
    $stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);$question_id = $data['question_id'];
?>
<ol class="question-test">
	<li class="listoption" value="<?php echo $number;?>">
    <div class="question">
    	<?php echo $data['content'];?>
        <ul style="list-style-type:<?php echo $data['numbering'];?>">
        	<?php
			$sql2 = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ";
			$stmt2 = $database->executeQuery($sql2);
            if($stmt2->rowCount() > 0)
            {
                $rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
                foreach($rows2 as $data2)
				{
					?>
                    <li>
                    <span class="option-circle<?php echo $picoEdu->ifMatch($data2['score'] > 0, ' option-circle-selected', '');?>"><?php
                        echo $data2['score']*1;
                        ?></span>
                    <div class="item-pilihan">
                        <div class="content-pilihan">
                    <?php echo $data2['content'];?>
                    </div>
                    </div>
                    </li>
                    <?php
				}
			}
			?>
        </ul>
    </div>    
    </li>
</ol>
<?php
}
}
?>
