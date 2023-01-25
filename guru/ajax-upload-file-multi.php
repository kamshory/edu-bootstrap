<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(@$school_id != 0)
{
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.*, 
(select `edu_question`.`order` from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
from `edu_test`
where `edu_test`.`test_id` = '$test_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	error_reporting(0);
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
	if(@$_GET['option'] == 'transfer')
	{
		$url = kh_filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
		$parsed = parse_url($url);
		$basename = basename(@$parsed['path']);
		$data = file_get_contents($url);
		if(strlen($data))
		{
			file_put_contents($test_dir."/".$basename, $data);
		}
	}
	else if(@$_GET['option'] == 'uploadbase64image')
	{
		$data = kh_filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING_NEW);
		$ext = kh_filter_input(INPUT_POST, 'ext', FILTER_SANITIZE_STRING_NEW);
		if(stripos($ext, 'svg'))
		{
			$ext = 'svg';
		}
		else
		{
			$ext = 'png';
		}
		$arr = explode(",", $data);
		if(count($arr) > 1)
		{
			$data = base64_decode($arr[1]);
			$basename = md5($data).".".$ext;
			if(strlen($data))
			{
				file_put_contents($test_dir."/".$basename, $data);
				echo $basename;
			}
		}
		exit();
	}
	else if(@$_GET['option'] == 'copyexternal')
	{
		$url = kh_filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
		$parsed = parse_url($url);
		$basename = basename(@$parsed['path']);
		$basename = str_replace(" ", "-", $basename);
		$data = file_get_contents($url);
		
		if(!file_exists($test_dir."/".$basename))
		{
			if(strlen($data))
			{
				file_put_contents($test_dir."/".$basename, $data);
				echo $basename;
			}
		}
		else
		{
			echo $basename;
		}
		exit();
	}
	else if(isset($_FILES["images"]) && is_array($_FILES["images"]["error"]))
	{
		foreach($_FILES["images"]["error"] as $key => $error){
			if($error == 0) 
			{
				$name = $_FILES["images"]["name"][$key];
				$name = trim(preg_replace("/\s+/","-",$name));
				// if exist before, file will not be deleted
				if(isset($_FILES['images']['tmp_name']))
				{
					if(is_uploaded_file($_FILES['images']['tmp_name'][$key])){
						copy($_FILES['images']['tmp_name'][$key], $test_dir."/".$name);
					} 
					move_uploaded_file($_FILES["images"]["tmp_name"][$key], $test_dir."/".$name);
				}
			}
		
		}
	}
	if($dh = opendir($test_dir))
	{
		while (($file = readdir($dh)) !== false){
			$arr = explode(".", $file);
			$filename = "media.edu/school/".$school_id."/test/".$test_id."/".$file;
			if(strtolower(end($arr)) == 'jpg' || strtolower(end($arr)) == 'jpeg' || strtolower(end($arr)) == 'png' || strtolower(end($arr)) == 'gif')
			{
				?>
				<div class="img-li"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><img src="<?php echo $filename;?>" /></a></div>
				<?php
			}
		}
		closedir($dh);
	}
}
}
