<?php
require_once "../config/config.php";
$id = $_POST['id'];
$conn->query("DELETE FROM services WHERE id=$id");
