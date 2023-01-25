<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";

if(!@$admin_id)
{
include_once dirname(__FILE__)."/login-form.php";
exit();
}
$dir2 = dirname(__FILE__)."/tmp";
if(isset($_POST['clear']))
{
	if(file_exists($dir2) && ($handle = opendir($dir2)))
	{
		$i=0;
		while (false !== ($ufile = readdir($handle))) 
		{
			@unlink("$dir2/$ufile");
		}
	}
	header("Location: ".basename($_SERVER['PHP_SELF']));
}


include_once dirname(__FILE__)."/lib.inc/header.php";
$li = "";
$totalsize = 0;
if(file_exists($dir2) && ($handle = opendir($dir2)))
{
	$i=0;
	while (false !== ($ufile = readdir($handle))) 
	{ 
		if($ufile == "." || $ufile == "..")
		{
			continue;
		}
		$fn = "$dir2/$ufile";
		$filesize = filesize($fn);
		$totalsize += $filesize;
		$li .= "<li>$fn [$filesize]</li>\r\n";
	}
}

if($li != "")
{
?>
<p>File di temporary</p>
<ol>
<?php
echo $li;
?>
</ol>
<p>Ukuran total <?php echo $totalsize;?></p>
<form name="clean" action="" method="post">
<input type="submit" id="clear" name="clear" value="Hapus Semua" />
</form>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
