<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(!empty($school_id))
{
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.*, 
(SELECT `edu_question`.`sort_order` FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id` ORDER BY `sort_order` DESC LIMIT 0, 1) AS `sort_order`
FROM `edu_test`
WHERE `edu_test`.`test_id` = '$test_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	error_reporting(0);
	
	$test_dir = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/school/$school_id/test/$test_id";
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirectory($test_dir, $dirBase, $permission, true);
	
	if(isset($_FILES["audios"]) && is_array($_FILES["audios"]["error"]))
	{
		foreach($_FILES["audios"]["error"] as $key => $error)
		{
			if($error == 0) 
			{					$name = $_FILES["audios"]["name"][$key];
				$name = trim(preg_replace("/\s+/","-",$name));
				$name = trim(str_replace(array("(", ")", "{","}", "[", "]", "'", '"'),"-",$name));
				if(isset($_FILES['audios']['tmp_name']))
				{
					if(is_uploaded_file($_FILES['audios']['tmp_name'][$key])){
						copy($_FILES['audios']['tmp_name'][$key], $test_dir."/".$name);
					} 
					move_uploaded_file($_FILES["audios"]["tmp_name"][$key], $test_dir."/".$name);
					$fileSync->createFile($test_dir."/".$name, true);
				}
			}
		}
	}
	if($dh = opendir($test_dir))
	{
		$files = array();
		while (($file = readdir($dh)) !== false){
			$arr = explode(".", $file);
			$filename = "media.edu/school/".$school_id."/test/".$test_id."/".$file;
			if(strtolower(end($arr)) == 'mp3' || strtolower(end($arr)) == 'mp4' || strtolower(end($arr)) == 'ogg' || strtolower(end($arr)) == 'wav' || strtolower(end($arr)) == 'webm')
			{
				$data = array(
					'filename'=>$filename,
					'basename'=>basename($filename),
					'size'=>filesize($test_dir."/".$file),
					'modified'=>filemtime($test_dir."/".$file)
				);
				$files[] = $data;
			}
		}
		closedir($dh);

		if(count($files))
		{
		if(@$_GET['option'] == 'compress')
		{
		?>
        <table width="100%" cellpadding="0" cellspacing="0" class="table table-striped table-sm">
        <thead>
        <tr>
            <td width="20">No</td>
            <td>Nama File</td>
            <td align="right">Ukuran</td>
            <td>Perubahan</td>
            <td width="70">Kompres</td>
        </tr>
        </thead>
        <tbody>
        <?php
		$no = 0;
		foreach($files as $key=>$data)
		{
			$no++;
			$filename = $data['filename'];
		?>
            <tr>
            <td align="right"><?php echo $no;?> </td>
            <td><span class="select-audio"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><?php echo $data['basename'];?></a></span></td>
            <td align="right"><span title="Ukuran <?php echo $data['size'];?>"><?php echo ($data['size']>=1048576)?(round($data['size']/1048576,2)."M"):(round($data['size']/1024)."k");?></span></td>
            <td><?php echo date("d/m/Y H:i", $data['modified']);?> </td>
            <td><a href="javascript:;" class="compress-audio-file" title="Kompres <?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>">Kompres</a></td>
            </tr>
		<?php
		}
		?>
        </tbody>
        </table>
        <?php
		}
		else
		{
		foreach($files as $key=>$data)
		{
				?>
                <div class="audio-li select-audio"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><span class="audio-80"><span><?php echo basename($filename);?></span></span></a></div>
                <?php
		}
		}
		}


	}
}
}
?>