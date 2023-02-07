<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$state_id = kh_filter_input(INPUT_GET, "state_id", FILTER_SANITIZE_STRING_NEW);
$state_name = trim(kh_filter_input(INPUT_GET, "state_name", FILTER_SANITIZE_STRING_NEW));
$country_id = trim(kh_filter_input(INPUT_GET, "country_id", FILTER_SANITIZE_STRING_NEW));

if($state_id == 0 && $state_name != '')
{
	$sql = "SELECT `state_id` FROM `state` WHERE `name` like '$state_name' AND `country_id` = '$country_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$state_id = $data['state_id'];
	}
}

$sql = "SELECT `city`.`city_id` as `v`, `city`.`name` as `l`
FROM `city` WHERE `city`.`country_id` = '$country_id' 
and (`city`.`state_id` = '$state_id' OR `city`.`state_id` = '' OR `city`.`state_id` is null or '$state_id' = '0') 
AND `city`.`active` = true AND `city`.`verify` = '1'
ORDER BY `city`.`type` asc, `city`.`name` asc
";
$city_list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$city_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($city_list);
