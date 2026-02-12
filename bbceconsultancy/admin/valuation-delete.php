<?php
// admin/valuation-delete.php
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

// admin guard
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  echo json_encode(['success' => false, 'error' => 'Not authorized']);
  exit;
}

// read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id <= 0) {
  echo json_encode(['success' => false, 'error' => 'Invalid id']);
  exit;
}

// delete
$stmt = $conn->prepare("DELETE FROM valuation_requests WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'DB error']);
}
$stmt->close();
$conn->close();
