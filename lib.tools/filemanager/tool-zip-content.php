<?php
include_once __DIR__."/functions.php";
include_once __DIR__."/auth.php";
include __DIR__."/conf.php"; //NOSONAR
if($fmanConfig->authentification_needed && !$userlogin)
{
	exit();
}
$filename = path_decode(kh_filter_input(INPUT_GET, "filepath"), $fmanConfig->rootdir);
$path = path_encode($filename, $fmanConfig->rootdir);
$json_exif = "";
if(file_exists($filename))
{
$zip = new ZipArchive; 
if ($zip->open($filename)) 
{ 
?>
<div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo $path;?>"><?php echo $path;?></div>
<div class="seleted-file-list">
	<?php
     for($i = 0; $i < $zip->numFiles; $i++) 
     {   
	 ?>
     <div><?php echo $zip->getNameIndex($i);?></div>
     <?php
     } 
	 ?>
     </div>
     <?php
} 
else 
{ 
     echo 'Error reading zip-archive!'; 
} 
}
?>
