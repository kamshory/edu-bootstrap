<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}


$pageTitle = "Sekolah";
$pagination = new \Pico\PicoPagination();
if(count(@$_POST) && isset($_POST['save']))
{
	$school_id = kh_filter_input(INPUT_POST, "school_id2", FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);

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
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$description = kh_filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);
	$school_type_id = kh_filter_input(INPUT_POST, "school_type_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$school_grade_id = kh_filter_input(INPUT_POST, "school_grade_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$public_private = kh_filter_input(INPUT_POST, "public_private", FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, "open", FILTER_SANITIZE_NUMBER_INT);
	$principal = kh_filter_input(INPUT_POST, "principal", FILTER_SANITIZE_SPECIAL_CHARS);
	$address = kh_filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);
	$language = kh_filter_input(INPUT_POST, "language", FILTER_SANITIZE_SPECIAL_CHARS);
	$country_id = kh_filter_input(INPUT_POST, "country_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$state_id = kh_filter_input(INPUT_POST, "state_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$city_id = kh_filter_input(INPUT_POST, "city_id", FILTER_SANITIZE_SPECIAL_CHARS);
	$student = kh_filter_input(INPUT_POST, "student", FILTER_SANITIZE_NUMBER_INT);
	$prevent_change_school = kh_filter_input(INPUT_POST, "prevent_change_school", FILTER_SANITIZE_NUMBER_UINT);
	$prevent_resign = kh_filter_input(INPUT_POST, "prevent_resign", FILTER_SANITIZE_NUMBER_UINT);
	$use_token = kh_filter_input(INPUT_POST, "use_token", FILTER_SANITIZE_NUMBER_UINT);
	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);



}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$token_school = md5($name.'-'.time().'-'.mt_rand(111111, 999999));
	$sql = "INSERT INTO `edu_school` 
	(`school_code`, `token_school`, `name`, `description`, `school_type_id`, `school_grade_id`, `public_private`, `open`, `principal`, `address`, `phone`, `email`, `language`, `country_id`, `prevent_change_school`, `prevent_resign`, `use_token`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
	('$school_code', '$token_school', '$name', '$description', '$school_type_id', '$school_grade_id', '$public_private', '$open', '$principal', '$address', '$phone', '$email', '$language', '$country_id', '$prevent_change_school', '$prevent_resign', '$use_token', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '$active')";
	$database->executeInsert($sql, true);
	$school_id = $database->getDatabaseConnection()->lastInsertId();
	$sql = "UPDATE `edu_school` SET `state_id` = '$state_id' WHERE `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	
	$sql = "UPDATE `edu_school` SET `city_id` = '$city_id' WHERE `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	header("Location:".$picoEdu->gateBaseSelfName()."?option=detail&school_id=$school_id");
}
if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$sql1 = "UPDATE `edu_student` SET `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	WHERE `school_id` = '$school_id' 
	";
	$database->executeUpdate($sql1, true);

	$sql2 = "UPDATE `edu_school` SET `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	WHERE `school_id` = '$school_id' 
	";
	$database->executeUpdate($sql2, true);

	$sql = "UPDATE `edu_school` SET
	`school_code` = '$school_code', `name` = '$name', `school_grade_id` = '$school_grade_id', `public_private` = '$public_private', 
	`open` = '$open', `principal` = '$principal', `address` = '$address', `phone` = '$phone', `email` = '$email', `country_id` = '$country_id',
	`use_token` = '$use_token'
	WHERE `school_id` = '$school_id'
	";
	$database->executeUpdate($sql, true);
	
	$sql = "UPDATE `edu_school` SET `state_id` = '$state_id' WHERE `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	
	$sql = "UPDATE `edu_school` SET `city_id` = '$city_id' WHERE `school_id` = '$school_id' ";
	$database->executeUpdate($sql, true);
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&school_id=$school_id");
}

if(isset($_POST['set_active']) && isset($_POST['school_id']))
{
	$schools = @$_POST['school_id'];
	if(isset($schools) && is_array($schools))
	{
		foreach($schools as $key=>$val)
		{
			$school_id = addslashes($val);
			$sql = "UPDATE `edu_school` SET `active` = true WHERE `school_id` = '$school_id'  ";
			$database->executeUpdate($sql, true);
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
			$sql = "UPDATE `edu_school` SET `active` = false WHERE `school_id` = '$school_id'  ";
			$database->executeUpdate($sql, true);
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
			$sql = "DELETE FROM `edu_school` WHERE `school_id` = '$school_id'  ";
			$database->executeDelete($sql, true);
		}
	}
}

if(@$_GET['option'] == 'add')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
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
			$('#state_id').replaceWith('<select class="form-control" name="state_id" id="state_id" required="required"></select>');
			$('#state_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Provinsi'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
			$('#city_id').replaceWith('<select class="form-control" name="city_id" id="city_id" required="required"></select>');
			$('#city_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
				$(this).replaceWith('<input class="form-control" type="text" name="state_id" id="state_id" required="required">');
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
				data:{state_id:state_name, country_id:country_id},
				success: function(data){
					$('#city_id').replaceWith('<select class="form-control" name="city_id" id="city_id" required="required"></select>');
					$('#city_id').empty();
					var html = '';
					html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
					var i, j;
					for(i in data)
					{
						html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
				$(this).replaceWith('<input class="form-control" type="text" name="city_id" id="city_id" required="required">');
				$('#city_id').select();
			}
		}
	});
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
});

</script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text" name="name" id="name" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><select class="form-control input-select" name="school_grade_id" id="school_grade_id">
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
		<td><select class="form-control input-select" name="public_private" id="public_private">
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
		</td><td><input type="text" class="form-control input-text" name="principal" id="principal" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea class="form-control input-textarea" name="address" id="address"></textarea></td>
		</tr>
		<tr>
		<td>Telepon
		</td><td><input type="tel" class="form-control input-text" name="phone" id="phone" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><input type="email" class="form-control input-text" name="email" id="email" autocomplete="off" data-type="email" /></td>
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
		</td><td><select class="form-control input-select" name="country_id" id="country_id">
		<option value=""></option>
		<?php
        $sql2 = "SELECT * FROM `country` WHERE `active` = true ORDER BY `sort_order` ASC
		";

		echo $picoEdu->createFilterDb(
			$sql2,
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
		</td><td><select class="form-control input-select" name="state_id" id="state_id">
		<option value="">- Pilih Provinsi -</option>
			<?php
            $sql2 = "SELECT * FROM `state` WHERE `active` = true AND `verify` = '1' AND `country_id` = '$data[country_id]' ORDER BY `type` ASC, `name` ASC
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
						'delimiter'=>\Pico\PicoConst::RAQUO,
						'values'=>array(
							'name'
						)
					)
				)
			);
            ?>
            <option value="--">- Tambah Provinsi -</option>
		</select></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota
		</td><td><select class="form-control input-select" name="city_id" id="city_id">
		<option value="">- Pilih Kabupaten/Kota -</option>
			<?php
            $sql2 = "SELECT * FROM `city` WHERE `active` = true AND `verify` = '1' AND `country_id` = '$data[country_id]' AND (`state_id` = '$data[state_id]' OR `state_id` = '' OR `state_id` is null) ORDER BY `type` ASC, `name` ASC 
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
						'delimiter'=>\Pico\PicoConst::RAQUO,
						'values'=>array(
							'name'
						)
					)
				)
			);
            ?>
		<option value="--">- Tambah Kabupaten/Kota -</option>
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
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
        <input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
<?php getDefaultValues($database, 'edu_school', array('school_type_id','school_grade_id','public_private','open','language','country_id','prevent_change_school','prevent_resign','use_token','active')); ?>
</form>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'edit')
{
$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR

$state_list = array();
$city_list = array();
$sql = "SELECT `edu_school`.* 
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);

$sql = "SELECT `state`.`state_id` AS `v`, `state`.`name` AS `l`
FROM `state` WHERE `state`.`country_id` = '".$data['country_id']."' 
";
$stmtx = $database->executeQuery($sql);
if ($stmtx->rowCount() > 0) {
	$state_list = $stmtx->fetchAll(\PDO::FETCH_ASSOC);
}
$sql = "SELECT `city`.`city_id` AS `v`, `city`.`name` AS `l`
FROM `city` WHERE `city`.`country_id` = '".$data['country_id']."' 
AND (`city`.`state_id` = '".$data['state_id']."' OR `city`.`state_id` = '' OR `city`.`state_id` is null) 
";
$stmtx = $database->executeQuery($sql);
if ($stmtx->rowCount() > 0) {
	$city_list = $stmtx->fetchAll(\PDO::FETCH_ASSOC);
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
			$('#state_id').replaceWith('<select class="form-control" name="state_id" id="state_id" required="required"></select>');
			$('#state_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Provinsi'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
			$('#city_id').replaceWith('<select class="form-control" name="city_id" id="city_id" required="required"></select>');
			$('#city_id').empty();
			var html = '';
			html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
			var i, j;
			for(i in data)
			{
				html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
				$(this).replaceWith('<input class="form-control" type="text" name="state_id" id="state_id" required="required">');
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
				data:{state_id:state_name, country_id:country_id},
				success: function(data){
					$('#city_id').replaceWith('<select class="form-control" name="city_id" id="city_id" required="required"></select>');
					$('#city_id').empty();
					var html = '';
					html += '<option value="">'+'Pilih Kabupaten/Kota'+'</option>';
					var i, j;
					for(i in data)
					{
						html += '<option value="'+data[i].v+'">'+data[i].l+'</option>';
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
				$(this).replaceWith('<input class="form-control" type="text" name="city_id" id="city_id" required="required">');
				$('#city_id').select();
			}
		}
	});
	var prefix = $('#country_id option:selected').attr('data-code');
	$('#phone_code').val(prefix);
});
</script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><input type="text" class="form-control input-text" name="name" id="name" value="<?php echo $data['name'];?>" autocomplete="off" />
        <input type="hidden" name="school_id2" id="school_id2" value="<?php echo $data['school_id'];?>" /></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><select class="form-control input-select" name="school_grade_id" id="school_grade_id">
		<option value=""></option>
		<?php
		echo $picoEdu->getSchoolGradeOption($data['school_grade_id']);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><select class="form-control input-select" name="public_private" id="public_private">
		<option value=""></option>
		<?php
		echo $picoEdu->getSchoolTypeOption($data['public_private']);
		?>
		</select></td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"<?php echo $picoEdu->ifMatch($data['open'], 1, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED);?>> Terbuka</label>
		</td>
		</tr>
		<tr>
		<td>Kepala Sekolah
		</td><td><input type="text" class="form-control input-text" name="principal" id="principal" value="<?php echo $data['principal'];?>" autocomplete="off" /></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><textarea class="form-control input-textarea" name="address" id="address"><?php echo $data['address'];?></textarea></td>
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
		<td>Bahasa
		</td><td>
        <select class="form-control" name="language" id="language" data-full-width="true">
			<option value="en">English</option>
			<option value="id">Bahasa Indonesia</option>
        </select>
        </td>
		</tr>
		<tr>
		<td>Negara
		</td><td><select class="form-control input-select" name="country_id" id="country_id">
		<option value=""></option>
		<?php
        $sql2 = "SELECT * FROM `country` WHERE `active` = true ORDER BY `sort_order` ASC
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
		<td>Provinsi
		</td><td><select class="form-control input-select" name="state_id" id="state_id">
		<option value="">- Pilih Provinsi -</option>
			<?php
            $sql2 = "SELECT * FROM `state` WHERE `active` = true AND `verify` = '1' AND `country_id` = '$data[country_id]' ORDER BY `type` ASC, `name` ASC
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
						'delimiter'=>\Pico\PicoConst::RAQUO,
						'values'=>array(
							'name'
						)
					)
				)
			);
            ?>
            <option value="--">- Tambah Provinsi -</option>
		</select></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota
		</td><td><select class="form-control input-select" name="city_id" id="city_id">
		<option value="">- Pilih Kabupaten/Kota -</option>
			<?php
            $sql2 = "SELECT * FROM `city` WHERE `active` = true AND `verify` = '1' AND `country_id` = '$data[country_id]' AND (`state_id` = '$data[state_id]' OR `state_id` = '' OR `state_id` is null) ORDER BY `type` ASC, `name` ASC 
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
						'delimiter'=>\Pico\PicoConst::RAQUO,
						'values'=>array(
							'name'
						)
					)
				)
			);
            ?>
		<option value="--">- Tambah Kabupaten/Kota -</option>
		</select></td>
		</tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_change_school" value="1" id="prevent_change_school"<?php if($data['prevent_change_school']==1) echo \Pico\PicoConst::INPUT_CHECKBOX_CHECKED;?>> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><label><input type="checkbox" class="input-checkbox" name="prevent_resign" value="1" id="prevent_resign"<?php if($data['prevent_resign']==1) echo \Pico\PicoConst::INPUT_CHECKBOX_CHECKED;?>> Ya</label>
		</td>
		</tr>
		<tr>
		<td>Sistem Token</td>
		<td><label><input type="checkbox" class="input-checkbox" name="use_token" value="1" id="use_token"<?php if($data['use_token']==1) echo \Pico\PicoConst::INPUT_CHECKBOX_CHECKED;?>> Ya</label>
		</td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr><td></td>
		<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
		<input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
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
$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id`) AS `student`,
(SELECT COUNT(DISTINCT `edu_class`.`class_id`) FROM `edu_class` WHERE `edu_class`.`school_id` = `edu_school`.`school_id`) AS `class`,
(SELECT COUNT(DISTINCT `edu_teacher`.`teacher_id`) FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = `edu_school`.`school_id`) AS `teacher`,
(SELECT `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) AS `country_id`,
(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) AS `admin_edit`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Kode Sekolah</td>
		<td><a href="../<?php echo $data['school_code'];?>" target="_blank"><?php echo $data['school_code'];?></a></td>
		</tr>
		<tr>
		<td>Token Sekolah</td>
		<td><?php echo $data['token_school'];?> </td>
		</tr>
		<tr>
		<td>Nama Sekolah</td>
		<td><a href="../<?php echo $data['school_code'];?>" target="_blank"><?php echo $data['name'];?></a></td>
		</tr>
		<tr>
		<td>Keterangan</td>
		<td><?php echo $data['description'];?> </td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?> </td>
		</tr>
		<tr>
		<td>N/S</td>
		<td><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?> </td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?> </td>
		</tr>
		<tr>
		<td>Address</td>
		<td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Phone</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
		<tr>
		<td>Bahasa</td>
		<td><?php echo $picoEdu->selectFromMap($data['language'], array('en'=>'English', 'id'=>'Bahasa Indonesia'));?> </td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?> </td>
		</tr>
		<tr>
		<td>Propinsi</td>
		<td><?php echo $data['state_id'];?> </td>
		</tr>
		<tr>
		<td>Kabupaten/Kota</td>
		<td><?php echo $data['city_id'];?> </td>
		</tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><?php echo ($data['prevent_change_school'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><?php echo ($data['prevent_resign'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Sistem Token</td>
		<td><?php echo ($data['use_token'])?'Ya':'Tidak';?> </td>
		</tr>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class'];?> </td>
		</tr>
		<tr>
		<td>Siswa</td>
		<td><?php echo $data['student'];?> </td>
		</tr>
		<tr>
		<td>Guru</td>
		<td><?php echo $data['teacher'];?> </td>
		</tr>
		<tr>
		<td>Waktu Buat</td>
		<td><?php echo $data['time_create'];?> </td>
		</tr>
		<tr>
		<td>Waktu Buat</td>
		<td><?php echo $data['time_edit'];?> </td>
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
		<td>Aktif</td>
		<td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<td></td>
		<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&school_id=<?php echo $data['school_id'];?>'" /> 
		<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
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
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Nama Sekolah</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_school`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}


$nt = '';


$sql = "SELECT `edu_school`.* $nt,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id`) AS `student`,
(SELECT COUNT(DISTINCT `edu_teacher`.`teacher_id`) FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = `edu_school`.`school_id`) AS `teacher`,
(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`
FROM `edu_school`
WHERE (1=1) $sql_filter
ORDER BY `edu_school`.`school_id` DESC
";
$sql_test = "SELECT `edu_school`.*
FROM `edu_school`
WHERE (1=1) $sql_filter
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

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-school_id" id="control-school_id" class="checkbox-selector" data-target=".school_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
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
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td><input type="checkbox" name="school_id[]" id="school_id" value="<?php echo $data['school_id'];?>" class="school_id" /></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&school_id=<?php echo $data['school_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['open'], 'Ya', 'Tidak');?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['principal'];?></a></td>
      <td><?php echo $data['state_id'];?> </td>
      <td><?php echo $data['city_id'];?> </td>
      <td><a href="siswa.php?school_id=<?php echo $data['school_id'];?>"><?php echo $data['student'];?></a></td>
      <td><a href="guru.php?school_id=<?php echo $data['school_id'];?>"><?php echo $data['teacher'];?></a></td>
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
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Menghapus data sekolah menyebabkan seluruh data sekolah tersebut tidak dapat diakses lagi. Data sekolah yang dihapus tidak dapat dikembalikan dan meskipun super administrator membuat sekolah baru dengan nama yang sama. Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=add'" />
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