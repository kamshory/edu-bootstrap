<?php
$sql = "SELECT * FROM `edu_admin` ";
$stmt = $database->executeQuery($sql);
$admin_id = $database->generateNewId();
if($stmt->rowCount() == 0)
{
    $username = 'admin';
    $password = 'admin';
    $passwordSession = md5($password);
    $passwordDatabase = md5($passwordSession);

    $sql = "INSERT INTO `edu_admin` 
    (`admin_id`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `username`, `admin_level`, `token_admin`, `email`, `phone`, `address`, `country_id`, `state_id`, `city_id`, `password`, `password_initial`, `auth`, `picture_rand`, `time_create`, `time_edit`, `time_last_activity`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `ip_last_activity`, `blocked`, `active`) VALUES
    ('$admin_id', '', 'Admin', 'M', 'Jambi', '2000-01-01', '$username', 1, '$passwordDatabase', 'admin@local', '', '', 0, 0, 0, 'c3284d0f94606de1fd2af172aba15bf3', 'admin', '', '742251', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '0', '$admin_id', '127.0.0.1', '127.0.0.1', '127.0.0.1', 0, 1)";
    $stmt = $database->executeInsert($sql, true);
    if($stmt->rowCount() > 0)
    {
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_password'] = $passwordSession;
        usleep(10000);
        header("Location: ".basename($_SERVER['PHP_SELF']));
    }
}