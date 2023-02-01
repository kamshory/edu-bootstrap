<?php
include_once dirname(__FILE__)."/auth.php";
$fmanConfig = new stdClass;
$fmanConfig->authentification_needed = true;		


/* When Kams File Manager is used on online system, it must be set true.*/

if(@$_GET['test_id']!='')
{
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `test_id` from `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() == 0)
	{
		exit();
	}
	else
	{
		$_SESSION['curdir'] = "school/$school_id/test/$test_id";
	}
}
else if(@$_GET['article_id']!='')
{
	$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `article_id` from `edu_article` where `article_id` = '$article_id' and `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() == 0)
	{
		exit();
	}
	else
	{
		$_SESSION['curdir'] = "school/$school_id/article/$article_id";
	}
}
else if(@$_GET['description']!='')
{
	$_SESSION['curdir'] = "school/$school_id/description";
}
if(@$_SESSION['curdir'] == '')
{
	$_SESSION['curdir'] = "school/$school_id/article";
}

if(@$_GET['section'] == 'info' && @$_GET['info_id']!='')
{
	$info_id = kh_filter_input(INPUT_GET, 'info_id', FILTER_SANITIZE_STRING_NEW);
	$_SESSION['curdir'] = "info/$info_id";
}

$fmanConfig->rootdir = dirname(dirname(dirname(__FILE__)))."/media.edu"."/".$_SESSION['curdir'];	
/* Root directory for uploaded file. Use .htaccess file to protect this directory from executing PHP files.*/
$fmanConfig->hiddendir = array();	 
/* File or directory under root directory to be hidden and forbidden to access it.*/
$fmanConfig->rooturl = "media.edu"."/".$_SESSION['curdir'];						
/* Root url for uploaded file. It can be relative or absoulute.*/
$fmanConfig->thumbnail = true;						
/* Thumbnail for image files.*/
$fmanConfig->thumbnail_quality = 75;				
/* Quality for thumbnail image.*/
$fmanConfig->thumbnail_max_size = 5000000; 
/* Maximum file size to show with thumbnail */
$fmanConfig->readonly = false;						
/* Is user allowed to modify the file or the directory including upload, delete, or extract files.*/
$fmanConfig->allow_upload_all_file = true;			
/* Is user allowed to upload file beside image.*/
$fmanConfig->allow_upload_image = true;			
/* Is user allowed to upload images.*/


$fmanConfig->cache_max_age_file = 3600; 			/* Maximum age for file thumbnail cache (in second) */
$fmanConfig->cache_max_age_dir = 120; 				/* Maximum age for directory thumbnail cache (in second) */


$fmanConfig->delete_forbidden_extension = true;	
/* Delete forbidden files on upload, rename, copy, or extract operation */
$fmanConfig->forbidden_extension = array('php', 'ini', 'sh', 'js', 'css', 'html', 'htm');

/* Note
   You can permit user to upload images but not other type for security reason.
   You can add .htaccess file to prevent user executing PHP script but its location is not on {$fmanConfig->rootdir}
   
   For example:
   Your root document of your system is
   /home/youname/public_html
   
   You set upload directory to
   /home/yourname/public_html/upload
   
   You can place an .htaccess file in
   /home/youname/public_html
   to redirect client access   
   
   
*/

$chkdir = explode("/", $_SESSION['curdir']);
$dir2create = dirname(dirname(dirname(__FILE__)))."/media.edu";
foreach($chkdir as $k=>$v)
{
	$dir2create .= "/".$v;
	if(!file_exists($dir2create))
	{
		$fileSync->createDirecory($dir2create, 0755, true);
	}
}



$fmanConfig->users = array(
	array("kamshory", "j4n94nk451ht4u0r4n9", "plain"),
	array("masroy", "indonesia", "plain")
);
/*
0 = username
1 = password
2 = type of password (plain, md5, sha1)
*/

