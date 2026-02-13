<?php
// admin/company-toggle.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }

$stmt = $conn->prepare("UPDATE companies SET status = 1 - status WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>'Toggle failed']);
