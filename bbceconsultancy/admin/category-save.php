<?php
require_once "../config/config.php";

$name = $_POST['name'];
$icon = $_POST['icon'];
$status = 1;

$imageName = null;
if (!empty($_FILES['image']['name'])) {
  $imageName = time().'_'.$_FILES['image']['name'];
  move_uploaded_file($_FILES['image']['tmp_name'], "uploads/categories/".$imageName);
}

$stmt = $conn->prepare("
  INSERT INTO service_categories (name, icon, image, sort_order, status)
  VALUES (?, ?, ?, (SELECT IFNULL(MAX(sort_order),0)+1 FROM service_categories), ?)
");
$stmt->bind_param("sssi", $name, $icon, $imageName, $status);
$stmt->execute();

header("Location: services.php");
