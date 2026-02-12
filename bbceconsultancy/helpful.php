<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("UPDATE reviews SET helpful_count = helpful_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = $stmt->affected_rows > 0;
    $stmt->close();
} else {
    $success = false;
}
$conn->close();
echo json_encode(['success' => $success]);