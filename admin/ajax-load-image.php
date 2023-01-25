<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
exit();
}
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.*, 
(select `edu_question`.`order` from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
from `edu_test`
where `edu_test`.`test_id` = '$test_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$test_dir = dirname(dirname(__FILE__))."/media.edu/school";
	if(!file_exists($test_dir))
	{
		@mkdir($test_dir);
	}
	$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id";
	if(!file_exists($test_dir))
	{
		@mkdir($test_dir);
	}
	$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test";
	if(!file_exists($test_dir))
	{
		@mkdir($test_dir);
	}
	$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
	if(!file_exists($test_dir))
	{
		@mkdir($test_dir);
	}

	if($dh = opendir($test_dir))
	{
		while (($file = readdir($dh)) !== false){
			$arr = explode(".", $file);
			$filename = "media.edu/school/".$school_id."/test/".$test_id."/".$file;
			if(strtolower(end($arr)) == 'jpg' 
			|| strtolower(end($arr)) == 'jpeg' 
			|| strtolower(end($arr)) == 'png' 
			|| strtolower(end($arr)) == 'gif'
			|| strtolower(end($arr)) == 'svg'
			)
			{
				?>
                <div class="img-li"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><img src="<?php echo $filename;?>" /></a></div>
                <?php
			}
		}
		closedir($dh);
	}

}

?>