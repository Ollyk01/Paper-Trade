<?php

$host = "sql104.infinityfree.com";
$username = "if0_39323148";
$password = "9jZYum5ycHp";
$database = "if0_39323148_paper_trade";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Function to add item to cart
function addToCart($conn, $user_id, $textbook_id, $quantity = 1) {
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND textbook_id = ?");
    $stmt->bind_param("ii", $user_id, $textbook_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Update quantity if already exists
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND textbook_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $textbook_id);
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, textbook_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $textbook_id, $quantity);
    }
    
    return $stmt->execute();
}

// Function to remove item from cart
function removeFromCart($conn, $user_id, $textbook_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND textbook_id = ?");
    $stmt->bind_param("ii", $user_id, $textbook_id);
    return $stmt->execute();
}

// Function to get cart items for a user
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, t.book_title, t.price, t.book_image, t.user_id as seller_id, 
               u.username as seller_name 
        FROM cart c
        JOIN textbooks t ON c.textbook_id = t.id
        JOIN users u ON t.user_id = u.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to update cart item quantity
function updateCartQuantity($conn, $user_id, $textbook_id, $quantity) {
    if($quantity <= 0) {
        return removeFromCart($conn, $user_id, $textbook_id);
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND textbook_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $textbook_id);
    return $stmt->execute();
}
?>