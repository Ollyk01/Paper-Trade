<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_GET['conversation_id']) || !isset($_GET['last_message_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$conversation_id = $_GET['conversation_id'];
$last_message_id = $_GET['last_message_id'];
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

// Get new messages
$stmt = $conn->prepare("
    SELECT m.*, u.username AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ? AND m.id > ?
    ORDER BY m.created_at ASC
");
$stmt->bind_param("ii", $conversation_id, $last_message_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mark received messages as read
if (!empty($messages)) {
    $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE 
                           WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>