<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT c.*, t.book_title, CAST(t.price AS DECIMAL(10,2)) as price, t.book_image, 
           t.user_id as seller_id, u.username as seller_name 
    FROM cart c
    JOIN textbooks t ON c.textbook_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>