<?php
include_once __DIR__."/functions.php";
include_once __DIR__."/auth.php";
include __DIR__."/conf.php"; //NOSONAR
if($fmanConfig->authentification_needed && !$userlogin)
{
	exit();
}
$rooturl = $fmanConfig->rootdir;
$seldir = kh_filter_input(INPUT_GET, "curdir", FILTER_SANITIZE_STRING_NEW);
$dir2 = path_decode(kh_filter_input(INPUT_GET, "seldir"), $fmanConfig->rootdir);
if(!is_dir($dir2)){
	$dir2 = path_decode('', $fmanConfig->rootdir);	
}
$arrdir = array();
if(file_exists($dir2) && ($handle = opendir($dir2)))
{
	$i=0;
	while (false !== ($ufile = readdir($handle))) 
	{ 
		$fn = "$dir2/$ufile";
		if($ufile == "." || $ufile == ".." ) 
		{
			continue;
		}
		try
		{
			$filetype = filetype($fn);
			unset($obj);
			if($filetype == "dir")
			{
				$obj['path'] = path_encode($fn, $fmanConfig->rootdir);
				$obj['location'] = path_encode(dirname($fn), $fmanConfig->rootdir);
				$obj['name'] = basename($fn);
				$arrdir[] = $obj;
			}
		}
		catch(\Exception $e)
		{
			try
			{
				unset($obj);
				if(is_dir($fn))
				{
					$obj['path'] = path_encode($fn, $fmanConfig->rootdir);
					$obj['location'] = path_encode(dirname($fn), $fmanConfig->rootdir);
					$obj['name'] = basename($fn);
					$arrdir[] = $obj;
				}
				
			}
			catch(\Exception $e)
			{
				// Do nothing
			}
		}
	}
}

$_order = array();
foreach ($arrdir as &$row){
$_order[] = &$row['name'];
}
array_multisort($_order, SORT_ASC, SORT_STRING, $arrdir);
if(!empty($arrdir))
{
?>
<ul>
<?php
foreach($arrdir as $k=>$val)
{
?>
<li class="row-data-dir dir-control" data-file-name="<?php echo $val['name'];?>" data-file-location="<?php echo $val['location'];?>" data-file-path="<?php echo str_replace("'", "\'", $val['path']);?>"><a href="javascript:;" onClick="return openDir('<?php echo str_replace("'", "\'", $val['path']);?>')"><?php echo $val['name'];?></a>
<?php
if(!empty($val['location']) && stripos($seldir, $val['path']) !== false)
{
// recursive
// list dir tree
echo builddirtree($seldir);
}
?>
</li>
<?php
}
?>
</ul>
<?php
}
?>