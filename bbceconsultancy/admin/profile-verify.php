<?php
// admin/profile-verify.php
require_once "../config/config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['ok' => false, 'message' => 'Not authenticated']);
    exit;
}

$adminId = $_SESSION['admin_id'];
$attempt = $_POST['attempt'] ?? '';

if ($attempt === '') {
    echo json_encode(['ok' => false, 'message' => 'Password required']);
    exit;
}

$stmt = $conn->prepare("SELECT password FROM admins WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['ok' => false, 'message' => 'Admin not found']);
    exit;
}

$stored = $row['password'] ?? '';

if ($attempt === $stored) {
    // Return the actual password (insecure; done intentionally per your request)
    echo json_encode(['ok' => true, 'password' => $stored]);
    exit;
}

echo json_encode(['ok' => false, 'message' => 'Incorrect password']);
exit;
