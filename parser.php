<?php

$arr_module = array(
	'home.php',
	'profil.php',
	'ganti-sekolah.php',
	'sekolah.php',
	'kelas.php',
	'artikel.php',
	'siswa.php',
	'guru.php',
	'ujian.php'
);

include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";

$uri_params = @$_GET['uri_params'];
$uri_params = trim($uri_params, "/");
$uri_original = $_SERVER['REQUEST_URI'];
$uri_original = trim($uri_original, "/");
$school_code_from_parser = '';
$uri_offset = stripos($uri_original, $uri_params, 0);
$uri_filtered = substr($uri_original, $uri_offset);

$first_question_mark = stripos($uri_filtered, "?", 0);
$first_slash = stripos($uri_filtered, "/", 0);
if($first_question_mark !== false && ($first_slash === false || $first_slash>$first_question_mark ))
{
	$tmp = explode("?", $uri_filtered, 2);
	$uri_filtered = implode("/?", $tmp);
}

$uri_arr1 = explode("/", $uri_filtered, 2);
if(count($uri_arr1) > 1)
{
	$school_code = $school_code_from_parser = $uri_arr1[0];
	$uri_php_self = $uri_arr1[1];
	$uri_arr2 = explode("?", $uri_php_self);
	if(count($uri_arr2) > 1)
	{
		$uri_args1 = $uri_arr2[1];
		parse_str($uri_args1, $_GET);
	}
	$modul_name = $uri_arr2[0];
	if($modul_name == '' || $modul_name == 'index.php')
	{
		$modul_name = 'sekolah.php';
	}
}
else
{
	$modul_name = "sekolah.php";
	$school_code = $school_code_from_parser = $uri_arr1[0];
}
if(file_exists(dirname(__FILE__)."/sch-".$modul_name))
{
	$school_code = $school_code_from_parser = preg_replace("/[^a-z\-\d]/i","-", $school_code);
	$sql_filter = "";
	if(is_numeric($school_code))
	{
		$school_code = addslashes($school_code);
		$sql_filter .= " and `school_id` = '$school_code' "; 
	}
	else
	{
		$school_code = addslashes($school_code);
		$sql_filter .= " and `school_code` = '$school_code' "; 
	}
	$sql = "select `edu_school`.*
	from `edu_school`
	where `edu_school`.`active` = '1' $sql_filter
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$school_id = $data['school_id'];
		$_GET['school_id'] = $school_id;
	}
	$modul_name = basename($modul_name);
	if(in_array($modul_name, $arr_module))
	{	
		include_once dirname(__FILE__)."/sch-".$modul_name;
	}
}
