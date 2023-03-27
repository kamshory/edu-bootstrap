<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	require_once dirname(__DIR__)."/lib.inc/add-first-admin.php";
	require_once __DIR__."/login-form.php";
	exit();
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
$imported = $database->getSystemVariable('import-'.$school_id, 'false') == 'true';
if($imported)
{
	?>
	<div class="alert alert-success">Impor data siswa baru. <a href="tambah-siswa.php">Klik di sini</a> untuk mendownload template. Isi data siswa sesuai dengan kelasnya lalu upload kembali.</div>
	<p>Pilih file</p>
	<form action="tambah-siswa.php" method="post" enctype="multipart/form-data" name="form1">		
	  <input type="file" name="file" id="file" accept=".xlsx" style="position:absolute; left:-10000px; top:-10000px;">
	  <div class="input-group mb-3" id="input-file-data">
		  <input type="button" class="btn btn-secondary input-file-button" value="Pilih File" />
		<input type="text" class="input-file-label form-control" readonly>
	  </div>
	  <input class="btn btn-success" type="submit" name="upload" id="upload" value="Upload File">
	  <input class="btn btn-secondary" type="button" name="cancel" id="cancel" value="Batalkan" onclick="window.location='sekolah.php'">
	</form>
	
	<?php
}
else
{
?>
<div class="alert alert-success">
Modul ini digunakan untuk mengimpor data awal untuk sekolah, kelas, siswa, dan guru. Contoh data dapat didownload <a href="planetedu.xlsx">di sini</a>. Apabila terjadi kesalahan saat melakukan import data, segera hapus data tersebut sebelum mengimpor data yang lain.</div>
<p>Pilih file</p>

<form action="" method="post" enctype="multipart/form-data" name="form1">
	
  <input type="file" name="file" id="file" accept=".xlsx" style="position:absolute; left:-10000px; top:-10000px;">
  <div class="input-group mb-3" id="input-file-data">
  	<input type="button" class="btn btn-secondary input-file-button" value="Pilih File" />
    <input type="text" class="input-file-label form-control" readonly>
  </div>
  <input class="btn btn-success" type="submit" name="upload" id="upload" value="Upload File">
  <input class="btn btn-secondary" type="button" name="cancel" id="cancel" value="Batalkan" onclick="window.location='sekolah.php'">
</form>

<?php
}
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}

?>
