<?php
class ListFile
{
	public $location = "";
	public $result_file = array();
	public $result_dir = array();

	public function __construct($location = null)
	{
		if ($location !== null) {
			$this->location = $location;
			$this->findAll($location);
		}
	}

	public function findAll($location) //NOSONAR
	{
		global $fmanConfig;
		if (file_exists($location) && ($handle = opendir($location))) {
			while (false !== ($ufile = readdir($handle))) {
				$fn = "$location/$ufile";
				if ($ufile == "." || $ufile == "..") {
					continue;
				}
				$filetype = filetype($fn);
				unset($obj);
				if ($filetype == "file") {
					$ft = getMIMEType($fn);
					$obj['url'] = $fmanConfig->rooturl . '/' . substr(path_encode($fn, $fmanConfig->rootdir), 5);
					$obj['path'] = path_encode($fn, $fmanConfig->rootdir);
					$obj['location'] = path_encode(dirname($fn), $fmanConfig->rootdir);
					$obj['name'] = basename($fn);
					$fs = filesize($fn);
					$obj['filesize'] = $fs;
					if ($fs >= 1048576) {
						$obj['size'] = number_format($fs / 1048576, 2, '.', '') . 'M';
					} else if ($fs >= 1024) {
						$obj['size'] = number_format($fs / 1024, 2, '.', '') . 'K';
					} else {
						$obj['size'] = $fs;
					}
					$obj['type'] = $ft->mime;
					$obj['extension'] = $ft->extension;
					$obj['permission'] = substr(sprintf('%o', fileperms($fn)), -4);
					$fti = filemtime($fn);
					$obj['filemtime'] = '<span title="' . date(\Pico\PicoConst::DATE_TIME_MYSQL, $fti) . '">' . date('y-m-d', $fti) . '</span>';

					if ((stripos($obj['type'], 'image') !== false || stripos($obj['type'], 'application/x-shockwave-flash') !== false) && $obj['filesize'] <= $fmanConfig->thumbnail_max_size) {
						try {
							$is = @getimagesize($fn);
							if ($is) {
								$obj['image_width'] = $is[0];
								$obj['image_height'] = $is[1];
								if (stripos($is['mime'], 'image') === 0) {
									$obj['type'] = $is['mime'];
								}
							} else {
								$obj['image_width'] = 0;
								$obj['image_height'] = 0;
							}
						} catch (Exception $e) {
							$obj['image_width'] = 0;
							$obj['image_height'] = 0;
						}
					} else {
						$obj['image_width'] = 0;
						$obj['image_height'] = 0;
					}
					$this->result_file[] = $obj;
				} else if ($filetype == "dir") {
					$obj['path'] = path_encode($fn, $fmanConfig->rootdir);
					$obj['location'] = path_encode(dirname($fn), $fmanConfig->rootdir);
					$obj['name'] = basename($fn);
					$obj['type'] = 'dir';
					$obj['permission'] = substr(sprintf('%o', fileperms($fn)), -4);
					$fti = filemtime($fn);
					$obj['filemtime'] = '<span title="' . date(\Pico\PicoConst::DATE_TIME_MYSQL, $fti) . '">' . date('y-m-d', $fti) . '</span>';
					$this->result_dir[] = $obj;

					// recursive
					$lv = new ListFile($fn);
					foreach ($lv->result_file as $val) {
						$this->result_file[] = $val;
					}
					foreach ($lv->result_dir as $val) {
						$this->result_dir[] = $val;
					}
				}
			}
		}
	}
}

function cleanForbiddenAll($dir, $fileSync)
{
	@chmod(dirname($dir), 0777);
	@chmod($dir, 0777);
	cleanForbidden($dir, $fileSync);
	@chmod(dirname($dir), 0755);
}
function cleanForbidden($dir, $fileSync)
{
	global $fmanConfig;
	$dir = rtrim($dir, "/");
	$mydir = opendir($dir);
	while (false !== ($file = readdir($mydir))) {
		if ($file != "." && $file != "..") {
			@chmod($dir . "/" . $file, 0777);
			if (@is_dir($dir . "/" . $file)) {
				chdir('.');
				cleanForbidden($dir . "/" . $file, $fileSync);
			} else {
				$fn = $dir . "/" . $file;
				$tt = getMIMEType($fn);
				if (in_array($tt->extension, $fmanConfig->forbidden_extension)) {
					$fileSync->deleteFile($fn, true);
				}
			}
		}
	}
	closedir($mydir);
}

function destroyAll($dir, $fileSync)
{
	@chmod(dirname($dir), 0777);
	@chmod($dir, 0777);
	destroy($dir, $fileSync);
	$fileSync->deleteDirecory($dir, true);
	@chmod(dirname($dir), 0755);
}
function destroy($dir, $fileSync)
{
	$dir = rtrim($dir, "/");
	$mydir = opendir($dir);
	while (false !== ($file = readdir($mydir))) {
		if ($file != "." && $file != "..") {
			@chmod($dir . "/" . $file, 0777);
			if (is_dir($dir . "/" . $file)) {
				chdir('.');
				destroy($dir . "/" . $file, $fileSync);
				$fileSync->deleteDirecory($dir . "/" . $file);
			} else {
				$fileSync->deleteFile($dir . "/" . $file);
			}
		}
	}
	closedir($mydir);
}

// copy all files and folders in directory to specified directory
function cp($wf, $wto)
{
	global $fileSync;
	if (!file_exists($wto)) {
		$fileSync->createDirecory($wto, 0755, true);
	}
	$arr = ls_a($wf);
	foreach ($arr as $fn) {
		if ($fn) {
			$fl = "$wf/$fn";
			$flto = "$wto/$fn";
			if (is_dir($fl)) {
				cp($fl, $flto);
			} else {
				@copy($fl, $flto);
				$fileSync->createFile($flto, true);
			}
		}
	}
}

function ls_a($wh)
{
	$files = "";
	if ($handle = opendir($wh)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (empty($files)) {
					$files = "$file";
				} else {
					$files = "$file\n$files";
				}
			}
		}
		closedir($handle);
	}
	return explode("\n", $files);
}
function chmoddir($dir, $perms)
{
	chmod($dir, $perms);
	$arr = ls_a($dir);
	foreach ($arr as $fn) {
		if ($fn) {
			$fn = $dir . "/" . $fn;
			$ft = filetype($fn);
			if ($ft == "file") {
				chmod($fn, $perms);
			} else {
				chmoddir($fn, $perms);
			}
		}
	}
}

function getMIMEType($filename) //NOSONAR
{
	$obj = new \stdClass();
	$arr = parse_ini_file(__DIR__."/ext.ini");

	$ext = '';
	$mime = '';

	$filename2 = strrev(strtolower($filename));

	foreach ($arr as $key => $val) {
		$ext2 = strrev($key) . '.';
		$pos = stripos($filename2, $ext2);
		if ($pos === 0) {
			$ext = $key;
			$mime = $val;
			break;
		}
	}
	if (!$ext) {
		if (stripos($filename, ".") !== false) {
			$arr2 = explode(".", $filename);
			$ext = $arr2[count($arr2) - 1];
		} else {
			$ext = "";
			$mime = "";
		}
	}
	$obj->extension = $ext;
	$obj->mime = $mime;
	return $obj;
}

function path_encode($dir, $root = null)
{
	if ($root === null) {
		global $fmanConfig;
		$rootdir = $fmanConfig->rootdir;
	} else {
		$rootdir = $root;
	}
	$dir = rtrim(str_replace(array("/..", "../", "./", "..\\", ".\\", "\\", "//"), "/", $dir), "/\\");
	$rootdir = trim(str_replace(array("/..", "../", "./", "..\\", ".\\", "\\", "//"), "/", $rootdir), "/\\");
	$dir2 = trim(str_replace($rootdir, 'base', $dir), "/");
	$dir2 = str_replace("//", "/", $dir2);
	return $dir2;
}
function path_decode($dir, $root = null)
{
	if (is_array($dir)) {
		$dir = "";
	}
	if ($root === null) {
		global $fmanConfig;
		$rootdir = $fmanConfig->rootdir;
	} else {
		$rootdir = $root;
	}
	$dir2 = $dir;
	if (substr($dir2, 0, 4) == "base") {
		$dir2 = substr($dir2, 4);
	}
	$dir2 = rtrim($dir2, "/\\");
	$rootdir = rtrim($rootdir, "/\\");
	$dir2 = str_replace(array("\\..", "/.."), "/", $dir2);
	$dir2 = str_replace("\\", "/", $dir2);
	$dir2 = str_replace("//", "/", $dir2);
	$dir2 = str_replace("//", "/", $dir2);
	$dir2 = str_replace("../", "/", $dir2);
	$dir2 = str_replace("//", "/", $dir2);
	$dir2 = rtrim($rootdir, "/\\") . "/" . ltrim($dir2, "/\\");
	$dir2 = rtrim($dir2, "/\\");
	return $dir2;
}

function path_decode_to_url($dir, $rooturl = "")
{
	if (is_array($dir)) {
		$dir = "";
	}
	$dir2 = $dir;
	if (substr($dir2, 0, 4) == "base") {
		$dir2 = substr($dir2, 4);
	}
	$dir2 = rtrim($dir2, "/\\");
	$dir2 = $rooturl . "/" . $dir2;
	$dir2 = rtrim($dir2, "/\\");
	return $dir2;
}

function path_encode_trash($dir, $trash = null)
{
	if ($trash === null) {
		global $fmanConfig;
		$trashdir = $fmanConfig->trashdir;
	} else {
		$trashdir = $trash;
	}
	$dir = rtrim(str_replace(array("/..", "../", "./", "..\\", ".\\", "\\", "//"), "/", $dir), "/\\");
	$trashdir = rtrim(str_replace(array("/..", "../", "./", "..\\", ".\\", "\\", "//"), "/", $trashdir), "/\\");
	return trim(str_replace($trashdir, 'base', $dir), "/");
}
function path_decode_trash($dir, $trash = null)
{
	if (is_array($dir)) {
		$dir = "";
	}
	if ($trash === null) {
		global $fmanConfig;
		$trashdir = $fmanConfig->trashdir;
	} else {
		$trashdir = $trash;
	}
	$dir2 = $dir;
	if (substr($dir2, 0, 4) == "base") {
		$dir2 = substr($dir2, 4);
	}
	$dir2 = rtrim($dir2, "/\\");
	$trashdir = rtrim($trashdir, "/\\");
	$dir2 = str_replace(array("/..", "../", "./", "..\\", ".\\", "\\", "//"), "/", $dir2);
	$dir2 = $trashdir . "/" . $dir2;
	$dir2 = rtrim($dir2, "/\\");
	return $dir2;
}

$file_list = '';
function dir_list($dir)
{
	global $file_list;
	$dh = opendir($dir);
	if ($dh) {
		while ($subitem = readdir($dh)) {
			if (preg_match('/^\.\.?$/', $subitem))
			{
				continue;
			}
			if (is_file($dir . "/" . $subitem))
			{
				$file_list .= "$dir/$subitem\r\n";
			}
			if (is_dir("$dir/$subitem"))
			{
				dir_list("$dir/$subitem");
			}
		}
		closedir($dh);
	}
}

function deleteForbidden($dir, $fileSync, $containsubdir = false) //NOSONAR
{
	global $fmanConfig;
	if ($fmanConfig->delete_forbidden_extension && file_exists($dir) && is_array($fmanConfig->forbidden_extension)) {
		if ($containsubdir) {
			cleanForbiddenAll($dir, $fileSync);
		} else {
			$dh = opendir($dir);
			if ($dh) {
				while ($subitem = readdir($dh)) {
					$fn = "$dir/$subitem";
					if ($subitem == "." || $subitem == "..") {
						continue;
					}
					$filetype = filetype($fn);
					if ($filetype == "file") {
						$tt = getMIMEType($fn);
						if (in_array($tt->extension, $fmanConfig->forbidden_extension)) {
							$fileSync->deleteFile($fn, $fileSync);
						}
					}
				}
				closedir($dh);
			}
		}
	}
}

function builddirtree($dir)
{
	$dir = str_replace("\\", "/", $dir);
	$arr = explode("/", $dir);
	$ret = "%s";
	$dt = array();
	$dt['path'] = "";
	$dt['name'] = "";
	$dt['location'] = "";
	foreach ($arr as $k => $val) {
		$dt['path'] = $dt['path'] . $val;
		$dt['name'] = basename($val);
		$dt['location'] = $dt['location'] . ($val);
		if ($k > 1) {
			$html = "<ul>\r\n";
			$html .= "<li class=\"row-data-dir dir-control\" data-file-name=\"" . $dt['name'] . "\" data-file-location=\"" . $dt['location'] . "\"><a href=\"javascript:;\" onClick=\"return openDir('" . $dt['path'] . "')\">" . $dt['name'] . "</a>";
			$html .= "%s</li>\r\n";
			$html .= "</ul>";
			$ret2 = sprintf($ret, $html);
			$ret = $ret2;
		}
		$dt['path'] = $dt['path'] . "/";
		$dt['name'] = $dt['name'] . "/";
		$dt['location'] = $dt['location'] . "/";
	}
	$ret = str_replace("%s", "", $ret);
	return $ret;
}

function getfmprofile($name, $authblogid, $default = null) //NOSONAR
{
	global $settings;
	if (isset($settings[$name])) {
		return $settings[$name];
	} else {
		return $default;
	}
}

function compressImageFile($path, $authblogid)
{
	if (getfmprofile('compressimageonupload', $authblogid, 0)) {
		global $fmanConfig;
		$maxsize = $fmanConfig->thumbnail_max_size;
		if (filesize($path) <= $maxsize) {
			// get mime type
			$info = @getimagesize($path);
			if (@stripos($info['mime'], 'image') !== false && (@stripos($info['mime'], 'jpeg') !== false || getfmprofile('imageformat', $authblogid, 0) == 1)) {
				// copress here
				$quality = getfmprofile('imagequality', $authblogid, 80);
				$interlace = getfmprofile('imageinterlace', $authblogid, 0);
				$maxwidth = getfmprofile('maximagewidth', $authblogid, 600);
				$maxheight = getfmprofile('maximageheight', $authblogid, 800);
				if (@stripos($info['mime'], 'jpeg') !== false) {
					// jpeg
					$imagelocation = imageResizeMax($path, $path, $maxwidth, $maxheight, $interlace, $quality); //NOSONAR
				} else if (@stripos($info['mime'], 'png') !== false) {
					// png
					$imagelocation = imageResizeMax($path, $path, $maxwidth, $maxheight, $interlace); //NOSONAR
				} else if (@stripos($info['mime'], 'gif') !== false) {
					// gif
					$imagelocation = imageResizeMax($path, $path, $maxwidth, $maxheight, $interlace); //NOSONAR
				}
			}
		}
	}
}


function imageResizeMax($source, $destination, $maxwidth, $maxheight, $interlace = false, $quality = 80)  //NOSONAR
{
	$image = new \stdClass();
	global $fileSync;
	$imageinfo = getimagesize($source);
	if (empty($imageinfo)) {
		if (file_exists($source)) {
			$fileSync->deleteFile($source, true);
		}
		return false;
	}
	$image->width  = $imageinfo[0];
	$image->height = $imageinfo[1];
	$image->type   = $imageinfo[2];
	switch ($image->type) {
		case IMAGETYPE_GIF:
			if (function_exists('ImageCreateFromGIF')) {
				$im = @ImageCreateFromGIF($source);
			} else {
				$fileSync->deleteFile($source, true);
				return false;
			}
			break;
		case IMAGETYPE_JPEG:
			if (function_exists('ImageCreateFromJPEG')) {
				$im = @ImageCreateFromJPEG($source);
			} else {
				$fileSync->deleteFile($source, true);
				return false;
			}
			break;
		case IMAGETYPE_PNG:
			if (function_exists('ImageCreateFromPNG')) {
				$im = @ImageCreateFromPNG($source);
			} else {
				$fileSync->deleteFile($source, true);
				return false;
			}
			break;
		default:
			$fileSync->deleteFile($source, true);
			return false;
	}
	if (!$im) {
		return false;
	}

	$currentwidth = $image->width;
	$currentheight = $image->height;
	// adapting image width
	if ($currentwidth > $maxwidth) {
		$tmpwidth = round($maxwidth);
		$tmpheight = round($currentheight * ($tmpwidth / $currentwidth));

		$currentwidth = $tmpwidth;
		$currentheight = $tmpheight;
	}
	// adapting image height
	if ($currentheight > $maxheight) {
		$tmpheight = round($maxheight);
		$tmpwidth = round($currentwidth * ($tmpheight / $currentheight));
		$currentwidth = $tmpwidth;
		$currentheight = $tmpheight;
	}
	$im2 = imagecreatetruecolor($currentwidth, $currentheight);
	$white = imagecolorallocate($im2, 255, 255, 255);
	imagefilledrectangle($im2, 0, 0, $currentwidth, $currentheight, $white);
	imagecopyresampled($im2, $im, 0, 0, 0, 0, $currentwidth, $currentheight, $image->width, $image->height);
	if (file_exists($source)) {
		$fileSync->deleteFile($source, true);
	}
	if ($interlace) {
		imageinterlace($im2, true);
	}
	imagejpeg($im2, $destination, $quality);
	return $destination;
}

function get_capture_info($exif)
{
	/* 
	Copyright 2013 Kamshory Developer
	*/
	$exifdata = array();
	$tmpdt = array();
	if (is_array($exif)) {
		$tmpdt['Camera Maker'] = @$exif['IFD0']['Make'];
		$tmpdt['Camera Model'] = @$exif['IFD0']['Model'];
		$tmpdt['Capture Time'] = '';
		if (!empty(@$exif['IFD0']['Datetime'])) {
			$tmpdt['Capture Time'] = @$exif['IFD0']['Datetime'];
		} else if (!empty(@$exif['EXIF']['DateTimeOriginal'])) {
			$tmpdt['Capture Time'] = @$exif['EXIF']['DateTimeOriginal'];
		}
		$tmpdt['Aperture F Number'] = @$exif['COMPUTED']['ApertureFNumber'];
		$tmpdt['Orientation'] = @$exif['IFD0']['Orientation'];
		$tmpdt['X Resolution'] = @$exif['IFD0']['XResolution'];
		$tmpdt['Y Resolution'] = @$exif['IFD0']['YResolution'];
		$tmpdt['YCbCr Positioning'] = @$exif['IFD0']['YCbCrPositioning'];
		$tmpdt['Exposure Time'] = @$exif['EXIF']['ExposureTime'];
		$tmpdt['F Number'] = @$exif['EXIF']['FNumber'];
		$tmpdt['ISO Speed Ratings'] = @$exif['EXIF']['ISOSpeedRatings'];
		$tmpdt['Shutter Speed Value'] = @$exif['EXIF']['ShutterSpeedValue'];
		$tmpdt['Aperture Value'] = @$exif['EXIF']['ApertureValue'];
		$tmpdt['Light Source'] = @$exif['EXIF']['LightSource'];
		$tmpdt['Flash'] = @$exif['EXIF']['Flash'];
		$tmpdt['Focal Length'] = @$exif['EXIF']['FocalLength'];
		$tmpdt['SubSec Time Original'] = @$exif['EXIF']['SubSecTimeOriginal'];
		$tmpdt['SubSec Time Digitized'] = @$exif['EXIF']['SubSecTimeDigitized'];
		$tmpdt['Flash Pix Version'] = @$exif['EXIF']['FlashPixVersion'];
		$tmpdt['Color Space'] = @$exif['EXIF']['ColorSpace'];
		$tmpdt['Custom Rendered'] = @$exif['EXIF']['CustomRendered'];
		$tmpdt['Exposure Mode'] = @$exif['EXIF']['ExposureMode'];
		$tmpdt['White Balance'] = @$exif['EXIF']['WhiteBalance'];
		$tmpdt['Digital Zoom Ratio'] = @$exif['EXIF']['DigitalZoomRatio'];
		$tmpdt['Scene Capture Type'] = @$exif['EXIF']['SceneCaptureType'];
		$tmpdt['Gain Control'] = @$exif['EXIF']['GainControl'];
		foreach ($tmpdt as $key => $val) {
			if (@$val != "") {
				$exifdata[$key] = $val;
			}
		}
		return $exifdata;
	}
	return null;
}

function matchUser($user, $username, $password)
{
	$passwordType = $user[2];
	switch ($passwordType) {
		case 'plain':
			if ($username == $user[0] && $password == $user[1]) {
				return true;
			}
			break;
		case 'md5':
			if ($username == $user[0] && md5($password) == $user[1]) {
				return true;
			}
			break;
		case 'sha1':
			if ($username == $user[0] && sha1($password) == $user[1]) {
				return true;
			}
			break;
	}
	return false;
}
