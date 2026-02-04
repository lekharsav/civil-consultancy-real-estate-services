<?php
$currentPage = basename($_SERVER['PHP_SELF']);
// admin/auth_check.php
require_once __DIR__ . '/../config/config.php';




if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
