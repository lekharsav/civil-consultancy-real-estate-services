<?php
require __DIR__ . '/config/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact_us.php");
    exit;
}

// Collect inputs safely
$client_name = trim($_POST['client_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$property_owner_name = trim($_POST['property_owner_name'] ?? '');
$property_address = trim($_POST['property_address'] ?? '');
$plot_no = trim($_POST['plot_no'] ?? '');
$area_of_plot = trim($_POST['area_of_plot'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Basic validation
if (empty($client_name) || empty($contact_number)) {
    header("Location: contact_us.php?error=1");
    exit;
}

// Secure prepared insert
$stmt = $conn->prepare("INSERT INTO valuation_requests 
(client_name, contact_number, address, property_owner_name, property_address, plot_no, area_of_plot, notes)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssssssss",
    $client_name,
    $contact_number,
    $address,
    $property_owner_name,
    $property_address,
    $plot_no,
    $area_of_plot,
    $notes
);

if ($stmt->execute()) {
    header("Location: contact_us.php?success=1");
} else {
    header("Location: contact_us.php?error=1");
}

$stmt->close();
$conn->close();
exit;
