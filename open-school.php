<?php
include_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";
$cfg->module_title = "Pilih Sekolah";
include_once dirname(__FILE__) . "/lib.inc/cfg.pagination.php";
if (@$_GET['option'] == 'select') {
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_INT);
	$sql = "
	select `edu_school3`.* from(
	
	select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
	`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`
	from `edu_member_school`
	inner join(`edu_school` as `edu_school1`) on(`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
	where `edu_member_school`.`member_id` = '$member_id' and `edu_member_school`.`role` = 'S'
	
	union
	
	select `edu_school2`.`school_id`, `edu_school2`.`name`, `edu_school2`.`school_grade_id`, `edu_school2`.`public_private`, 
	`edu_school2`.`principal`, `edu_school2`.`active`, `edu_school2`.`open`
	from `edu_school` as `edu_school2`
	where `edu_school2`.`open` = '1' and `edu_school2`.`active` = '1' 
	) as `edu_school3`
	where 1 and `edu_school3`.`school_id` = '$school_id'
	order by `edu_school3`.`open` asc, `edu_school3`.`name` asc
	";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$sql = "update `edu_student` set `school_id` = '$school_id' where `student_id` = '$member_id' ";
		$database->execute($sql);
		header('Location: index.php');
		exit();
	}
}
$base_dir = 'siswa/';
$school_code_from_parser = 'student';
if (@$_GET['option'] == 'detail') {
	include_once dirname(__FILE__) . "/lib.assets/theme/default/header-home.php";
	$edit_key = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_INT);
	$nt = '';
	$sql = "select `edu_school`.* $nt,
	(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id`) as `student`,
	(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
	(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
	(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
	(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_create`) as `admin_create`,
	(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) as `admin_edit`
	from `edu_school` 
	where 1
	and `edu_school`.`school_id` = '$edit_key'
";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
		<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/home-row-table.css" />
		<div class="main-content">
			<div class="main-content-wrapper">
				<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
					<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
						<tr>
							<td>Nama Sekolah</td>
							<td><?php echo $data['name'];?></td>
						</tr>
						<tr>
							<td>Jenjang</td>
							<td><?php 
							if ($data['school_grade_id'] == 3) {
								echo 'SD';
							}
							if ($data['school_grade_id'] == 4) {
								echo 'SMP';
							}
							if ($data['school_grade_id'] == 5){
								echo 'SMA Sederajat';
							}  
							?></td>
						</tr>
						<tr>
							<td>Negeri/Swasta</td>
							<td><?php if ($data['public_private'] == 'U') {echo 'Negeri';}
								if ($data['public_private'] == 'I') {echo 'Swasta';} ?></td>
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
							<td>Telepon</td>
							<td><?php echo $data['phone'];?></td>
						</tr>
						<tr>
							<td>Email</td>
							<td><?php echo $data['email'];?></td>
						</tr>
						<tr>
							<td>Bahasa</td>
							<td><?php if ($data['language'] == 'en') {echo 'English';}
								if ($data['language'] == 'id') {echo 'Bahasa Indonesia';} ?></td>
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
							<td>Jumlah Siswa</td>
							<td><?php echo $data['student']; ?> siswa</td>
						</tr>
						<tr>
							<td>Dibuat</td>
							<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
						</tr>
					</table>
					<div class="button-area">
						<input type="button" name="select" id="select" class="def-button" value="Pilih" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=select&school_id=<?php echo $data['school_id']; ?>'" />
						<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="def-button" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>'" /> </td>
					</div>
				</form>
			</div>
		</div>
	<?php
	} else {
	?>
		<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>">Klik di sini untuk kembali.</a></div>
	<?php
	}
	include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-home.php";
} else {
	include_once dirname(__FILE__) . "/lib.assets/theme/default/header-home.php";
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/home-row-table.css" />
	<div class="main-content">
		<div class="main-content-wrapper">
			<div class="search-control">
				<form id="searchform" name="form1" method="get" action="">
					<input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q'], " 	
    ")))); ?>" />
					<input type="submit" name="search" id="search" value="Cari" class="com-button def-button" />
				</form>
			</div>
			<div class="search-result">
				<?php
				$sql_filter = "";
				$pagination->array_get = array();
				if ($pagination->query) {
					$pagination->array_get[] = 'q';
					$sql_filter .= " and (`edu_school3`.`name` like '%" . addslashes($pagination->query) . "%' )";
				}


				$nt = '';

				$sql = "
				select `edu_school3`.* from(

				select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
				`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`
				from `edu_member_school`
				inner join(`edu_school` as `edu_school1`) on(`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
				where `edu_member_school`.`member_id` = '$member_id' and `edu_member_school`.`role` = 'S'

				union

				select `edu_school2`.`school_id`, `edu_school2`.`name`, `edu_school2`.`school_grade_id`, `edu_school2`.`public_private`, 
				`edu_school2`.`principal`, `edu_school2`.`active`, `edu_school2`.`open`
				from `edu_school` as `edu_school2`
				where `edu_school2`.`open` = '1' and `edu_school2`.`active` = '1' 
				) as `edu_school3`
				where 1 $sql_filter
				order by `edu_school3`.`open` asc, `edu_school3`.`name` asc
				";
				$sql_test = $sql;

				$stmt1 = $database->executeQuery($sql_test);
				$pagination->total_record = $stmt1->rowCount();
				$stmt2 = $database->executeQuery($sql . $pagination->limit_sql);
				$pagination->total_record_with_limit = $stmt2->rowCount();
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
						<div class="search-pagination search-pagination-top">
							<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
							<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
						</div>

						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table bordered hide-some-cell">
							<thead>
								<tr>
									<td width="20">No</td>
									<td>Nama Sekolah</td>
									<td>Jenjang</td>
								</tr>
							</thead>
							<tbody>
								<?php
								$no = $pagination->offset;
								$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								foreach($rows as $data) {
									$no++;
								?>
									<tr<?php echo (@$data['active']) ? " class=\"data-active\"" : " class=\"data-inactive\""; ?>>
										<td align="right"><?php echo $no;?></td>
										<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&school_id=<?php echo $data['school_id']; ?>"><?php echo $data['name']; ?></a></td>
										<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&school_id=<?php echo $data['school_id']; ?>"><?php 
										if ($data['school_grade_id'] == 3) {
											echo 'SD';
										}
										if ($data['school_grade_id'] == 4) {
											echo 'SMP';
										}
										if ($data['school_grade_id'] == 5) {
											echo 'SMA'; 
										}
										?></a></td>
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

					</form>
				<?php
				} else if (@$_GET['q']) {
				?>
					<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
				<?php
				} else {
				?>
					<div class="warning">Data tidak ditemukan. <a href="impor-data.php?option=add">Klik di sini untuk membuat sekolah.</a></div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
<?php
	include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-home.php";
}
?>