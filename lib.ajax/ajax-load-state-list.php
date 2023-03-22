<?php
include_once dirname(__DIR__)."/lib.inc/functions-pico.php";
$country_id = trim(kh_filter_input(INPUT_GET, "country_id", FILTER_SANITIZE_STRING_NEW));

$sql = "SELECT `state`.`state_id` AS `v`, `state`.`name` AS `l`
FROM `state` WHERE `state`.`country_id` = '$country_id' 
AND `state`.`active` = true AND `state`.`verify` = '1'
ORDER BY `state`.`type` ASC, `state`.`name` ASC
";
$list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
echo json_encode($list);
