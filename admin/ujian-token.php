<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";

$pageTitle = "Token Ujian";
$pagination = new \Pico\PicoPagination();
if(count(@$_POST) && isset($_POST['save']))
{
	$token_id = kh_filter_input(INPUT_POST, "token_id", FILTER_SANITIZE_NUMBER_INT);
	$token_id2 = kh_filter_input(INPUT_POST, "token_id2", FILTER_SANITIZE_NUMBER_INT);
	if(!isset($_POST['token_id']))
	{
		$token_id = $token_id2;
	}
	$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
	$class_id = kh_filter_input(INPUT_POST, "class_id", FILTER_SANITIZE_STRING_NEW);
	$student_id = kh_filter_input(INPUT_POST, "student_id", FILTER_SANITIZE_STRING_NEW);
	$time_create = $time_edit = $database->getLocalDateTime();
	$time_expire = kh_filter_input(INPUT_POST, "time_expire", FILTER_SANITIZE_STRING_NEW);
	$admin_create = $admin_id;
	$admin_edit = $admin_id;
	$active = 1;
}

if(isset($_POST['set_inactive']) && isset($_POST['token_id']))
{
	$tokens = @$_POST['token_id'];
	if(isset($tokens) && is_array($tokens))
	{
		foreach($tokens as $key=>$val)
		{
			$token_id = addslashes($val);
			$sql = "UPDATE `edu_token` SET `active` = false WHERE `token_id` = '$token_id' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$now = $database->getLocalDateTime();
	$oneday = date(\Pico\PicoConst::DATE_TIME_MYSQL, time()-86400);
	$sql = "DELETE FROM `edu_token` WHERE `time_expire` < '$oneday'
	";
	$database->executeDelete($sql, true);
	$sql = "UPDATE `edu_token` SET `active` = false WHERE `time_expire` < '$now'
	";
	$database->executeUpdate($sql, true);
	if($class_id)
	{
		if(empty($student_id))
		{
			// membuat token untuk semua siswa
			$sql = "SELECT `student_id` FROM `edu_student` WHERE `class_id` = '$class_id' AND `active` = true
			";
			$students = array();
			$stmtx = $database->executeQuery($sql);
			if($stmtx->rowCount() > 0)
			{
				$rowsx = $stmtx->fetchAll(\PDO::FETCH_ASSOC);
				foreach($rowsx as $data)
				{
					$students[] = $data['student_id'];
				}
			}
			$count = count($students);
			$tokens = $picoEdu->generateToken($count, 6);
			foreach($tokens as $idx=>$val)
			{
				$token = $val;
				$student_id = $students[$idx];
				$token_id = $database->generateNewId();
				$sql = "INSERT INTO `edu_token` 
				(`token_id`, `token`, `school_id`, `class_id`, `student_id`, `test_id`, `time_create`, `time_edit`, `time_expire`, 
				`admin_create`, `admin_edit`, `active`) VALUES
				('$token_id', '$token', '$school_id', '$class_id', '$student_id', '$test_id', '$time_create', '$time_edit', '$time_expire', 
				'$admin_create', '$admin_edit', '$active')";
				$database->executeInsert($sql, true);
			}
			header("Location: ".$picoEdu->gateBaseSelfName()."?class_id=$class_id&test_id=$test_id");
		}
		else
		{
			// membuat token untuk satu siswa
			$count = 1;
			$tokens = $picoEdu->generateToken($count, 6);
			$token = $tokens[0];
			$sql = "INSERT INTO `edu_token` 
			(`token`, `school_id`, `class_id`, `student_id`, `test_id`, `time_create`, `time_edit`, `time_expire`, 
			`admin_create`, `admin_edit`, `active`) VALUES
			('$token', '$school_id', '$class_id', '$student_id', '$test_id', '$time_create', '$time_edit', '$time_expire', 
			'$admin_create', '$admin_edit', '$active')";
			$database->executeInsert($sql, true);
			header("Location: ".$picoEdu->gateBaseSelfName()."?class_id=$class_id&test_id=$test_id");
		}
	}
}
if(@$_GET['option'] == 'print') {
	require_once __DIR__."/cetak-ujian-token.php";
} 
else if (@$_GET['option'] == 'add') {
	require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#class_id', function(e){
		var class_id = $(this).val();
		$('#student_id').empty().append('<option value="">- Semua Siswa -</option>');
		$.ajax({
			url:'../lib.ajax/ajax-load-student-by-class.php', 
			type:'GET',
			dataType:"json",
			data:{class_id:class_id},
			success:function(data){
				var i;
				for(i in data)
				{
					$('#student_id').append('<option value="'+data[i].v+'">'+data[i].l+'</option>');
				}
			}
		});
	});
});
</script>
<form name="formedu_token" id="formedu_token" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this, 'Wajib')">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Ujian</td>
		<td><select class="form-control input-select" name="test_id" id="test_id" required="required">
		<option value=""></option>
		<?php
		$sql2 = "SELECT * FROM `edu_test`
		WHERE `school_id` = '$school_id'
		AND (`test_availability` = 'F' OR `available_to` > '$now')
		ORDER BY `test_id` DESC
		";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'test_id')
				),
				'selectCondition'=>array(
					'source'=>'test_id',
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
		</select></td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><select class="form-control input-select" name="class_id" id="class_id" required="required">
		<option value=""></option>
		<?php
		$sql2 = "SELECT * FROM `edu_class`
		WHERE `active` = true AND `school_id` = '$school_id'
		ORDER BY `sort_order` ASC
		";
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
		<td>Siswa</td>
		<td><select class="form-control input-select" name="student_id" id="student_id">
		<option value="">- Semua Siswa -</option>
		</select></td>
		</tr>
		<tr>
		<td>Kedaluarsa</td>
		<td><input type="datetime-local" class="form-control input-text input-text-datetime" name="time_expire" id="time_expire" value="<?php echo date(\Pico\PicoConst::DATE_TIME_MYSQL, time() + 3600); ?>" autocomplete="off" required="required" /></td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" onclick="return confirm('Apakah Anda yakin akan membuat token ini?')" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_token', array('active')); ?>
<?php
		require_once __DIR__ . "/lib.inc/footer.php"; //NOSONAR

		} else if (@$_GET['option'] == 'detail') {
			require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
			$edit_key = kh_filter_input(INPUT_GET, "token_id", FILTER_SANITIZE_NUMBER_INT);
			$nt = '';
			$sql = "SELECT `edu_token`.* $nt,
			(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_token`.`admin_create`) AS `creator_name`,
			(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_token`.`admin_edit`) AS `editor_name`,
			(SELECT `edu_student`.`name` FROM `edu_student` WHERE `edu_student`.`student_id` = `edu_token`.`student_id`) AS `student_name`,
			(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_token`.`class_id`) AS `class_name`,
			(SELECT `edu_test`.`name` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_token`.`test_id`) AS `test_name`
			FROM `edu_token` 
			WHERE `school_id` = '$school_id'
			AND `edu_token`.`token_id` = '$edit_key'
			";
			$stmt = $database->executeQuery($sql);
			if($stmt->rowCount() > 0) {
				$data = $stmt->fetch(\PDO::FETCH_ASSOC);
				?>
<form name="formedu_token" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Token</td>
		<td><?php echo $data['token']; ?> </td>
		</tr>
		<tr>
		<td>Ujian</td>
		<td><?php echo $data['test_name']; ?> </td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class_name']; ?> </td>
		</tr>
		<tr>
		<td>Siswa</td>
		<td><?php echo $data['student_name']; ?> </td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo $data['time_create']; ?> </td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit']; ?> </td>
		</tr>
		<tr>
		<td>Kedaluarsa</td>
		<td><?php echo $data['time_expire']; ?> </td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['creator_name']; ?> </td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['editor_name']; ?> </td>
		</tr>
		<tr>
		<td>Active</td>
		<td><?php echo $data['active'] ? 'Ya' : 'Tidak'; ?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&token_id=<?php echo $data['token_id']; ?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
			} else {
				?>
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>	
<?php
			}
			require_once __DIR__ . "/lib.inc/footer.php"; //NOSONAR

		} else {
			$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);
			$class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
			$now = $database->getLocalDateTime();
			$oneday = date(\Pico\PicoConst::DATE_TIME_MYSQL, time() - 86400);
			require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
			if (isset($_POST['cleanup'])) {
				$sql = "DELETE FROM `edu_invalid_signin` WHERE `signin_type` = 'T' ";
				$stmt = $database->executeDelete($sql, true);
				$num_deleted = $stmt->rowCount();
				if ($num_deleted) {
					?>
    <div class="alert alert-success">Sebanyak <?php echo $num_deleted; ?> token salah yang dimasukkan siswa telah berhasil dihapus.</div>
    <?php
				} else {
					?>
    <div class="alert alert-success">Tidak ada token salah yang dimasukkan siswa.</div>
    <?php
				}
			}
			?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
});
function printToken(frm)
{
	var tokens = [];
	$(frm).find('.token_id').each(function(index, element) {
        if($(this)[0].checked)
		{
			tokens.push($(this).val());
		}
    });
	if(tokens.length)
	{
		window.open('<?php echo $picoEdu->gateBaseSelfName();?>?option=print&tokens='+tokens.join(','));
	}
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
<span class="search-label">Ujian</span>
<select class="form-control" name="test_id" id="test_id">
	<option value=""></option>
    <?php
	$sql2 = "SELECT * FROM `edu_test`
	WHERE `school_id` = '$school_id'
	ORDER BY `test_id` DESC
	";
	echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'test_id')
			),
			'selectCondition'=>array(
				'source'=>'test_id',
				'value'=>$test_id
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
<span class="search-label">Kelas</span>
<select class="form-control" name="class_id" id="class_id">
	<option value=""></option>
    <?php
		$sql2 = "SELECT * FROM `edu_class`
		WHERE `active` = true AND `school_id` = '$school_id'
		ORDER BY `sort_order` ASC
		";
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
<span class="search-label">Token</span>
<input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
<input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
if($pagination->getQuery()) {
	$pagination->appendQueryName('q');
	$sql_filter .= " AND (`edu_token`.`token` like '%" . addslashes($pagination->getQuery()) . "%' )";
}
if ($class_id != 0) {
	$pagination->appendQueryName('class_id');
	$sql_filter .= " AND `edu_token`.`class_id` = '$class_id' ";
}
if ($test_id != 0) {
	$pagination->appendQueryName('test_id');
	$sql_filter .= " AND `edu_token`.`test_id` = '$test_id' ";
}
if ($test_id != 0 || $class_id != 0) {
	$pagination->setLimitSql("");
}
$sql_filter .= " AND `edu_token`.`active` = true ";
$nt = '';

$sql = "SELECT `edu_token`.* $nt,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_token`.`admin_create`) AS `admin_create_name`,
(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_token`.`teacher_create`) AS `teacher_create_name`,
(SELECT `edu_student`.`name` FROM `edu_student` WHERE `edu_student`.`student_id` = `edu_token`.`student_id`) AS `student_name`,
(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_token`.`class_id`) AS `class_name`,
(SELECT `edu_test`.`name` FROM `edu_test` WHERE `edu_test`.`test_id` = `edu_token`.`test_id`) AS `test_name`
FROM `edu_token`
WHERE `school_id` = '$school_id' $sql_filter
ORDER BY `edu_token`.`token_id` DESC
";
$sql_test = "SELECT `edu_token`.*
FROM `edu_token`
WHERE `school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0) {
	if ($test_id == 0 && $class_id == 0) {
		$pagination->createPagination($picoEdu->gateBaseSelfName(), true);
		$paginationHTML = $pagination->buildHTML();
	}
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (min-width:600px)
{
#q{
	width:80px;
}
}
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(4){
		display:none;
	}
}
</style>
<?php
if ($test_id == 0 && $class_id == 0) {
	?>
<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>
<?php
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
<thead>
<tr>
<td width="16"><input type="checkbox" name="control-token_id" id="control-token_id" class="checkbox-selector" data-target=".token_id" value="1"></td>
<td width="25">No</td>
<td>Token</td>
<td>Ujian</td>
<td>Kelas</td>
<td>Siswa</td>
<td>Dibuat</td>
<td>Kedaluarsa</td>
<td>Pembuat</td>
</tr>
</thead>
<tbody>
<?php
$no = $pagination->getOffset();
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
foreach ($rows as $data) {
$no++;
?>
<tr class="<?php echo $picoEdu->getRowClass($data);?>">
<td><input type="checkbox" name="token_id[]" id="token_id" value="<?php echo $data['token_id']; ?>" class="token_id" /></td>
<td align="right"><?php echo $no; ?> </td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['token']; ?></a></td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['test_name']; ?></a></td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['class_name']; ?></a></td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['student_name']; ?></a></td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['time_create']; ?></a></td>
<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&token_id=<?php echo $data['token_id']; ?>"><?php echo $data['time_expire']; ?></a></td>
<td><?php
	if ($data['teacher_create']) {
		?><a href="guru.php?option=detail&teacher_id=<?php echo $data['teacher_create']; ?>"><?php echo $data['teacher_create_name']; ?></a><?php
	} else {
		?><a href="admin.php?option=detail&admin_id=<?php echo $data['admin_create']; ?>"><?php echo $data['admin_create_name']; ?></a><?php
	}
	?> </td>
</tr>
<?php
}
?>
</tbody>
</table>

<?php
if ($test_id == 0 && $class_id == 0) {
	?>
<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>
<?php
}
?>
<div class="button-area">
<input type="button" name="print" id="print" value="Cetak" class="btn btn-success" onclick="printToken($(this).closest('form'))" />
<input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" onclick="return confirm('Apakah Anda akan menonaktifkan token ini?')" />
<input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=add'" />
<input type="submit" name="cleanup" id="cleanup" value="Hapus Token Salah" class="btn btn-success" onclick="return confirm('Apakah Anda akan menghapus semua token salah yang dimasukkan siswa?')" />
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
require_once __DIR__ . "/lib.inc/footer.php"; //NOSONAR
}

?>