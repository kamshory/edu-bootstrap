<?php
include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
include_once dirname(__FILE__) . "/lib.inc/sessions.php";
if (isset($_GET['school_id'])) {
	$page_school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
}
if (@$page_school_id) {
	include_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";

	$sql = "select `edu_school`.*,
	(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
	(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
	(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
	(select count(distinct `edu_class`.`class_id`) from `edu_class` where `edu_class`.`school_id` = `edu_school`.`school_id` group by `edu_class`.`school_id` limit 0,1) as `num_class`,
	(select count(distinct `edu_teacher`.`teacher_id`) from `edu_teacher` where `edu_teacher`.`school_id` = `edu_school`.`school_id` group by `edu_teacher`.`school_id` limit 0,1) as `num_teacher`,
	(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id` group by `edu_student`.`school_id` limit 0,1) as `num_student`
	from `edu_school` 
	where 1
	and `edu_school`.`school_id` = '$page_school_id'
	";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$school_name = $data['name'];
		$school_code = $data['school_code'];

		$page_tab = kh_filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING_NEW);
		if ($page_tab == '') {
			$cfg->page_title = $school_name;
			include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
?>
			<div class="page-title">
				<h3><?php echo $data['name']; ?></h3>
			</div>
			<div class="page-content"><?php echo ($data['description']); ?></div>
			<?php
			include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
		} else if ($page_tab == 'profile' && @$auth_student_id) {
			// student profile

			if (isset($_POST['save']) && @$_GET['option'] == 'edit') {
				$reg_number_national = kh_filter_input(INPUT_POST, 'reg_number_national', FILTER_SANITIZE_SPECIAL_CHARS);
				$name = kh_filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
				$gender = kh_filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
				$birth_place = kh_filter_input(INPUT_POST, 'birth_place', FILTER_SANITIZE_SPECIAL_CHARS);
				$birth_day = kh_filter_input(INPUT_POST, 'birth_day', FILTER_SANITIZE_STRING_NEW);
				$phone = kh_filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
				$email = kh_filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
				$password = kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD);
				$address = kh_filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
				$religion_id = kh_filter_input(INPUT_POST, 'religion_id', FILTER_SANITIZE_SPECIAL_CHARS);
				$time_create = $time_edit = $picoEdu->getLocalDateTime();
				$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
				$admin_create = $admin_edit = $admin_id;
				$sql = "update `edu_student` set 
					`reg_number_national` = '$reg_number_national', `name` = '$name', `gender` = '$gender', `birth_place` = '$birth_place', `birth_day` = '$birth_day', `phone` = '$phone', `address` = '$address', `time_edit` = '$time_edit', `admin_edit` = '$admin_edit', `ip_edit` = '$ip_edit'
					where `student_id` = '$student_id' and `school_id` = '$school_id' ";
					$database->execute($sql);
				if ($email != '') {
					$sql = "update `edu_student` set 
					`email` = '$email'
					where `student_id` = '$student_id' and `school_id` = '$school_id' ";
					$database->execute($sql);
				}
				if ($password != '') {
					$sql = "update `edu_student` set 
					`password` = md5(md5('$password')), `password_initial` = ''
					where `student_id` = '$student_id' and `school_id` = '$school_id' ";
					$database->execute($sql);
					$sql = "update `member` set 
					`password` = md5(md5('$password'))
					where `member_id` = '$student_id'  ";
					$database->execute($sql);
					$_SESSION['password'] = md5($password);
					$ksession->forcesave();
				}
				header("Location: profil.php");
			}

			$cfg->page_title = "Pofil Siswa " . $school_name;


			if (@$_GET['option'] == 'edit') {
				include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
				$sql = "select `edu_student`.* 
					from `edu_student` 
					where `edu_student`.`school_id` = '$school_id'
					and `edu_student`.`student_id` = '$student_id'
					";
				$stmt = $database->executeQuery($sql);
				if ($stmt->rowCount() > 0) {
					$data = $stmt->fetch(PDO::FETCH_ASSOC);
			?>
					<form name="formedu_student" id="formedu_student" action="" method="post" enctype="multipart/form-data">
						<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
							<tr>
								<td>Nama</td>
								<td><input type="text" class="input-text input-text-long" name="name" id="name" value="<?php echo $data['name']; ?>" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>NISN</td>
								<td><input type="text" class="input-text input-text-long" name="reg_number_national" id="reg_number_national" value="<?php echo $data['reg_number_national']; ?>" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>Jenis Kelamin</td>
								<td><select class="input-select" name="gender" id="gender">
										<option value=""></option>
										<option value="M" <?php if ($data['gender'] == 'M') echo ' selected="selected"'; ?>>Laki-Laki</option>
										<option value="W" <?php if ($data['gender'] == 'W') echo ' selected="selected"'; ?>>Perempuan</option>
									</select></td>
							</tr>
							<tr>
								<td>Tempat Lahir</td>
								<td><input type="text" class="input-text input-text-long" name="birth_place" id="birth_place" value="<?php echo $data['birth_place']; ?>" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>Tanggal Lahir</td>
								<td><input type="date" class="input-text input-text-date" name="birth_day" id="birth_day" value="<?php echo ($data['birth_day']); ?>" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>Telepon
								</td>
								<td><input type="tel" class="input-text input-text-long" name="phone" id="phone" value="<?php echo $data['phone']; ?>" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>Email</td>
								<td><input type="email" class="input-text input-text-long" name="email" id="email" value="<?php echo $data['email']; ?>" autocomplete="off" data-type="email" /></td>
							</tr>
							<tr>
								<td>Password</td>
								<td><input type="password" class="input-text input-text-long" name="password" id="password" autocomplete="off" /></td>
							</tr>
							<tr>
								<td>Alamat</td>
								<td><textarea name="address" class="input-text input-text-long" id="address" autocomplete="off"><?php echo $data['address']; ?></textarea></td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" name="save" id="save" class="com-button" value="Simpan" /> <input type="button" name="showall" id="showall" value="Tampilkan" class="com-button" onclick="window.location='profil.php'" /></td>
							</tr>
						</table>
					</form>
				<?php
				} else {
				?>
					<div class="warning">Data tidak ditemukan. <a href="profil.php">Klik di sini untuk kembali.</a></div>
				<?php
				}
				include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
			} else {
				include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
				$nt = '';
				$sql = "select `edu_student`.* , `edu_school`.`name` as `school_name`, `edu_school`.`open` as `school_open`,
					(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_student`.`admin_create`) as `admin_create`,
					(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_student`.`admin_edit`) as `admin_edit`,
					(select `edu_class`.`name` from `edu_class` where `edu_class`.`class_id` = `edu_student`.`class_id` limit 0,1) as `class_id`
					from `edu_student` 
					left join(`edu_school`) on(`edu_school`.`school_id` = `edu_student`.`school_id`)
					where `edu_student`.`school_id` = '$school_id'
					and `edu_student`.`student_id` = '$student_id'
					";
					$stmt = $database->executeQuery($sql);
					if ($stmt->rowCount() > 0) {
						$data = $stmt->fetch(PDO::FETCH_ASSOC);
				?>
					<form name="formedu_student" action="" method="post" enctype="multipart/form-data">
						<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
							<tr>
								<td>Nama</td>
								<td><?php echo $data['name'];?></td>
							</tr>
							<tr>
								<td>NIS</td>
								<td><?php echo $data['reg_number'];?></td>
							</tr>
							<tr>
								<td>NISN</td>
								<td><?php echo $data['reg_number_national'];?></td>
							</tr>
							<?php
							if ($data['school_name']) {
							?>
								<tr>
									<td>Sekolah</td>
									<td><?php echo $data['school_name'];?></td>
								</tr>
							<?php
							}
							?>
							<tr>
								<td>Tingkat</td>
								<td><?php
									echo $picoEdu->getGradeName($data['grade_id']);
									?>
								<td>
							</tr>
							<tr>
								<td>Kelas</td>
								<td><?php echo $data['class_id'];?></td>
							</tr>
							<tr>
								<td>Jenis Kelamin</td>
								<td><?php echo $picoEdu->getGenderName($data['gender']);?></td>
							</tr>
							<tr>
								<td>Tempat Lahir</td>
								<td><?php echo $data['birth_place'];?></td>
							</tr>
							<tr>
								<td>Tanggal Lahir</td>
								<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?></td>
							</tr>
							<tr>
								<td>Telepon
								</td>
								<td><?php echo $data['phone'];?></td>
							</tr>
							<tr>
								<td>Email</td>
								<td><?php echo $data['email'];?></td>
							</tr>
							<tr>
								<td>Alamat</td>
								<td><?php echo $data['address'];?></td>
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
								<td></td>
								<td>
									<input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='./?tab=profile&option=edit'" />
									<input type="button" name="selectschool" id="selectschool" class="com-button" value="Pilih Sekolah" onclick="window.location='siswa/ganti-sekolah.php'" />
									<?php
									if ($data['school_open'] == '1') {
									?>
										<input type="button" name="selectclass" id="selectclass" class="com-button" value="Pilih Kelas" onclick="window.location='siswa/ganti-kelas.php'" />
										<input type="button" name="unsubscribe" id="unsubscribe" class="com-button" value="Berhenti Menjadi Siswa" onclick="window.location='siswa/unsubscribe.php'" />
									<?php
									}
									?>
								</td>
							</tr>
						</table>
					</form>
			<?php
				}
				include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
			}
		} else if ($page_tab == 'about') {
			$cfg->page_title = "Tentang " . $school_name;
			include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
			?>
			<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Nama Sekolah</td>
					<td><?php echo $data['name'];?></td>
				</tr>
				<tr>
					<td>Jenjang Sekolah</td>
					<td><?php if ($data['school_grade_id'] == 1) echo 'Play Group';
						if ($data['school_grade_id'] == 2) echo 'Taman Kanak-Kanak';
						if ($data['school_grade_id'] == 3) echo 'SD Sederajat';
						if ($data['school_grade_id'] == 4) echo 'SMP Sederajat';
						if ($data['school_grade_id'] == 5) echo 'SMA Sederajat';
						if ($data['school_grade_id'] == 6) echo 'Perguruan Tinggi';?></td>
				</tr>
				<tr>
					<td>Negeri/Swasta</td>
					<td><?php if ($data['public_private'] == 'U') echo 'Negeri';
						if ($data['public_private'] == 'I') echo 'Swasta';?></td>
				</tr>
				<tr>
					<td>Kepala Sekolah</td>
					<td><?php echo ($data['principal']);?></td>
				</tr>
				<tr>
					<td>Alamat Sekolah</td>
					<td><?php echo $data['address'];?></td>
				</tr>
				<tr>
					<td>Telepon Sekolah</td>
					<td><?php echo $data['phone'];?></td>
				</tr>
				<tr>
					<td>Email Sekolah</td>
					<td><?php echo $data['email'];?></td>
				</tr>
				<tr>
					<td>Bahasa</td>
					<td><?php if ($data['language'] == 'en') echo 'English';
						if ($data['language'] == 'id') echo 'Bahasa Indonesia';?></td>
				</tr>
				<tr>
					<td>Negara</td>
					<td><?php echo ($data['country_id']);?></td>
				</tr>
				<tr>
					<td>Provinsi</td>
					<td><?php echo ($data['state_id']);?></td>
				</tr>
				<tr>
					<td>Kabupaten/Kota</td>
					<td><?php echo ($data['city_id']);?></td>
				</tr>
				<tr>
					<td>Jumlah Kelas</td>
					<td><?php echo ($data['num_class']);?></td>
				</tr>
				<tr>
					<td>Jumlah Siswa</td>
					<td><?php echo ($data['num_student']); ?> orang</td>
				</tr>
				<tr>
					<td>Jumlah Guru</td>
					<td><?php echo ($data['num_teacher']); ?> orang</td>
				</tr>
			</table>
			<?php
			include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
		} else if ($page_tab == 'student') {
			$cfg->page_title = "Siswa " . $school_name;
			include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
			$sql = "select `edu_class`.*,
				(select `edu_school_program`.`name` from `edu_school_program` where `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) as `school_program_id`,
				(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`class_id` = `edu_class`.`class_id`) as `num_student`
				from `edu_class`
				where `edu_class`.`active` = '1' and `edu_class`.`school_id` = '$page_school_id'
				order by `edu_class`.`grade_id` asc, `edu_class`.`order` asc
				";
			$stmt = $database->executeQuery($sql);
			
			if ($stmt->rowCount() > 0) {
			?>
				<form name="form1" method="post" action="">
					<style type="text/css">
						@media screen and (max-width:599px) {

							.hide-some-cell thead tr td:nth-child(3),
							.hide-some-cell thead tr td:nth-child(4),
							.hide-some-cell tbody tr td:nth-child(3),
							.hide-some-cell tbody tr td:nth-child(4) {
								display: none;
							}

							.hide-some-cell tfoot {
								display: none;
							}
						}
					</style>

					<table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
						<thead>
							<tr>
								<td width="20">No</td>
								<td>Kelas</td>
								<td>Tingkat</td>
								<td>Jurusan</td>
								<td>Siswa</td>
							</tr>
						</thead>
						<tbody>
							<?php
							$no = 0;
							$numstudent = 0;
							$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
							foreach($rows as $data) {
								$no++;
							?>
								<tr>
									<td align="right"><?php echo $no;?></td>
									<td><?php echo $data['name'];?></td>
									<td><?php echo $data['grade_id'];?></td>
									<td><?php echo $data['school_program_id'];?></td>
									<td><?php echo $data['num_student'];?></td>
								</tr>
							<?php
								$numstudent += $data['num_student'];
							}
							$sql = "select *
								from `edu_student` where `edu_student`.`school_id` = '$page_school_id' 
								";
							$tsmt2 = $database->executeQuery($sql);
							$numstudent2 = $stmt2->rowCount();
							if ($numstudent2 > $numstudent) {
							?>
								<tr>
									<td align="right"><?php echo $no;?></td>
									<td></td>
									<td></td>
									<td></td>
									<td><?php echo ($numstudent2 - $numstudent);?></td>
								</tr>
							<?php
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="4">Total</td>
								<td><?php echo $numstudent2;?></td>
							</tr>
						</tfoot>
					</table>

				</form>
				<?php
			} else {
				$sql = "select `edu_school`.*, 
					(select count(distinct `edu_student`.`student_id`) from `edu_student`
					where `edu_student`.`school_id` = `edu_school`.`school_id` and `edu_student`.`gender` = 'M') as `M`,
					(select count(distinct `edu_student`.`student_id`) from `edu_student`
					where `edu_student`.`school_id` = `edu_school`.`school_id` and `edu_student`.`gender` = 'W') as `W`
					from `edu_school`
					where `edu_school`.`school_id` = '$page_school_id' 
					";
					$stmt = $database->executeQuery($sql);
					if ($stmt->rowCount() > 0) {
						$data = $stmt->fetch(PDO::FETCH_ASSOC);
				?>
					<h3>Jumlah Siswa <?php echo $data['name']; ?></h3>
					<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
						<tr>
							<td>Laki-Laki</td>
							<td><?php echo $data['M']; ?> orang</td>
						</tr>
						<tr>
							<td>Perempuan</td>
							<td><?php echo $data['W']; ?> orang</td>
						</tr>
						<tr>
							<td>Jumlah</td>
							<td><?php echo $data['M'] + $data['W']; ?> orang</td>
						</tr>
					</table>
				<?php
				} else {
				?>
					<p>Tidak ada data untuk ditampilkan.</p>
				<?php
				}
			}
			include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
		} else if ($page_tab == 'teacher') {
			$cfg->page_title = "Guru " . $school_name;
			include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
			$sql = "select `edu_teacher`.*
				from `edu_teacher`
				where `edu_teacher`.`active` = '1' and `edu_teacher`.`school_id` = '$page_school_id'
				order by `edu_teacher`.`name` asc
				";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				?>
				<form name="form1" method="post" action="">
					<style type="text/css">
						@media screen and (max-width:599px) {}
					</style>

					<table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
						<thead>
							<tr>
								<td width="20">No</td>
								<td>Nama</td>
								<td>L/P</td>
							</tr>
						</thead>
						<tbody>
							<?php
							$no = 0;
							$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
							foreach($rows as $data) {
								$no++;
							?>
								<tr>
									<td align="right"><?php echo $no;?></td>
									<td><?php echo $data['name'];?></td>
									<td><?php 
									if ($data['gender'] == 'M') {
										echo 'L';
									}
									if ($data['gender'] == 'W') {
										echo 'P';
									}
									?></td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>

				</form>
				<?php
			}
			include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
		} else if ($page_tab == 'article') {
			$cfg->page_title = "Artikel " . $school_name;
			include_once dirname(__FILE__) . "/lib.inc/dom.php";
			$article_id = kh_filter_input(INPUT_GET, 'article_id', FILTER_SANITIZE_STRING_NEW);
			if ($article_id) {
				$sql = "select `edu_article`.*, `member`.`name` as `creator`
					from `edu_article` 
					left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
					where `edu_article`.`article_id` = '$article_id' and `edu_article`.`school_id` = '$page_school_id' and `edu_article`.`active` = '1' ";
					$stmt = $database->executeQuery($sql);
					if ($stmt->rowCount() > 0) {
						$data = $stmt->fetch(PDO::FETCH_ASSOC);
					$cfg->page_title = $data['name'] . " - " . $school_name;
					include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
				?>
					<div class="article-title">
						<h3 data-active="<?php echo $data['active']; ?>"><?php echo $data['title']; ?></h3>
					</div>
					<div class="article-content"><?php echo $data['content']; ?></div>
					<div class="article-time">Dibuat <strong><?php echo $data['time_create']; ?></strong></div>
					<div class="article-creator">Oleh <strong><?php echo $data['creator']; ?></strong></div>
					<div class="article-link">
						<a href="artikel.php">Lihat Semua</a>
					</div>
				<?php
					include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
				} else {
					include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
				?>
					<p>Artiikel tidak ditemukan.</p>
					<?php
					include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
				}
			} else {
				include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
				$sql_filter_article = "";
				$sql = "select `edu_article`.*, `member`.`name` as `creator`
					from `edu_article` 
					left join(`member`) on(`member`.`member_id` = `edu_article`.`member_create`) 
					where `edu_article`.`active` = '1' and `edu_article`.`school_id` = '$page_school_id'
					order by `edu_article`.`article_id` desc
					";
				$stmt = $database->executeQuery($sql);
				if ($stmt->rowCount() > 0) {
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach($rows as $data)
					{

						$obj = parsehtmldata('<html><body>' . ($data['content']) . '</body></html>');
						$arrparno = array();
						$arrparlen = array();
						$cntmax = ""; // do not remove
						$content = ""; // do not remove
						$i = 0;
						$minlen = 300;

						if (isset($obj->p) && count($obj->p) > 0) {
							$max = 0;
							foreach ($obj->p as $parno => $par) {
								$arrparlen[$i] = strlen($par);
								if ($arrparlen[$i] > $max) {
									$max = $arrparlen[$i];
									$cntmax = $par;
								}
								if ($arrparlen[$i] >= $minlen) {
									$content = $par;
									break;
								}
							}
							if (!$content) {

								$content = $cntmax;
							}
						}
						if (!$content) {
							$content = "&nbsp;";
						}
						$maxlen = 300;
						if (strlen($content) > $maxlen) {
							$content .= " ";
							$pos = stripos($content, ". ", $maxlen);
							if ($pos === false) {
								$pos = stripos($content, ".", $maxlen);
							}
							if ($pos === false) {
								$pos = stripos($content, " ", $maxlen);
							}
							if ($pos === false) $pos = $maxlen;
							$content = substr($content, 0, $pos + 1);
							$content = tidyHTML($content);
						}

					?>
						<div class="article-item">
							<div class="article-title">
								<h3 data-active="<?php echo $data['active']; ?>"><a href="<?php echo $school_code . "/?tab=article&article_id=" . $data['article_id']; ?>"><?php echo $data['title']; ?></a></h3>
							</div>
							<div class="article-content">
								<p><?php echo $content; ?></p>
							</div>
							<div class="article-time">Dibuat <strong><?php echo $data['time_create']; ?></strong></div>
							<div class="article-creator">Oleh <strong><?php echo $data['creator']; ?></strong></div>
							<div class="article-link">
								<a href="<?php echo $school_code . "/?tab=article&article_id=" . $data['article_id']; ?>">Baca</a>
								<?php
								if (@$auth_teacher_id && @$auth_teacher_school_id && @$auth_teacher_school_id == $data['school_id'] && @$auth_teacher_id = $data['member_create']) {
								?>
									<a href="artikel.php?option=edit&article_id=<?php echo $data['article_id']; ?>">Ubah</a>
									<a class="delete-post" data-id="<?php echo $data['article_id']; ?>" href="artikel.php?option=delete&article_id=<?php echo $data['article_id']; ?>">Hapus</a>
								<?php
								}
								?>
							</div>
						</div>
				<?php
					}
				}
				include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
			}
		} else if ($page_tab == 'test') {
			$cfg->page_title = "Ujian " . $school_name;
			include_once dirname(__FILE__) . "/lib.assets/theme/default/header-sekolah.php";
			$array_class = $picoEdu->getArrayClass($page_school_id);
			$nt = '';

			$sql = "select `edu_test`.*
				from `edu_test`
				where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$page_school_id' 
				order by `edu_test`.`test_id` desc
				";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				?>
				<form name="form1" method="post" action="">
					<style type="text/css">
						@media screen and (max-width:599px) {
							.hide-some-cell tr td:nth-child(5) {
								display: none;
							}
						}

						@media screen and (max-width:399px) {
							.hide-some-cell tr td:nth-child(4) {
								display: none;
							}
						}
					</style>
					<table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
						<thead>
							<tr>
								<td width="20">No</td>
								<td>Ujian</td>
								<td>Kelas</td>
								<td>Pelajaran</td>
							</tr>
						</thead>
						<tbody>
							<?php
							$no = 0;
							$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
							foreach($rows as $data) {
								$no++;
							?>
								<tr>
									<td align="right"><?php echo $no;?></td>
									<td><?php echo $data['name'];?></td>
									<td><?php $class = $picoEdu->textClass($array_class, $data['class']);
										$class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class); ?>"><?php echo $class_sort; ?></a></td>
									<td><?php echo $data['subject'];?></td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</form>
<?php
			}
			include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-sekolah.php";
		}
	} else {
		include_once dirname(__FILE__) . "/lib.assets/theme/default/header-home.php";
		include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-home.php";
	}
} else {
	include_once dirname(__FILE__) . "/lib.assets/theme/default/header-home.php";
	include_once dirname(__FILE__) . "/lib.assets/theme/default/footer-home.php";
}
?>