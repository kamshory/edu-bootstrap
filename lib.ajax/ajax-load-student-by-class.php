<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$class_id = trim(kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW));

$sql = "SELECT `edu_student`.`student_id` AS `v`, `edu_student`.`name` AS `l`
FROM `edu_student` WHERE `edu_student`.`class_id` = '$class_id' 
AND `edu_student`.`active` = true 
ORDER BY `edu_student`.`name` asc
";
$list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($list);
