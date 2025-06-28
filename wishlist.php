<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$book_id = $data['book_id'];
$action = $data['action'] ?? 'toggle';

// Check if book exists in wishlist
$stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND book_id = ?");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;

if ($action === 'toggle') {
    if ($exists) {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
        }
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $book_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
    }
}
?>