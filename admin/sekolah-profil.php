<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-admin.php";
exit();
}
$cfg->module_title = "Profil Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(isset($_POST['save']) && count(@$_POST))
{
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

	$school_grade_id = kh_filter_input(INPUT_POST, 'school_grade_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$public_private = kh_filter_input(INPUT_POST, 'public_private', FILTER_SANITIZE_SPECIAL_CHARS);
	$open = kh_filter_input(INPUT_POST, 'open', FILTER_SANITIZE_NUMBER_UINT);
	$principal = kh_filter_input(INPUT_POST, 'principal', FILTER_SANITIZE_SPECIAL_CHARS);
	$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
	$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$language = kh_filter_input(INPUT_POST, 'language', FILTER_SANITIZE_SPECIAL_CHARS);
	$country_id = kh_filter_input(INPUT_POST, 'country_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$state_id = kh_filter_input(INPUT_POST, 'state_id', FILTER_SANITIZE_SPECIAL_CHARS);
	$city_id = kh_filter_input(INPUT_POST, 'city_id', FILTER_SANITIZE_SPECIAL_CHARS);

	$prevent_change_school = kh_filter_input(INPUT_POST, 'prevent_change_school', FILTER_SANITIZE_NUMBER_UINT);
	$prevent_resign = kh_filter_input(INPUT_POST, 'prevent_resign', FILTER_SANITIZE_NUMBER_UINT);
	$use_token = kh_filter_input(INPUT_POST, 'use_token', FILTER_SANITIZE_NUMBER_UINT);


	$sql1 = "update `edu_student` set `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	where `school_id` = '$school_id' 
	";
	$database->executeUpdate($sql1);

	$sql2 = "update `edu_school` set `prevent_change_school` = '$prevent_change_school', `prevent_resign` = '$prevent_resign'
	where `school_id` = '$school_id' 
	";
	$database->exeexecuteUpdatecute($sql2);

	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	
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
	header("Location: ".basename($_SERVER['PHP_SELF']));
		
}

if(isset($_POST['set_active']) && isset($_POST['school_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['school_id'], "edu_school", 'school_id', 1);
}
if(isset($_POST['set_inactive']) && isset($_POST['school_id']))
{
	$picoEdu->changerecordstatus('active', $_POST['school_id'], "edu_school", 'school_id', 0);
}


if(@$_GET['option']=='edit')
{
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
$stmt = $database->executeQuery($sql);
$state_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT `city`.`city_id` as `v`, `city`.`name` as `l`
from `city` where `city`.`country_id` = '".$data['country_id']."' 
and (`city`.`state_id` = '".$data['state_id']."' or `city`.`state_id` = '' or `city`.`state_id` is null) 
";
$stmt = $database->executeQuery($sql);
$city_list = $stmt->fetchAll(PDO::FETCH_ASSOC);



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
		<option value="U"<?php 
		if ($data['public_private'] == 'U') {
			echo ' selected="selected"';
		}
		?>>Negeri</option>
		<option value="I"<?php if ($data['public_private'] == 'I') {
			echo ' selected="selected"';
		}
		?>>Swasta</option>
		</select></td>
		</tr>
		<tr>
		<td>Terbuka</td>
		<td><label><input type="checkbox" class="input-checkbox" name="open" value="1" id="open"<?php if ($data['open'] == 1) {
		echo ' checked="checked"';
		}
		?>> Terbuka</label>
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
        $sql = "select * from `country` where `active` = '1' order by `order` asc
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {

			$rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows2 as $data2) {
				?>
            <option data-code="<?php echo $data2['phone_code']; ?>" value="<?php echo $data2['country_id']; ?>"<?php if ($data2['country_id'] == $data['country_id'])
					echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
            <?php
			}
		}
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
            $stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {

				$rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
					?>
                <option value="<?php echo $data2['name']; ?>"<?php if ($data2['state_id'] == $data['state_id'])
					  echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
                <?php
				}
			}
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
            $stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {

				$rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
					?>
                <option value="<?php echo $data2['name']; ?>"<?php if ($data2['city_id'] == $data['city_id'])
					  echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
                <?php
				}
			}
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
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id`) as `student`,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) as `admin_edit`,
(select `edu_admin3`.`name` from `edu_admin` as `edu_admin3` where `edu_admin3`.`admin_id` = `edu_school`.`admin_import_first` limit 0,1) as `admin_import_first`,
(select `edu_admin4`.`name` from `edu_admin` as `edu_admin4` where `edu_admin4`.`admin_id` = `edu_school`.`admin_import_last` limit 0,1) as `admin_import_last`
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
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><?php echo $data['school_code'];?></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php 
		if ($data['school_grade_id'] == 1) {
			echo 'Play Group';
		}
		if ($data['school_grade_id'] == 2) {
			echo 'Taman Kanak-Kanak';
		}
		if ($data['school_grade_id'] == 3) {
			echo 'SD Sederajat';
		}
		if ($data['school_grade_id'] == 4) {
			echo 'SMP Sederajat';
		} 
		if($data['school_grade_id'] == 5) {
			echo 'SMA Sederajat';
		}
		if ($data['school_grade_id'] == 6) {
			echo 'Perguruan Tinggi';
		}?></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><?php if($data['public_private']=='U') {
			echo 'Negeri';
		}
		if ($data['public_private'] == 'I') {
			echo 'Swasta';
		}
		?></td>
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
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
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
		<td>Bahasa</td>
		<td><?php if ($data['language'] == 'en') {
		echo 'English';
		}
		if ($data['language'] == 'id') {
			echo 'Bahasa Indonesia';
		}
		?></td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?></td>
		</tr>
		<tr>
		<td>Provinsi</td>
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
		<td>Impor Pertama</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_import_first'])));?></td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_first'];?></td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_first'];?></td>
		</tr>

		<tr>
		<td>Impor Terakhir</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_import_last'])));?></td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_last'];?></td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_last'];?></td>
		</tr>
		<tr>
		<td></td>
		<td>
        <input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit'" />
        <input type="button" name="switch" id="switch" class="com-button" value="Ganti Sekolah" onclick="window.location='ganti-sekolah.php'" />
        <input type="button" name="import-data" id="import-data" class="com-button" value="Impor Data" onclick="window.location='impor-data.php'" />
        <input type="button" name="public" id="public" class="com-button" value="Halaman Umum" onclick="window.open('../<?php echo $data['school_code'];?>')" />
        </td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Anda tidak terdaftar sebagai Administrator sekolah. <a href="impor-data.php">Klik di sini untuk import data.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
?>