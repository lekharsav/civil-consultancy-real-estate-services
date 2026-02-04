<?php
// config/config.php

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bbceconsultancy";

/* ======================
   SESSION (30 DAYS)
====================== */
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
  session_start();
}
if (isset($_SESSION['admin_id']) && isset($conn)) {
    $aid = (int)$_SESSION['admin_id'];
    $conn->query("UPDATE admins SET last_active = NOW() WHERE id = $aid");
}
/* ======================
   DATABASE
====================== */
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
  die("DB Connection Failed");
}
