<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
$class_id = trim(kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW));

$sql = "SELECT `edu_student`.`student_id` as `v`, `edu_student`.`name` as `l`
from `edu_student` where `edu_student`.`class_id` = '$class_id' 
and `edu_student`.`active` = '1' 
order by `edu_student`.`name` asc
";
$list = array();
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($list);
