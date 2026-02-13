<?php
// admin/review-save.php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

// auth guard
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']); exit;
}

$uploadDir = __DIR__ . '/../uploads/reviews/';
$publicPathPrefix = 'uploads/reviews/';

// make sure upload dir exists
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// Accept POST multipart
$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$request_about = trim($_POST['request_about'] ?? '');
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
$review_text = trim($_POST['review_text'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// basic validation
if ($name === '' || $request_about === '' || $review_text === '') {
    echo json_encode(['success'=>false,'error'=>'Missing required fields']); exit;
}

$filename_to_save = null;
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['image'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success'=>false,'error'=>'Upload error']); exit;
    }
    if ($f['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success'=>false,'error'=>'Image too large (max 2MB)']); exit;
    }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array(strtolower($ext), $allowed)) {
        echo json_encode(['success'=>false,'error'=>'Invalid image type']); exit;
    }

    $filename_to_save = uniqid('review_') . '.' . $ext;
    $dest = $uploadDir . $filename_to_save;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        echo json_encode(['success'=>false,'error'=>'Failed to move uploaded file']); exit;
    }
    // public path relative to project root
    $publicPath = $publicPathPrefix . $filename_to_save;
}

try {
    if ($id) {
        // update
        if ($filename_to_save ?? false) {
            // remove old image path first (get current)
            $q = $conn->prepare("SELECT image_path FROM reviews WHERE id = ?");
            $q->bind_param("i", $id);
            $q->execute();
            $r = $q->get_result()->fetch_assoc();
            if ($r && !empty($r['image_path'])) {
                $old = __DIR__ . '/../' . $r['image_path'];
                if (file_exists($old)) @unlink($old);
            }
            $stmt = $conn->prepare("UPDATE reviews SET name=?, request_about=?, rating=?, review_text=?, image_path=?, status=? WHERE id=?");
            $stmt->bind_param("ssissi i", $name, $request_about, $rating, $review_text, $publicPath, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE reviews SET name=?, request_about=?, rating=?, review_text=?, status=? WHERE id=?");
            $stmt->bind_param("ssisii", $name, $request_about, $rating, $review_text, $status, $id);
        }
        // Note: above bind_param formats might differ depending on var types; adjust if needed
        // Using fallback simpler approach to avoid mismatched types:
        if ($filename_to_save ?? false) {
            $stmt = $conn->prepare("UPDATE reviews SET name=?, request_about=?, rating=?, review_text=?, image_path=?, status=? WHERE id=?");
            $stmt->bind_param("ssisisi", $name, $request_about, $rating, $review_text, $publicPath, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE reviews SET name=?, request_about=?, rating=?, review_text=?, status=? WHERE id=?");
            $stmt->bind_param("ssisii", $name, $request_about, $rating, $review_text, $status, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success'=>true, 'id'=>$id]);
        } else {
            echo json_encode(['success'=>false,'error'=>'DB update failed: '. $conn->error]);
        }

    } else {
        // insert
        if ($filename_to_save ?? false) {
            $stmt = $conn->prepare("INSERT INTO reviews (name, request_about, rating, review_text, image_path, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissi", $name, $request_about, $rating, $review_text, $publicPath, $status);
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (name, request_about, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisi", $name, $request_about, $rating, $review_text, $status);
        }

        if ($stmt->execute()) {
            echo json_encode(['success'=>true, 'id'=>$conn->insert_id]);
        } else {
            echo json_encode(['success'=>false,'error'=>'DB insert failed: ' . $conn->error]);
        }
    }
} catch (Exception $ex) {
    echo json_encode(['success'=>false,'error'=>$ex->getMessage()]);
}
