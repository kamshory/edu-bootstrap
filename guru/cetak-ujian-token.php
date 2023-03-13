<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
$tokens = kh_filter_input(INPUT_GET, "tokens", FILTER_SANITIZE_STRING_NEW);
$arr = explode(",", $tokens);
foreach($arr as $key=>$val)
{
  $arr[$key] = "'".addslashes($val)."'";
}
$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school`.*, `edu_school`.`name` AS `school_name`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if ($stmt->rowCount() > 0) {
  $rows = array();
  $data = $stmt->fetch(\PDO::FETCH_ASSOC);

  $tokens = implode(",", $arr);
  $sql = "SELECT `edu_token`.* , `edu_student`.`name` AS `student_name`, `edu_student`.`reg_number` AS `reg_number`, 
  (SELECT `edu_test`.`name` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_token`.`test_id`) AS `test_name`
  FROM `edu_token` 
  INNER JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_token`.`student_id`)
  WHERE `edu_token`.`school_id` = '$school_id' 
  AND `edu_token`.`token_id` IN ($tokens)
  ORDER BY `edu_student`.`reg_number` ASC ";
  $stmt = $database->executeQuery($sql);
  
  $url = rtrim($database->getSystemVariable('base_url_test'), "/");

  if ($stmt->rowCount() > 0) {
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $token_images = array();
    foreach($rows as $token_image)
    {
      ob_start();
      QRCode::png($url."/".$token_image['test_id']."/".$token_image['token'], null);
      $imageString = base64_encode(ob_get_contents());
      $token_images[$token_image['token_id']] = $imageString;
      ob_end_clean();   
    }

  }
  ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="<?php echo $cfg->base_url; ?>">
<link rel="shortcut icon" type="image/x-ico" href="<?php echo $cfg->base_assets;?>favicon.ico" />
<title>Token Ujian - <?php echo $cfg->app_name; ?></title>
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
	margin:15px 0 35px 0;
  padding: 10px 0px 10px 140px;
  position: relative;
}
.user-item .image{
  position: absolute;
  margin-left: -130px;
  margin-top: -25px;
  vertical-align: top;
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
<h3><?php echo $data['school_name']; ?></h3>
</div>
<div class="main">
<?php

  foreach($rows as $data) 
  {
        ?>

<div class="cut-here"></div>
<div class="user-item">
  <div class="image">
  <img src="data:image/png;base64,<?php echo $token_images[$data['token_id']];?>" alt="">
  </div>
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="main-table">
  <tr>
  <td colspan="4"><?php echo $data['test_name'];?> </td>
  </tr>
  <tr>
    <td width="25%">NIS</td>
    <td width="35%">Nama</td>
    <td width="15%">Token</td>
    <td width="25%">Kedaluarsa</td>
  </tr>
  <tr>
    <td><?php echo $data['reg_number'];?> </td>
    <td><?php echo $data['student_name'];?> </td>
    <td><?php echo $data['token'];?> </td>
    <td><?php echo translateDate(date('d M H:i', strtotime($data['time_expire'])));?> </td>
  </tr>
</table>
</div>
<?php
      }
      ?>
</div>
</div>
</body>
</html>
<?php 
}
?>