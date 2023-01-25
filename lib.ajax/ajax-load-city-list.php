<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$state_id = kh_filter_input(INPUT_GET, 'state_id', FILTER_SANITIZE_NUMBER_UINT);
$state_name = trim(kh_filter_input(INPUT_GET, 'state_name', FILTER_SANITIZE_STRING_NEW));
$country_id = trim(kh_filter_input(INPUT_GET, 'country_id', FILTER_SANITIZE_STRING_NEW));

if($state_id == 0 && $state_name != '')
{
	$sql = "SELECT `state_id` from `state` where `name` like '$state_name' and `country_id` = '$country_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$state_id = $data['state_id'];
	}
}

$sql = "SELECT `city`.`city_id` as `v`, `city`.`name` as `l`
from `city` where `city`.`country_id` = '$country_id' 
and (`city`.`state_id` = '$state_id' or `city`.`state_id` = '' or `city`.`state_id` is null or '$state_id' = '0') 
and `city`.`active` = '1' and `city`.`verify` = '1'
order by `city`.`type` asc, `city`.`name` asc
";
$city_list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$city_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($city_list);
