<?php
require_once '../config/config.php';
require_once '../config/auth.php';

logout();

header("Location: " . SITE_URL . "student/login.php");
exit();
?>