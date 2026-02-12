<?php
// admin/valuation-update.php
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

// admin guard
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  echo json_encode(['success' => false, 'error' => 'Not authorized']);
  exit;
}

// read JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$status = isset($data['status']) ? trim($data['status']) : '';
$admin_notes = isset($data['admin_notes']) ? trim($data['admin_notes']) : '';

if ($id <= 0 || $status === '') {
  echo json_encode(['success' => false, 'error' => 'Missing data']);
  exit;
}

// Ensure status column exists; if not, add it (safe, minimal change)
$colRes = $conn->query("SHOW COLUMNS FROM `valuation_requests` LIKE 'status'");
if (!$colRes || $colRes->num_rows === 0) {
  $alter = $conn->query("ALTER TABLE `valuation_requests` ADD COLUMN `status` VARCHAR(100) NULL DEFAULT 'New'");
  // ignore failure here (will error at update below if fails)
}

// Update status; optionally store admin_notes in `notes` (append) if provided
if ($admin_notes !== '') {
  // append admin note to notes column (preserve old notes)
  $stmt = $conn->prepare("UPDATE valuation_requests SET status = ?, notes = CONCAT(IFNULL(notes, ''), '\n\nAdmin note: ', ?) WHERE id = ?");
  $stmt->bind_param("ssi", $status, $admin_notes, $id);
} else {
  $stmt = $conn->prepare("UPDATE valuation_requests SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $status, $id);
}

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'DB error']);
}
$stmt->close();
$conn->close();
