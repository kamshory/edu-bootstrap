<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-admin.php";
exit();
}
if(empty(@$real_school_id))
{
include_once dirname(__FILE__)."/belum-ada-sekolah.php";
exit();
}
$cfg->module_title = "Perubahan Kelas Siswa";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(isset($_POST['setclass']) && isset($_POST['students']))
{
	$students = $_POST['students'];
	$class_id = kh_filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_STRING_NEW);
	if(is_array($students))
	{
		foreach($students as $key=>$val)
		{
			$student_id = addslashes($val);
			$sql = "update `edu_student` set `class_id` = '$class_id', 
			`grade_id` = (select `edu_class`.`grade_id` from `edu_class` where `edu_class`.`class_id` = '$class_id')
			where `student_id` = '$student_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if(count(@$_POST) && isset($_POST['save']))
{
	$student_id = kh_filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_STRING_NEW);
	$student_id2 = kh_filter_input(INPUT_POST, 'student_id2', FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['student_id']))
	{
		$student_id = $student_id2;
	}
	$reg_number = kh_filter_input(INPUT_POST, 'reg_number', FILTER_SANITIZE_SPECIAL_CHARS);
	$reg_number_national = kh_filter_input(INPUT_POST, 'reg_number_national', FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, 'grade_id', FILTER_SANITIZE_STRING_NEW);
	$class_id = kh_filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, 'birth_place', FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, 'birth_day', FILTER_SANITIZE_STRING_NEW);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
	$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
	$religion_id = kh_filter_input(INPUT_POST, 'religion_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$blocked = kh_filter_input(INPUT_POST, 'blocked', FILTER_SANITIZE_NUMBER_INT);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_UINT);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$admin_create = $admin_edit = $admin_id;
}
if(isset($_POST['setclass']) && isset($_POST['students']) && isset($_POST['class_id']))
{
	$class_id = kh_filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `grade_id` from `edu_class` where `class_id` = '$class_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$grade_id = $data['grade_id'];
		$students_str = @$_POST['students'];
		if (isset($students_str)) {
			$students = explode(",", $students_str);
			if (is_array($students)) {
				foreach ($students as $key => $val) {
					$student_id = addslashes($val);
					$sql = "update `edu_student` set `class_id` = '$class_id', `grade_id` = '$grade_id' 
					where `student_id` = '$student_id' and `school_id` = '$school_id' ";
					$database->executeUpdate($sql);
				}
			}
		}
	}
}
if(isset($_POST['set_active']) && isset($_POST['student_id']))
{
	$students = @$_POST['student_id'];
	if(isset($students) && is_array($students))
	{
		foreach($students as $key=>$val)
		{
			$student_id = addslashes($val);
			$sql = "update `edu_student` set `active` = '1' where `student_id` = '$student_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['student_id']))
{
	$students = @$_POST['student_id'];
	if(isset($students) && is_array($students))
	{
		foreach($students as $key=>$val)
		{
			$student_id = addslashes($val);
			$sql = "update `edu_student` set `active` = '0' where `student_id` = '$student_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['student_id']))
{
	$students = @$_POST['student_id'];
	if(isset($students) && is_array($students))
	{
		foreach($students as $key=>$val)
		{
			$student_id = addslashes($val);
			$sql = "DELETE FROM `edu_student` where `student_id` = '$student_id' and `school_id` = '$school_id' ";
			$database->executeDelete($sql);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql = "update `edu_student` set 
	`reg_number` = '$reg_number', `reg_number_national` = '$reg_number_national', `grade_id` = '$grade_id', `class_id` = '$class_id', `name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
	where `student_id` = '$student_id2' and `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	if($email != '')
	{
		$sql = "update `edu_student` set 
		`email` = '$email'
		where `student_id` = '$student_id2' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql);
	}
	if($password != '')
	{
		$sql = "update `edu_student` set 
		`password` = md5(md5('$password')), `password_initial` = '$password'
		where `student_id` = '$student_id2' and `school_id` = '$school_id' ";
		$database->executeUpdate($sql);
	}
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&student_id=$student_id2");
}
if(@$_GET['option']=='edit')
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	$edit_key = kh_filter_input(INPUT_GET, 'student_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_student`.* 
	from `edu_student` 
	where 1 and `edu_student`.`school_id` = '$school_id' 
	and `edu_student`.`student_id` = '$edit_key'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_student" id="formedu_student" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>NIS</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number" id="reg_number" value="<?php echo $data['reg_number'];?>" autocomplete="off" /><input type="hidden" name="student_id2" id="student_id2" value="<?php echo ($data['student_id']);?>" /></td>
		</tr>
		<tr>
		<td>NISN</td>
		<td><input type="text" class="input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><select class="input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php
		echo $picoEdu->createGradeOption($data['grade_id']);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><select class="input-select" name="class_id" id="class_id">
		<option value=""></option>
		<?php 
		$sql2 = "select * from `edu_class` where `active` = '1' and `school_id` = '$school_id' order by `order` asc ";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'class_id')
				),
				'selectCondition'=>array(
					'source'=>'class_id',
					'value'=>$data['class_id']
				),
				'caption'=>array(
					'delimiter'=>' &raquo; ',
					'values'=>array(
						'name'
					)
				)
			)
		);
	
		?>
		</select></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M"<?php if($data['gender'] == 'M') {echo ' selected="selected"';}?>>Laki-Laki</option>
		<option value="W"<?php if($data['gender'] == 'W') {echo ' selected="selected"';}?>>Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="input-text input-text-long" name="birth_place" id="birth_place" value="<?php echo $data['birth_place'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /> TTTT-BB-HH</td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><input type="tel" class="input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="input-text input-text-long" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea name="address" class="input-text input-text-long" id="address" autocomplete="off"><?php echo $data['address'];?></textarea></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php if($data['blocked']==1) echo ' checked="checked"';?>> Blokir</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php if($data['active']==1) echo ' checked="checked"';?>> Aktif</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'student_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_student`.* ,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_student`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_student`.`admin_edit`) as `admin_edit`,
(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` limit 0,1) as `class_id`
from `edu_student` 
where 1 and `edu_student`.`school_id` = '$school_id' 
and `edu_student`.`student_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_student" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>NIS</td>
		<td><?php echo $data['reg_number'];?></td>
		</tr>
		<tr>
		<td>NISN</td>
		<td><?php echo $data['reg_number_national'];?></td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class_id'];?></td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><?php echo $data['birth_place'];?></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?></td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Password
		</td><td><?php echo $data['password_initial'];?></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><?php echo $data['blocked']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $data['active']?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&student_id=<?php echo $data['student_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
?>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery.ui.touch-punch.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
    $(document).on('click', '#setclass', function(e){
		var students_id = [];
		var number_of_studeng = 0;
		$('.student_id').each(function(index, element) {
            if($(this)[0].checked)
			{
				students_id.push($(this).val());
				number_of_studeng ++;
			}
        });
		$('#number-of-student').text(number_of_studeng);
		$('#updateclassfrm input[name="students"]').val(students_id.join(','));
		$('.dialog-update-class').dialog({
			modal:true,
			width:300,
			height:200,
			title:'Atur Kelas Siswa Dipilih',
			buttons:{
				'Simpan':function(){
					$('.dialog-update-class').dialog('close');
					$('#updateclassfrm').submit();
					},
				'Batal':function(){
					$('.dialog-update-class').dialog('close');
				}
			}
		});
	});
});
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Kelas</span>
  <select class="input-select" name="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
    $sql2 = "select * from `edu_class` where `active` = '1' and `school_id` = '$school_id' order by `order` asc ";
    echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'class_id')
			),
			'selectCondition'=>array(
				'source'=>'class_id',
				'value'=>$class_id
			),
			'caption'=>array(
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);

    ?>
    </select>
    <span class="search-label">Nama Siswa</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_student`.`name` like '%".addslashes($pagination->query)."%' )";
}
if($class_id != 0)
{
	$pagination->array_get[] = 'class_id';
	$sql_filter .= " and (`edu_student`.`class_id` = '$class_id' )";
}

$nt = '';


$sql = "SELECT `edu_student`.* , `edu_class`.`name` as `class_id`, `edu_class`.`order` as `order`
from `edu_student`
left join(`edu_class`) on(`edu_class`.`class_id` = `edu_student`.`class_id`)
where 1 and `edu_student`.`school_id` = '$school_id' $sql_filter
order by `order` asc, `edu_student`.`name` asc
";
$sql_test = "SELECT `edu_student`.*
from `edu_student`
where 1 and `edu_student`.`school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = "";
foreach($pagination->result as $i=>$obj)
{
$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
?>

<div class="dialogs" style="display:none">
<div class="dialog-update-class">
	<div class="dialog-update-class-inner">
    	<form name="updateclassfrm" id="updateclassfrm" action="" method="post">
        	<div class="input-label" style="padding:4px 0;">Kelas Baru</div>
            <div class="input-control">
            	<select class="input-select" name="class_id" id="class_id" style="width:100%; box-sizing:border-box;" required="required">
                <option value="">- Pilih Kelas Baru -</option>
                <?php 
                $sql2 = "select * from `edu_class` where `active` = '1' and `school_id` = '$school_id' order by `order` asc ";
                echo $picoEdu->createFilterDb(
					$sql2,
					array(
						'attributeList'=>array(
							array('attribute'=>'value', 'source'=>'class_id')
						),
						'selectCondition'=>array(
							'source'=>'class_id',
							'value'=>$class_id
						),
						'caption'=>array(
							'delimiter'=>' &raquo; ',
							'values'=>array(
								'name'
							)
						)
					)
				);
			
                ?>
                </select>
                <input type="hidden" name="students" id="students" value="" />
                <input type="hidden" name="setclass" value="setclass" />
            </div>
        	<div class="input-label" style="padding:4px 0;">Dipilih <span id="number-of-student"></span> Siswa</div>
        </form>
    </div>
</div>
</div>

<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
</style>

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-student_id" id="control-student_id" class="checkbox-selector" data-target=".student_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Ubah" border="0" /></td>
      <td width="25">No</td>
      <td>NIS</td>
      <td>NISN</td>
      <td>Nama</td>
      <td>Tingkat</td>
      <td>Kelas</td>
      <td>L/P</td>
      <td>Blokir</td>
      <td>Aktif</td>
</tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr<?php echo (@$data['active'])?" class=\"data-active\"":" class=\"data-inactive\"";?>>
      <td><input type="checkbox" name="student_id[]" id="student_id" value="<?php echo $data['student_id'];?>" class="student_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&student_id=<?php echo $data['student_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Ubah" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['reg_number'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['reg_number_national'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['class_id'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['gender']);?></a></td>
      <td><?php echo $data['blocked']?'Ya':'Tidak';?></td>
      <td><?php echo $data['active']?'Ya':'Tidak';?></td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="com-button" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="com-button" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="setclass" id="setclass" value="Atur Kelas" class="com-button" />
  </div>
</form>
<?php
}
else if(@$_GET['q'])
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>