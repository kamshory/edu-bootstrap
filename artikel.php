<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
$cfg->page_title = "Artikel";
include_once dirname(__FILE__)."/lib.inc/cfg.pagination.php";
if(isset($_GET['school_id']))
{
	$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
}

if(isset($_GET['article_id']))
{
	$article_id = kh_filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_STRING_NEW);
	$sql_filter_article = " AND `edu_article`.`article_id` = '$article_id' ";

	if(isset($school_id))
	{
		$sql_filter_article .= " AND `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "SELECT * FROM `edu_article` WHERE `edu_article`.`active` = true $sql_filter_article ";
}
else
{
	$sql_filter_article = "";
	if(isset($school_id))
	{
		$sql_filter_article .= " AND `edu_article`.`school_id` = '$school_id' ";
	}
	$sql = "SELECT * FROM `edu_article` WHERE `edu_article`.`active` = true $sql_filter_article ";
}
