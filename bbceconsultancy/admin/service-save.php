<?php
require_once "../config/config.php";

$id = $_POST['id'] ?? null;
$category_id = $_POST['category_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$used_for = $_POST['used_for'];

$imageName = null;
if (!empty($_FILES['image']['name'])) {
  $imageName = time().'_'.$_FILES['image']['name'];
  move_uploaded_file($_FILES['image']['tmp_name'], "uploads/services/".$imageName);
}

if ($id) {
  $sql = "UPDATE services SET title=?, description=?, used_for=?";
  if ($imageName) $sql .= ", image=?";
  $sql .= " WHERE id=?";

  $stmt = $conn->prepare($sql);
  if ($imageName) {
    $stmt->bind_param("ssssi",$title,$description,$used_for,$imageName,$id);
  } else {
    $stmt->bind_param("sssi",$title,$description,$used_for,$id);
  }
} else {
  $stmt = $conn->prepare("
    INSERT INTO services (category_id, title, description, used_for, image, sort_order, status)
    VALUES (?, ?, ?, ?, ?, 
      (SELECT IFNULL(MAX(sort_order),0)+1 FROM services WHERE category_id=?), 1)
  ");
  $stmt->bind_param("issssi",$category_id,$title,$description,$used_for,$imageName,$category_id);
}

$stmt->execute();
header("Location: services.php");
