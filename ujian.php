<?php
include_once dirname(__FILE__)."/lib.inc/auth.php";
$cfg->page_title = "Ujian Online";

if(isset($_GET['school_id']) && @$_GET['option'] == 'register' && @$member_id > 0)
{
	if(isset($_GET['ref']))
	{
		$ref = kh_filter_input(INPUT_GET, 'ref', FILTER_DEFAULT);
		$ref = rawurldecode($ref);
	}
	else
	{
		$ref = "";
	}

	$member_id = @$member_id . '';
	$student_id = $member_id;
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $member_id;
	$ip_create = $ip_edit = addslashes($_SERVER['REMOTE_ADDR']);
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_UINT);
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_NUMBER_UINT);
	
	$sql = "
	select `edu_test`.*, `edu_test`.`name` as `test_name`, 
	`edu_school`.`school_code` as `school_code`,
	`edu_school`.`name` as `school_name`,
	`edu_school`.`open` as `school_open`, 
	`edu_school`.`active` as `school_active`,
	`edu_student`.`student_id` as `student_id`,
	`edu_member_school`.`member_id` as `student_in_school_id`,
	(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`student_id` = '$member_id' and `edu_member_school`.`role` = 'S') as `student_registered`
	from `edu_test` 
	inner join(`edu_school`) on(`edu_school`.`school_id` = `edu_test`.`school_id`)
	left join(`edu_student`) on(`edu_student`.`school_id` = `edu_test`.`school_id` and `edu_student`.`student_id` = '$member_id')
	left join(`edu_member_school`) on(`edu_member_school`.`school_id` = `edu_test`.`school_id` and `edu_member_school`.`member_id` = '$member_id' and `edu_member_school`.`role` = 'S')
	where `edu_test`.`test_id` = '$test_id' 
	and `edu_test`.`open` = '1' 
	and `edu_test`.`active` = '1'
	and `edu_school`.`open` = '1' 
	and `edu_school`.`active` = '1'
	";
	$stmt = $database->executeQuery($sql);

	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$school_code = $data['school_code'];
		$class = $data['class'];
		$arrclass = explode(",", trim($class, ","));
		$class_id = $arrclass[0];
	
		$ref = "$school_code/test/?option=login&test_id=$test_id";
	
		$sql = "select `edu_member_school`.*
		from `edu_member_school`
		where `edu_member_school`.`member_id` = '$member_id' 
		and `edu_member_school`.`school_id` = '$school_id' 
		and `edu_member_school`.`role` = 'S'
		";
		$stmt = $database->executeQuery($sql);

		if($stmt->rowCount() > 0)
		{
			$sql2 = "update `edu_member_school` set `class_id` = '$class_id' 
			where `student_id` = '$student_id' and `role` = 'S' ";
			$database->execute($sql2);
		}
		else
		{
			$sql2 = "insert into `edu_member_school` 
			(`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) values
			('$student_id', '$school_id', 'S', '$class_id', '$time_create', '1')
			";
			$database->execute($sql2);
		}
		$token_student = md5($school_id.'-'.$member_id.'-'.time().'-'.mt_rand(111111, 999999));
		$reg_number = '';
		$reg_number_national = '';

		$sql = "select * from `member` where `member_id` = '$member_id' ";

		$stmt = $database->executeQuery($sql);

		$member_data = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$name = addslashes($member_data['name']);				
		$gender = addslashes($member_data['gender']);				
		$birth_place = addslashes($member_data['birth_place']);				
		$birth_day = addslashes($member_data['birth_day']);				
		$phone = addslashes($member_data['phone']);				
		$email = addslashes($member_data['email']);				
		$password = addslashes($member_data['password']);				

		
		$sql = "INSERT INTO `edu_student` 
		(`student_id`, `token_student`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, 
		`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
		('$student_id', '$token_student', '$school_id', '$name', '$gender', '$birth_place', '$birth_day', '$phone', '$email', '$password',  
		'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 0, 1)
		";
		$database->execute($sql);
		
		$sql = "update `edu_student` set `school_id` = '$school_id', `class_id` = '$class_id' where `student_id` = '$student_id' ";
		$database->execute($sql);
		
		if($ref == '')
		{
			$ref = 'ujian.php';
		}
		header("Location: $ref");
	}




}
if(isset($_GET['test_id']) && @$_GET['option'] == 'join' && isset($_GET['register']))
{
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_NUMBER_UINT);
	if(!@$member_id)
	{
		include_once dirname(__FILE__)."/login-form.php";
		exit();
	}
	else
	{
		$student_id = $member_id;
		$time_create = $time_edit = $picoEdu->getLocalDateTime();
		$admin_create = $admin_edit = $member_id;
		$ip_create = $ip_edit = addslashes($_SERVER['REMOTE_ADDR']);
		$sql = "select `edu_test`.*, `edu_test`.`name` as `test_name`, 
		`edu_school`.`school_code` as `school_code`,
		`edu_school`.`name` as `school_name`,
		`edu_school`.`open` as `school_open`, 
		`edu_school`.`active` as `school_active`,
		`edu_student`.`student_id` as `student_id`,
		`edu_member_school`.`member_id` as `student_in_school_id`,
		(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`student_id` = '$member_id' and `edu_member_school`.`role` = 'S') as `student_registered`
		from `edu_test` 
		inner join(`edu_school`) on(`edu_school`.`school_id` = `edu_test`.`school_id`)
		left join(`edu_student`) on(`edu_student`.`school_id` = `edu_test`.`school_id` and `edu_student`.`student_id` = '$member_id')
		left join(`edu_member_school`) on(`edu_member_school`.`school_id` = `edu_test`.`school_id` and `edu_member_school`.`member_id` = '$member_id' and `edu_member_school`.`role` = 'S')
		where `edu_test`.`test_id` = '$test_id' 
		and `edu_test`.`open` = '1' and `edu_test`.`active` = '1'
		and `edu_school`.`open` = '1' and `edu_school`.`active` = '1'
		";
		$stmt = $database->executeQuery($sql);

		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$school_code = $data['school_code'];
			$school_id = $data['school_id'];
			$class = $data['class'];
			$arrclass = explode(",", trim($class));
			$class_id = $arrclass[0];
			if($data['student_id'] == $member_id)
			{
				// sedang terdaftar di sekolah ybs
				// langsung ujian
				$ref = "$school_code/test/?option=login&test_id=$test_id";
				header("Location: $ref");

			}
			else if($data['student_in_school_id'] == $member_id)
			{

				$sqlx = "select `edu_student`.*
				from `edu_student`
				where `student_id` = '$student_id' and `prevent_change_school` = '1'
				";
				if($database->executeQuery($sqlx)->rowCount() > 0)
				{
					// siswa tak boleh pindah sekolah
					include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
					?>
					<div class="main-content">
						<div class="main-content-wrapper">
							<h1>Tidak Diijinkan Pindah Sekolah</h1>
							<p>Sekolah Anda tidak mengijinkan Anda masuk <strong><?php echo $data['school_name'];?></strong> untuk mengikuti ujian <strong><?php echo $data['test_name'];?></strong>.</p>
							<p>Silakan hubungi administrator sekolah Anda atau Anda dapat membuat akun baru untuk mengikuti ujian <strong><?php echo $data['test_name'];?></strong> di <strong><?php echo $data['school_name'];?></strong>.</p>
						</div>
					</div>    
					<?php
					include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
				}
				else
				{
					// siswa pernah terdaftar di sekolah ybs
					// ubah sekolah
					$sql = "update `edu_student` set `school_id` = '$school_id', `class_id` = '$class_id'
					where `student_id` = '$student_id' ";
					$database->execute($sql);
					$ref = "$school_code/test/?option=login&test_id=$test_id";
					header("Location: $ref");
				}
			}
			else if($data['student_registered'])
			{
				$sqlx = "select `edu_student`.*
				from `edu_student`
				where `student_id` = '$student_id' and `prevent_change_school` = '1'
				";
				if($database->executeQuery($sqlx)->rowCount() > 0)
				{
					// siswa tak boleh pindah sekolah
					include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
					?>
					<div class="main-content">
						<div class="main-content-wrapper">
							<h1>Tidak Diijinkan Pindah Sekolah</h1>
							<p>Sekolah Anda tidak mengijinkan Anda masuk <strong><?php echo $data['school_name'];?></strong> untuk mengikuti ujian <strong><?php echo $data['test_name'];?></strong>.</p>
							<p>Silakan hubungi administrator sekolah Anda atau Anda dapat membuat akun baru untuk mengikuti ujian <strong><?php echo $data['test_name'];?></strong> di <strong><?php echo $data['school_name'];?></strong>.</p>
						</div>
					</div>    
					<?php
					include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
				}
				else
				{
					// pernah terdaftar sebagai siswa
					// buat catatan
	
					$sql2 = "insert into `edu_member_school` 
					(`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) values
					('$student_id', '$school_id', 'S', '$class_id', '$time_create', '1')
					";
					$database->execute($sql2);
	
					// ubah sekolah
					$sql = "update `edu_student` set `school_id` = '$school_id', `class_id` = '$class_id'
					where `student_id` = '$student_id' ";
					$database->execute($sql);
					$ref = "$school_code/test/?option=login&test_id=$test_id";
					header("Location: $ref");
				}
			}
			else
			{
				// tidak pernah terdaftar sebagai siswa
				include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
				?>
                <div class="main-content">
                    <div class="main-content-wrapper">
                        <h1>Tidak Terdaftar</h1>
                        <p>Anda tidak terdaftar sebagai siswa. Anda harus mendaftarkan diri terlebih dahulu sebagai siswa untuk bisa mengikuti ujian <strong><?php echo $data['test_name'];?></strong>.</p>
                        <p>Apakah Anda akan mendaftar di <strong><?php echo $data['school_name'];?></strong>?</p>
                        <div class="article-link">
                        <a href="ujian.php?option=register&school_id=<?php echo $data['school_id'];?>&test_id=<?php echo $data['test_id'];?>&ref=<?php echo rawurlencode($_SERVER['REQUEST_URI']);?>">Ya</a>
                        <a href="ujian.php">Tidak</a>
                        </div>
                    </div>
                </div>    
                <?php
				include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
			}
		}
		exit();
	}
}
else
{
include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";
$school_grade = array(
	'0'=>'',
	'3'=>'SD Sederajat',
	'4'=>'SMP Sederajat',
	'5'=>'SMA Sederajat'
);
?>
    <div class="main-content">
    	<div class="main-content-wrapper">
        <div class="article-content">
    	<h1>Daftar Ujian Online</h1>
        <p>Anda dapat mengikuti ujian online berikut ini secara gratis dan tidak perlu membayar. Silakan pilih ujian online yang akan Anda ikuti sesuai dengan jenjang pendidikan Anda. Ujian ini terbuka bagi siapa saja tanpa ada pengecualian.</p>
        <?php
		$school_data = array();
		$sql = "select `edu_test`.*, `edu_test`.`name` as `test_name`, `edu_school`.`name` as `school_name`, `edu_school`.`school_grade_id`
		from `edu_test`
		inner join(`edu_school`) on(`edu_school`.`school_id` = `edu_test`.`school_id`)
		where 1 
		and `edu_test`.`open` = '1' and `edu_test`.`active` = '1'
		and `edu_school`.`open` = '1' and `edu_school`.`active` = '1'
		order by `edu_school`.`school_grade_id` asc, `edu_test`.`subject` asc, `edu_test`.`name` asc
		";
		$stmt = $database->executeQuery($sql);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($rows as $data)
		{
			if(!isset($school_data[$data['school_grade_id']]))
			{
				$school_data[$data['school_grade_id']] = array();
			}
			$school_data[$data['school_grade_id']][] = $data;
		}
		
		
		
		if(count($school_data))
		{
			foreach($school_data as $school_grade_id=>$data_item)
			{
				if(isset($school_grade[$data_item[0]['school_grade_id']]))
				{
				?>
				<h3><?php echo $school_grade[$data_item[0]['school_grade_id']];?></h3>
				<?php
				}
				?>
                <table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table">
                <thead>
                  <tr>
                    <td width="20">No</td>
                    <td>Ujian</td>
                    <td>Pelajaran</td>
                    <td>Tersedia</td>
                    <td>Soal</td>
                  </tr>
                </thead>
                
                <tbody>

				<?php
				$no = 0;
				foreach($data_item as $data)
				{
					$no++;
					?>
				 
                  <tr>
                    <td align="right"><?php echo $no;?></td>
                    <td><a href="ujian.php?option=join&register=true&test_id=<?php echo $data['test_id'];?>"><?php echo $data['test_name'];?></a></td>
                    <td><a href="ujian.php?option=join&register=true&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
                    <td><a href="ujian.php?option=join&register=true&test_id=<?php echo $data['test_id'];?>"><?php if($data['test_availability']=='F') echo 'Selamanya'; if($data['test_availability']=='L') echo 'Terbatas';?></a></td>
                    <td><a href="ujian.php?option=join&register=true&test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
                  </tr>
					<?php
				}
				?>
                </tbody>
                </table>
				<?php
			}
		}
		?>
        
    </div>
    </div>
    </div>
<?php
include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
}
?>

