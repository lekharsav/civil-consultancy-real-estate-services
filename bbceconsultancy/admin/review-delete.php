<?php
// admin/review-delete.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }

// get image path
$stmt = $conn->prepare("SELECT image_path FROM reviews WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if ($row && !empty($row['image_path'])) {
    $path = __DIR__ . '/../' . $row['image_path'];
    if (file_exists($path)) @unlink($path);
}

// delete record
$stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>'Delete failed']);
