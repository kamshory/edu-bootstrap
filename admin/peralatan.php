<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";

if(!@$admin_id)
{
require_once dirname(__FILE__)."/login-form.php";
exit();
}
$pageTitle = "Peralatan";
$dir2 = dirname(__FILE__)."/tmp";
if(isset($_POST['clear']))
{
	if(file_exists($dir2) && ($handle = opendir($dir2)))
	{
		$i=0;
		while (false !== ($ufile = readdir($handle))) 
		{
			$fileSync->deleteFile("$dir2/$ufile", true);
		}
	}
	header("Location: ".basename($_SERVER['PHP_SELF']));
}


require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
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
<input class="btn btn-danger" type="submit" id="clear" name="clear" value="Hapus Semua" />
</form>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>
