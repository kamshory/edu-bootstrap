<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
if(@$school_id == 0)
{
exit();
}
$id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
$sql = "select * from `edu_test_collection` where `test_collection_id` = '$id' and `active` = '1' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$basename = $data['file_path'];
	$file_path = dirname(dirname(__FILE__))."/media.edu/question-collection/data/".$basename;
	if(file_exists($file_path))
	{
		$text_all = loadXmlData($file_path);

		?>
        <div class="title">
        	<h3><?php echo $data['name'];?></h3>
        	<h4><?php 
			echo $picoEdu->getGradeName($data['grade_id']);
			?></h4>
        </div>
        <div class="question-text-area">
        <?php
		echo $text_all;
		?>
        </div>
        <?php
	}
}
?>