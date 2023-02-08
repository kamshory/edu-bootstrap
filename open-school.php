<?php
require_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";
$cfg->page_title = "Pilih Sekolah";
require_once dirname(__FILE__) . "/lib.inc/cfg.pagination.php";
if (@$_GET['option'] == 'select') {
	$school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "
	select `edu_school3`.* from(
	
	select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
	`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`
	FROM `edu_member_school`
	INNER JOIN (`edu_school` AS `edu_school1`) ON (`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
	WHERE `edu_member_school`.`member_id` = '$member_id' AND `edu_member_school`.`role` = 'S'
	
	union
	
	select `edu_school2`.`school_id`, `edu_school2`.`name`, `edu_school2`.`school_grade_id`, `edu_school2`.`public_private`, 
	`edu_school2`.`principal`, `edu_school2`.`active`, `edu_school2`.`open`
	FROM `edu_school` AS `edu_school2`
	WHERE `edu_school2`.`open` = '1' AND `edu_school2`.`active` = true 
	) AS `edu_school3`
	WHERE `edu_school3`.`school_id` = '$school_id'
	ORDER BY `edu_school3`.`open` ASC, `edu_school3`.`name` ASC
	";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$sql = "UPDATE `edu_student` SET `school_id` = '$school_id' WHERE `student_id` = '$member_id' ";
		$database->execute($sql);
		header('Location: index.php');
		exit();
	}
}
$base_dir = 'siswa/';
$school_code_from_parser = 'student';
if (@$_GET['option'] == 'detail') {
	include_once dirname(__FILE__) . "/lib.assets/theme/default/header-home.php";
	$edit_key = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
	$nt = '';
	$sql = "SELECT `edu_school`.* $nt,
	(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id`) AS `student`,
	(SELECT `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) AS `country_id`,
	(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
	(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`,
	(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_create`) AS `admin_create`,
	(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) AS `admin_edit`
	FROM `edu_school` 
	where 1
	AND `edu_school`.`school_id` = '$edit_key'
";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
		<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/home-row-table.css" />
		<div class="main-content">
			<div class="main-content-wrapper">
				<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
					<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
						<tr>
							<td>Nama Sekolah</td>
							<td><?php echo $data['name'];?> </td>
						</tr>
						<tr>
							<td>Jenjang</td>
							<td><?php 
							echo $picoEdu->getSchoolGradeName($data['school_grade_id']);
							?> </td>
						</tr>
						<tr>
							<td>Negeri/Swasta</td>
							<td><?php if ($data['public_private'] == 'U') {echo 'Negeri';}
								if ($data['public_private'] == 'I') {echo 'Swasta';} ?> </td>
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
							<td>Bahasa</td>
							<td><?php if ($data['language'] == 'en') {echo 'English';}
								if ($data['language'] == 'id') {echo 'Bahasa Indonesia';} ?> </td>
						</tr>
						<tr>
							<td>Negara</td>
							<td><?php echo $data['country_id'];?> </td>
						</tr>
						<tr>
							<td>Provinsi</td>
							<td><?php echo $data['state_id'];?> </td>
						</tr>
						<tr>
							<td>Kabupaten/Kota</td>
							<td><?php echo $data['city_id'];?> </td>
						</tr>
						<tr>
							<td>Jumlah Siswa</td>
							<td><?php echo $data['student']; ?> siswa</td>
						</tr>
						<tr>
							<td>Dibuat</td>
							<td><?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
						</tr>
					</table>
				</table>
		<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
							<tr>
								<td></td>
								<td>
						<input type="button" name="select" id="select" class="def-button" value="Pilih" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?option=select&school_id=<?php echo $data['school_id']; ?>'" />
						<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="def-button" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>'" /> 
					</td>
						</table>
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
					<input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q'], " 	
    ")))); ?>" />
					<input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success def-button" />
				</form>
			</div>
			<div class="search-result">
				<?php
				$sql_filter = "";
				
				if ($pagination->query) {
					$pagination->array_get[] = 'q';
					$sql_filter .= " AND (`edu_school3`.`name` like '%" . addslashes($pagination->query) . "%' )";
				}


				$nt = '';

				$sql = "
				select `edu_school3`.* from(

				select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
				`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`
				FROM `edu_member_school`
				INNER JOIN (`edu_school` AS `edu_school1`) ON (`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
				WHERE `edu_member_school`.`member_id` = '$member_id' AND `edu_member_school`.`role` = 'S'

				union

				select `edu_school2`.`school_id`, `edu_school2`.`name`, `edu_school2`.`school_grade_id`, `edu_school2`.`public_private`, 
				`edu_school2`.`principal`, `edu_school2`.`active`, `edu_school2`.`open`
				FROM `edu_school` AS `edu_school2`
				WHERE `edu_school2`.`open` = '1' AND `edu_school2`.`active` = true 
				) AS `edu_school3`
				WHERE (1=1) $sql_filter
				ORDER BY `edu_school3`.`open` ASC, `edu_school3`.`name` ASC
				";
				
				$sql_test = "
				select `edu_school3`.* from(

				select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
				`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`
				FROM `edu_member_school`
				INNER JOIN (`edu_school` AS `edu_school1`) ON (`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
				WHERE `edu_member_school`.`member_id` = '$member_id' AND `edu_member_school`.`role` = 'S'

				union

				select `edu_school2`.`school_id`, `edu_school2`.`name`, `edu_school2`.`school_grade_id`, `edu_school2`.`public_private`, 
				`edu_school2`.`principal`, `edu_school2`.`active`, `edu_school2`.`open`
				FROM `edu_school` AS `edu_school2`
				WHERE `edu_school2`.`open` = '1' AND `edu_school2`.`active` = true 
				) AS `edu_school3`
				WHERE (1=1) $sql_filter
				";

				$stmt1 = $database->executeQuery($sql_test);
				$pagination->total_record = $stmt1->rowCount();
				$stmt2 = $database->executeQuery($sql . $pagination->limit_sql);
				$pagination->total_record_with_limit = $stmt2->rowCount();
				if ($pagination->total_record_with_limit) {
					$pagination->start = $pagination->offset + 1;
					$pagination->end = $pagination->offset + $pagination->total_record_with_limit;

					$pagination->result = $pagination->createPagination(
						basename($_SERVER['PHP_SELF']),
						$pagination->total_record,
						$pagination->limit,
						$pagination->num_page,
						$pagination->offset,
						
						true,
						$pagination->str_first,
						$pagination->str_last,
						$pagination->str_prev,
						$pagination->str_next
					);
					$paginationHTML = $pagination->createPaginationHtml();
					
				?>
					<form name="form1" method="post" action="">
						<div class="d-flex search-pagination search-pagination-top">
							<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
							<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
						</div>

						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm bordered hide-some-cell">
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
									<tr class="<?php echo $picoEdu->getRowClass($data);?>">
										<td align="right"><?php echo $no;?> </td>
										<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&school_id=<?php echo $data['school_id']; ?>"><?php echo $data['name']; ?></a></td>
										<td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&school_id=<?php echo $data['school_id']; ?>"><?php 
										echo $picoEdu->getSchoolGradeName($data['school_grade_id']);
										?></a></td>
										</tr>
									<?php
								}
									?>
							</tbody>
						</table>

						<div class="d-flex search-pagination search-pagination-bottom">
							<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
							<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
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