<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	exit();
}
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
$status = kh_filter_input(INPUT_GET, "status", FILTER_SANITIZE_STRING_NEW);

$arr_status = \Pico\PicoConst::TEST_STATUS;

if(@$_GET['option'] == 'kick-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$test_member_id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test_member`.* FROM `edu_test_member` WHERE `test_member_id` = '$test_member_id' AND `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$waktu = $database->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$sql = "DELETE FROM `sessions` WHERE `id` = '$sessions_id' ";
		$database->executeDelete($sql, true);
		$sql = "UPDATE `edu_test_member` SET `time_exit` = '$waktu', `ip_exit` = '$ip', `member_edit` = '$admin_id', `status` = '3' WHERE `test_member_id` = '$test_member_id'";	
		$database->executeUpdate($sql, true);
	}
}
if(@$_GET['option'] == 'block-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$test_member_id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
	$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test_member`.* FROM `edu_test_member` WHERE `test_member_id` = '$test_member_id' AND `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$waktu = $database->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$student_id = $data['student_id'];
		$sql = "DELETE FROM `sessions` WHERE `id` = '$sessions_id' ";
		$database->executeDelete($sql, true);
		$sql = "UPDATE `edu_test_member` SET `time_exit` = '$waktu', `ip_exit` = '$ip', `member_edit` = '$admin_id', `status` = '4' WHERE `test_member_id` = '$test_member_id'";	
		$database->executeUpdate($sql, true);
		$sql = "UPDATE `edu_student` SET `blocked` = true WHERE `edu_student_id` = '$student_id' AND `school_id` = '$school_id' AND `teacher_id` = '$auth_teacher_id' ";
		$database->executeUpdate($sql, true);
	}
}



$filter = "";
if($test_status == '1')
{
	$filter .= " AND `edu_test_member`.`status` = '1' ";
}
else if($test_status == '2')
{
	$filter .= " AND `edu_test_member`.`status` = '2' ";
}
else if($test_status == '3')
{
	$filter .= " AND `edu_test_member`.`status` = '3' ";
}
else if($test_status == '4')
{
	$filter .= " AND `edu_test_member`.`status` = '4' ";
}

$sql = "SELECT `edu_test_member`.* , `edu_student`.`reg_number`,
(SELECT COUNT(DISTINCT `u`.`student_id`) FROM `edu_test_member` AS `u` WHERE `u`.`student_id` = `edu_test_member`.`student_id` AND `u`.`school_id` = `edu_test_member`.`school_id` AND `u`.`test_id` = `edu_test_member`.`test_id` AND `u`.`test_member_id` != `edu_test_member`.`test_member_id` AND `u`.`status` = '1' AND `edu_test_member`.`status` = '1' and left(`u`.`time_enter`, 10) = left(`edu_test_member`.`time_enter`, 10)) AS `duplicated_login`,
`edu_student`.`name` AS `name_student`,
(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` AND `edu_class`.`school_id` = `edu_test_member`.`school_id`) AS `name_class`
FROM `edu_test_member` 
INNER JOIN (`edu_student`) ON (`edu_student`.`student_id` = `edu_test_member`.`student_id`)
WHERE `edu_test_member`.`test_id` = '$test_id' $filter
GROUP BY `edu_test_member`.`test_member_id`
ORDER BY `edu_test_member`.`time_enter` ASC ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?> 
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm tabel-peserta-test">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>NIS</td>
      <td>Nama</td>
      <td>Kelas</td>
      <td>Waktu Masuk</td>
      <td>IP Masuk</td>
      <td>Waktu Keluar</td>
      <td>IP Keluar</td>
      <td>Status</td>
      <td>Admin</td>
      <td>Keluarkan</td>
      <td>Blokir</td>
    </tr>
    </thead>
    
    <tbody>
<?php
	$no = 0;
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
		$no++;
		$cls = "";
		if($data['duplicated_login']>0)
		{
			$cls = "duplicated";
		}
?>
    <tr class="<?php echo $cls;?>">
      <td align="right"><?php echo $no;?> </td>
      <td><?php echo $data['reg_number'];?> </td>
      <td><?php echo $data['name_student'];?> </td>
      <td><?php echo $data['name_class'];?> </td>
      <td><?php echo translateDate(date('j M Y H:i:s', strtotime($data['time_enter'])));?> </td>
      <td><?php echo $data['ip_enter'];?> </td>
      <td><?php if ($data['time_exit']) {
			  echo translateDate(date('j M Y H:i:s', strtotime($data['time_exit'])));
		  }?> </td>
      <td><?php echo $data['ip_exit'];?> </td>
      <td><?php echo $arr_status[$data['status']];?> </td>
      <td><?php echo $data['member_edit'];?> </td>
      <td><?php if($data['status'] == '1'){?><a class="kick-student" href="<?php echo $picoEdu->gateBaseSelfName();?>?option=kick-student&id=<?php echo $data['test_member_id'];?>&test_id=<?php echo $test_id;?>" data-id="<?php echo $data['test_member_id'];?>" data-name-student="<?php echo $data['name_student'];?>">Keluarkan</a><?php }?> </td>
      <td><?php if($data['status'] == '1'){?><a class="block-student" href="<?php echo $picoEdu->gateBaseSelfName();?>?option=kick-student&id=<?php echo $data['test_member_id'];?>&test_id=<?php echo $test_id;?>" data-id="<?php echo $data['test_member_id'];?>" data-name-student="<?php echo $data['name_student'];?>">Blokir</a><?php }?> </td>
    </tr>

<?php
	}
}
?>
    </tbody>
  </table>
