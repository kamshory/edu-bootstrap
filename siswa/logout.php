<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/functions-pico.php";
require_once dirname(dirname(__FILE__)) . "/lib.inc/sessions.php";
if (isset($_GET['confirm-logout'])) {
    unset($_SESSION['student_username']);
    unset($_SESSION['student_password']);
    header("Location: index.php");
}

require_once dirname(dirname(__FILE__)) . "/lib.inc/logout.php";
