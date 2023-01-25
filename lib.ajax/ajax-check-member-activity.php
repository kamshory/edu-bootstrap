<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth.php";

$member_id = kh_filter_input(INPUT_GET, 'member_id', FILTER_SANITIZE_NUMBER_UINT);
$sql = "SELECT `last_seen_time`, `last_activity_time`
from `member`
where `member_id` = '$member_id'
and `active` = '1'
";

$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$activity = time()-strtotime($data['last_activity_time']);
	$seen = time()-strtotime($data['last_seen_time']);
	if ($seen <= 60) {
		$status = 1;
	} else {
		$status = 0;
	}
	$arr = array(
			"activity"=>($activity),
			"seen"=>($seen),
			"status"=>$status
			);
}
else
{
	$arr = new StdClass();
}
echo json_encode($arr);
