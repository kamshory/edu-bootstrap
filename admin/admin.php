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

$pageTitle = "Administrator";
$pagination = new \Pico\PicoPagination();
$my_admin = $adminLoggedIn->admin_id;

if(count(@$_POST) && isset($_POST['save']))
{
	$admin_id = $admin_id2 = kh_filter_input(INPUT_POST, "admin_id2", FILTER_SANITIZE_STRING_NEW);
	$name = kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
	$gender = kh_filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_place = kh_filter_input(INPUT_POST, "birth_place", FILTER_SANITIZE_SPECIAL_CHARS);
	$birth_day = kh_filter_input(INPUT_POST, "birth_day", FILTER_SANITIZE_STRING_NEW);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$phone = kh_filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	$time_create = $time_edit = $database->getLocalDateTime();
	
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$blocked = kh_filter_input(INPUT_POST, "blocked", FILTER_SANITIZE_NUMBER_UINT);
	$active = kh_filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_UINT);
	
	if($admin_id2 == $adminLoggedIn->admin_id)
	{
		$blocked = 0;
		$active = 1;
	}
}

if(isset($_POST['set_active']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		$val = addslashes($val);
		if($val != $adminLoggedIn->admin_id)
		{
			$sql = "UPDATE `edu_admin` SET `active` = true WHERE `admin_id` = '$val' AND `school_id` = '$school_id'";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['set_inactive']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		if($val != $adminLoggedIn->admin_id)
		{
			$val = addslashes($val);
			$sql = "UPDATE `edu_admin` SET `active` = false WHERE `admin_id` = '$val' AND `school_id` = '$school_id'";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['delete']) && isset($_POST['admin_id']))
{
	$admin_arr = $_POST['admin_id'];
	foreach($admin_arr as $key=>$val)
	{
		if($val != $adminLoggedIn->admin_id)
		{
			$val = addslashes($val);
			$sql = "DELETE FROM `edu_member_school` WHERE `member_id` = '$val' AND `role` = 'A' AND `school_id` = '$school_id' ";
			$database->executeDelete($sql, true);
			$sql = "UPDATE `edu_admin` SET `school_id` = '' WHERE `admin_id` = '$val' AND `school_id` = '$school_id' ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'add')
{
	$sql = "SELECT * FROM `edu_school` WHERE `school_id` = '$school_id' ";
	$stmt = $database->executeQuery($sql);
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	$country_id = $data['country_id'];
	$language = $data['language'];

	$data = array();

	$gender = $picoEdu->mapGender($gender);
	$phone = $picoEdu->fixPhone($phone);
	$email = $picoEdu->filterEmailAddress($email);

	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	
	$token_admin = md5($phone."-".$email."-".time()."-".mt_rand(111111, 999999));
	
	$user_data = array();
	$user_data['name'] = $name;
	$user_data['gender'] = $gender;
	$user_data['email'] = $email;
	$user_data['phone'] = $phone;
	$user_data['password'] = md5(md5($password));
	$user_data['birth_day'] = $birth_day;
	$user_data['address'] = $address;
	$user_data['country_id'] = $country_id;
	$user_data['language'] = $language;


								
	if(!empty($name) && !empty($username))
	{
		$chk = $picoEdu->getExistsingUser($user_data, null);
		$admin_id = addslashes($chk['member_id']);
		$username = addslashes($chk['username']);

		$passwordHash = md5(md5($password));
		
		$sql = "INSERT INTO `edu_admin` 
		(`admin_id`, `school_id`, `username`, `name`, `token_admin`, `email`, `phone`, `password`, 
		`password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
		`ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
		('$admin_id', '$school_id', '$username', '$name', '$token_admin', '$email', '$phone', '$passwordHash', 
		'$password', '$gender', '$birth_day', '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
		'$ip_create', '$ip_edit', false, true);
		";
		$database->executeInsert($sql, true);
		
		$sql2 = "INSERT INTO `edu_member_school` 
		(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
		('$admin_id', '$school_id', 'A', '$time_create', true)
		";
		$database->executeInsert($sql2, true);
		header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&admin_id=$admin_id");
	}
}

if(isset($_POST['save']) && @$_GET['option'] == 'edit')
{
	$passwordHash = md5(md5($password));

	$sql = "UPDATE `edu_admin` SET 
	`name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', 
	`time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit', `blocked` = '$blocked', `active` = '$active'
	WHERE `admin_id` = '$admin_id2' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";
	$database->executeUpdate($sql, true);
	
	$sql = "UPDATE `edu_admin` SET 
	`email` = '$email' WHERE `admin_id` = '$admin_id2' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";
	$database->executeUpdate($sql, true);
	
	$sql = "UPDATE `edu_admin` SET 
	`phone` = '$phone' WHERE `admin_id` = '$admin_id2' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";
	$database->executeUpdate($sql, true);

	if($username != '')
	{
		$sql = "UPDATE `edu_admin` SET 
		`username` = '$username'
		WHERE `admin_id` = '$admin_id2' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";
		$database->executeUpdate($sql, true);
	}
	
	if($password != '')
	{
		$sql = "UPDATE `edu_admin` SET 
		`password` = '$passwordHash'
		WHERE `admin_id` = '$admin_id2' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";
		$database->executeUpdate($sql, true);
	}
	
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&admin_id=$admin_id");
}



if(@$_GET['option'] == 'add')
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	?>
	<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td>Username</td>
			<td><input type="text" class="form-control input-text" name="username" id="username" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Nama</td>
			<td><input type="text" class="form-control input-text" name="name" id="name" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Jenis Kelamin</td>
			<td><select class="form-control input-select" name="gender" id="gender">
			<option value=""></option>
			<option value="M">Laki-Laki</option>
			<option value="W">Perempuan</option>
			</select></td>
			</tr>
			<tr>
			<td>Tempat Lahir</td>
			<td><input type="text" class="form-control input-text" name="birth_place" id="birth_place" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Tanggal Lahir</td>
			<td><input type="date" class="form-control input-text input-text-date" name="birth_day" id="birth_day" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Email</td>
			<td><input type="email" class="form-control input-text" name="email" id="email" autocomplete="off" data-type="email" /></td>
			</tr>
			<tr>
			<td>Telepon</td>
			<td><input type="tel" class="form-control input-text" name="phone" id="phone" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Password</td>
			<td><input type="password" class="form-control input-text" name="password" id="password" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Blokir</td>
			<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"> Blokir</label>
			</td>
			</tr>
			<tr>
			<td>Aktif</td>
			<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"> Aktif</label>
			</td>
			</tr>
		</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td></td>
			<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn btn-secondary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
			</tr>
		</table>
	</form>
	<?php getDefaultValues($database, 'edu_admin', array('blocked','active')); ?>
	<?php
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}


else if(@$_GET['option'] == 'edit')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "admin_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_admin`.* 
FROM `edu_admin` 
WHERE 1=1 
AND `edu_admin`.`school_id` = '$school_id' 
AND `edu_admin`.`admin_id` = '$edit_key' 
AND (`admin_level` != '1' OR `admin_id` = '$my_admin')
";

$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	?>
	<form name="formedu_admin" id="formedu_admin" action="" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td>Username</td>
			<td><input type="text" class="form-control input-text" name="username" id="username" value="<?php echo $data['username'];?>" autocomplete="off" />
			<input type="hidden" name="admin_id2" id="admin_id2" value="<?php echo $data['admin_id'];?>" /></td>
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
			<td>Email</td>
			<td><input type="email" class="form-control input-text" name="email" id="email" value="<?php echo $data['email'];?>" autocomplete="off" data-type="email" /></td>
			</tr>
			<tr>
			<td>Telepon</td>
			<td><input type="tel" class="form-control input-text" name="phone" id="phone" value="<?php echo $data['phone'];?>" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Password</td>
			<td><input type="password" class="form-control input-text" name="password" id="password" autocomplete="off" /></td>
			</tr>
			<tr>
			<td>Blokir</td>
			<td><label><input type="checkbox" class="input-checkbox" name="blocked" value="1" id="blocked"<?php if($data['blocked']==1) {echo \Pico\PicoConst::INPUT_CHECKBOX_CHECKED;}?>> Blokir</label>
			</td>
			</tr>
			<tr>
			<td>Aktif</td>
			<td><label><input type="checkbox" class="input-checkbox" name="active" value="1" id="active"<?php if ($data['active'] == 1) {
				echo \Pico\PicoConst::INPUT_CHECKBOX_CHECKED;
			}?>> Aktif</label>
			</td>
			</tr>
		</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr><td></td>
			<td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
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

else if(@$_GET['option'] == 'detail')
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$edit_key = kh_filter_input(INPUT_GET, "admin_id", FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_admin`.* $nt,
	(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_admin`.`admin_create` limit 0,1) AS `admin_create`,
	(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_admin`.`admin_edit` limit 0,1) AS `admin_edit`
	FROM `edu_admin` 
	WHERE `edu_admin`.`school_id` = '$school_id' 
	AND `edu_admin`.`admin_id` = '$edit_key' AND (`admin_level` != '1' OR `admin_id` = '$my_admin') 
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	?>
	<form name="formedu_admin" action="" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
			<tr>
			<td>Username</td>
			<td><?php echo $data['username'];?> </td>
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
			<td>Email</td>
			<td><?php echo $data['email'];?> </td>
			</tr>
			<tr>
			<td>Telepon</td>
			<td><?php echo $data['phone'];?> </td>
			</tr>
			<tr>
			<td>Password </td>
			<td><?php echo $data['password_initial'];?> </td>
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
			<td><input type="button" name="edit" id="edit" class="btn btn-primary" value="Ubah" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&admin_id=<?php echo $data['admin_id'];?>'" /> 
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
	<span class="search-label">Admin</span>
		<input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
	<input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
	</form>
	</div>
	<div class="search-result">
	<?php
	$sql_filter = "";
	
	if($pagination->getQuery())
	{
		$pagination->appendQueryName('q');
		$sql_filter .= " AND (`edu_admin`.`name` LIKE '%".addslashes($pagination->getQuery())."%' )";
	}
	$sql_filter .= " AND (`admin_level` != '1' OR `admin_id` = '$my_admin') ";

	$nt = '';

	$sql = "SELECT `edu_admin`.* $nt
	FROM `edu_admin`
	WHERE `edu_admin`.`school_id` = '$school_id' $sql_filter
	ORDER BY `edu_admin`.`admin_id` ASC
	";
	$sql_test = "SELECT `edu_admin`.*
	FROM `edu_admin`
	WHERE `edu_admin`.`school_id` = '$school_id' $sql_filter
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
		@media screen and (max-width:800px)
		{
			.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8){
				display:none;
			}
		}
		@media screen and (max-width:399px)
		{
			.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
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
			<td width="16"><input type="checkbox" name="control-admin_id" id="control-admin_id" class="checkbox-selector" data-target=".admin_id" value="1"></td>
			<td width="16"><i class="fas fa-pencil"></i></td>
			<td width="25">No</td>
			<td>Username</td>
			<td>Nama</td>
			<td>L/P</td>
			<td>Email</td>
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
			<td><input type="checkbox" name="admin_id[]" id="admin_id" value="<?php echo $data['admin_id'];?>" class="admin_id" /></td>
			<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&admin_id=<?php echo $data['admin_id'];?>"><i class="fas fa-pencil"></i></a></td>
			<td align="right"><?php echo $no;?> </td>
			<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['username'];?></a></td>
			<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['name'];?></a></td>
			<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['gender'];?></a></td>
			<td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&admin_id=<?php echo $data['admin_id'];?>"><?php echo $data['email'];?></a></td>
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
	<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=add">Klik di sini untuk membuat baru.</a></div>
	<?php
	}
	?>
	</div>

	<?php
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>