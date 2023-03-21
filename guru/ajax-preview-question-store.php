<?php
require_once dirname(__DIR__)."/lib.inc/auth-guru.php";
require_once dirname(__DIR__)."/lib.inc/lib.test.php";
if(!isset($school_id) || empty($school_id))
{
	exit();
}

$id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$id' AND `active` = true ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$picoTest = new \Pico\PicoTestCreator();
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	$basename = $data['file_path'];
	$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
	if(file_exists($file_path))
	{
		$text_all = $picoTest->loadXmlData($file_path);

		\Pico\BrowserCache::setMaxAge(3600);

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