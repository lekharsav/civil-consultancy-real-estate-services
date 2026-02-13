<?php
// admin/company-delete.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }

// delete logo path if exists
$stmt = $conn->prepare("SELECT logo_path FROM companies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if ($row && !empty($row['logo_path'])) {
    $p = __DIR__ . '/../' . $row['logo_path'];
    if (file_exists($p)) @unlink($p);
}

$stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>'Delete failed']);
