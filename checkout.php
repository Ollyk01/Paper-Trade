<?php
session_start();
include('server/connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get cart items
$cart_items = [];
$total = 0;

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
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Paper Trade</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2E7D32;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: #757575;
            --white: #ffffff;
            --black: #212121;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--black);
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .checkout-form {
            flex: 2;
            min-width: 300px;
            background: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .order-summary {
            flex: 1;
            min-width: 300px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            max-height: 80vh;
        }
        
        .order-summary-header {
            padding: 20px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .order-summary-content {
            padding: 20px;
            overflow-y: auto;
            flex-grow: 1;
        }
        
        .order-summary-footer {
            padding: 20px;
            border-top: 1px solid var(--medium-gray);
            background: var(--light-gray);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .order-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-item img {
            width: 70px;
            height: 90px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 4px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 15px;
        }
        
        .order-item-meta {
            font-size: 13px;
            color: var(--dark-gray);
            margin-bottom: 3px;
        }
        
        .checkout-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .checkout-btn:hover {
            background: var(--secondary-color);
        }
        
        .total-amount {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .total-label {
            color: var(--dark-gray);
        }
        
        .total-value {
            color: var(--black);
        }
        
        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--black);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .order-summary {
                order: -1;
                max-height: none;
            }
        }
        
        /* Scrollbar styling */
        .order-summary-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .order-summary-content::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 4px;
        }
        
        .order-summary-content::-webkit-scrollbar-thumb {
            background: var(--medium-gray);
            border-radius: 4px;
        }
        
        .order-summary-content::-webkit-scrollbar-thumb:hover {
            background: var(--dark-gray);
        }
    </style>
</head>
<body>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <h2 class="section-title">Shipping Information</h2>
            <form id="checkoutForm" action="server/process_order.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                </div>
                
                <div class="form-group">
                    <label for="address">Shipping Address</label>
                    <textarea id="address" name="address" rows="4" required></textarea>
                </div>
                
                <input type="hidden" name="total_amount" value="<?= $total ?>">
                
                <button type="submit" class="checkout-btn">
                    <i class="fas fa-lock"></i> Proceed to Payment
                </button>
            </form>
        </div>
        
        <div class="order-summary">
            <div class="order-summary-header">
                <h2 class="section-title">Order Summary</h2>
            </div>
            
            <div class="order-summary-content">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <img src="assets/images/Categories/<?= htmlspecialchars($item['book_image']) ?>" alt="<?= htmlspecialchars($item['book_title']) ?>">
                        <div class="order-item-details">
                            <div class="order-item-title"><?= htmlspecialchars($item['book_title']) ?></div>
                            <div class="order-item-meta">Seller: <?= htmlspecialchars($item['seller_name']) ?></div>
                            <div class="order-item-meta">Price: R<?= number_format($item['price'], 2) ?></div>
                            <div class="order-item-meta">Quantity: <?= $item['quantity'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary-footer">
                <div class="total-amount">
                    <span class="total-label">Total:</span>
                    <span class="total-value">R<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>