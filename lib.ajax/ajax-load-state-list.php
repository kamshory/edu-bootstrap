<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$country_id = trim(kh_filter_input(INPUT_GET, "country_id", FILTER_SANITIZE_STRING_NEW));

$sql = "SELECT `state`.`state_id` as `v`, `state`.`name` as `l`
FROM `state` WHERE `state`.`country_id` = '$country_id' 
and `state`.`active` = true and `state`.`verify` = '1'
ORDER BY `state`.`type` asc, `state`.`name` asc
";
$list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($list);
