<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$textbook_id = $data['textbook_id'];
$user_id = $_SESSION['user_id'];

if(addToCart($conn, $user_id, $textbook_id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
}
?>