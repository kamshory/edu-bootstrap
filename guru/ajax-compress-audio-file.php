<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(@$school_id > 0 && isset($_POST['filename']) && isset($_POST['test_id']))
{
	$absolute_dir = dirname(dirname(__FILE__));
	$filename = trim(@$_POST['filename']);
	$test_id = trim(@$_POST['test_id']);
	$no = abs($_POST['no'])*1;
	$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
	$path = $test_dir."/".trim($filename, "/");
	if(file_exists($path))
	{
		$arr = explode(".", $path);
		if(strtolower(end($arr)) == 'mp3' || strtolower(end($arr)) == 'mp4' || strtolower(end($arr)) == 'ogg' || strtolower(end($arr)) == 'wav' || strtolower(end($arr)) == 'webm')
		{
			$rand = mt_rand(111111, 999999);
			shell_exec("lame -b 32 $path $path-$rand");
			if(file_exists($path."-".$rand))
			{
				@unlink($path);
				@rename($path."-".$rand, $path);
			}
			$data = array(
				'filename'=>$filename,
				'basename'=>basename($filename),
				'size'=>filesize($path),
				'modified'=>filemtime($path)
			);
			?>
			<tr>
			<td align="right"><?php echo $no;?></td>
			<td><span class="select-audio"><a href="<?php echo $filename;?>" title="<?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>"><?php echo $data['basename'];?></a></span></td>
			<td align="right"><span title="Ukuran <?php echo $data['size'];?>"><?php echo ($data['size']>=1048576)?(round($data['size']/1048576,2)."M"):(round($data['size']/1024)."k");?></span></td>
			<td><?php echo date("d/m/Y H:i", $data['modified']);?></td>
			<td><a href="javascript:;" class="compress-audio-file" title="Kompres <?php echo basename($filename);?>" data-name="<?php echo basename($filename);?>">Dikompres</a></td>
			</tr>
			<?php

		}
	}
}
?>