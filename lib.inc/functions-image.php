<?php
if(!function_exists("exif_read_data"))
{
	include_once dirname(__FILE__)."/exif.php";
}
function imageresizemax($source, $destination, $maxwidth, $maxheight, $interlace=false, $type = 'jpeg', $quality = 80)
{
	$imageinfo = getimagesize($source);
	$image = new StdClass();
    if (empty($imageinfo)) 
	{
        if (file_exists($source)) 
		{
            unlink($source);
        }
        return false;
    }
    $image->width  = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->type   = $imageinfo[2];
    switch ($image->type) 
	{
        case IMAGETYPE_GIF:
            if (function_exists('ImageCreateFromGIF')) 
			{
                $im = @ImageCreateFromGIF($source);
            } 
			else 
			{
                unlink($source);
                return false;
            }
            break;
        case IMAGETYPE_JPEG:
            if (function_exists('ImageCreateFromJPEG')) 
			{
                $im = @ImageCreateFromJPEG($source);
            } 
			else 
			{
                unlink($source);
                return false;
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('ImageCreateFromPNG')) 
			{
                $im = @ImageCreateFromPNG($source);
            } 
			else 
			{
                unlink($source);
                return false;
            }
            break;
        default:
            unlink($source);
            return false;
    }
	if(!$im)
	{
		return false;
	}
	
	$currentwidth = $image->width;
	$currentheight = $image->height;
	// adapting image width
	if($currentwidth > $maxwidth)
	{
		$tmpwidth = round($maxwidth);
		$tmpheight = round($currentheight*($tmpwidth/$currentwidth));
		
		$currentwidth = $tmpwidth;
		$currentheight = $tmpheight;
	}
	// adapting image height
	if($currentheight > $maxheight)
	{
		$tmpheight = round($maxheight);
		$tmpwidth = round($currentwidth*($tmpheight/$currentheight));
		$currentwidth = $tmpwidth;
		$currentheight = $tmpheight;
	}
	$im2 = imagecreatetruecolor($currentwidth, $currentheight);
	$white = imagecolorallocate($im2, 255, 255, 255);
	imagefilledrectangle($im2,0,0,$currentwidth, $currentheight,$white);
	imagecopyresampled($im2,$im,0,0,0,0,$currentwidth,$currentheight,$image->width,$image->height);
	if (file_exists($source)) 
	{
		unlink($source);
	}
	if($interlace)
	{
		imageinterlace($im2, true);
	}
	if($type == 'png')
	{
	imagepng($im2,$destination);
	}
	else
	{
	imagejpeg($im2,$destination,$quality);
	}
	return $destination;
}

function read_exif_data_file($filename)
{
	$is = @getimagesize($filename);
	if(function_exists("exif_read_data"))
	{
		$exif = @exif_read_data($filename, 0, true, true);
	}
	/*
	else
	{
		$exif = @read_exif_data_alt($filename);
	}
	*/
	if(@count($exif))
	{
		$exif['original_width'] = $is[0];
		$exif['original_height'] = $is[1];
	}
	
	return $exif;
}

function pack_exif_data($exif)
{
	if(count($exif))
	{
		$width = $exif['original_width'];
		$height = $exif['original_height'];
		if(isset($exif['IFD0']['Make']) && isset($exif['IFD0']['Model']) && strpos($exif['IFD0']['Model'], $exif['IFD0']['Make']) === 0)
		{
			$exif['IFD0']['Make'] = '';
		}
		
		$camera = (isset($exif['IFD0']['Make']))?((@$exif['IFD0']['Make'].' '.@$exif['IFD0']['Model'])):'-';
		$time_capture = (@$exif['IFD0']['Datetime'])?(@$exif['IFD0']['Datetime']):(@$exif['EXIF']['DateTimeOriginal'])?(@$exif['EXIF']['DateTimeOriginal']):'-';
		if(isset($exif['GPS']))
		{
			$gpsinfo = $exif['GPS'];
			
			$latar = explode("/",@$gpsinfo['GPSLatitude'][0]);
			if(count($latar)>1 && $latar[1])
			$latd = $latar[0]/$latar[1];
			$latar = explode("/",@$gpsinfo['GPSLatitude'][1]);
			if(count($latar)>1 && $latar[1])
			$latm = $latar[0]/$latar[1];
			$latar = explode("/",@$gpsinfo['GPSLatitude'][2]);
			if(count($latar)>1 && $latar[1])
			$lats = $latar[0]/$latar[1];
			$reallat = dmstoreal($latd, $latm, $lats);
			if(stripos(@$gpsinfo['GPSLatitudeRef'],"S")!==false)
			$reallat = $reallat*-1;
			$latitude = "$latd; $latm; $lats ".@$gpsinfo['GPSLatitudeRef'];
			$latitude = trim($latitude, " ; ");
			
			$longar = explode("/",@$gpsinfo['GPSLongitude'][0]);
			if(count($longar)>1 && $longar[1])
			$longd = $longar[0]/$longar[1];
			$longar = explode("/",@$gpsinfo['GPSLongitude'][1]);
			if(count($longar)>1 && $longar[1])
			$longm = $longar[0]/$longar[1];
			$longar = explode("/",@$gpsinfo['GPSLongitude'][2]);
			if(count($longar)>1 && $longar[1])
			$longs = $longar[0]/$longar[1];
			
			$reallong = dmstoreal($longd, $longm, $longs);
			if(stripos(@$gpsinfo['GPSLongitudeRef'],"W")!==false)
			$reallong = $reallong*-1;
			$longitude = "$longd; $longm; $longs ".@$gpsinfo['GPSLongitudeRef'];
			
			$longitude = trim($longitude, " ; ");
			
			$alar = explode("/",@$gpsinfo['GPSAltitude']);
			if(count($alar)>1 && $alar[1])
			$altitude = $alar[0]/$alar[1];
			$altref = @$gpsinfo['GPSAltitudeRef'];
		}
		else
		{
			$latitude = "-";
			$longitude = "-";
			$altitude = "-";
			$altref = "";
		}
		$exifdata = array(
		'width'=>@$width,
		'height'=>@$height,
		'time'=>@$time_capture,
		'camera'=>@$camera,
		'latitude'=>@$latitude,
		'longitude'=>@$longitude,
		'altitude'=>@$altitude,
		'capture_info'=>get_capture_info(@$exif)
		);
		return $exifdata;
	}
	else
	{
	}
	return null;
}

function get_capture_info($exif)
{
	/* 
	Copyright 2013 Kamshory Developer
	*/
	$exifdata = array();
	$tmpdt = array();
	if(is_array($exif))
	{
		$tmpdt['Camera_Maker'] = @$exif['IFD0']['Make'];
		$tmpdt['Camera_Model'] = @$exif['IFD0']['Model'];
		$tmpdt['Capture_Time'] = (@$exif['IFD0']['Datetime'])?(@$exif['IFD0']['Datetime']):(@$exif['EXIF']['DateTimeOriginal'])?(@$exif['EXIF']['DateTimeOriginal']):'';
		$tmpdt['Aperture_F_Number'] = @$exif['COMPUTED']['ApertureFNumber'];
		$tmpdt['Orientation'] = @$exif['IFD0']['Orientation'];
		$tmpdt['X_Resolution'] = @$exif['IFD0']['XResolution'];
		$tmpdt['Y_Resolution'] = @$exif['IFD0']['YResolution'];
		$tmpdt['YCbCr_Positioning'] = @$exif['IFD0']['YCbCrPositioning'];
		$tmpdt['Exposure_Time'] = @$exif['EXIF']['ExposureTime'];
		$tmpdt['F_Number'] = @$exif['EXIF']['FNumber'];
		$tmpdt['ISO_Speed_Ratings'] = @$exif['EXIF']['ISOSpeedRatings'];
		$tmpdt['Shutter_Speed_Value'] = @$exif['EXIF']['ShutterSpeedValue'];
		$tmpdt['Aperture_Value'] = @$exif['EXIF']['ApertureValue'];
		$tmpdt['Light_Source'] = @$exif['EXIF']['LightSource'];
		$tmpdt['Flash'] = @$exif['EXIF']['Flash'];
		$tmpdt['Focal_Length'] = @$exif['EXIF']['FocalLength'];
		$tmpdt['SubSec_Time_Original'] = @$exif['EXIF']['SubSecTimeOriginal'];
		$tmpdt['SubSec_Time_Digitized'] = @$exif['EXIF']['SubSecTimeDigitized'];
		$tmpdt['Flash_Pix_Version'] = @$exif['EXIF']['FlashPixVersion'];
		$tmpdt['Color_Space'] = @$exif['EXIF']['ColorSpace'];
		$tmpdt['Custom_Rendered'] = @$exif['EXIF']['CustomRendered'];
		$tmpdt['Exposure_Mode'] = @$exif['EXIF']['ExposureMode'];
		$tmpdt['White_Balance'] = @$exif['EXIF']['WhiteBalance'];
		$tmpdt['Digital_Zoom_Ratio'] = @$exif['EXIF']['DigitalZoomRatio'];
		$tmpdt['Scene_Capture_Type'] = @$exif['EXIF']['SceneCaptureType'];
		$tmpdt['Gain_Control'] = @$exif['EXIF']['GainControl'];
		foreach($tmpdt as $key=>$val)
		{
			if(@$val != "")
			{
				$exifdata[$key] = $val;
			}
		}
		return $exifdata;
	}
	return null;
}
function flip_horizontal($im)
{
	$wid = imagesx($im);
	$hei = imagesy($im);
	$im2 = imagecreatetruecolor($wid, $hei);	
	for($i = 0;$i < $wid; $i++)
	{
		for($j = 0;$j < $hei; $j++)
		{
			$ref = imagecolorat($im, $i, $j);
			imagesetpixel($im2, ($wid - $i - 1), $j,$ref);
		}
	}
	return $im2;
}
 
function flip_vertical($im)
{
	$wid = imagesx($im);
	$hei = imagesy($im);
	$im2 = imagecreatetruecolor($wid, $hei);
	
	for($i = 0;$i < $wid; $i++)
	{
		for($j = 0;$j < $hei; $j++)
		{
			$ref = imagecolorat($im, $i, $j);
			imagesetpixel($im2, $i, ($hei - $j - 1),$ref);
		}
	}
	return $im2;
}

function create_thumb_image($originalfile, $destination, $dwidth, $dheight, $interlace=false, $quality=80) 
{
	$image = new StdClass();
    $imageinfo = getimagesize($originalfile);
    if (empty($imageinfo)) 
	{
        if (file_exists($originalfile)) {
            unlink($originalfile);
        }
        return false;
    }
    $image->width  = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->type   = $imageinfo[2];
    switch ($image->type) {
        case IMAGETYPE_GIF:
            if (function_exists('ImageCreateFromGIF')) 
			{
                $im = @ImageCreateFromGIF($originalfile);
            } 
			else 
			{
                //notice('GIF not supported on this server');
                unlink($originalfile);
                return false;
            }
            break;
        case IMAGETYPE_JPEG:
            if (function_exists('ImageCreateFromJPEG')) 
			{
                $im = @ImageCreateFromJPEG($originalfile);
            } 
			else 
			{
                //notice('JPEG not supported on this server');
                unlink($originalfile);
                return false;
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('ImageCreateFromPNG')) 
			{
                $im = @ImageCreateFromPNG($originalfile);
            } 
			else 
			{
                //notice('PNG not supported on this server');
                unlink($originalfile);
                return false;
            }
            break;
        default:
            unlink($originalfile);
            return false;
    }
    //if (function_exists('imagecreatetruecolor') and $CFG->gdversion >= 2) 
	{
        $im1 = imagecreatetruecolor($dwidth,$dheight);
    } 
    $cx = $image->width / 2;
    $cy = $image->height / 2;
    if ($image->width < $image->height) 
	{
        $half = floor($image->width / 2.0);
    } 
	else 
	{
        $half = floor($image->height / 2.0);
    }
	$mindim = min($image->width,$image->height);
	$xstart = 0;
	$ystart = 0;
    if ($image->width > $image->height) 
	{
		$xstart = floor((max($image->width,$image->height) - min($image->width,$image->height))/2.0);
    } 
	else 
	{
		$ystart = floor((max($image->width,$image->height) - min($image->width,$image->height))/2.0);
    }
    imagecopyresampled($im1, $im, 0, 0, $xstart, $ystart, $dwidth, $dheight, $mindim, $mindim);
	if($interlace)
	{
		imageinterlace($im1, true);
	}
		
    if (function_exists('ImageJpeg')) 
	{
        @touch($destination);  // Helps in Safe mode
        if (
		ImageJpeg($im1, $destination, $quality)
		) 
		{
            @chmod($destination, 0666);
            return 1;
        }
    } 
	
    return 0;
}

