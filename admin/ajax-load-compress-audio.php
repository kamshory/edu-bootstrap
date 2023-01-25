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
		?>
        <table width="100%" cellpadding="0" cellspacing="0" class="row-table">
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
            <td align="right"><?php echo $no;?></td>
            <td><span class="select-audio"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><?php echo $data['basename'];?></a></span></td>
            <td align="right"><span title="Ukuran <?php echo $data['size'];?>"><?php echo ($data['size']>=1048576)?(round($data['size']/1048576,2)."M"):(round($data['size']/1024)."k");?></span></td>
            <td><?php echo date("d/m/Y H:i", $data['modified']);?></td>
            <td><a href="javascript:;" class="compress-audio-file" title="Kompres <?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>">Kompres</a></td>
            </tr>
		<?php
		}
		?>
        </tbody>
        </table>
        <?php
		}
	}

}

?>