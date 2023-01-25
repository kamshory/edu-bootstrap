<?php
if(isset($_POST['data']) && isset($_POST['filename']))
{
	$data = $_POST['data'];
	$filename = $_POST['filename'];
	$arr = explode(".", $filename);
	$ext = end($arr);
	if(stripos($ext, "doc") !== false)
	{
		header("Content-Type: application/vnd.docx");
		header("Content-Disposition: attachment; filename=\"".$filename."\"");
		echo base64_decode($data);
	}
}
