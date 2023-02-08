<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(!isset($school_id) || empty($school_id))
{
	exit();
}
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.*, 
(SELECT `edu_question`.`sort_order` FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` DESC LIMIT 0, 1) AS `sort_order`
FROM `edu_test`
WHERE `edu_test`.`test_id` = '$test_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	
	$test_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirecory($dir2prepared, $dirBase, $permission, true);

	if($dh = opendir($test_dir))
	{
		while (($file = readdir($dh)) !== false){
			$arr = explode(".", $file);
			$filename = "media.edu/school/".$school_id."/test/".$test_id."/".$file;
			if(strtolower(end($arr)) == 'jpg' || strtolower(end($arr)) == 'jpeg' || strtolower(end($arr)) == 'png' || strtolower(end($arr)) == 'gif')
			{
				?>
                <div class="img-li"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><img alt="" src="<?php echo $filename;?>" /></a></div>
                <?php
			}
		}
		closedir($dh);
	}

}

?>