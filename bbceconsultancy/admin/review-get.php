<?php
// admin/review-get.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }

$stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$review = $res->fetch_assoc();
if ($review) echo json_encode(['success'=>true,'review'=>$review]);
else echo json_encode(['success'=>false,'error'=>'Not found']);
