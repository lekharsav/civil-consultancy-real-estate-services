<?php
require_once "../config/config.php";

$id = $_POST['id'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE services SET status=? WHERE id=?");
$stmt->bind_param("ii",$status,$id);
$stmt->execute();
