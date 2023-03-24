<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	$sql = "SELECT * FROM `edu_admin` ";
	$stmt = $database->executeQuery($sql);
	$admin_id = $database->generateNewId();
	$username = 'admin';
	$password = 'admin';
	$passwordSession = md5($password);
	$passwordDatabase = md5($passwordSession);
	if($stmt->rowCount() == 0)
	{
		$sql = "INSERT INTO `edu_admin` 
		(`admin_id`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `username`, `admin_level`, `token_admin`, `email`, `phone`, `address`, `country_id`, `state_id`, `city_id`, `password`, `password_initial`, `auth`, `picture_rand`, `time_create`, `time_edit`, `time_last_activity`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `ip_last_activity`, `blocked`, `active`) VALUES
		('$admin_id', '', 'Admin', 'M', 'Jambi', '2000-01-01', '$username', 1, '$passwordDatabase', 'admin@local', '', '', 0, 0, 0, 'c3284d0f94606de1fd2af172aba15bf3', 'admin', '', '742251', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '0', '$admin_id', '127.0.0.1', '127.0.0.1', '127.0.0.1', 0, 1)";
		$stmt = $database->executeInsert($sql, true);
		if($stmt->rowCount() > 0)
		{
			$_SESSION['admin_username'] = $username;
			$_SESSION['admin_password'] = $passwordSession;
			usleep(10000);
			header("Location: ".basename($_SERVER['PHP_SELF']));
		}
	}
	require_once __DIR__."/login-form.php";
	exit();
}

/**
 * Import data preprocessor
 */
class ImportExcel {

	/**
	 * Check if school use national ID or not
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @return bool true if school use national ID and false if school is not use national ID
	 */
	public function isUseNationalId($objWorksheetSource, $sheetNameSchool)
	{
		$useNationalId = false;
		try
		{
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetNameSchool);
			$highestRow = $objWorksheet->getHighestRow(); 
			$highestColumn = $objWorksheet->getHighestColumn(); 
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			
			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for($row = 2; $row <= $highestRow; ++$row) 
			{
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) 
				{
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if(
					strtolower($data['use_national_id'])
					|| strtolower($data['use_national_id']) == 1
					|| strtolower($data['use_national_id']) == 'y' 
					|| strtolower($data['use_national_id']) == 'yes'
					|| strtolower($data['use_national_id']) == 'ya'
					|| strtolower($data['use_national_id']) == 'true'
					)
				{
					$useNationalId = true;
					break;
				}
			}
		}
		catch(Exception $e)
		{
			// Do nothing
		}
		return $useNationalId;
	}

	/**
	 * Validate imported data
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @param string $sheetNameStudent Sheet name for student
	 * @param string $columnNameStudent Lower case of column name name student ID
	 * @param string $sheetNameTeacher Sheet name for teacher
	 * @param string $columnNameTeacher Lower case of column name name teacher ID
	 * @return array Contain response_code and response_text
	 */
	public function validate($objWorksheetSource, $sheetNameSchool, $sheetNameStudent, $columnNameStudent, $sheetNameTeacher, $columnNameTeacher)
	{
		$useNationalId = $this->isUseNationalId($objWorksheetSource, $sheetNameSchool);
		$validData1 = true;
		$validData2 = true;		
		$message = "Sukses";
		$response_code = "00";

		if($useNationalId)
		{
			$validData1 = $this->validData($objWorksheetSource, $sheetNameStudent, $columnNameStudent);
			$validData2 = $this->validData($objWorksheetSource, $sheetNameTeacher, $columnNameTeacher);		

			if($useNationalId)
			{
				if(!$validData1 && !$validData2)
				{
					$message = "Data siswa dan guru tidak lengkap";
					$response_code = "05";
				}
				else if(!$validData1)
				{
					$message = "Data siswa tidak lengkap";
					$response_code = "05";
				}
				else if(!$validData2)
				{
					$message = "Data guru tidak lengkap";
					$response_code = "05";
				}
				if($validData1 && $validData2)
				{
					$response_code = "00";
				}
			}
			
		}
		return array(
			'response_code'=>$response_code,
			'response_text'=>$message
		);
	}

	public function validData($objWorksheetSource, $sheetName, $columnName)
	{
		$validData = true;
		try
		{
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetName);
			$highestRow = $objWorksheet->getHighestRow(); 
			$highestColumn = $objWorksheet->getHighestColumn(); 
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			
			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for($row = 2; $row <= $highestRow; ++$row) 
			{
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) 
				{
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if(empty($data[$columnName]))
				{
					$validData = false;
					break;
				}
			}
		}
		catch(Exception $e)
		{
			// Do nothing
		}
		return $validData;
	}
	
	public function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}

if(isset($_POST['upload']) && isset($_FILES['file']['name']))
{ 
	if(isset($_FILES['file']['tmp_name']))
	{
		require_once dirname(__DIR__)."/lib.inc/import-data.php";
	}
	exit();
}

$pageTitle = "Impor Data";

if(@$_GET['option'] == 'success')
{
if(isset($_GET['school_id']))
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$nt = '';

$sql = "SELECT `edu_school`.* $nt,
(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_school`.`admin_create` limit 0,1) AS `admin_create`,
(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_school`.`admin_edit` limit 0,1) AS `admin_edit`,
(SELECT `edu_admin3`.`name` FROM `edu_admin` AS `edu_admin3` WHERE `edu_admin3`.`admin_id` = `edu_school`.`admin_import_first` limit 0,1) AS `admin_import_first`,
(SELECT `edu_admin4`.`name` FROM `edu_admin` AS `edu_admin4` WHERE `edu_admin4`.`admin_id` = `edu_school`.`admin_import_last` limit 0,1) AS `admin_import_last`,
(SELECT COUNT(DISTINCT `edu_admin`.`admin_id`) FROM `edu_admin` WHERE `edu_admin`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_admin`.`school_id` limit 0,1) AS `num_admin`,
(SELECT COUNT(DISTINCT `edu_class`.`class_id`) FROM `edu_class` WHERE `edu_class`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_class`.`school_id` limit 0,1) AS `num_class`,
(SELECT COUNT(DISTINCT `edu_teacher`.`teacher_id`) FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_teacher`.`school_id` limit 0,1) AS `num_teacher`,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_student`.`school_id` limit 0,1) AS `num_student`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formschool" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><?php echo $data['school_code'];?> </td>
		</tr>
		<tr>
		<td>Jenjang Pendidikan</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?> </td>
		</tr>
		<tr>
		<td>Negeri Swasta</td>
		<td><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?> </td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?> </td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
        <tr>
          <td>Jumlah Kelas</td>
          <td><?php echo $data['num_class'];?> </td>
        </tr>
        <tr>
          <td>Jumlah Siswa</td>
          <td><?php echo $data['num_student'];?> orang</td>
        </tr>
        <tr>
          <td>Jumlah Guru</td>
          <td><?php echo $data['num_teacher'];?> orang</td>
        </tr>
		<tr>
		  <td>Jumlah Admin</td>
		  <td><?php echo $data['num_admin'];?> orang</td>
      </tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><?php echo $picoEdu->trueFalse($data['prevent_change_school'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><?php echo $picoEdu->trueFalse($data['prevent_resign'], 'Ya', 'Tidak');?> </td>
		</tr>

		<tr>
		<td>Impor Pertama</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_import_first'])));?> </td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_first'];?> </td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_first'];?> </td>
		</tr>

		<tr>
		<td>Impor Terakhir</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_import_last'])));?> </td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_last'];?> </td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_last'];?> </td>
		</tr>


		<tr>
		<td>Waktu Buat</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Waktu Ubah</td>
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
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="update" id="update" value="Ubah Data" class="btn btn-primary" onclick="window.location='sekolah-profil.php?option=edit'" />		  
		<input type="button" name="import" id="import" value="Impor Data" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
exit();
}
}
else if(@$_GET['option'] == 'duplicated')
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-warning">GAGAL! Data sekolah dengan name yang sama telah dimasukkan sebelumnya. Mohon periksa kembali data yang Anda masukkan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Impor lagi</a>.</div>
<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
else
{
	
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-success">
Modul ini digunakan untuk mengimpor data sekolah, kelas, siswa, dan guru. Contoh data dapat didownload <a href="planetedu.xlsx">di sini</a>. Apabila terjadi kesalahan saat melakukan import data, segera hapus data tersebut sebelum mengimpor data yang lain.</div>
<p>Pilih file</p>
<style type="text/css">
.input-group > *:first-child{
	border-top-right-radius: 0 0;
	border-bottom-right-radius: 0 0;
}
.input-group > *:last-child{
	border-top-left-radius: 0 0;
	border-bottom-left-radius: 0 0;
}
.input-file-label.form-control[readonly]{
	background-color: #FFFFFF;
}
</style>
<form action="" method="post" enctype="multipart/form-data" name="form1">
	
  <input type="file" name="file" id="file" accept=".xlsx" style="position:absolute; left:-10000px; top:-10000px;">
  <div class="input-group mb-3" id="input-file-data">
  	<input type="button" class="btn btn-secondary input-file-button" value="Pilih File" />
    <input type="text" class="input-file-label form-control" readonly>
  </div>
  <input class="btn btn-success" type="submit" name="upload" id="upload" value="Upload File">
  <input class="btn btn-secondary" type="button" name="cancel" id="cancel" value="Batalkan" onclick="window.location='sekolah.php'">
</form>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', 'input[type="file"]', function(e){
		var files = $(this)[0].files;
		var fileName = files[0].name;
		$(this).closest('form').find('.input-file-label').val(fileName);
	});
    $(document).on('click', 'input.input-file-button', function(e){
		$(this).closest('form').find('input[type="file"]').click();
	});
});
</script>
<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
?>
