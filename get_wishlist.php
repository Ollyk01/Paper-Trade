<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT w.book_id, t.book_title, t.price, t.book_image 
    FROM wishlist w
    JOIN textbooks t ON w.book_id = t.id
    WHERE w.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>