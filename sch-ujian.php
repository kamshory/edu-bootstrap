<?php
include_once dirname(__FILE__) . "/lib.inc/auth-guru.php";
if (@!$auth_teacher_id)
	include_once dirname(__FILE__) . "/lib.inc/auth-siswa.php";

if (isset($_GET['school_id'])) {
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
}
if (!$school_id) {
	exit();
}
include_once dirname(__FILE__) . "/lib.inc/cfg.pagination.php";

if (@$auth_student_id && @$auth_school_id) {
	if (@$_GET['option'] == 'answer' && isset($_GET['answer_id'])) {
		$answer_id = kh_filter_input(INPUT_GET, 'answer_id', FILTER_SANITIZE_NUMBER_UINT);
		$now = $picoEdu->getLocalDateTime();
		include_once dirname(__FILE__) . "/lib.inc/header.php";

		$sql = "select `edu_test`.* , `edu_answer`.`final_score`, `edu_answer`.`percent`, `edu_answer`.`start`, `edu_answer`.`end`
		from `edu_answer`
		inner join (`edu_test`) on (`edu_test`.`test_id` = `edu_answer`.`test_id`)
		where `edu_answer`.`answer_id` = '$answer_id' and `edu_answer`.`student_id` = '$student_id'
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
			<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/test.css" />
			<style type="text/css">
				.test-info {
					padding: 10px;
					border: 1px solid #DDDDDD;
					background-color: #FAFAFA;
					margin-bottom: 10px;
				}
			</style>
			<div class="test-info">
				<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
					<tr>
						<td>Ujian</td>
						<td><?php echo $data['name'];?></td>
					</tr>
					<tr>
						<td>Jumlah Soal</td>
						<td><?php echo $data['number_of_question'];?></td>
					</tr>
					<tr>
						<td>Jumlah Pilihan</td>
						<td><?php echo $data['number_of_option'];?></td>
					</tr>
					<tr>
						<td>Nilai Standard</td>
						<td><?php echo $data['standard_score'];?></td>
					</tr>
					<tr>
						<td>Penalti
						</td>
						<td><?php echo $data['penalty'];?></td>
					</tr>
					<tr>
						<td>Otomatis Kirim Jawaban</td>
						<td><?php echo $data['autosubmit'] ? 'Ya' : 'Tidak';?></td>
					</tr>
					<tr>
						<td>Mulai Ujian
						</td>
						<td><?php echo $data['start'];?></td>
					</tr>
					<tr>
						<td>Selesai Ujian
						</td>
						<td><?php echo $data['end'];?></td>
					</tr>
					<tr>
						<td>Nilai Akhir
						</td>
						<td><?php echo $data['final_score'];?></td>
					</tr>
					<tr>
						<td>Persen
						</td>
						<td><?php echo $data['percent'];?></td>
					</tr>
				</table>
			</div>
			<?php
			$sql = "select `edu_question`.* , `edu_answer`.`answer` as `answer` , instr(`edu_answer`.`answer`,`edu_question`.`question_id`) as `pos`,
			`edu_test`.`publish_answer`, `edu_test`.`time_answer_publication`
			from `edu_question` 
			left join (`edu_answer`) on (`edu_answer`.`answer` like concat('%[',`edu_question`.`question_id`,',%' ))
			left join (`edu_test`) on (`edu_test`.`test_id` = `edu_question`.`test_id`)
			where `edu_answer`.`answer_id` = '$answer_id' and `edu_answer`.`student_id` = '$student_id'
			group by `edu_question`.`question_id` 
			order by `pos` asc ";
			$stmt = $database->executeQuery($sql);

			if ($stmt->rowCount() > 0) {
			?>
				<ol class="test-question">
					<?php
					$i = 0;
					$no = $pagination->offset;
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach($rows as $data) {
						$j = $i % 2;
						$no++;
						$qid = $data['question_id'];
						$answer = $data['answer'];
					?>
						<li value="<?php echo $no; ?>">
							<div class="question">
								<?php echo $data['content']; ?>
								<?php
								$sql2 = "select `edu_option`.* , '$answer' like concat('%,',`edu_option`.`option_id`,']%') as `my_answer`
								from `edu_option` 
								where  `edu_option`.`question_id` = '$qid' group by  `edu_option`.`option_id` order by  `edu_option`.`order` asc";
								$stmt2 = $database->executeQuery($sql2);
								if ($stmt2->rowCount() > 0) 
								{
								?>
									<div class="option">
										<ol class="listoption" style="list-style-type:<?php echo $data['numbering']; ?>">
											<?php
											$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
											foreach($rows2 as $data2){
											?>
												<li>
													<?php
													if ($data['publish_answer'] && $data['time_answer_publication'] <= $now) {
													?>
														<span class="option-circle<?php if ($data2['score']) echo ' option-circle-selected'; ?>"><?php
																																				echo $data2['score'] * 1;
																																				?></span>
													<?php
													}
													?>
													<div class="list-option-item<?php echo ($data2['my_answer']) ? ' list-option-item-selected' : ''; ?>">
														<div class="option-content">
															<?php
															echo $data2['content'];
															?>
														</div>
													</div>
												</li>
											<?php
											}
											?>
										</ol>
									</div>
								<?php
								}
								?>
							</div>
						</li>
					<?php
						$i++;
					}
					?>
				</ol>
			<?php
			}
		} else {
			?>
			<div class="warning">Ujian tidak ditemukan.</div>
		<?php
		}
		include_once dirname(__FILE__) . "/lib.inc/footer.php";
	} else if (@$_GET['option'] == 'history' && isset($_GET['test_id'])) {
		$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_NUMBER_UINT);
		include_once dirname(__FILE__) . "/lib.inc/header.php";
		$sql = "select `edu_answer`.*
		from `edu_answer`
		where `edu_answer`.`student_id` = '$student_id' and `edu_answer`.`test_id` = '$test_id' 
		order by `edu_answer`.`start` asc
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) 
		{
		?>
			<style type="text/css">
				@media screen and (max-width:599px) {
					.hide-some-cell tr td:nth-child(3),
					.hide-some-cell tr td:nth-child(4),
					.hide-some-cell tr td:nth-child(5),
					.hide-some-cell tr td:nth-child(6),
					.hide-some-cell tr td:nth-child(7),
					.hide-some-cell tr td:nth-child(8),
					.hide-some-cell tr td:nth-child(9) {
						display: none;
					}
				}
			</style>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
				<thead>
					<tr>
						<td width="25">No</td>
						<td>Pelaksanaan Ujian</td>
						<td>Durasi</td>
						<td>Soal</td>
						<td>Benar</td>
						<td>Salah</td>
						<td>N.Awal</td>
						<td>Penalti</td>
						<td>N.Akhir</td>
						<td>Persen</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = $pagination->offset;
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach($rows as $data) {
						$no++;
						$data['number_of_question'] = substr_count($data['answer'], "]");
					?>
						<tr>
							<td align="right"><?php echo $no;?></td>
							<td><a href="ujian.php?option=answer&answer_id=<?php echo $data['answer_id']; ?>"><?php echo translateDate(date('d M Y H:i:s', strtotime($data['start']))); ?></a></td>
							<td><?php echo gmdate('H:i:s', strtotime($data['end']) - strtotime($data['start']));?></td>
							<td><?php if ($data['number_of_question']) {
									echo $data['number_of_question'];
								} else {
									echo '-';
								} ?></td>
							<td><?php echo $data['true'];?></td>
							<td><?php echo $data['false'];?></td>
							<td><?php echo $data['initial_score'];?></td>
							<td><?php echo $data['penalty'];?></td>
							<td><?php echo $data['final_score'];?></td>
							<td><?php echo $data['percent'];?></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		<?php
		}
		include_once dirname(__FILE__) . "/lib.inc/footer.php";
	} else if (@$_GET['option'] == 'detail' && isset($_GET['test_id'])) {
		include_once dirname(__FILE__) . "/lib.inc/header.php";
		$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_NUMBER_UINT);
		$nt = '';
		$sql = "select `edu_test`.* $nt,
		(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher_id`
		from `edu_test` 
		where 1
		and `edu_test`.`test_id` = '$test_id' and `edu_test`.`school_id` = '$school_id'
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$array_class = $picoEdu->getArrayClass($school_id);
		?>
			<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
				<tr>
					<td>Nama Ujian</td>
					<td><?php echo $data['name'];?></td>
				</tr>
				<tr>
					<td>Kelas
					</td>
					<td><?php $class = $picoEdu->textClass($array_class, $data['class']);
						$class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class); ?>"><?php echo $class_sort; ?></a></td>
				</tr>
				<tr>
					<td>Mata Pelajaran
					</td>
					<td><?php echo $data['subject'];?></td>
				</tr>
				<tr>
					<td>Guru
					</td>
					<td><?php echo $data['teacher_id'];?></td>
				</tr>
				<tr>
					<td>Keterangan
					</td>
					<td><?php echo $data['description'];?></td>
				</tr>
				<tr>
					<td>Petunjuk
					</td>
					<td><?php echo $data['guidance'];?></td>
				</tr>
				<tr>
					<td>Terbuka
					</td>
					<td><?php echo $data['open'] ? 'Ya' : 'Tidak';?></td>
				</tr>
				<tr>
					<td>Dibatasi</td>
					<td><?php echo $data['has_limits'] ? 'Ya' : 'Tidak';?></td>
				</tr>
				<?php
				if ($data['has_limits']) {
				?>
					<tr>
						<td>Batas Percobaan</td>
						<td><?php echo $data['trial_limits'];?></td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td>Nilai Kelulusan
					</td>
					<td><?php echo $data['threshold'];?></td>
				</tr>
				<tr>
					<td>Metode Penilaian</td>
					<td><?php 
					if ($data['assessment_methods'] == 'H') {
						echo "Nilai Tertinggi";
					}
					if ($data['assessment_methods'] == 'N') {
						echo "Nilai Terbaru";
					} ?></td>
				</tr>
				<tr>
					<td>Jumlah Soal</td>
					<td><?php echo $data['number_of_question'];?></td>
				</tr>
				<tr>
					<td>Jumlah Pilihan</td>
					<td><?php echo $data['number_of_option'];?></td>
				</tr>
				<tr>
					<td>Soal Perhalaman</td>
					<td><?php echo $data['question_per_page'];?></td>
				</tr>
				<tr>
					<td>Durasi
					</td>
					<td><?php echo date('H:i:s', $data['duration']);?></td>
				</tr>
				<?php
				if ($data['has_alert']) {
				?>
				<?php
				}
				?>
				<tr>
					<td>Otomatis Kirim Jawaban</td>
					<td><?php echo $data['autosubmit'] ? 'Ya' : 'Tidak';?></td>
				</tr>
				<tr>
					<td>Nilai Standard</td>
					<td><?php echo $data['standard_score'];?></td>
				</tr>
				<tr>
					<td>Penalti
					</td>
					<td><?php echo $data['penalty'];?></td>
				</tr>
				<?php
				if ($data['publish_answer']) {
				?>
					<tr>
						<td>Pengumuman Kunci Jawaban</td>
						<td><?php echo $data['time_answer_publication'];?></td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td>Ketersediaan Ujian
					</td>
					<td><?php 
					if ($data['test_availability'] == 'F') {
						echo 'Selamanya';
					}
					if ($data['test_availability'] == 'L') {
						echo 'Terbatas';
					} ?></td>
				</tr>
				<?php
				if ($data['test_availability'] == 'L') {
				?>
					<tr>
						<td>Tersedia Mulai</td>
						<td><?php echo $data['available_from'];?></td>
					</tr>
					<tr>
						<td>Tersedia Hingga</td>
						<td><?php echo $data['available_to'];?></td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td></td>
					<td><input type="button" name="join" id="join" class="com-button" value="Ikuti" onclick="window.location='<?php echo $cfg->base_url; ?>/siswa/ujian/index.php?test_id=<?php echo $data['test_id']; ?>'" />
						<input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='ujian.php'" />
					</td>
				</tr>
			</table>
		<?php
		}
		include_once dirname(__FILE__) . "/lib.inc/footer.php";
	} else {
		include_once dirname(__FILE__) . "/lib.inc/header.php";
		$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
		$array_class = $picoEdu->getArrayClass($school_id);
		?>
		<style type="text/css">
			.menu-control {
				margin: 0;
				padding: 2px 0;
				position: absolute;
				z-index: 100;
				left: 30px;
				top: 100px;
				background-color: #FFFFFF;
				border: 1px solid #DDDDDD;
				box-shadow: 0 0 3px #E5E5E5;
				display: none;
			}

			.menu-control::before {
				content: "";
				width: 10px;
				height: 0px;
				border: 10px solid transparent;
				border-right: 10px solid #DDDDDD;
				position: absolute;
				margin-left: -30px;
				margin-top: 30px;
			}

			.menu-control li {
				list-style-type: none;
				margin: 0;
				padding: 0 2px;
			}

			.menu-control>li:first-child::before {
				content: "";
				width: 9px;
				height: 0px;
				border: 9px solid transparent;
				border-right: 9px solid #FFFFFF;
				position: absolute;
				margin-left: -28px;
				margin-top: 31px;
			}

			.menu-control li a {
				background-color: #FEFEFE;
				display: block;
				padding: 5px 16px;
				border-bottom: 1px solid #EEEEEE;
			}

			.menu-control li a:hover {
				background-color: #428AB7;
				color: #FFFFFF;
			}

			.menu-control li:last-child a {
				border-bottom: none;
			}
		</style>
		<script type="text/javascript">
			window.onload = function() {
				$(document).on('change', '#searchform select', function(e) {
					$(this).closest('form').submit();
				});
				$(document).on('click', '.show-controls', function(e) {
					var obj = $(this);
					if (obj.hasClass('menu-show')) {
						$('.show-controls').each(function(index, element) {
							$(this).removeClass('menu-show');
						});
						$('.menu-control').css({
							display: 'none'
						});
					} else {
						$('.show-controls').each(function(index, element) {
							$(this).removeClass('menu-show');
						});
						var left = obj.offset().left + 40;
						var top = obj.offset().top - 34;
						var id = obj.attr('data-test-id');
						obj.addClass('menu-show');
						$('.menu-control').empty().append(buildMenu(id)).css({
							left: left,
							top: top,
							display: 'block'
						});
					}
					e.preventDefault();
				});
			}

			function buildMenu(id) {
				var html =
					'<li><a href="ujian.php?option=report&test_id=' + id + '">Riwayat Ujian</a></li>\r\n' +
					'<li><a href="ujian.php?option=history&test_id=' + id + '">Laporan Hasil Ujian</a></li>\r\n' +
					'<li><a href="ujian.php?option=detail&test_id=' + id + '">Informasi Ujian</a></li>\r\n';
				return html;
			}
		</script>
		<div class="search-control">
			<form id="searchform" name="form1" method="get" action="">
				<span class="search-label">Kelas</span>
				<select class="input-select" name="class_id" id="class_id">
					<option value="">- Pilih Kelas -</option>
					<?php
					$sql = "select * from `edu_class` where `school_id` = '$school_id' ";				
					$stmt2 = $database->executeQuery($sql);
					if ($stmt2->rowCount() > 0) {
						foreach($rows2 as $data2) {
							?>
						<option value="<?php $data2['class_id']; ?>" <?php if ($data2['class_id'] == $class_id)
							   echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
					<?php
						}
					}
					?>
				</select>
				<span class="search-label">Ujian</span>
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
				$sql_filter .= " and (`edu_test`.`name` like '%" . addslashes($pagination->query) . "%' )";
			}


			$nt = '';

			$sql = "select `edu_test`.* $nt,
			(select count(distinct `edu_answer`.`answer_id`) from `edu_answer` 
			where `edu_answer`.`test_id` = `edu_test`.`test_id` and `edu_answer`.`student_id` = '$student_id') as `ntest`
			from `edu_test`
			where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' $sql_filter
			order by `edu_test`.`test_id` desc
			";
			$sql_test = "select `edu_test`.*
			from `edu_test`
			where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' $sql_filter
			";

			$stmt = $database->executeQuery($sql_test);
			$pagination->total_record = $stmt->rowCount();
			$stmt = $database->executeQuery($sql.$pagination->limit_sql);
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
					<ul class="menu-control">
					</ul>

					<div class="search-pagination search-pagination-top">
						<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
						<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
					</div>

					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
						<thead>
							<tr>
								<td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></td>
								<td width="25">No</td>
								<td>Ujian</td>
								<td>Kelas</td>
								<td>Pelajaran</td>
								<td>Ikut</td>
							</tr>
						</thead>
						<tbody>
							<?php
							$no = $pagination->offset;
							$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
							foreach($rows as $data) {
								$no++;
							?>
								<tr>
									<td><a class="show-controls" data-test-id="<?php echo $data['test_id']; ?>" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></a></td>
									<td align="right"><?php echo $no;?></td>
									<td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['name']; ?></a></td>
									<td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php $class = $picoEdu->textClass($array_class, $data['class']);
																													$class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class); ?>"><?php echo $class_sort; ?></a></td>
									<td><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['subject']; ?></a></td>
									<td><?php if ($data['ntest']) { ?><a href="ujian.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['ntest']; ?> &times;</a><?php } else echo '-';?></td>
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
				<div class="warning">Data tidak ditemukan.</div>
			<?php
			}
			?>
		</div>
	<?php
		include_once dirname(__FILE__) . "/lib.inc/footer.php";
	}
} else if (@$auth_teacher_id && @$auth_school_id) {
	include_once dirname(__FILE__) . "/lib.inc/header.php";
	$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$array_class = $picoEdu->getArrayClass($school_id);
	?>
	<script type="text/javascript">
		window.onload = function() {
			$(document).on('change', '#searchform select', function() {
				$(this).closest('form').submit();
			});
		}
	</script>
	<div class="search-control">
		<form id="searchform" name="form1" method="get" action="">
			<span class="search-label">Kelas</span>
			<select class="input-select" name="class_id" id="class_id">
				<option value="">- Pilih Kelas -</option>
				<?php
				$sql2 = "select * from `edu_class` where `school_id` = '$school_id' ";
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
			<span class="search-label">Ujian</span>
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
			$sql_filter .= " and (`edu_test`.`name` like '%" . addslashes($pagination->query) . "%' )";
		}


		$nt = '';

		$sql = "select `edu_test`.* $nt
from `edu_test`
where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
order by `edu_test`.`test_id` desc
";
		$sql_test = "select `edu_test`.*
from `edu_test`
where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' and `edu_test`.`teacher_id` = '$auth_teacher_id' $sql_filter
";

$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
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
					@media screen and (max-width:599px) {
						.hide-some-cell tr td:nth-child(4) {
							display: none;
						}
					}

					@media screen and (max-width:399px) {
						.hide-some-cell tr td:nth-child(3) {
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
							<td width="25">No</td>
							<td>Ujian</td>
							<td>Kelas</td>
							<td>Pelajaran</td>
						</tr>
					</thead>
					<tbody>
						<?php
						$no = $pagination->offset;
						$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach($rows as $data){
							$no++;
						?>
							<tr>
								<td align="right"><?php echo $no;?></td>
								<td><a href="../guru/ujian-daftar.php"><?php echo $data['name']; ?></a></td>
								<td><?php $class = $picoEdu->textClass($array_class, $data['class']);
									$class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class); ?>"><?php echo $class_sort; ?></a></td>
								<td><a href="../guru/ujian-daftar.php"><?php echo $data['subject']; ?></a></td>
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
			<div class="warning">Data tidak ditemukan. <a href="../guru/ujian-daftar.php?option=add">Klik di sini untuk membuat baru.</a></div>
		<?php
		}
		?>
	</div>
<?php
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
} else {
	include_once dirname(__FILE__) . "/lib.inc/header.php";
	$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
	$array_class = $picoEdu->$picoEdu->getArrayClass($school_id);
?>
	<script type="text/javascript">
		window.onload = function() {
			$(document).on('change', '#searchform select', function() {
				$(this).closest('form').submit();
			});
		}
	</script>
	<div class="search-control">
		<form id="searchform" name="form1" method="get" action="">
			<span class="search-label">Kelas</span>
			<select class="input-select" name="class_id" id="class_id">
				<option value="">- Pilih Kelas -</option>
				<?php
				$sql2 = "select * from `edu_class` where `school_id` = '$school_id' ";
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
			<span class="search-label">Ujian</span>
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
			$sql_filter .= " and (`edu_test`.`name` like '%" . addslashes($pagination->query) . "%' )";
		}


		$nt = '';

		$sql = "select `edu_test`.* $nt
		from `edu_test`
		where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' $sql_filter
		order by `edu_test`.`test_id` desc
		";
		$sql_test = "select `edu_test`.*
		from `edu_test`
		where `edu_test`.`active` = '1' and `edu_test`.`school_id` = '$school_id' $sql_filter
		";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
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
					@media screen and (max-width:599px) {
						.hide-some-cell tr td:nth-child(4) {
							display: none;
						}
					}

					@media screen and (max-width:399px) {
						.hide-some-cell tr td:nth-child(3) {
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
							<td width="25">No</td>
							<td>Ujian</td>
							<td>Kelas</td>
							<td>Pelajaran</td>
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
			<div class="warning">Data tidak ditemukan.</div>
		<?php
		}
		?>
	</div>
<?php
	include_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>