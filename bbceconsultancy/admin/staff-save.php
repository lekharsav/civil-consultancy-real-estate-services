<?php
require_once "../config/config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

function fail($msg){
    echo json_encode(['success'=>false,'error'=>$msg]);
    exit;
}

$name        = trim($_POST['name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$id          = trim($_POST['id'] ?? ''); // CAN BE EMPTY

if ($name === '' || $category === '') {
    fail("Name and category are required");
}

/* ================= IMAGE UPLOAD ================= */
$imageName = null;
$uploadDir = __DIR__ . '/../uploads/staff/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($_FILES['image']['type'], $allowed)) {
        fail("Invalid image format");
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = time().'_'.uniqid().'.'.$ext;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$imageName)) {
        fail("Image upload failed");
    }
}

/* ================= INSERT ================= */
if ($id === '') {

    $stmt = $conn->prepare(
        "INSERT INTO staff (name, category, designation, phone, email, image)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "ssssss",
        $name,
        $category,
        $designation,
        $phone,
        $email,
        $imageName
    );

    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
        exit;
    }

    fail("Insert failed");
}

/* ================= UPDATE ================= */
$id = (int)$id;

// remove old image if new uploaded
if ($imageName) {
    $old = $conn->prepare("SELECT image FROM staff WHERE id=?");
    $old->bind_param("i",$id);
    $old->execute();
    $oldImg = $old->get_result()->fetch_assoc()['image'] ?? '';

    if ($oldImg && file_exists($uploadDir.$oldImg)) {
        unlink($uploadDir.$oldImg);
    }

    $stmt = $conn->prepare(
        "UPDATE staff SET
         name=?, category=?, designation=?, phone=?, email=?, image=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "ssssssi",
        $name,$category,$designation,$phone,$email,$imageName,$id
    );

} else {

    $stmt = $conn->prepare(
        "UPDATE staff SET
         name=?, category=?, designation=?, phone=?, email=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "sssssi",
        $name,$category,$designation,$phone,$email,$id
    );
}

if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
    exit;
}

fail("Update failed");
