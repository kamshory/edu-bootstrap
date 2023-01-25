<?php
include_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
if (!@$school_id) {
	include_once dirname(__FILE__) . "/bukan-admin.php";
	exit();
}
if (!@$real_school_id) {
	include_once dirname(__FILE__) . "/belum-ada-sekolah.php";
	exit();
}
$cfg->module_title = "Daftar Kelas";
include_once dirname(dirname(__FILE__)) . "/lib.inc/cfg.pagination.php";
if (count(@$_POST) && isset($_POST['save'])) {
	$class_id = kh_filter_input(INPUT_POST, 'grade_id', FILTER_SANITIZE_STRING_NEW);
	$class_id2 = kh_filter_input(INPUT_POST, 'class_id2', FILTER_SANITIZE_NUMBER_UINT);
	if (!isset($_POST['class_id'])) {
		$class_id = $class_id2;
	}
	$class_code = kh_filter_input(INPUT_POST, 'class_code', FILTER_SANITIZE_SPECIAL_CHARS);
	$grade_id = kh_filter_input(INPUT_POST, 'grade_id', FILTER_SANITIZE_STRING_NEW);
	$school_program_id = kh_filter_input(INPUT_POST, 'school_program_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$order = kh_filter_input(INPUT_POST, 'order', FILTER_SANITIZE_NUMBER_INT);
	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);
}

if (isset($_POST['set_active']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		foreach ($classs as $key => $val) {
			$class_id = addslashes($val);
			$sql = "update `edu_class` set `active` = '1' where `class_id` = '$class_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if (isset($_POST['set_inactive']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		if (is_array($classs)) {
			$class_id = addslashes($val);
			$sql = "update `edu_class` set `active` = '0' where `class_id` = '$class_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}
if (isset($_POST['delete']) && isset($_POST['class_id'])) {
	$classs = @$_POST['class_id'];
	if (isset($classs) && is_array($classs)) {
		foreach ($classs as $key => $val) {
			$class_id = addslashes($val);
			$sql = "DELETE FROM `edu_class` where `class_id` = '$class_id' and `school_id` = '$school_id' ";
			$database->executeDelete($sql);
		}
	
	}
}

if (isset($_POST['save']) && @$_GET['option'] == 'add') {
	$token_class = md5($school_id . '-' . $name . '-' . time() . '-' . mt_rand(111111, 999999));
	$class_id = $database->generateNewId();
	$sql = "INSERT INTO `edu_class` 
	(`class_id`, `school_id`, `class_code`, `token_class`, `grade_id`, `school_program_id`, `name`, `time_create`, `time_edit`, 
	`admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `order`, `active`) values
	('$class_id', '$school_id', '$class_code', '$token_class', '$grade_id', '$school_program_id', '$name', '$time_create', '$time_edit', 
	'$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$order', '$active')";
	$database->executeInsert($sql);
	$id = $database->getDatabaseConnection()->lastInsertId();
	if ($id == 0) {
		$id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_NUMBER_UINT);
	}
	header("Location: " . basename($_SERVER['PHP_SELF']) . "?option=detail&class_id=$id");
}
if (isset($_POST['save']) && @$_GET['option'] == 'edit') {
	$sql = "update `edu_class` set 
	`class_code` = '$class_code', `grade_id` = '$grade_id', `school_program_id` = '$school_program_id', `name` = '$name', 
	`time_create` = '$time_create', `time_edit` = '$time_edit', `admin_create` = '$admin_create', `admin_edit` = '$admin_edit', 
	`ip_create` = '$ip_create', `ip_edit` = '$ip_edit', `order` = '$order', `active` = '$active'
	where `class_id` = '$class_id2' and `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	header("Location: " . basename($_SERVER['PHP_SELF']) . "?option=detail&class_id=$class_id");
}
if (@$_GET['option'] == 'add') {
	include_once dirname(__FILE__) . "/lib.inc/header.php";
?>
	<form name="formedu_class" id="formedu_class" action="" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
				<td>Kode Kelas</td>
				<td><input type="text" class="input-text input-text-long" name="class_code" id="class_code" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Tingkat
				</td>
				<td><select class="input-select" name="grade_id" id="grade_id">
						<option value=""></option>
						<?php
						echo $picoEdu->createGradeOption(null);
						?>
					</select></td>
			</tr>
			<tr>
				<td>Jurusan</td>
				<td>
					<select name="school_program_id" id="school_program_id">
						<option value=""></option>
						<?php
						$sql2 = "SELECT `edu_school_program`.*
						from `edu_school_program`
						where `edu_school_program`.`school_id` = '$school_id' and `active` = '1' 
						order by `edu_school_program`.`name` asc
						";
						$stmt2 = $database->executeQuery($sql2);
						if ($stmt2->rowCount() > 0) {
							$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							foreach ($rows as $data2) {
								?>
							<option value="<?php echo $data2['school_program_id']; ?>"><?php echo $data2['name']; ?></option>
						<?php
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Nama Kelas
				</td>
				<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Order</td>
				<td><input type="number" class="input-text input-text-short" name="order" id="order" autocomplete="off" /></td>
			</tr>
			<tr>
				<td>Aktif</td>
				<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Aktif</label></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>'" /></td>
			</tr>
		</table>
	</form>
	<?php getDefaultValues($database, 'edu_class', array('grade_id', 'school_program_id', 'active')); ?>
	<?php
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
} 
else if (@$_GET['option'] == 'edit') 
{
	include_once dirname(__FILE__) . "/lib.inc/header.php";
	$edit_key = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT `edu_class`.* 
	from `edu_class` 
	where `edu_class`.`school_id` = '$school_id'
	and `edu_class`.`class_id` = '$edit_key'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
		<form name="formedu_class" id="formedu_class" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Kode Kelas</td>
					<td><input type="text" class="input-text input-text-long" name="class_code" id="class_code" value="<?php echo ($data['class_code']); ?>" autocomplete="off" /><input type="hidden" name="class_id2" id="class_id2" value="<?php echo $data['class_id']; ?>" /></td>
				</tr>
				<tr>
					<td>Tingkat
					</td>
					<td><select class="input-select" name="grade_id" id="grade_id">
							<option value=""></option>
							<?php
							echo $picoEdu->createGradeOption($data['grade_id']);
							?>
						</select></td>
				</tr>
				<tr>
					<td>Jurusan</td>
					<td>
						<select name="school_program_id" id="school_program_id">
							<option value=""></option>
							<?php
							$sql2 = "SELECT `edu_school_program`.*
							from `edu_school_program`
							where `edu_school_program`.`school_id` = '$school_id' and `active` = '1' 
							order by `edu_school_program`.`name` asc
							";
							$stmt2 = $database->executeQuery($sql2);
							if ($stmt2->rowCount() > 0) {
								$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								foreach ($rows as $data2) {
									?>
								<option value="<?php echo $data2['school_program_id']; ?>" <?php if ($data['school_program_id'] == $data2['school_program_id'])
									   echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
							<?php
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Nama Kelas
					</td>
					<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name']; ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Order</td>
					<td><input type="number" class="input-text input-text-short" name="order" id="order" value="<?php echo ($data['order']); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<td>Aktif</td>
					<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active" <?php if ($data['active'] == 1) echo ' checked="checked"'; ?>> Aktif</label></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>'" /></td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>">Klik di sini untuk kembali.</a></div>
	<?php
	}
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
} else if (@$_GET['option'] == 'print') {
	include_once dirname(__FILE__) . "/cetak-login-siswa.php";
} 
else if (@$_GET['option'] == 'detail') 
{
	include_once dirname(__FILE__) . "/lib.inc/header.php";
	$edit_key = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_class`.* $nt,
	(select `edu_school_program`.`name` from `edu_school_program` where `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) as `school_program_id`,
	(select `edu_admin1`.`name` from `edu_admin` as `edu_admin1` where `edu_admin1`.`admin_id` = `edu_class`.`admin_create` limit 0,1) as `admin_create`,
	(select `edu_admin2`.`name` from `edu_admin` as `edu_admin2` where `edu_admin2`.`admin_id` = `edu_class`.`admin_edit` limit 0,1) as `admin_edit`
	from `edu_class` 
	where `edu_class`.`school_id` = '$school_id'
	and `edu_class`.`class_id` = '$edit_key'
	";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
		<form name="formedu_class" action="" method="post" enctype="multipart/form-data">
			<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Kode Kelas</td>
					<td><?php echo $data['class_code'];?></td>
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
					<td><?php echo $data['school_program_id'];?></td>
				</tr>
				<tr>
					<td>Nama Kelas
					</td>
					<td><?php echo $data['name'];?></td>
				</tr>
				<tr>
					<td>Time Create</td>
					<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
				</tr>
				<tr>
					<td>Time Edit</td>
					<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
				</tr>
				<tr>
					<td>Admin Create</td>
					<td><?php echo ($data['admin_create']);?></td>
				</tr>
				<tr>
					<td>Admin Edit</td>
					<td><?php echo ($data['admin_edit']);?></td>
				</tr>
				<tr>
					<td>IP Create</td>
					<td><?php echo ($data['ip_create']);?></td>
				</tr>
				<tr>
					<td>IP Edit</td>
					<td><?php echo ($data['ip_edit']);?></td>
				</tr>
				<tr>
					<td>Order</td>
					<td><?php echo $data['order'];?></td>
				</tr>
				<tr>
					<td>Aktif
					</td>
					<td><?php echo $data['active'] ? 'Ya' : 'Tidak';?></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=edit&class_id=<?php echo $data['class_id']; ?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>'" /></td>
				</tr>
			</table>
		</form>
	<?php
	} else {
	?>
		<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>">Klik di sini untuk kembali.</a></div>
	<?php
	}
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
} else {
	include_once dirname(__FILE__) . "/lib.inc/header.php";
	?>
	<div class="search-control">
		<form id="searchform" name="form1" method="get" action="">
			<span class="search-label">Nama Kelas</span>
			<input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q'], " 	
    ")))); ?>" />
			<input type="submit" name="search" id="search" value="Cari" class="com-button" />
		</form>
	</div>
	<div class="search-result">
		<?php
		$sql_filter = "";
		$pagination->array_get = array();
		if ($pagination->query) {
			$pagination->array_get[] = 'q';
			$sql_filter .= " and (`edu_class`.`name` like '%" . addslashes($pagination->query) . "%' )";
		}


		$nt = '';


		$sql = "SELECT `edu_class`.* $nt,
		(select `edu_school_program`.`name` from `edu_school_program` where `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) as `school_program_id`,
		(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`class_id` = `edu_class`.`class_id`) as `num_student`
		from `edu_class`
		where `edu_class`.`school_id` = '$school_id' $sql_filter
		order by `edu_class`.`order` asc
		";
				$sql_test = "SELECT `edu_class`.*
		from `edu_class`
		where `edu_class`.`school_id` = '$school_id' $sql_filter
		";
		$stmt = $database->executeQuery($sql_test);
		$pagination->total_record = $stmt->rowCount();
		$stmt = $database->executeQuery($sql . $pagination->limit_sql);
		$pagination->total_record_with_limit = $stmt->rowCount();
		if ($pagination->total_record_with_limit) {
			$pagination->start = $pagination->offset + 1;
			$pagination->end = $pagination->offset + $pagination->total_record_with_limit;

			$pagination->result = $picoEdu->createPagination(
				basename($_SERVER['PHP_SELF']),
				$pagination->total_record,
				$pagination->limit,
				$pagination->num_page,
				$pagination->offset,
				$pagination->array_get,
				true,
				$pagination->str_first,
				$pagination->str_last,
				$pagination->str_prev,
				$pagination->str_next
			);
			$pagination->str_result = "";
			foreach ($pagination->result as $i => $obj) {
				$cls = ($obj->sel) ? " class=\"pagination-selected\"" : "";
				$pagination->str_result .= "<a href=\"" . $obj->ref . "\"$cls>" . $obj->text . "</a> ";
			}
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

				<div class="search-pagination search-pagination-top">
					<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
					<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
				</div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
					<thead>
						<tr>
							<td width="16"><input type="checkbox" name="control-class_id" id="control-class_id" class="checkbox-selector" data-target=".class_id" value="1"></td>
							<td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
							<td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-print-16" alt="Print" border="0" /></td>
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
						$no = $pagination->offset;
						$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach($rows as $data) {
							$no++;
						?>
							<tr<?php echo (@$data['active']) ? " class=\"data-active\"" : " class=\"data-inactive\""; ?>>
								<td><input type="checkbox" name="class_id[]" id="class_id" value="<?php echo $data['class_id']; ?>" class="class_id" /></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=edit&class_id=<?php echo $data['class_id']; ?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=print&class_id=<?php echo $data['class_id']; ?>" target="_blank"><img src="lib.tools/images/trans.gif" class="icon-16 icon-print-16" alt="Print" border="0" /></a></td>
								<td align="right"><?php echo $no;?></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo ($data['class_code']); ?></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo $data['name']; ?></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo ($data['grade_id']); ?></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo ($data['school_program_id']); ?></a></td>
								<td><a href="siswa.php?class_id=<?php echo $data['class_id']; ?>"><?php echo ($data['num_student']); ?></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo ($data['order']); ?></a></td>
								<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&class_id=<?php echo $data['class_id']; ?>"><?php echo $data['active'] ? 'Ya' : 'Tidak'; ?></a></td>
								</tr>
							<?php
						}
							?>
					</tbody>
				</table>

				<div class="search-pagination search-pagination-bottom">
					<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
					<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
				</div>

				<div class="button-area">
					<input type="submit" name="set_active" id="set_active" value="Aktifkan" class="com-button" />
					<input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="com-button" />
					<input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
					<input type="button" name="add" id="add" value="Tambah" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=add'" />
				</div>
			</form>
		<?php
		} else if (@$_GET['q']) {
		?>
			<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
		<?php
		} else {
		?>
			<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=add">Klik di sini untuk membuat baru.</a></div>
		<?php
		}
		?>
	</div>

<?php
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>