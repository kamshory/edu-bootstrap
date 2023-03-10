<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
if(!isset($school_id) || empty($school_id)) {
	require_once dirname(__FILE__) . "/bukan-admin.php";
	exit();
}
if(empty($real_school_id)) {
	require_once dirname(__FILE__) . "/belum-ada-sekolah.php";
	exit();
}
$pageTitle = "Kelas";

$pagination = new \Pico\PicoPagination();
if (count(@$_POST) && isset($_POST['save'])) {
	$class_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_STRING_NEW);
	$class_id2 = kh_filter_input(INPUT_POST, "class_id2", FILTER_SANITIZE_STRING_NEW);
	if (!isset($_POST['class_id'])) {
		$class_id = $class_id2;
	}
	$class_code = kh_filter_input(INPUT_POST, "class_code", FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, "grade_id", FILTER_SANITIZE_STRING_NEW);
	$school_program_id = kh_filter_input(INPUT_POST, "school_program_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$sort_order = kh_filter_input(INPUT_POST, "sort_order", FILTER_SANITIZE_NUMBER_INT);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
}

if (isset($_POST['set_active']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		foreach ($classs as $key => $val) {
			$class_id = addslashes($val);
			$sql = "UPDATE `edu_class` SET `active` = true WHERE `class_id` = '$class_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}
if (isset($_POST['set_inactive']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		$class_id = addslashes($val);
		$sql = "UPDATE `edu_class` SET `active` = false WHERE `class_id` = '$class_id' AND `school_id` = '$school_id' ";
		$database->executeUpdate($sql, true);
	}
}
if (isset($_POST['delete']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		foreach ($classs as $key => $val) {
			$class_id = addslashes($val);
			$sql = "DELETE FROM `edu_class` WHERE `class_id` = '$class_id' AND `school_id` = '$school_id' ";
			$database->executeDelete($sql, true);
		}	
	}
}

if (isset($_POST['save']) && @$_GET['option'] == 'add') {
	$token_class = md5($school_id . '-' . $name . '-' . time() . '-' . mt_rand(111111, 999999));
	$class_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_class` 
	(`class_id`, `school_id`, `class_code`, `token_class`, `grade_id`, `school_program_id`, `name`, `time_create`, `time_edit`, 
	`admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `sort_order`, `active`) VALUES
	('$class_id', '$school_id', '$class_code', '$token_class', '$grade_id', '$school_program_id', '$name', '$time_create', '$time_edit', 
	'$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$sort_order', '$active')";
	$database->executeInsert($sql, true);
	$id = $database->getDatabaseConnection()->lastInsertId();
	if ($id == 0) {
		$id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_STRING_NEW);
	}
	header("Location: " . $picoEdu->gateBaseSelfName() . "?option=detail&class_id=$id");
}
if (isset($_POST['save']) && @$_GET['option'] == 'edit') {
	$sql = "UPDATE `edu_class` SET 
	`class_code` = '$class_code', `grade_id` = '$grade_id', `school_program_id` = '$school_program_id', `name` = '$name', 
	`time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', 
	`ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `sort_order` = '$sort_order', `active` = '$active'
	WHERE `class_id` = '$class_id2' AND `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	header("Location: " . $picoEdu->gateBaseSelfName() . "?option=detail&class_id=$class_id");
}
if (@$_GET['option'] == 'add') {
	require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
?>
	<form name="formedu_class" id="formedu_class" action="" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
				<td>Kode Kelas</td>
				<td><input type="text" class="form-control input-text" name="class_code" id="class_code" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Tingkat
				</td>
				<td><select class="form-control input-select" name="grade_id" id="grade_id">
						<option value=""></option>
						<?php
						echo $picoEdu->createGradeOption(null);
						?>
					</select></td>
			</tr>
			<tr>
				<td>Jurusan</td>
				<td>
					<select class="form-control" name="school_program_id" id="school_program_id">
						<option value=""></option>
						<?php
							$sql2 = "SELECT `edu_school_program`.*
							FROM `edu_school_program`
							WHERE `edu_school_program`.`school_id` = '$school_id' AND `active` = true 
							ORDER BY `edu_school_program`.`name` ASC
							";
							echo $picoEdu->createFilterDb(
								$sql2,
								array(
									'attributeList'=>array(
										array('attribute'=>'value', 'source'=>'school_program_id')
									),
									'selectCondition'=>array(
										'source'=>'school_program_id',
										'value'=>null
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
				</td>
			</tr>
			<tr>
				<td>Nama Kelas
				</td>
				<td><input type="text" class="form-control input-text" name="name" id="name" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Order</td>
				<td><input type="number" class="form-control input-text" name="sort_order" id="sort_order" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Aktif</td>
				<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Aktif</label></td>
			</tr>
		</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
				<td></td>
				<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
				<input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
			</tr>
		</table>
	</form>
	<?php getDefaultValues($database, 'edu_class', array('grade_id', 'school_program_id', 'active')); ?>
	<?php
	require_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
} 
else if (@$_GET['option'] == 'edit') 
{
	require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
	$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_class`.* 
	FROM `edu_class` 
	WHERE `edu_class`.`school_id` = '$school_id'
	AND `edu_class`.`class_id` = '$edit_key'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	?>
		<form name="formedu_class" id="formedu_class" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Kode Kelas</td>
					<td><input type="text" class="form-control input-text" name="class_code" id="class_code" value="<?php echo $data['class_code']; ?>" autocomplete="off" /><input type="hidden" name="class_id2" id="class_id2" value="<?php echo $data['class_id'];?>" /></td>
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
					<td>Jurusan</td>
					<td>
						<select class="form-control" name="school_program_id" id="school_program_id">
							<option value=""></option>
							<?php
							$sql2 = "SELECT `edu_school_program`.*
							FROM `edu_school_program`
							WHERE `edu_school_program`.`school_id` = '$school_id' AND `active` = true 
							ORDER BY `edu_school_program`.`name` ASC
							";
							echo $picoEdu->createFilterDb(
								$sql2,
								array(
									'attributeList'=>array(
										array('attribute'=>'value', 'source'=>'school_program_id')
									),
									'selectCondition'=>array(
										'source'=>'school_program_id',
										'value'=>$data['school_program_id']
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
					</td>
				</tr>
				<tr>
					<td>Nama Kelas
					</td>
					<td><input type="text" class="form-control input-text" name="name" id="name" value="<?php echo $data['name']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Order</td>
					<td><input type="number" class="form-control input-text" name="sort_order" id="sort_order" value="<?php echo $data['sort_order'];?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Aktif</td>
					<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php echo $picoEdu->ifMatch($data['active'], true, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED);?>> Aktif</label></td>
				</tr>
			</table>
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td></td>
					<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
					<input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>
	<?php
	}
	require_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
} else if (@$_GET['option'] == 'print') {
	require_once dirname(__FILE__) . "/cetak-login-siswa.php";
} 
else if (@$_GET['option'] == 'detail') 
{
	require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
	$edit_key = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_class`.* $nt,
	(SELECT `edu_school_program`.`name` FROM `edu_school_program` WHERE `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) AS `school_program_id`,
	(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_class`.`admin_create` limit 0,1) AS `admin_create`,
	(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_class`.`admin_edit` limit 0,1) AS `admin_edit`
	FROM `edu_class` 
	WHERE `edu_class`.`school_id` = '$school_id'
	AND `edu_class`.`class_id` = '$edit_key'
	";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	?>
		<form name="formedu_class" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Kode Kelas</td>
					<td><?php echo $data['class_code'];?> </td>
				</tr>
				<tr>
					<td>Tingkat
					</td>
					<td><?php
						echo $picoEdu->getGradeName($data['grade_id']);
						?>
					<td>
				</tr>
				<tr>
					<td>Jurusan</td>
					<td><?php echo $data['school_program_id'];?> </td>
				</tr>
				<tr>
					<td>Nama Kelas
					</td>
					<td><?php echo $data['name'];?> </td>
				</tr>
				<tr>
					<td>Time Create</td>
					<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
				</tr>
				<tr>
					<td>Time Edit</td>
					<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
				</tr>
				<tr>
					<td>Admin Create</td>
					<td><?php echo $data['admin_create'];?> </td>
				</tr>
				<tr>
					<td>Admin Edit</td>
					<td><?php echo $data['admin_edit'];?> </td>
				</tr>
				<tr>
					<td>IP Create</td>
					<td><?php echo $data['ip_create'];?> </td>
				</tr>
				<tr>
					<td>IP Edit</td>
					<td><?php echo $data['ip_edit'];?> </td>
				</tr>
				<tr>
					<td>Order</td>
					<td><?php echo $data['sort_order'];?> </td>
				</tr>
				<tr>
					<td>Aktif
					</td>
					<td><?php echo $data['active'] ? 'Ya' : 'Tidak';?> </td>
				</tr>
			</table>
			<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td></td>
					<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&class_id=<?php echo $data['class_id'];?>'" /> 
					<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>
	<?php
	}
	require_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
} else {
	require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR

	$school_program_id = kh_filter_input(INPUT_GET, "school_program_id", FILTER_SANITIZE_STRING_NEW);

	?>
	<script type="text/javascript">
	window.onload = function()
	{
		$(document).on('change', '#searchform select', function(){
			$(this).closest('form').submit();
		});
	}
	</script>
	<div class="search-control">
		<form id="searchform" name="form1" method="get" action="">

		<span class="search-label">Jurusan</span> 
		<select class="form-control input-select" name="school_program_id" id="school_program_id">
		<option value=""></option>
		<?php 
		$sql2 = "SELECT `edu_school_program`.* FROM `edu_school_program` WHERE `edu_school_program`.`school_id` = '$school_id' ORDER BY `name` ASC ";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'school_program_id')
				),
				'selectCondition'=>array(
					'source'=>'school_program_id',
					'value'=>$school_program_id
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

			<span class="search-label">Nama Kelas</span>
			<input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
			<input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
		</form>
	</div>
	<div class="search-result">
		<?php
		$sql_filter = "";
		
		if($pagination->getQuery()) {
			$pagination->appendQueryName('q');
			$sql_filter .= " AND (`edu_class`.`name` like '%" . addslashes($pagination->getQuery()) . "%' )";
		}
		if($school_program_id != '')
		{
			$pagination->appendQueryName('school_program_id');
			$sql_filter .= " and `edu_class`.`school_program_id` = '$school_program_id' ";
		}

		$nt = '';

		$sql = "SELECT `edu_class`.* $nt,
		(SELECT `edu_school`.`name` FROM `edu_school` WHERE `edu_school`.`school_id` = `edu_class`.`school_id` limit 0,1) AS `school_name`,
		(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`class_id` = `edu_class`.`class_id`) AS `num_student`,
		`edu_school_program`.`name` AS `school_program`
		FROM `edu_class`
		LEFT JOIN (`edu_school_program`) ON (`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`) 
		WHERE `edu_class`.`school_id` = '$school_id' $sql_filter
		ORDER BY `edu_class`.`school_id` DESC, `edu_school_program`.`sort_order` ASC, `edu_class`.`sort_order` ASC
		";

		$sql_test = "SELECT `edu_class`.*
		FROM `edu_class`
		WHERE `edu_class`.`school_id` = '$school_id' $sql_filter
		";
		$stmt = $database->executeQuery($sql_test);
		$pagination->setTotalRecord($stmt->rowCount());
		$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
		$pagination->setTotalRecordWithLimit($stmt->rowCount());
		if($pagination->getTotalRecordWithLimit() > 0) {
			
			

			$pagination->createPagination($picoEdu->gateBaseSelfName(), true);
      $paginationHTML = $pagination->buildHTML();

		?>
			<form name="form1" method="post" action="">
				<style type="text/css">
					@media screen and (max-width:800px) {

						.hide-some-cell tr td:nth-child(4),
						.hide-some-cell tr td:nth-child(8),
						.hide-some-cell tr td:nth-child(10) {
							display: none;
						}
					}
				</style>

				<div class="d-flex search-pagination search-pagination-top">
					<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
					<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
				</div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
					<thead>
						<tr>
							<td width="16"><input type="checkbox" name="control-class_id" id="control-class_id" class="checkbox-selector" data-target=".class_id" value="1"></td>
							<td width="16"><i class="fas fa-pencil"></i></td>
							<td width="16"><i class="fas fa-print"></i></td>
							<td width="25">No</td>
							<td>Kode Kelas</td>
							<td>Nama Kelas</td>
							<td>Tingkat</td>
							<td>Jurusan</td>
							<td>Siswa</td>
							<td>Order</td>
							<td>Aktif
							</td>
						</tr>
					</thead>
					<tbody>
						<?php
						$no = $pagination->getOffset();
						$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
						foreach($rows as $data) {
							$no++;
						?>
							<tr class="<?php echo $picoEdu->getRowClass($data);?>">
								<td><input type="checkbox" name="class_id[]" id="class_id" value="<?php echo $data['class_id'];?>" class="class_id" /></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&class_id=<?php echo $data['class_id'];?>"><i class="fas fa-pencil"></i></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=print&class_id=<?php echo $data['class_id'];?>" target="_blank"><i class="fas fa-print"></i></a></td>
								<td align="right"><?php echo $no;?> </td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['class_code']; ?></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['name']; ?></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['grade_id']; ?></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['school_program']; ?></a></td>
								<td><a href="siswa.php?class_id=<?php echo $data['class_id'];?>"><?php echo $data['num_student']; ?></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['sort_order'];?></a></td>
								<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['active'] ? 'Ya' : 'Tidak'; ?></a></td>
								</tr>
							<?php
						}
							?>
					</tbody>
				</table>

				<div class="d-flex search-pagination search-pagination-bottom">
					<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
					<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
				</div>

				<div class="button-area">
					<input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
					<input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
					<input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
					<input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=add'" />
				</div>
			</form>
		<?php
		} else if (@$_GET['q'] != '') {
		?>
			<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
		<?php
		} else {
		?>
			<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=add">Klik di sini untuk membuat baru.</a></div>
		<?php
		}
		?>
	</div>

<?php
	require_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
}
?>