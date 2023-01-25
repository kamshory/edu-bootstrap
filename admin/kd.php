<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
$answer_id = kh_filter_input(INPUT_GET, 'answer_id', FILTER_SANITIZE_NUMBER_UINT);
$result = $picoEdu->getTextScore($answer_id);
print_r($result);

?>