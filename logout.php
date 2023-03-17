<?php
include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
include_once dirname(__FILE__) . "/lib.inc/sessions.php";
if (isset($_GET['confirm-logout'])) {
    include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
    include_once dirname(__FILE__) . "/lib.inc/sessions.php";
    unset($_SESSION['student_username']);
    unset($_SESSION['student_password']);
    unset($_SESSION['teacher_username']);
    unset($_SESSION['teacher_password']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_password']);
    session_destroy();
    header("Location: index.php");
}
require_once dirname(__FILE__) . "/lib.inc/logout.php";
