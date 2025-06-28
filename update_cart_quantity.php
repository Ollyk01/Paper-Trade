<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$textbook_id = $data['textbook_id'];
$change = $data['change'];
$user_id = $_SESSION['user_id'];

// First get current quantity
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND textbook_id = ?");
$stmt->bind_param("ii", $user_id, $textbook_id);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();

$newQuantity = $current['quantity'] + $change;

if(updateCartQuantity($conn, $user_id, $textbook_id, $newQuantity)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}
?>