<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['conversation_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$conversation_id = $_POST['conversation_id'];
$message = trim($_POST['message']);
$user_id = $_SESSION['user_id'];

// Verify user is part of the conversation
$stmt = $conn->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Insert message
$stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $conversation_id, $user_id, $message);

if ($stmt->execute()) {
    // Return additional data if needed
    echo json_encode([
        'success' => true,
        'message_id' => $conn->insert_id,
        'timestamp' => date('H:i')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>