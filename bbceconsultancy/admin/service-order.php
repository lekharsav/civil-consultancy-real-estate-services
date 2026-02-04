<?php
require_once "../config/config.php";
$data = json_decode(file_get_contents("php://input"), true);

foreach ($data as $row) {
  $stmt = $conn->prepare("UPDATE services SET sort_order=? WHERE id=?");
  $stmt->bind_param("ii",$row['order'],$row['id']);
  $stmt->execute();
}
