<?php
// admin/company-save.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}

$uploadDir = __DIR__ . '/../uploads/companies/';
$publicPathPrefix = 'uploads/companies/';

if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

if ($name === '') { echo json_encode(['success'=>false,'error'=>'Company name required']); exit; }

$logoPublicPath = null;
if (!empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['logo'];
    if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'error'=>'Upload error']); exit; }
    if ($f['size'] > 2 * 1024 * 1024) { echo json_encode(['success'=>false,'error'=>'Logo too large (max 2MB)']); exit; }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array(strtolower($ext), $allowed)) { echo json_encode(['success'=>false,'error'=>'Invalid logo type']); exit; }
    $fname = uniqid('company_') . '.' . $ext;
    $dest = $uploadDir . $fname;
    if (!move_uploaded_file($f['tmp_name'], $dest)) { echo json_encode(['success'=>false,'error'=>'Failed to move logo']); exit; }
    $logoPublicPath = $publicPathPrefix . $fname;
}

if ($id) {
    // update
    if ($logoPublicPath) {
        // delete old
        $q = $conn->prepare("SELECT logo_path FROM companies WHERE id = ?");
        $q->bind_param("i",$id);
        $q->execute();
        $old = $q->get_result()->fetch_assoc();
        if ($old && !empty($old['logo_path'])) {
            $op = __DIR__ . '/../' . $old['logo_path'];
            if (file_exists($op)) @unlink($op);
        }
        $stmt = $conn->prepare("UPDATE companies SET name=?, type=?, description=?, logo_path=?, status=? WHERE id=?");
        $stmt->bind_param("ssssii", $name, $type, $description, $logoPublicPath, $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE companies SET name=?, type=?, description=?, status=? WHERE id=?");
        $stmt->bind_param("sssii", $name, $type, $description, $status, $id);
    }
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$id]);
    else echo json_encode(['success'=>false,'error'=>$conn->error]);
} else {
    // insert
    if ($logoPublicPath) {
        $stmt = $conn->prepare("INSERT INTO companies (name, type, description, logo_path, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $type, $description, $logoPublicPath, $status);
    } else {
        $stmt = $conn->prepare("INSERT INTO companies (name, type, description, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $type, $description, $status);
    }
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
    else echo json_encode(['success'=>false,'error'=>$conn->error]);
}
