<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
exit();
}
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$status = kh_filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING_NEW);

$arr_status = array(
1=>'Ujian',
2=>'Selesai',
3=>'Dikeluarkan',
4=>'Diblokir'
);

if(@$_GET['option'] == 'kick-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test_member`.* from `edu_test_member` where `test_member_id` = '$id' and `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$waktu = $picoEdu->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$sql = "DELETE FROM `sessions` where `id` = '$sessions_id' ";
		$database->executeDelete($sql);
		$sql = "update `edu_test_member` set `time_exit` = '$waktu', `ip_exit` = '$ip', `member_edit` = '$admin_id', `status` = '3' where `test_member_id` = '$id'";	
		$database->executeUpdate($sql);
	}
}
if(@$_GET['option'] == 'block-student' && isset($_GET['test_id']) && isset($_GET['id']))
{
	$id = kh_filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_test_member`.* from `edu_test_member` where `test_member_id` = '$id' and `status` = '1'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$waktu = $picoEdu->getLocalDateTime();
		$ip = addslashes($_SERVER['REMOTE_ADDR']);
		$sessions_id = $data['sessions_id'];
		$student_id = $data['student_id'];
		$sql = "DELETE FROM `sessions` where `id` = '$sessions_id' ";
		$database->executeDelete($sql);
		$sql = "update `edu_test_member` set `time_exit` = '$waktu', `ip_exit` = '$ip', `member_edit` = '$admin_id', `status` = '4' where `test_member_id` = '$id'";	
		$database->executeUpdate($sql);
		$sql = "update `edu_student` set `blocked` = '1' where `edu_student_id` = '$student_id' and `school_id` = '$school_id' and `teacher_id` = '$auth_teacher_id' ";
		$database->executeUpdate($sql);
	}
}



$filter = "";
if($test_status == '1')
{
	$filter .= " and `edu_test_member`.`status` = '1' ";
}
else if($test_status == '2')
{
	$filter .= " and `edu_test_member`.`status` = '2' ";
}
else if($test_status == '3')
{
	$filter .= " and `edu_test_member`.`status` = '3' ";
}
else if($test_status == '4')
{
	$filter .= " and `edu_test_member`.`status` = '4' ";
}

$sql = "SELECT `edu_test_member`.* , `edu_student`.`reg_number`,
(select count(distinct `u`.`student_id`) from `edu_test_member` as `u` where `u`.`student_id` = `edu_test_member`.`student_id` and `u`.`school_id` = `edu_test_member`.`school_id` and `u`.`test_id` = `edu_test_member`.`test_id` and `u`.`test_member_id` != `edu_test_member`.`test_member_id` and `u`.`status` = '1' and `edu_test_member`.`status` = '1' and left(`u`.`time_enter`, 10) = left(`edu_test_member`.`time_enter`, 10)) as `duplikat_login`,
`edu_student`.`name` as `name_student`,
(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` and `edu_class`.`school_id` = `edu_test_member`.`school_id`) as `name_class`
from `edu_test_member` 
inner join(`edu_student`) on(`edu_student`.`student_id` = `edu_test_member`.`student_id`)
where `edu_test_member`.`test_id` = '$test_id' $filter
group by `edu_test_member`.`test_member_id`
order by `edu_test_member`.`time_enter` asc";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?> 
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table tabel-peserta-test">
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
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
		$no++;
		$cls = "";
		if($data['duplikat_login']>0)
		{
			$cls = "duplikat";
		}
?>
    <tr class="<?php echo $cls;?>">
      <td align="right"><?php echo $no;?></td>
      <td><?php echo $data['reg_number'];?></td>
      <td><?php echo $data['name_student'];?></td>
      <td><?php echo $data['name_class'];?></td>
      <td><?php echo translateDate(date('j M Y H:i:s', strtotime($data['time_enter'])));?></td>
      <td><?php echo $data['ip_enter'];?></td>
      <td><?php if ($data['time_exit']) {
			  echo translateDate(date('j M Y H:i:s', strtotime($data['time_exit'])));
		  }?></td>
      <td><?php echo $data['ip_exit'];?></td>
      <td><?php echo $arr_status[$data['status']];?></td>
      <td><?php echo $data['member_edit'];?></td>
      <td><?php if($data['status'] == '1'){?><a class="kick-student" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=kick-student&id=<?php echo $data['test_member_id'];?>&test_id=<?php echo $test_id;?>" data-id="<?php echo $data['test_member_id'];?>" data-name-student="<?php echo $data['name_student'];?>">Keluarkan</a><?php }?></td>
      <td><?php if($data['status'] == '1'){?><a class="block-student" href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=kick-student&id=<?php echo $data['test_member_id'];?>&test_id=<?php echo $test_id;?>" data-id="<?php echo $data['test_member_id'];?>" data-name-student="<?php echo $data['name_student'];?>">Blokir</a><?php }?></td>
    </tr>

<?php
	}
}
?>
    </tbody>
  </table>
