<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
$url = 'http://192.168.0.11/';
require_once dirname(dirname(__FILE__)) . "/lib.inc/phpqrcode/phpqrcode.php";
ob_start();
QRCode::png($url, null);
$imageString = base64_encode( ob_get_contents() );
ob_end_clean();

$nt = '';
$sql = "SELECT `edu_class`.* $nt,
(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_class`.`school_id`) as `school_name`
from `edu_class` 
where `edu_class`.`school_id` = '$school_id'
and `edu_class`.`class_id` = '$class_id'  
";
$stmt = $database->executeQuery($sql);

if($stmt->rowCount() > 0)
{
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  $class_id = $data['class_id'];
}
else
{
$class_id = 0;
$sql = "SELECT `edu_school`.*, `edu_school`.`name` as `school_name`
from `edu_school` 
where `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);

if($stmt->rowCount() > 0)
{
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="<?php echo $cfg->base_url;?>">
<link rel="shortcut icon" type="image/jpeg" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/images/favicon.png" />
<title>Username dan Password Siswa - <?php echo $cfg->app_name;?><?php echo rtrim(' - '.@$data['name'], ' - ');?></title>
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
  padding: 10px 0px 10px 120px;
  position: relative;
}
.user-item .image{
  position: absolute;
  margin-left: -110px;
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
<h1>Username dan Password Siswa</h1>
<?php
if($class_id)
{
?>
<h2>Kelas <?php echo $data['name'];?></h2>
<?php
}
?>
<h3><?php echo $data['school_name'];?></h3>
</div>
<div class="main">
<?php
if($class_id)
{
$filter = " and `edu_student`.`class_id` = '$class_id' ";
}
else
{
$filter = "";
}
$sql = "SELECT `edu_student`.* 
from `edu_student` 
where 1 and `edu_student`.`school_id` = '$school_id' and `edu_student`.`active` = '1' $filter
order by `edu_student`.`name` asc ";
$stmt = $database->executeQuery($sql);

if ($stmt->rowCount() > 0) {
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($rows as $data) {
    ?>

<div class="cut-here"></div>

<div class="user-item">
  <div class="image">
    <img src="data:image/png;base64,<?php echo $imageString;?>" alt="">
  </div>
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="main-table">
  <tr>
    <td width="18%">URL</td>
    <td width="15%">Nomor Induk</td>
    <td width="32%">Nama</td>
    <td width="20%">Username</td>
    <td width="15%">Password</td>
  </tr>
  <tr>
    <td><?php echo trim($url, "/"); ?></td>
    <td><?php echo $data['reg_number']; ?></td>
    <td><?php echo $data['name']; ?></td>
    <td><?php echo $data['username']; ?></td>
    <td><?php echo $data['password_initial']; ?></td>
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