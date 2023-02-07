<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$admin_id = $admin_login->admin_id;
$tokens = kh_filter_input(INPUT_GET, "tokens", FILTER_SANITIZE_STRING_NEW);
$arr = explode(",", $tokens);
foreach($arr as $key=>$val)
{
	$arr[$key] = "'".addslashes($val)."'";
}
$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="<?php echo $cfg->base_url;?>">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<title>Token Ujian - <?php echo $cfg->app_name;?></title>
<style type="text/css">
body{
	margin:0;
	padding:0;
}
.all{
	padding:10px;
}
.main-table{
	border-collapse:collapse;
}
.main-table td{
	padding:4px 5px;
}
.header{
	margin-bottom:10px;
}
h1, h2, h3{
	text-align:center;
	margin:0;
	padding:4px 0;
	text-transform:uppercase;
}
h1{
	font-size:18px;
}
h2{
	font-size:16px;
}
h3{
	font-size:14px;
}
.user-item{
	margin:15px 0;
}
.cut-here {
    height: 0px;
    border-bottom: 1px dashed #333333;
    margin: 18px 15px 18px 15px;
    display: block;
	position:relative;
}
.cut-here::before {
    content: '\2702';
    font-size: 12px;
    position: absolute;
    top: -9px;
    left: -15px;
}
.cut-here::after {
	transform:rotate(180deg);
    content: '\2702';
    font-size: 12px;
    position: absolute;
    top: -7px;
    right: -15px;
}
</style>
</head>

<body>
<div class="all">
<div class="header">
<h1>Token Ujian</h1>
</div>
<div class="main">
<?php
$tokens = implode(",", $arr);
$sql = "SELECT `edu_token`.* , `edu_student`.`name` AS `student_name`, `edu_student`.`reg_number` AS `reg_number`, 
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_token`.`teacher_create`) AS `teacher_name`,
(SELECT `edu_test`.`name` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_token`.`test_id`) AS `test_name`
FROM `edu_token` 
INNER JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_token`.`student_id`)
WHERE `edu_token`.`token_id` in ($tokens)
ORDER BY `edu_student`.`reg_number` ASC ";
$stmt = $database->executeQuery($sql);
    if ($stmt->rowCount() > 0) {
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $data) {
        ?>

<div class="cut-here"></div>
<div class="user-item">
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="main-table">
  <tr>
    <td width="25%">Ujian</td>
    <td width="25%">NIS</td>
    <td width="25%">Nama</td>
    <td width="10%">Token</td>
    <td width="15%">Kedaluarsa</td>
  </tr>
  <tr>
    <td><?php echo $data['test_name']; ?> </td>
    <td><?php echo $data['reg_number']; ?> </td>
    <td><?php echo $data['student_name']; ?> </td>
    <td><?php echo $data['token']; ?> </td>
    <td><?php echo translateDate(date('d M H:i', strtotime($data['time_expire']))); ?> </td>
  </tr>
</table>
</div>
<?php
      }
    }
?>
</div>
</div>
</body>
</html>
<?php

?>