<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$admin_id = $admin_login->admin_id;

$cfg->module_title = "Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(count(@$_POST) && isset($_POST['save']))
{
	$school_id = kh_filter_input(INPUT_POST, 'school_id2', FILTER_SANITIZE_NUMBER_INT);
	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);

	$name = preg_replace("/[^A-Za-z\.\-\d_]/i"," ",$name);
	$name = trim($name, " ._- ");
	$name = preg_replace('/(\s)+/', ' ', $name);
	$name = trim($name, " ._- ");
	
	
	$school_code = strtolower($name);
	$school_code = preg_replace("/[^a-z\-\d]/i","-",$school_code);
	$school_code = str_replace("---", "-", $school_code);
	$school_code = str_replace("--", "-", $school_code);
	$school_code = str_replace("--", "-", $school_code);
	
	$school_code = addslashes($school_code);

	$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
	$description = kh_filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
	$school_type_id = kh_filter_input(INPUT_POST, 'school_type_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$school_grade_id = kh_filter_input(INPUT_POST, 'school_grade_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$public_private = kh_filter_input(INPUT_POST, 'public_private', FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, 'open', FILTER_SANITIZE_NUMBER_INT);
	$principal = kh_filter_input(INPUT_POST, 'principal', FILTER_SANITIZE_SPECIAL_CHARS);
	$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
	$language = kh_filter_input(INPUT_POST, 'language', FILTER_SANITIZE_SPECIAL_CHARS);
	$country_id = kh_filter_input(INPUT_POST, 'country_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$state_id = kh_filter_input(INPUT_POST, 'state_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$city_id = kh_filter_input(INPUT_POST, 'city_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$student = kh_filter_input(INPUT_POST, 'student', FILTER_SANITIZE_NUMBER_INT);

	$prevent_change_school = kh_filter_input(INPUT_POST, 'prevent_change_school', FILTER_SANITIZE_NUMBER_UINT);
	$prevent_resign = kh_filter_input(INPUT_POST, 'prevent_resign', FILTER_SANITIZE_NUMBER_UINT);
	$use_token = kh_filter_input(INPUT_POST, 'use_token', FILTER_SANITIZE_NUMBER_UINT);

	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];

	$active = kh_filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);



}

if(isset($_POST['save']) && @$_GET['option']=='add')
{
	$token_school = md5($name.'-'.time().'-'.mt_rand(111111, 999999));
	$sql = "INSERT INTO `edu_school` 
	(`school_code`, `token_school`, `name`, `description`, `school_type_id`, `school_grade_id`, `public_private`, `open`, `principal`, `address`, `phone`, `email`, `language`, `country_id`, `prevent_change_school`, `prevent_resign`, `use_token`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) values
	('$school_code', '$token_school', '$name', '$description', '$school_type_id', '$school_grade_id', '$public_private', '$open', '$principal', '$address', '$phone', '$email', '$language', '$country_id', '$prevent_change_school', '$prevent_resign', '$use_token', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->executeInsert($sql);
	$school_id = $database->getDatabaseConnection()->lastInsertId();
	$sql = "update `edu_school` set `state_id` = state_name_to_id('$state_id', `country_id`) where `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	
	$sql = "update `edu_school` set `city_id` = city_name_to_id('$city_id', `state_id`, `country_id`) where `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	header("Location:".basename($_SERVER['PHP_SELF'])."?option=detail&school_id=$school_id");
}
if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$sql1 = "update `edu_student` set `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	where `school_id` = '$school_id' 
	";
	$database->executeUpdate($sql1);

	$sql2 = "update `edu_school` set `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	where `school_id` = '$school_id' 
	";
	$database->executeUpdate($sql2);

	$sql = "update `edu_school` set
	`school_code` = '$school_code', `name` = '$name', `school_grade_id` = '$school_grade_id', `public_private` = '$public_private', 
	`open` = '$open', `principal` = '$principal', `address` = '$address', `phone` = '$phone', `email` = '$email', `country_id` = '$country_id',
	`use_token` = '$use_token'
	where `school_id` = '$school_id'
	";
	$database->executeUpdate($sql);
	$sql = "update `edu_school` set `state_id` = state_name_to_id('$state_id', `country_id`) where `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	
	$sql = "update `edu_school` set `city_id` = city_name_to_id('$city_id', `state_id`, `country_id`) where `school_id` = '$school_id' ";
	$database->executeUpdate($sql);
	header("Location: ".basename($_SERVER['PHP_SELF'])."?option=detail&school_id=$school_id");
}

if(isset($_POST['set_active']) && isset($_POST['school_id']))
{
	$schools = @$_POST['school_id'];
	if(isset($schools) && is_array($schools))
	{
		foreach($schools as $key=>$val)
		{
			$school_id = addslashes($val);
			$sql = "update `edu_school` set `active` = '1' where `school_id` = '$school_id'  ";
			$database->execute($sql);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['school_id']))
{
	$schools = @$_POST['school_id'];
	if(isset($schools) && is_array($schools))
	{
		foreach($schools as $key=>$val)
		{
			$school_id = addslashes($val);
			$sql = "update `edu_school` set `active` = '0' where `school_id` = '$school_id'  ";
			$database->executeUpdate($sql);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['school_id']))
{
	$schools = @$_POST['school_id'];
	if(isset($schools) && is_array($schools))
	{
		foreach($schools as $key=>$val)
		{
			$school_id = addslashes($val);
			$sql = "DELETE FROM `edu_school` where `school_id` = '$school_id'  ";
			$database->executeDelete($sql);
		}
	}
}

if(@$_GET['option']=='add')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$state_list = array();
$city_list = array();

?>
<script type="text/javascript">
var state_list = <?php echo json_encode($state_list);?>;
var city_list = <?php echo json_encode($city_list);?>;
function onChangeCountry()
{
	var country_id = $('#country_id').val();
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
	$.ajax({
		url:'../lib.ajax/ajax-load-state-list.php',
		type:'GET',
		dataType:"json",
		data:{country_id:country_id},
		success: function(data){
			$('#state_id').replaceWith('<select name="state_id" id="state_id" required="required" data-full-width="true"></select>');
			$('#state_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Provinsi'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
			}
			html += '<option value="--">'+'Tambah Provinsi'+'</option>';
			$('#state_id').append(html);
		}
	});
	$.ajax({
		url:'../lib.ajax/ajax-load-city-list.php',
		type:'GET',
		dataType:"json",
		data:{country_id:country_id},
		success: function(data){
			$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
			$('#city_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
			}
			html += '<option value="--">'+'Tambah Kabupaten/Kota'+'</option>';
			$('#city_id').append(html);
		}
	});
}
$(document).ready(function(e) {
	$(document).on('change', '#country_id', function(e){
		onChangeCountry();
	});
	$(document).on('change', 'select#state_id', function(e){
		var val = $(this).val();
		if(val == '--')
		{
			if(confirm('Apakah Anda akan mengubah jenis masukan?'))
			{
				$(this).replaceWith('<input type="text" name="state_id" id="state_id" required="required" data-full-width="true">');
				$('#state_id').select();
			}
		}
		else
		{
			var state_name = val;
			var country_id = $('#country_id').val();
			$.ajax({
				url:'../lib.ajax/ajax-load-city-list.php',
				type:'GET',
				dataType:"json",
				data:{state_name:state_name, country_id:country_id},
				success: function(data){
					$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
					$('#city_id').empty();
					var html = '';
					html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
					var i, j;
					for(i in data)
					{
						html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
					}
					html += '<option value="--">'+'Tambah Kabupaten/Kota'+'</option>';
					$('#city_id').append(html);
				}
			});
		}
	});
	$(document).on('change', 'select#city_id', function(e){
		var val = $(this).val();
		if(val == '--')
		{
			if(confirm('Apakah Anda akan mengubah jenis masukan?'))
			{
				$(this).replaceWith('<input type="text" name="city_id" id="city_id" required="required" data-full-width="true">');
				$('#city_id').select();
			}
		}
	});
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
});

</script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><select class="input-select" name="school_grade_id" id="school_grade_id">
		<option value=""></option>
		<option value="1">Play Group</option>
		<option value="2">Taman Kanak-Kanak</option>
		<option value="3">Sekolah Dasar</option>
		<option value="4">Sekolah Menengah Pertama</option>
		<option value="5">Sekolah Menengah Atas</option>
		<option value="6">Perguruan Tinggi</option>
		</select></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><select class="input-select" name="public_private" id="public_private">
		<option value=""></option>
		<option value="U">Negeri</option>
		<option value="I">Swasta</option>
		</select></td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"> Terbuka</label>
		</td>
		</tr>
		<tr>
		<td>Kepala Sekolah
		</td><td><input type="text" class="input-text input-text-long" name="principal" id="principal" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea class="input-textarea" name="address" id="address"></textarea></td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><input type="tel" class="input-text input-text-long" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="input-text input-text-long" name="email" id="email" autocomplete="off" data-type="email" /></td>
		</tr>
		<tr>
		<td>Bahasa
		</td><td>
        <select name="language" id="language" data-full-width="true">
			<option value="en">English</option>
			<option value="id">Bahasa Indonesia</option>
        </select>
        </td>
		</tr>
		<tr>
		<td>Negara
		</td><td><select class="input-select" name="country_id" id="country_id">
		<option value=""></option>
		<?php
        $sql = "select * from `country` where `active` = '1' order by `order` asc
		";

		echo $picoEdu->createFilterDb(
			$sql,
			array(
				'attributeList'=>array(
					array('attribute'=>'data-code', 'source'=>'phone_code'),
					array('attribute'=>'value', 'source'=>'country_id')
				),
				'selectCondition'=>array(
					'source'=>'country_id',
					'value'=>null
				),
				'caption'=>array(
					'delimiter'=>'',
					'values'=>array(
						'phone_code', 'name'
					)
				)
			)
		);

		
		?>
		</select></td>
		</tr>
		<tr>
		<td>Provinsi
		</td><td><select class="input-select" name="state_id" id="state_id">
		<option value="">- Pilih Provinsi -</option>
			<?php
            $sql = "select * from `state` where `active` = '1' and `verify` = '1' and `country_id` = '$data[country_id]' order by `type` asc, `name` asc
            ";
            echo $picoEdu->createFilterDb(
				$sql2,
				array(
					'attributeList'=>array(
						array('attribute'=>'value', 'source'=>'state_id')
					),
					'selectCondition'=>array(
						'source'=>'state_id',
						'value'=>$data['state_id']
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
            <!--<option value="--">- Tambah Provinsi -</option>-->
		</select></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota
		</td><td><select class="input-select" name="city_id" id="city_id">
		<option value="">- Pilih Kabupaten/Kota -</option>
			<?php
            $sql = "select * from `city` where `active` = '1' and `verify` = '1' and `country_id` = '$data[country_id]' and (`state_id` = '$data[state_id]' or `state_id` = '' or `state_id` is null) order by `type` asc, `name` asc 
            ";
            echo $picoEdu->createFilterDb(
				$sql2,
				array(
					'attributeList'=>array(
						array('attribute'=>'value', 'source'=>'city_id')
					),
					'selectCondition'=>array(
						'source'=>'city_id',
						'value'=>$data['city_id']
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
		<!--<option value="--">- Tambah Kabupaten/Kota -</option>-->
		</select></td>
		</tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_change_school" value="1" id="prevent_change_school"> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_resign" value="1" id="prevent_resign"> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Sistem Token</td>
		<td><label><input type="checkbox" class="input-checkbox" name="use_token" value="1" id="use_token"> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Ya</label>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
<?php getDefaultValues($database, 'edu_school', array('school_type_id','school_grade_id','public_private','open','language','country_id','prevent_change_school','prevent_resign','use_token','active')); ?>
</form>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else if(@$_GET['option']=='edit')
{
$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
include_once dirname(__FILE__)."/lib.inc/header.php";

$state_list = array();
$city_list = array();
$sql = "SELECT `edu_school`.* 
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT `state`.`state_id` as `v`, `state`.`name` as `l`
from `state` where `state`.`country_id` = '".$data['country_id']."' 
";
$stmtx = $database->executeQuery($sql);
if ($stmtx->rowCount() > 0) {
	$state_list = $stmtx->fetchAll(PDO::FETCH_ASSOC);
}
$sql = "SELECT `city`.`city_id` as `v`, `city`.`name` as `l`
from `city` where `city`.`country_id` = '".$data['country_id']."' 
and (`city`.`state_id` = '".$data['state_id']."' or `city`.`state_id` = '' or `city`.`state_id` is null) 
";
$stmtx = $database->executeQuery($sql);
if ($stmtx->rowCount() > 0) {
	$city_list = $stmtx->fetchAll(PDO::FETCH_ASSOC);
}

?>
<script type="text/javascript">
var state_list = <?php echo json_encode($state_list);?>;
var city_list = <?php echo json_encode($city_list);?>;
function onChangeCountry()
{
	var country_id = $('#country_id').val();
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
	$.ajax({
		url:'../lib.ajax/ajax-load-state-list.php',
		type:'GET',
		dataType:"json",
		data:{country_id:country_id},
		success: function(data){
			$('#state_id').replaceWith('<select name="state_id" id="state_id" required="required" data-full-width="true"></select>');
			$('#state_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Provinsi'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
			}
			html += '<option value="--">'+'Tambah Provinsi'+'</option>';
			$('#state_id').append(html);
		}
	});
	$.ajax({
		url:'../lib.ajax/ajax-load-city-list.php',
		type:'GET',
		dataType:"json",
		data:{country_id:country_id},
		success: function(data){
			$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
			$('#city_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
			}
			html += '<option value="--">'+'Tambah Kabupaten/Kota'+'</option>';
			$('#city_id').append(html);
		}
	});
}
$(document).ready(function(e) {
	$(document).on('change', '#country_id', function(e){
		onChangeCountry();
	});
	$(document).on('change', 'select#state_id', function(e){
		var val = $(this).val();
		if(val == '--')
		{
			if(confirm('Apakah Anda akan mengubah jenis masukan?'))
			{
				$(this).replaceWith('<input type="text" name="state_id" id="state_id" required="required" data-full-width="true">');
				$('#state_id').select();
			}
		}
		else
		{
			var state_name = val;
			var country_id = $('#country_id').val();
			$.ajax({
				url:'../lib.ajax/ajax-load-city-list.php',
				type:'GET',
				dataType:"json",
				data:{state_name:state_name, country_id:country_id},
				success: function(data){
					$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
					$('#city_id').empty();
					var html = '';
					html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
					var i, j;
					for(i in data)
					{
						html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
					}
					html += '<option value="--">'+'Tambah Kabupaten/Kota'+'</option>';
					$('#city_id').append(html);
				}
			});
		}
	});
	$(document).on('change', 'select#city_id', function(e){
		var val = $(this).val();
		if(val == '--')
		{
			if(confirm('Apakah Anda akan mengubah jenis masukan?'))
			{
				$(this).replaceWith('<input type="text" name="city_id" id="city_id" required="required" data-full-width="true">');
				$('#city_id').select();
			}
		}
	});
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
});

</script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" />
        <input type="hidden" name="school_id2" id="school_id2" value="<?php echo ($data['school_id']);?>" /></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><select class="input-select" name="school_grade_id" id="school_grade_id">
		<option value=""></option>
		<option value="1"<?php if($data['school_grade_id'] == '1') echo ' selected="selected"';?>>Play Group</option>
		<option value="2"<?php if($data['school_grade_id'] == '2') echo ' selected="selected"';?>>Taman Kanak-Kanak</option>
		<option value="3"<?php if($data['school_grade_id'] == '3') echo ' selected="selected"';?>>Sekolah Dasar</option>
		<option value="4"<?php if($data['school_grade_id'] == '4') echo ' selected="selected"';?>>Sekolah Menengah Pertama</option>
		<option value="5"<?php if($data['school_grade_id'] == '5') echo ' selected="selected"';?>>Sekolah Menengah Atas</option>
		<option value="6"<?php if($data['school_grade_id'] == '6') echo ' selected="selected"';?>>Perguruan Tinggi</option>
		</select></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><select class="input-select" name="public_private" id="public_private">
		<option value=""></option>
		<option value="U"<?php if($data['public_private'] == 'U') echo ' selected="selected"';?>>Negeri</option>
		<option value="I"<?php if($data['public_private'] == 'I') echo ' selected="selected"';?>>Swasta</option>
		</select></td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"<?php if($data['open']==1) echo ' checked="checked"';?>> Terbuka</label>
		</td>
		</tr>
		<tr>
		<td>Kepala Sekolah
		</td><td><input type="text" class="input-text input-text-long" name="principal" id="principal" value="<?php echo $data['principal'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea class="input-textarea" name="address" id="address"><?php echo $data['address'];?></textarea></td>
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
		<td>Bahasa
		</td><td>
        <select name="language" id="language" data-full-width="true">
			<option value="en">English</option>
			<option value="id">Bahasa Indonesia</option>
        </select>
        </td>
		</tr>
		<tr>
		<td>Negara
		</td><td><select class="input-select" name="country_id" id="country_id">
		<option value=""></option>
		<?php
        $sql2 = "select * from `country` where `active` = '1' order by `order` asc
		";
		echo $picoEdu->createFilterDb(
			$sql2,
			array(
				'attributeList'=>array(
					array('attribute'=>'value', 'source'=>'country_id')
				),
				'selectCondition'=>array(
					'source'=>'country_id',
					'value'=>$data['country_id']
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
		<td>Provinsi
		</td><td><select class="input-select" name="state_id" id="state_id">
		<option value="">- Pilih Provinsi -</option>
			<?php
            $sql2 = "select * from `state` where `active` = '1' and `verify` = '1' and `country_id` = '$data[country_id]' order by `type` asc, `name` asc
            ";
            echo $picoEdu->createFilterDb(
				$sql2,
				array(
					'attributeList'=>array(
						array('attribute'=>'value', 'source'=>'state_id')
					),
					'selectCondition'=>array(
						'source'=>'state_id',
						'value'=>$data['state_id']
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
            <!--<option value="--">- Tambah Provinsi -</option>-->
		</select></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota
		</td><td><select class="input-select" name="city_id" id="city_id">
		<option value="">- Pilih Kabupaten/Kota -</option>
			<?php
            $sql2 = "select * from `city` where `active` = '1' and `verify` = '1' and `country_id` = '$data[country_id]' and (`state_id` = '$data[state_id]' or `state_id` = '' or `state_id` is null) order by `type` asc, `name` asc 
            ";
            echo $picoEdu->createFilterDb(
				$sql2,
				array(
					'attributeList'=>array(
						array('attribute'=>'value', 'source'=>'city_id')
					),
					'selectCondition'=>array(
						'source'=>'city_id',
						'value'=>$data['city_id']
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
		<!--<option value="--">- Tambah Kabupaten/Kota -</option>-->
		</select></td>
		</tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_change_school" value="1" id="prevent_change_school"<?php if($data['prevent_change_school']==1) echo ' checked="checked"';?>> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_resign" value="1" id="prevent_resign"<?php if($data['prevent_resign']==1) echo ' checked="checked"';?>> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Sistem Token</td>
		<td><label><input type="checkbox" class="input-checkbox" name="use_token" value="1" id="use_token"<?php if($data['use_token']==1) echo ' checked="checked"';?>> Ya</label>
		</td>
		</tr>
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id`) as `student`,
(select count(distinct `edu_class`.`class_id`) from `edu_class` where `edu_class`.`school_id` = `edu_school`.`school_id`) as `class`,
(select count(distinct `edu_teacher`.`teacher_id`) from `edu_teacher` where `edu_teacher`.`school_id` = `edu_school`.`school_id`) as `teacher`,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) as `admin_edit`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Kode Sekolah</td>
		<td><a href="../<?php echo $data['school_code'];?>" target="_blank"><?php echo $data['school_code'];?></a></td>
		</tr>
		<tr>
		<td>Token Sekolah</td>
		<td><?php echo ($data['token_school']);?></td>
		</tr>
		<tr>
		<td>Nama Sekolah</td>
		<td><a href="../<?php echo $data['school_code'];?>" target="_blank"><?php echo $data['name'];?></a></td>
		</tr>
		<tr>
		<td>Keterangan</td>
		<td><?php echo $data['description'];?></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></td>
		</tr>
		<tr>
		<td>N/S</td>
		<td><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><?php echo ($data['open'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?></td>
		</tr>
		<tr>
		<td>Address</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Bahasa</td>
		<td><?php if($data['language']=='en') echo 'English'; if($data['language']=='id') echo 'Bahasa Indonesia';?></td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?></td>
		</tr>
		<tr>
		<td>Propinsi</td>
		<td><?php echo $data['state_id'];?></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota</td>
		<td><?php echo $data['city_id'];?></td>
		</tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><?php echo ($data['prevent_change_school'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><?php echo ($data['prevent_resign'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Sistem Token</td>
		<td><?php echo ($data['use_token'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo ($data['class']);?></td>
		</tr>
		<tr>
		<td>Siswa</td>
		<td><?php echo ($data['student']);?></td>
		</tr>
		<tr>
		<td>Guru</td>
		<td><?php echo ($data['teacher']);?></td>
		</tr>
		<tr>
		<td>Waktu Buat</td>
		<td><?php echo $data['time_create'];?></td>
		</tr>
		<tr>
		<td>Waktu Buat</td>
		<td><?php echo $data['time_edit'];?></td>
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
		<td>Aktif</td>
		<td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_id=<?php echo $data['school_id'];?>'" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Nama Sekolah</span>
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
$sql_filter .= " and (`edu_school`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';


$sql = "SELECT `edu_school`.* $nt,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id`) as `student`,
(select count(distinct `edu_teacher`.`teacher_id`) from `edu_teacher` where `edu_teacher`.`school_id` = `edu_school`.`school_id`) as `teacher`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`
from `edu_school`
where 1 $sql_filter
order by `edu_school`.`school_id` desc
";
$sql_test = "SELECT `edu_school`.*
from `edu_school`
where 1 $sql_filter
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
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:999px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(11){
		display:none;
	}
}
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(12){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(10){
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
      <td width="16"><input type="checkbox" name="control-school_id" id="control-school_id" class="checkbox-selector" data-target=".school_id" value="1"></td>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Nama Sekolah</td>
      <td>Jenjang</td>
      <td>N/S</td>
      <td>Terbuka</td>
      <td>Kepala Sekolah</td>
      <td>Propinsi</td>
      <td>Kabupaten/Kota</td>
      <td>Siswa</td>
      <td>Guru</td>
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
      <td><input type="checkbox" name="school_id[]" id="school_id" value="<?php echo $data['school_id'];?>" class="school_id" /></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&school_id=<?php echo $data['school_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></a></td>
      <td><?php echo ($data['open'])?'Ya':'Tidak';?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['principal'];?></a></td>
      <td><?php echo $data['state_id'];?></td>
      <td><?php echo $data['city_id'];?></td>
      <td><a href="siswa.php?school_id=<?php echo $data['school_id'];?>"><?php echo ($data['student']);?></a></td>
      <td><a href="guru.php?school_id=<?php echo $data['school_id'];?>"><?php echo ($data['teacher']);?></a></td>
      <td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
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
  <input type="submit" name="delete" id="delete" value="Hapus" class="com-button delete-button" onclick="return confirm('Menghapus data sekolah menyebabkan seluruh data sekolah tersebut tidak dapat diakses lagi. Data sekolah yang dihapus tidak dapat dikembalikan dan meskipun super administrator membuat sekolah baru dengan nama yang sama. Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=add'" />
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