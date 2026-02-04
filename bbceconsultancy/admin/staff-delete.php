<?php
// admin/staff-delete.php
require_once "../config/config.php";
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;

if (!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }

// fetch image name
$stmt = $conn->prepare("SELECT image FROM staff WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) { echo json_encode(['success'=>false,'error'=>'Record not found']); exit; }

// delete DB row
$stmt2 = $conn->prepare("DELETE FROM staff WHERE id = ?");
$stmt2->bind_param("i",$id);
$ok = $stmt2->execute();

if ($ok) {
    if (!empty($r['image'])) {
        $path = __DIR__ . '/../uploads/staff/' . $r['image'];
        if (is_file($path)) @unlink($path);
    }
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Delete failed']);
