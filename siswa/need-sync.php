<?php
require_once dirname(__DIR__)."/lib.inc/functions-pico.php";
require_once dirname(__DIR__)."/lib.inc/sessions.php";

if(isset($_POST['sync']))
{
	$auth_student_id = kh_filter_input(INPUT_POST, "id", FILTER_SANITIZE_STRING_NEW);
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$auth = kh_filter_input(INPUT_POST, "auth", FILTER_SANITIZE_STRING_NEW);
	$password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD);
	if($password != '')
	{
		$sql = "SELECT `edu_student`.*
		FROM `edu_student` 
		WHERE `edu_student`.`student_id` = '$auth_student_id' 
		AND `edu_student`.`email` LIKE '$email' 
		AND `edu_student`.`auth` LIKE '$auth' ";
		$stmt1 = $database->executeQuery($sql);

		$passwordHash = md5(md5($password));

		if($stmt1->rowCount() > 0)
		{
			$sql = "SELECT * 
			FROM `member` 
			WHERE `email` LIKE '$email' 
			AND `member_id` != '$auth_student_id' 
			";
			$stmt2 = $database->executeQuery($sql);
			if($stmt2->rowCount() == 0)
			{
				$passwordHash = md5(md5($password));
				$sql = "UPDATE `member` SET `email` = '$email', `password` = '$passwordHash', `active` = true, `blocked` = false 
				WHERE `member_id` = '$auth_student_id' ";
				$database->executeUpdate($sql, true);
				$sql = "SELECT `username`, `member_id`
				FROM `member`
				WHERE `email` LIKE '$email' 
				AND `password` LIKE '$passwordHash'
				)
				";
				$stmt3 = $database->executeQuery($sql);
				if($stmt3->rowCount() > 0)
				{
					$data = $stmt3->fetch(\PDO::FETCH_ASSOC);
					$_SESSION['username'] = $data['username'];
					$_SESSION['password'] = md5($password);
					header("Location: profil.php");
				}
			}
			else
			{
				// someone take email
				header("Location: need-sync.php?option=email-taken&id=$auth_student_id&email=$email&auth=$auth");
			}
		}
	}
}

$auth_student_id = kh_filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING_NEW);
$email = kh_filter_input(INPUT_GET, "email", FILTER_SANITIZE_EMAIL);
$auth = kh_filter_input(INPUT_GET, "auth", FILTER_SANITIZE_STRING_NEW);

$sql = "SELECT `edu_student`.*
FROM `edu_student` 
WHERE `edu_student`.`student_id` = '$auth_student_id' AND `edu_student`.`email` like '$email' AND `edu_student`.`auth` like '$auth' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?><?php
require_once dirname(__DIR__)."/lib.inc/auth-siswa.php";
$pageTitle = "Pengguna Tidak Terdaftar";
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
?>
<?php
if(@$_GET['option'] == 'email-taken')
{
?>
<h3>Sinkronisasi Gagal</h3>
<p>Mohon maaf <strong><?php echo $data['name'];?></strong></p>
<p>Email yang Anda gunakan di <?php echo $cfg->app_name;?> telah digunakan oleh pengguna lain di Planetbiru. Kami tidak melakukan perubahan apapun kecuali:</p>
<ol>
  <li>Anda meminta kepada sekolah untuk mengganti alamat email dengan yang baru yang Anda yakin milik Anda dan tidak digunakan oleh orang lain.</li>
  <li>Anda melakukan <a href="https://www.planetbiru.com/reset-password.php" target="_blank">reset password dari Planetbiru</a>.</li>
</ol>
<p><a href="https://www.planetbiru.com/reset-password.php" target="_blank">Klik di sini untuk reset password</a></p>
<p><a href="../">Kembali ke depan</a></p>
<?php
}
else
{
?>
<h3>Memerlukan Sinkronisasi</h3>
<p>Selamat Datang <strong><?php echo $data['name'];?></strong></p>
<p>Password <?php echo $cfg->app_name;?> Anda tidak sesuai dengan password Planetbiru Anda. Anda harus menyamakannya dengan cara melakukan sinkronisasi. Setelah proses sinkronisasi, password <?php echo $cfg->app_name;?> akan sama dengan password Planetbiru dan Anda dapat masuk ke dalam dua akun sekaligus dengan menggunakan satu password saja.</p>
<form name="form1" method="post" action="need-sync.php">
  <div class="input-label">Email Saat Ini</div> 
  <div class="input-control"><input type="email" name="email" id="email" value="<?php echo $data['email'];?>" readonly></div>
  <div class="input-label">Password Saat Ini</div> 
  <div class="input-control"><input type="password" name="password" id="password"></div>
  <div class="input-control">
  	<input class="btn btn-primary" type="submit" name="sync" id="sync" value="Sinkronisasi">
  	<input class="btn btn-primary2" type="button" name="reset" id="reset" value="Reset Password" onclick="window.location='../reset-password.php'">
  </div>
  <input type="hidden" name="id" value="<?php echo $data['student_id'];?>">
  <input type="hidden" name="email" value="<?php echo $data['email'];?>">
  <input type="hidden" name="auth" value="<?php echo $data['auth'];?>">
</form>

<p><a href="https://www.planetbiru.com/register.php" target="_blank">Klik di sini jika belum terdaftar</a></p>
<p><a href="../">Kembali ke depan</a></p>
<?php
}
?>
<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
?>
<?php
}
?>