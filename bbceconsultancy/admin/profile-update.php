<?php
// admin/profile-update.php
require_once "../config/config.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$adminId = $_SESSION['admin_id'];

$name = trim($_POST['name'] ?? '');
$role = trim($_POST['role'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$avatarName = null;
$uploadDir = __DIR__ . '/../uploads/admins/';

// create uploads folder if missing
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!empty($_FILES['avatar']['name'])) {
    $f = $_FILES['avatar'];
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $avatarName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($f['name']));
    $target = $uploadDir . $avatarName;
    if (!move_uploaded_file($f['tmp_name'], $target)) {
        // ignore upload failure but you may add an error handling
        $avatarName = null;
    }
}

// Build update query
$fields = [];
$params = [];
$types = '';

if ($name !== '') { $fields[] = 'name = ?'; $params[] = $name; $types .= 's'; }
if ($role !== '') { $fields[] = 'role = ?'; $params[] = $role; $types .= 's'; }
if ($phone !== '') { $fields[] = 'phone = ?'; $params[] = $phone; $types .= 's'; }
if ($avatarName) { $fields[] = 'avatar = ?'; $params[] = $avatarName; $types .= 's'; }

if (!empty($fields)) {
    $sql = "UPDATE admins SET " . implode(', ', $fields) . " WHERE id = ?";
    $params[] = $adminId; $types .= 'i';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
}

// update session name if changed
if ($name) $_SESSION['admin_name'] = $name;

header("Location: profile.php");
exit;
