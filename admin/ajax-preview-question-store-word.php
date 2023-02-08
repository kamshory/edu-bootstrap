<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
require_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
if(!isset($school_id) || empty($school_id))
{
	exit();
}
$id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
$key = kh_filter_input(INPUT_GET, "key", FILTER_SANITIZE_NUMBER_UINT);
$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$id' AND `active` = true ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$basename = $data['file_path'];
	$file_path = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data/".$basename;
	if(file_exists($file_path))
	{
		$text_all = loadXmlData_word($file_path, $key);

		$expires = 0;
		header("Pragma: public");
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		header("Cache-Control: max-age=$expires");
		header("User-Cache-Control: max-age=$expires");

		?>
        <div class="test-header">
        	<h3><?php echo $data['name'];?></h3>
        	<h4><?php 
			echo $picoEdu->getGradeName($data['grade_id']);
			?></h4>
        </div>
        <div class="question-text-area" data-collection-id="<?php echo $id;?>">
        <?php
		echo $text_all;
		?>
        </div>
        <?php
	}
}
?>