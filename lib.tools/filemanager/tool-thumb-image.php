<?php
include_once __DIR__ . "/functions.php";
include_once __DIR__ . "/auth.php";
include __DIR__ . "/conf.php"; //NOSONAR
if ($fmanConfig->authentification_needed && !$userlogin) {
	exit();
}
if (!$fmanConfig->thumbnail_quality) {
	$fmanConfig->thumbnail_quality = 80;
}

$filepath = path_decode(kh_filter_input(INPUT_GET, "filepath"), $fmanConfig->rootdir);

function gettumbpict($originalfile, $maxw, $maxh)
{
	global $fmanConfig;
	$image = new StdClass();
	$filesize = filesize($originalfile);
	if ($filesize > $fmanConfig->thumbnail_max_size) {
		return false;
	}
	$imageinfo = @getimagesize($originalfile);
	if (empty($imageinfo)) {
		return 0;
	}
	$image->width = $imageinfo[0];
	$image->height = $imageinfo[1];
	$image->type = $imageinfo[2];
	$newwidth = $image->width;
	$newheight = $image->height;
	if (!$newwidth || !$newheight) {
		return false;
	}
	if ($maxw > 0 && $image->width > $maxw) {
		$newwidth = $maxw;
		$newheight = $image->height * $maxw / $image->width;
	}
	if ($maxh > 0 && $newheight > $maxh) {
		$tmp = $newheight;
		$newheight = $maxh;
		$newwidth = $newwidth * $maxh / $tmp;
	}
	switch ($image->type) {
		case IMAGETYPE_GIF:
			if (function_exists('ImageCreateFromGIF')) {
				$im = @ImageCreateFromGIF($originalfile);
			} else {
				return false;
			}
			break;
		case IMAGETYPE_JPEG:
			if (function_exists('ImageCreateFromJPEG')) {
				$im = @ImageCreateFromJPEG($originalfile);
			} else {
				return false;
			}
			break;
		case IMAGETYPE_PNG:
			if (function_exists('ImageCreateFromPNG')) {
				$im = @ImageCreateFromPNG($originalfile);
			} else {
				return false;
			}
			break;
		default:
			return false;
	}
	$im1 = imagecreatetruecolor($newwidth, $newheight);
	$white = imagecolorallocate($im1, 255, 255, 255);
	if (!$im) {
		return false;
	}
	imagefilledrectangle($im1, 0, 0, $newwidth, $newheight, $white);
	imagecopyresized($im1, $im, 0, 0, 0, 0, $newwidth, $newheight, $image->width, $image->height);
	return $im1;
}

if (file_exists($filepath)) {
	$filetype = filetype($filepath);
	if ($filetype == "file") {
		$expires = $fmanConfig->cache_max_age_file;
		header("Pragma: public");
		header("Cache-Control: maxage=" . $expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
		if (!function_exists('imagecreatefrompng')) {
			readfile(__DIR__ . "/style/images/common/image.png");
			header('Content-Type: image/png');
		} else {
			$image = gettumbpict($filepath, 96, 96);
			if ($image) {
				header('Content-Type: image/jpeg');
				@imagejpeg($image, null, $fmanConfig->thumbnail_quality);
			} else {
				$image = imagecreatefrompng(__DIR__ . "/style/images/binfile.png");
				header('Content-Type: image/png');
				@imagepng($image);
			}
		}
	}
	if ($filetype == "dir") {
		$expires = $fmanConfig->cache_max_age_dir;
		header("Pragma: public");
		header("Cache-Control: maxage=" . $expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
		if (!function_exists('imagecreatefrompng')) {
			readfile(__DIR__ . "/style/images/folder.png");
			header('Content-Type: image/png');
		} else {
			$image = imagecreatefrompng(__DIR__ . "/style/images/folder.png");
			if ($handle = opendir($filepath)) {
				$i = 0;
				while (false !== ($ufile = readdir($handle))) {
					$fn = "$filepath/$ufile";
					if ($ufile == "." || $ufile == "..") {
						continue;
					}
					$filetype = filetype($fn);
					if ($filetype == "file") {
						$img2[$i] = gettumbpict($fn, 32, 32);
						if ($img2[$i]) {
							$width = imagesx($img2[$i]);
							$height = imagesy($img2[$i]);
							$x1 = floor((32 - $width) / 2);

							$y1 = floor((32 - $height) / 2);
							if ($i < 2) {
								$y = 22;
							} else {
								$y = 56;
							}
							if ($i % 2 == 0) {
								$x = 12;
							} else {
								$x = 52;
							}
							@imagecopy($image, $img2[$i], $x + $x1, $y + $y1, 0, 0, $width, $height);
							$i++;
						}
					}
					if ($i > 3) {
						break;
					}
				}
			}

			header('Content-Type: image/jpeg');
			@imagejpeg($image, null, $fmanConfig->thumbnail_quality);
		}
	}
}
