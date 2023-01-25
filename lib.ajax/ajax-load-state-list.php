<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$country_id = trim(kh_filter_input(INPUT_GET, 'country_id', FILTER_SANITIZE_STRING_NEW));

$sql = "SELECT `state`.`state_id` as `v`, `state`.`name` as `l`
from `state` where `state`.`country_id` = '$country_id' 
and `state`.`active` = '1' and `state`.`verify` = '1'
order by `state`.`type` asc, `state`.`name` asc
";
$list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($list);
