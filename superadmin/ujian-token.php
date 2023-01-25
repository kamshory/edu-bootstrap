<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$admin_id = $admin_login->admin_id;

$cfg->module_title = "Token Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	if(isset($_POST['school_id']))
	{
		$school_id = kh_filter_input(INPUT_POST, 'school_id', FILTER_SANITIZE_STRING_NEW);
	}
	$token_id = kh_filter_input(INPUT_POST, 'token_id', FILTER_SANITIZE_NUMBER_INT);
	$token_id2 = kh_filter_input(INPUT_POST, 'token_id2', FILTER_SANITIZE_NUMBER_INT);
	if(!isset($_POST['token_id']))
	{
		$token_id = $token_id2;
	}
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$class_id = kh_filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$student_id = kh_filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_STRING_NEW);
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$time_expire = kh_filter_input(INPUT_POST, 'time_expire', FILTER_SANITIZE_STRING_NEW);
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
			$sql = "update `edu_token` set `active` = '0' where `token_id` = '$token_id' and `school_id` = '$school_id' ";
			$database->executeUpdate($sql);
		}
	}
}


if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$now = $picoEdu->getLocalDateTime();
	$oneday = date('Y-m-d H:i:s', time()-86400);
	$sql = "DELETE FROM `edu_token` where `time_expire` < '$oneday'
	";
	$database->executeDelete($sql);
	$sql = "update `edu_token` set `active` = '0' where `time_expire` < '$now'
	";
	$database->executeUpdate($sql);
	if($class_id)
	{
		if($student_id == 0)
		{
			// membuat token untuk semua siswa
			$sql = "SELECT `student_id` from `edu_student` where `class_id` = '$class_id' and `active` = '1'
			";
			$stmt = $database->executeQuery($sql);
			$students = array();
			if($stmt->rowCount() > 0)
			{
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($rows as $data)
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
				`admin_create`, `admin_edit`, `active`) values
				('$token_id', '$token', '$school_id', '$class_id', '$student_id', '$test_id', '$time_create', '$time_edit', '$time_expire', 
				'$admin_create', '$admin_edit', '$active')";
				$database->executeInsert($sql);
			}
			header("Location: ".basename($_SERVER['PHP_SELF'])."?class_id=$class_id&test_id=$test_id");
		}
		else
		{
			// membuat token untuk satu siswa
			$count = 1;
			$tokens = $picoEdu->generateToken($count, 6);
			$token = $tokens[0];
			$token_id = $database->generateNewId();
			$sql = "INSERT INTO `edu_token` 
			(`token_id`, `token`, `school_id`, `class_id`, `student_id`, `test_id`, `time_create`, `time_edit`, `time_expire`, 
			`admin_create`, `admin_edit`, `active`) values
			('$token_id', '$token', '$school_id', '$class_id', '$student_id', '$test_id', '$time_create', '$time_edit', '$time_expire', 
			'$admin_create', '$admin_edit', '$active')";
			$database->executeInsert($sql);
			header("Location: ".basename($_SERVER['PHP_SELF'])."?class_id=$class_id&test_id=$test_id");
		}
	}
}
if(@$_GET['option']=='print')
{
	include_once dirname(__FILE__)."/cetak-ujian-token.php";
}
else if(@$_GET['option']=='add')
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#school_id', function(e){
		window.location = '<?php echo basename($_SERVER['PHP_SELF']);?>?option=add&school_id='+$(this).val();
	});
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
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Sekolah</td>
		<td><select class="input-select" name="school_id" id="school_id" required="required">
		<option value="">- Pilih Sekolah -</option>
		<?php 
		$sql2 = "select * from `edu_school`
		where `active` = '1'
		order by `school_grade_id` asc
		";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'school_id')
				),
				'selectCondition'=>array(
					'source'=>'school_id',
					'value'=>$school_id
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
		<td>Ujian</td>
		<td><select class="input-select" name="test_id" id="test_id" required="required">
		<option value="">- Pilih Ujian -</option>
		<?php 
		$sql2 = "select * from `edu_test`
		where 1 and `school_id` = '$school_id'
		and (`test_availability` = 'F' or `available_to` > '$now')
		order by `test_id` desc
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
		<td>Kelas</td>
		<td><select class="input-select" name="class_id" id="class_id" required="required">
		<option value="">- Pilih Kelas -</option>
		<?php 
		$sql2 = "select * from `edu_class`
		where `active` = '1' and `school_id` = '$school_id'
		order by `order` asc
		";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'class_id')
				),
				'selectCondition'=>array(
					'source'=>'class_id',
					'value'=>null
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
		<td>Siswa</td>
		<td><select class="input-select" name="student_id" id="student_id">
		<option value="">- Semua Siswa -</option>
		</select></td>
		</tr>
		<tr>
		<td>Kedaluarsa</td>
		<td><input type="text" class="input-text input-text-datetime" name="time_expire" id="time_expire" value="<?php echo date('Y-m-d H:i:s', time()+3600);?>" autocomplete="off" required="required" /></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" onclick="return confirm('Apakah Anda yahin akan membuat token ini?')" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php getDefaultValues($database, 'edu_token', array('active')); ?>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'token_id', FILTER_SANITIZE_NUMBER_INT);
$nt = '';
$sql = "SELECT `edu_token`.* $nt,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_token`.`admin_create`) as `creator_name`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_token`.`admin_edit`) as `editor_name`,
(select `edu_student`.`name` from `edu_student` where `edu_student`.`student_id` = `edu_token`.`student_id`) as `student_name`,
(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_token`.`class_id`) as `class_name`,
(select `edu_test`.`name` from `edu_test` where `edu_test`.`test_id` = `edu_token`.`test_id`) as `test_name`
from `edu_token` 
where 1 and `school_id` = '$school_id'
and `edu_token`.`token_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_token" action="" method="post" enctype="multipart/form-data">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Token</td>
		<td><?php echo $data['token'];?></td>
		</tr>
		<tr>
		<td>Ujian</td>
		<td><?php echo $data['test_name'];?></td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class_name'];?></td>
		</tr>
		<tr>
		<td>Siswa</td>
		<td><?php echo $data['student_name'];?></td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo $data['time_create'];?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo $data['time_edit'];?></td>
		</tr>
		<tr>
		<td>Kedaluarsa</td>
		<td><?php echo $data['time_expire'];?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['creator_name'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['editor_name'];?></td>
		</tr>
		<tr>
		<td>Active</td>
		<td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&token_id=<?php echo $data['token_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
$now = $picoEdu->getLocalDateTime();
$oneday = date('Y-m-d H:i:s', time()-86400);
include_once dirname(__FILE__)."/lib.inc/header.php";
if(isset($_POST['cleanup']))
{
	$sql = "DELETE FROM `edu_invalid_signin` where `signin_type` = 'T' ";
	$stmt = $database->executeDelete($sql);
	$num_deleted = $stmt->rowCount();
	if($num_deleted > 0)
	{
	?>
    <div class="info">Sebanyak <?php echo $num_deleted;?> token salah yang dimasukkan siswa telah berhasil dihapus.</div>
    <?php
	}
	else
	{
	?>
    <div class="info">Tidak ada token salah yang dimasukkan siswa.</div>
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
		window.open('<?php echo basename($_SERVER['PHP_SELF']);?>?option=print&tokens='+tokens.join(','));
	}
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
<span class="search-label">Ujian</span>
<select name="test_id" id="test_id">
	<option value=""></option>
    <?php
	$sql = "select * from `edu_test`
	where 1 and `school_id` = '$school_id'
	and (`test_availability` = 'F' or `available_to` > '$now')
	order by `test_id` desc
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
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);
	?>
</select>
<span class="search-label">Kelas</span>
<select name="class_id" id="class_id">
	<option value=""></option>
    <?php
	$sql2 = "select * from `edu_class`
	where `active` = '1' and `school_id` = '$school_id'
	order by `order` asc
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
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);

	?>
</select>
<span class="search-label">Token</span>
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
$sql_filter .= " and (`edu_token`.`token` like '%".addslashes($pagination->query)."%' )";
}
if($class_id != 0)
{
$pagination->array_get[] = 'class_id';
$sql_filter .= " and `edu_token`.`class_id` = '$class_id' ";
}
if($test_id != 0)
{
$pagination->array_get[] = 'test_id';
$sql_filter .= " and `edu_token`.`test_id` = '$test_id' ";
}
if($test_id == 0 && $class_id == 0)
{
	$pagination->limit_sql = "";
}
$sql_filter .= " and `edu_token`.`active` = '1' ";
$nt = '';

$sql = "SELECT `edu_token`.* $nt,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_token`.`admin_create`) as `admin_create_name`,
(select `edu_student`.`name` from `edu_student` where `edu_student`.`student_id` = `edu_token`.`student_id`) as `student_name`,
(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_token`.`class_id`) as `class_name`,
(select `edu_test`.`name` from `edu_test` where `edu_test`.`test_id` = `edu_token`.`test_id`) as `test_name`
from `edu_token`
where 1 and `school_id` = '$school_id' $sql_filter
order by `edu_token`.`token_id` desc
";
$sql_test = "SELECT `edu_token`.*
from `edu_token`
where 1 and `school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
if($test_id == 0 && $class_id == 0)
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
if($test_id == 0 && $class_id == 0)
{
?>
<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>
<?php
}
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
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
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr<?php echo (@$data['active'])?" class=\"data-active\"":" class=\"data-inactive\"";?>>
      <td><input type="checkbox" name="token_id[]" id="token_id" value="<?php echo $data['token_id'];?>" class="token_id" /></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['token'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['test_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['class_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['student_name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['time_create'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&token_id=<?php echo $data['token_id'];?>"><?php echo $data['time_expire'];?></a></td>
      <td><?php
      if($data['teacher_create'])
	  {
		  ?><a href="guru.php?option=detail&teacher_id=<?php echo $data['teacher_create'];?>"><?php echo ($data['teacher_create_name']);?></a><?php
	  }
	  else
	  {
		  ?><a href="admin.php?option=detail&admin_id=<?php echo $data['admin_create'];?>"><?php echo ($data['admin_create_name']);?></a><?php
	  }
	  ?></td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<?php
if($test_id == 0 && $class_id == 0)
{
?>
<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>
<?php
}
?>
<div class="button-area">
  <input type="button" name="print" id="print" value="Cetak" class="com-button" onclick="printToken($(this).closest('form'))" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="com-button" onclick="return confirm('Apakah Anda akan menonaktifkan token ini?')" />
  <input type="button" name="add" id="add" value="Tambah" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
  <input type="submit" name="cleanup" id="cleanup" value="Hapus Token Salah" class="com-button" onclick="return confirm('Apakah Anda akan menghapus semua token salah yang dimasukkan siswa?')" />
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>