<?php
// admin/review-helpful.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }

$voter_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// try insert into review_helpful_votes; unique constraint prevents duplicates
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO review_helpful_votes (review_id, voter_ip) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $voter_ip);
    if (!$stmt->execute()) {
        // duplicate or other error
        $conn->rollback();
        echo json_encode(['success'=>false,'error'=>'Already voted or DB error']);
        exit;
    }

    // increment helpful_count on reviews
    $stmt2 = $conn->prepare("UPDATE reviews SET helpful_count = helpful_count + 1 WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // get new helpful_count
    $stmt3 = $conn->prepare("SELECT helpful_count FROM reviews WHERE id = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $res = $stmt3->get_result();
    $row = $res->fetch_assoc();
    $newCount = $row ? (int)$row['helpful_count'] : 0;

    $conn->commit();
    echo json_encode(['success'=>true,'helpful_count'=>$newCount]);
} catch (Exception $ex) {
    $conn->rollback();
    echo json_encode(['success'=>false,'error'=>$ex->getMessage()]);
}
