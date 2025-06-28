<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Prepare update query
$query = "UPDATE users SET username = ?, surname = ?, student_number = ?";
$params = [$data['username'], $data['surname'], $data['student_number']];
$types = "sss";

// Add password update if provided
if(!empty($data['password'])) {
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $query .= ", password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

$query .= " WHERE id = ?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}
?>