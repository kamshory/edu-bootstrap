<?php
include_once dirname(__FILE__)."/functions.php";
include_once dirname(__FILE__)."/auth.php";
include dirname(__FILE__)."/conf.php"; //NOSONAR
if($fmanConfig->authentification_needed && !$userlogin)
{
	exit();
}
$dir2 = path_decode(kh_filter_input(INPUT_GET, "dir"), $fmanConfig->rootdir);
if(!is_dir($dir2)){
$dir2 = path_decode('base', $fmanConfig->rootdir);	
}
$arrfile2 = array();
$arrfile = array();
$arrdir = array();
if(file_exists($dir2))
{
	$handle = opendir($dir2);
	if($handle)
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
			$obj = array();
			if($filetype=="file")
			{
				$ft = getMIMEType($fn);
				$obj['url'] = $fmanConfig->rooturl.'/'.substr(path_encode($fn, $fmanConfig->rootdir),5);
				$obj['name'] = basename($fn);
				$fs = filesize($fn);
				$obj['filesize'] = $fs;

				$obj['type'] = $ft->mime;
				$fti = filemtime($fn);
				$obj['filemtime'] = date(\Pico\PicoConst::DATE_TIME_MYSQL, $fti);
				if(stripos($obj['type'], 'image') !== false && $obj['filesize'] <= $fmanConfig->thumbnail_max_size)
				{
					try
					{
						$is = getimagesize($fn);
						if($is)
						{
							$obj['image_width'] = $is[0];
							$obj['image_height'] = $is[1];
							if(stripos($is['mime'], 'image')===0) 
							{
								$obj['type'] = $is['mime'];
							}
						}
						else
						{
						$obj['image_width'] = 0;
						$obj['image_height'] = 0;
						}
					}
					catch(Exception $e)
					{
						$obj['image_width'] = 0;
						$obj['image_height'] = 0;
					}
				}
				else
				{
					$obj['image_width'] = 0;
					$obj['image_height'] = 0;
				}
				$arrfile[] = $obj;
			}
		}
	}
}
$sortby = kh_filter_input(INPUT_GET, "sortby", FILTER_SANITIZE_STRING_NEW);
if(!in_array($sortby, array('name', 'filesize', 'type', 'permission', 'filemtime')))
{
	$sortby = '';
}							
if($sortby == '')
{
	$sortby = 'type';
}
$sortorder = kh_filter_input(INPUT_GET, "sortorder", FILTER_SANITIZE_STRING_NEW);
if(!in_array($sortorder, array('asc', 'desc')))
{
	$sortorder = '';
}							
if($sortorder == '')
{
	$sortorder = 'asc';
}


$_order = array();
foreach ($arrdir as &$row)//NOSONAR
{
$_order[] = &$row['name'];
}
array_multisort($_order, SORT_ASC, SORT_STRING, $arrdir);

$_order = array();
$_order2 = array();
foreach ($arrfile as &$row){
$_order[] = &$row[$sortby];
$_order2[] = &$row['name'];
}
array_multisort($_order, ($sortorder=='desc')?SORT_DESC:SORT_ASC, $_order2, SORT_ASC, SORT_STRING, $arrfile);

if(count($arrfile))
{
	foreach($arrfile as $key=>$val)
	{
		if(stripos($val['type'], 'image/') === 0)
		{
			$arrfile2[] = $val;
		}
	}
}
if($picoEdu->gateBaseSelfName() == basename(__FILE__))
{
header("Content-Type:text/plain; charset=utf-8");
}
echo json_encode($arrfile2);

