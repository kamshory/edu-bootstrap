<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
if(empty($real_school_id))
{
	require_once dirname(__FILE__)."/belum-ada-sekolah.php";
	exit();
}
$pageTitle = "Perubahan Kelas Siswa";
$pagination = new \Pico\PicoPagination();
if(isset($_POST['setclass']) && isset($_POST['students']))
{
	$students = $_POST['students'];
	$class_id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_STRING_NEW);
	if(is_array($students))
	{
		foreach($students as $key=>$val)
		{
			$student_id = addslashes($val);
			$sql = "UPDATE `edu_student` SET `class_id` = '$class_id', 
			`grade_id` = (SELECT `edu_class`.`grade_id` FROM `edu_class` WHERE `edu_class`.`class_id` = '$class_id')
			WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(count(@$_POST) && isset($_POST['save']))
{
	$student_id = kh_filter_input(INPUT_POST, "student_id", FILTER_SANITIZE_STRING_NEW);
	$student_id2 = kh_filter_input(INPUT_POST, "student_id2", FILTER_SANITIZE_STRING_NEW);
	if(!isset($_POST['student_id']))
	{
		$student_id = $student_id2;
	}
	$reg_number = kh_filter_input(INPUT_POST, "reg_number", FILTER_SANITIZE_SPECIAL_CHARS);
	$reg_number_national = kh_filter_input(INPUT_POST, "reg_number_national", FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_STRING_NEW);
	$class_id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, "birth_place", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, "birth_day", FILTER_SANITIZE_STRING_NEW);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	$address = kh_filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
	$religion_id = kh_filter_input(INPUT_POST, "religion_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$blocked = kh_filter_input(INPUT_POST, "blocked", FILTER_SANITIZE_NUMBER_UINT);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$admin_create = $admin_edit = $admin_id;
}
if(isset($_POST['setclass']) && isset($_POST['students']) && isset($_POST['class_id']))
{
	$class_id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `grade_id` FROM `edu_class` WHERE `class_id` = '$class_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$grade_id = $data['grade_id'];
		$students_str = @$_POST['students'];
		if (isset($students_str)) {
			$students = explode(",", $students_str);
			if (is_array($students)) {
				foreach ($students as $key => $val) {
					$student_id = addslashes($val);
					$sql = "UPDATE `edu_student` SET `class_id` = '$class_id', `grade_id` = '$grade_id' 
					WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
					$database->executeUpdate($sql, true);
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
			$sql = "UPDATE `edu_student` SET `active` = true WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
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
			$sql = "UPDATE `edu_student` SET `active` = false WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
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
			$sql = "DELETE FROM `edu_student` WHERE `student_id` = '$student_id' AND `school_id` = '$school_id' ";
			$database->executeDelete($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql = "UPDATE `edu_student` SET 
	`reg_number` = '$reg_number', `reg_number_national` = '$reg_number_national', `grade_id` = '$grade_id', `class_id` = '$class_id', `name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
	WHERE `student_id` = '$student_id2' AND `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	if($email != '')
	{
		$sql = "UPDATE `edu_student` SET 
		`email` = '$email'
		WHERE `student_id` = '$student_id2' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	if($password != '')
	{
		$passwordHash = md5(md5($password));
		$sql = "UPDATE `edu_student` SET 
		`password` = '$passwordHash', `password_initial` = '$password'
		WHERE `student_id` = '$student_id2' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&student_id=$student_id2");
}
if(@$_GET['option'] == 'edit')
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$edit_key = kh_filter_input(INPUT_GET, "student_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_student`.* 
	FROM `edu_student` 
	WHERE `edu_student`.`school_id` = '$school_id' 
	AND `edu_student`.`student_id` = '$edit_key'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_student" id="formedu_student" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>NIS</td>
		<td><input type="text" class="form-control input-text" name="reg_number" id="reg_number" value="<?php echo $data['reg_number'];?>" autocomplete="off" /><input type="hidden" name="student_id2" id="student_id2" value="<?php echo $data['student_id'];?>" /></td>
		</tr>
		<tr>
		<td>NISN</td>
		<td><input type="text" class="form-control input-text" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><select class="form-control input-select" name="grade_id" id="grade_id">
		<option value=""></option>
		<?php
		echo $picoEdu->createGradeOption($data['grade_id']);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><select class="form-control input-select" name="class_id" id="class_id">
		<option value=""></option>
		<?php 
		$sql2 = "SELECT * FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' ORDER BY `sort_order` ASC ";
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
					'delimiter'=>\Pico\PicoConst::RAQUO,
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
		<td><input type="text" class="form-control input-text" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><select class="form-control input-select" name="gender" id="gender">
		<option value=""></option>
		<option value="M"<?php echo $picoEdu->ifMatch($data['gender'], 'M', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Laki-Laki</option>
		<option value="W"<?php echo $picoEdu->ifMatch($data['gender'], 'W', \Pico\PicoConst::SELECT_OPTION_SELECTED);?>>Perempuan</option>
		</select></td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><input type="text" class="form-control input-text" name="birth_place" id="birth_place" value="<?php echo $data['birth_place'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo $data['birth_day'];?>" autocomplete="off" /><span class="date-format-tip"> TTTT-BB-HH</span></td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><input type="tel" class="form-control input-text" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="form-control input-text" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input type="password" class="form-control input-text" name="password" id="password" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea name="address" class="form-control input-text" id="address" autocomplete="off"><?php echo $data['address'];?></textarea></td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php echo $picoEdu->ifMatch($data['blocked'], true, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED);?>> Blokir</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label>
		</td>
		</tr>
		</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else if(@$_GET['option'] == 'detail')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "student_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_student`.* ,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_edit`) AS `admin_edit`,
(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` limit 0,1) AS `class_id`
FROM `edu_student` 
WHERE `edu_student`.`school_id` = '$school_id' 
AND `edu_student`.`student_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_student" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>NIS</td>
		<td><?php echo $data['reg_number'];?> </td>
		</tr>
		<tr>
		<td>NISN</td>
		<td><?php echo $data['reg_number_national'];?> </td>
		</tr>
		<tr>
		<td>Tingkat</td>
		<td><?php echo $picoEdu->getGradeName($data['grade_id']);?><td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class_id'];?> </td>
		</tr>
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?> </td>
		</tr>
		<tr>
		<td>Tempat Lahir</td>
		<td><?php echo $data['birth_place'];?> </td>
		</tr>
		<tr>
		<td>Tanggal Lahir</td>
		<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?> </td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
		<tr>
		<td>Password
		</td><td><?php echo $data['password_initial'];?> </td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?> </td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
		<tr>
		<td>Blokir</td>
		<td><?php echo $picoEdu->trueFalse($data['blocked'], 'Ya', 'Tidak')?> </td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&student_id=<?php echo $data['student_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR

}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
?>


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
  <select class="form-control input-select" name="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
    $sql2 = "SELECT * FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' ORDER BY `sort_order` ASC ";
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
				'delimiter'=>\Pico\PicoConst::RAQUO,
				'values'=>array(
					'name'
				)
			)
		)
	);

    ?>
    </select>
	<span class="search-label">Nama Siswa</span>
	<input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
	<input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
	$pagination->appendQueryName('q');
	$sql_filter .= " AND (`edu_student`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}
if($class_id != 0)
{
	$pagination->appendQueryName('class_id');
	$sql_filter .= " AND (`edu_student`.`class_id` = '$class_id' )";
}

$nt = '';

$sql = "SELECT `edu_student`.* , `edu_class`.`name` AS `class_id`, `edu_class`.`sort_order` AS `sort_order`
FROM `edu_student`
LEFT JOIN (`edu_class`) ON (`edu_class`.`class_id` = `edu_student`.`class_id`)
WHERE `edu_student`.`school_id` = '$school_id' $sql_filter
ORDER BY `sort_order` ASC, `edu_student`.`name` ASC
";
$sql_test = "SELECT `edu_student`.*
FROM `edu_student`
WHERE `edu_student`.`school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{



$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
$paginationHTML = $pagination->buildHTML();
?>

<div class="dialogs" style="display:none">
<div class="dialog-update-class">
	<div class="dialog-update-class-inner">
    	<form name="updateclassfrm" id="updateclassfrm" action="" method="post">
        	<div class="input-label" style="padding:4px 0;">Kelas Baru</div>
            <div class="input-control">
            	<select class="form-control input-select" name="class_id" id="class_id" style="width:100%; box-sizing:border-box;" required="required">
                <option value="">- Pilih Kelas Baru -</option>
                <?php 
                $sql2 = "SELECT * FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' ORDER BY `sort_order` ASC ";
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
							'delimiter'=>\Pico\PicoConst::RAQUO,
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

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-student_id" id="control-student_id" class="checkbox-selector" data-target=".student_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
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
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td><input type="checkbox" name="student_id[]" id="student_id" value="<?php echo $data['student_id'];?>" class="student_id" /></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&student_id=<?php echo $data['student_id'];?>"><i class="fas fa-pencil"></i></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['reg_number'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['reg_number_national'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['class_id'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['gender'];?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['blocked'], 'Ya', 'Tidak')?> </td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="setclass" id="setclass" value="Atur Kelas" class="btn btn-primary" />
  </div>
</form>
<?php
}
else if(@$_GET['q'] != '')
{
?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>