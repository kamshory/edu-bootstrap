<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
$cfg->module_title = "Artikel";
include_once dirname(__FILE__)."/lib.inc/cfg.pagination.php";
if(isset($_GET['school_id']))
{
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
}

if(isset($_GET['article_id']))
{
	$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
	$sql_filter_article = " and `edu_article`.`article_id` = '$article_id' ";

	if(isset($school_id))
	{
		$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "select * from `edu_article` where `edu_article`.`active` = '1' $sql_filter_article ";
}
else
{
	$sql_filter_article = "";
	if(isset($school_id))
	{
		$sql_filter_article .= " and `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "select * from `edu_article` where `edu_article`.`active` = '1' $sql_filter_article ";
}
