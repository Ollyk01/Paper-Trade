<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: checkout.php');
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Verify order belongs to user
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: checkout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment | Paper Trade</title>
</head>
<body>
    <h1>Payment Page (Under Construction)</h1>
    <p>Order #<?= $order_id ?> Total: R<?= number_format($order['total_amount'], 2) ?></p>
    <!-- Payment gateway integration will go here -->
</body>
</html>