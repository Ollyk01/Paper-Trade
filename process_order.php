<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = $_POST;

// Start transaction
$conn->begin_transaction();

try {
    // 1. Create the order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, shipping_address, total_amount) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("isd", $user_id, $data['address'], $data['total_amount']);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // 2. Get cart items
    $stmt = $conn->prepare("
        SELECT c.textbook_id, c.quantity, t.price, t.user_id as seller_id
        FROM cart c
        JOIN textbooks t ON c.textbook_id = t.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // 3. Create order items
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, textbook_id, quantity, price, seller_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiidi", $order_id, $item['textbook_id'], $item['quantity'], $item['price'], $item['seller_id']);
        $stmt->execute();
    }
    
    // 4. Clear the cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to payment page with order ID
    header("Location: payment.php?order_id=" . $order_id);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
    exit;
}
?>