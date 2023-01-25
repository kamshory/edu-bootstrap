<?php

function resizePng($im, $dst_width, $dst_height) {
    $width = imagesx($im);
    $height = imagesy($im);

    $newImg = imagecreatetruecolor($dst_width, $dst_height);

    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
    $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
    imagefilledrectangle($newImg, 0, 0, $width, $height, $transparent);
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $dst_width, $dst_height, $width, $height);
    return $newImg;
}
$dir2 = dirname(__FILE__);
$scale = 1;
if(file_exists($dir2))
{
	if($handle = opendir($dir2))
	{
		$i=0;
		while (false !== ($ufile = readdir($handle))) 
		{ 
			$fn = "$dir2/$ufile";
			if($ufile == "." || $ufile == ".." ) 
			{
			continue;
			}
			$filetype = filetype($fn);
			unset($obj);
			if($filetype=="file" && stripos($fn, ".png") !== false && filesize($fn) > 5000)
			{
				echo $fn."<br>";
				$img = imagecreatefrompng($fn);
				$dst_width = round(imagesx($img)*$scale);
				$dst_height = round(imagesy($img)*$scale);
				$newimage = resizePng($img, $dst_width, $dst_height);
				imagepng($newimage, $fn);
				//imagepng($img, $fn);
			}
		}
	}
}


?>