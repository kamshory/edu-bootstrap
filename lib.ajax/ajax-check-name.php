<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth.php";

$mlid = $member_login->member_id;

$json = array('valid'=>false, 'corrected'=>'');

if(isset($_POST['name']))
{
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING_NEW);
	$name = trim(preg_replace("/[^a-zA-Z 0-9\.\-]+/", " ", $name), " -. ");
	$name = trim(preg_replace("/\s+/", " ", $name));
	if($name != '')
	{
		if($picoEdu->checkValidName($name))
		{
			$coorected = $name;
			$arr = explode(" ", $coorected);
			foreach($arr as $k=>$v)
			{
				$arr[$k][0] = strtoupper($arr[$k][0]);
			}
			$coorected = implode(" ", $arr);
			$json = array('valid'=>true, 'corrected'=>$coorected);
		}
		else
		{
			$coorected = $name;
			$arr = explode(" ", $coorected);
			foreach($arr as $k=>$v)
			{
				$arr[$k][0] = strtoupper($arr[$k][0]);
			}
			$coorected = implode(" ", $arr);
			$json = array('valid'=>false, 'corrected'=>$coorected);
		}
	}
	else
	{
			$json = array('valid'=>false, 'corrected'=>'');
	}
}
echo json_encode($json);
?>