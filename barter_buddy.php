<?php
session_start();
include('server/connection.php');

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('location: index.php?error=Please login first');
    exit;
}

// Check user role
$allowed_roles = ['seller', 'student', 'admin'];
if (!in_array($_SESSION['role'] ?? '', $allowed_roles)) {
    header('location: index.php?error=Unauthorized access');
    exit;
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_content']) && isset($_POST['textbook_id'])) {
    $textbook_id = (int)$_POST['textbook_id'];
    $user_id = $_SESSION['user_id'];
    $message_content = $_POST['message_content'];
    
    // Get textbook details
    $stmt = $conn->prepare("SELECT * FROM textbooks WHERE id = ?");
    $stmt->bind_param("i", $textbook_id);
    $stmt->execute();
    $textbook = $stmt->get_result()->fetch_assoc();
    
    if ($textbook) {
        $seller_id = $textbook['user_id'];
        
        // Check if ANY conversation already exists between these users
        $stmt = $conn->prepare("SELECT id FROM conversations 
                               WHERE (user1_id = ? AND user2_id = ?) 
                               OR (user1_id = ? AND user2_id = ?)
                               ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("iiii", $user_id, $seller_id, $seller_id, $user_id);
        $stmt->execute();
        $conversation = $stmt->get_result()->fetch_assoc();
        
        if (!$conversation) {
            // Create new conversation if none exists
            $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id) 
                                   VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $seller_id);
            $stmt->execute();
            $conversation_id = $conn->insert_id;
        } else {
            $conversation_id = $conversation['id'];
        }
        
        // Add the message
        $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, content) 
                               VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $user_id, $message_content);
        $stmt->execute();
        
        // Return success response
        echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Textbook not found']);
    exit;
}

// Fetch all textbooks
$query = "SELECT * FROM textbooks";
$stmt = $conn->prepare($query);
$stmt->execute();
$textbooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barter Buddy</title>
    <link rel="stylesheet" href="barter.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }
        
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-btn:hover {
            color: black;
        }
        
        #messageInput {
            width: 100%;
            height: 150px;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
            resize: none;
            margin: 10px 0;
        }
        
        .send-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .send-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <section class="barter-header">
        <nav>
            <div class="container">
            <a href ="index.php"><img id="logo" src="IMAGES/Paper_Trade-removebg-preview (1).png"></a>
            
            <div class="nav-bar" id="navBar">
                <i class="fa fa-times" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="index.php" id="home">HOME</a></li>
                    <li><a href="acc.php">MY ACC</a></li>
                    <li><a href="#" id="openCartLink">CART</a></li>
                </ul>
            </div>

            <i class="fa fa-bars" onclick="showMenu()"></i>
            </div>
        </nav> 
        </section>

        <!-----JavaScript for the menu------->
<script>
    var navBar = document.getElementById("navBar");
 
    function showMenu() {
        navBar.style.right = "0";
    }
 
    function hideMenu() {
        navBar.style.right = "-200px";
    }
</script>

 <header class="header">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search for books..." id="searchInput" ><i class="fa fa-search search-icon" aria-hidden="true"></i>
        </div>
        
        <div class="header-actions">
            <button class="btn btn-secondary" id="messagesBtn">
               <i class="fa fa-comments" aria-hidden="true"></i> Messages
            </button>
            <button class="btn btn-primary" id="postTradeBtn">
               <i class="fa fa-plus" aria-hidden="true"></i> Post Trade
            </button>
        </div>
    </header>

    <main class="main-content">
        <section class="hero">
            <h1>Welcome to Paper Trade! </h1>
            <p>Exchange textbooks you no longer need for the ones you do. Save money and help other students succeed.</p>
        </section>

        <section class="filters-section">
            <div class="filters-btn" id="filtersBtn">
                <i class="fa fa-filter" aria-hidden="true"></i>Filter
            </div>
        </section>

        <!-- Original Card Layout with Textbook Data -->
        <div class="card-container">
            <?php if(empty($textbooks)): ?>
                <p>No textbooks found.</p>
            <?php else: ?>
                <?php foreach($textbooks as $book): ?>
                    <div class="card">
                        <div class="top-info">
                            <span>Posted <?= rand(1, 7) ?> days ago</span>
                            <span class="status">available</span>
                        </div>
                        <div class="has">
                            <span class="condition <?= strtolower($book['condition']) ?>">
                                <?= htmlspecialchars($book['condition']) ?>
                            </span>
                            <h3><?= htmlspecialchars($book['book_title']) ?></h3>
                            <p>by <?= htmlspecialchars($book['book_author']) ?></p>
                        </div>
                        <div class="exchange-icon">⇄</div>
                        <div class="wants">
                            <strong>Wants:</strong>
                            <p>Any book in same category<br><span>or R<?= number_format($book['price'], 2) ?></span></p>
                        </div>
                        
                        <div class="actions">
                            <button onclick="openMessageModal(<?= $book['id'] ?>, '<?= htmlspecialchars($book['book_title']) ?>')">💬 Message</button>
                            <button class="primary">Request Trade</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeMessageModal()">&times;</span>
            <h3>Send Message About <span id="messageBookTitle"></span></h3>
            <textarea id="messageInput" placeholder="Type your message here..."></textarea>
            <button class="send-btn" onclick="sendMessage()">Send Message</button>
        </div>
    </div>

    <!-- Post Trade Modal -->
    <div id="postTradeModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closePostTradeModal()">&times;</span>
            <h3>Post a Trade</h3>
            <form id="tradeForm">
                <label for="hasBook"><strong>Book You Have</strong></label>
                <input type="text" id="hasBook" required>

                <label for="hasAuthor"><strong>Author</strong></label>
                <input type="text" id="hasAuthor" required>

                <label for="condition"><strong>Condition</strong></label>
                <select id="condition" required>
                    <option value="">Select condition...</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                </select>

                <label for="wantsBook"><strong>Book You Want</strong></label>
                <input type="text" id="wantsBook" required>

                <label for="wantsAuthor"><strong>Author</strong></label>
                <input type="text" id="wantsAuthor" required>

                <label for="description"><strong>Description</strong></label>
                <textarea id="description"></textarea>

                <button type="submit" class="send-btn">Post Trade</button>
            </form>
        </div>
    </div>

<script>
    // Mobile menu toggle
    var navBar = document.getElementById("navBar");
 
    function showMenu() {
        navBar.style.right = "0";
    }
 
    function hideMenu() {
        navBar.style.right = "-200px";
    }

    // Message modal functionality
    let currentTextbookId = null;
    
    function openMessageModal(textbookId, bookTitle) {
        currentTextbookId = textbookId;
        document.getElementById("messageBookTitle").textContent = bookTitle;
        document.getElementById("messageModal").style.display = "block";
        document.getElementById("messageInput").focus();
    }
    
    function closeMessageModal() {
        document.getElementById("messageModal").style.display = "none";
        document.getElementById("messageInput").value = "";
        currentTextbookId = null;
    }
    
    function sendMessage() {
        const message = document.getElementById("messageInput").value.trim();
        if (!message) {
            alert("Please enter a message.");
            return;
        }
        
        if (!currentTextbookId) {
            alert("Error: No textbook selected.");
            return;
        }
        
        // Send message via AJAX
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message_content=${encodeURIComponent(message)}&textbook_id=${currentTextbookId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Message sent successfully!");
                closeMessageModal();
                // Optionally redirect to messages page
                // window.location.href = `messages.php?conversation_id=${data.conversation_id}`;
            } else {
                alert("Error sending message: " + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while sending the message.");
        });
    }
    
    // In the JavaScript section, replace the current post trade modal functions with:

document.getElementById("postTradeBtn").addEventListener("click", function () {
  openPostTradeModal();
});

function openPostTradeModal() {
  document.getElementById("postTradeModal").style.display = "block";
}

function closePostTradeModal() {
  document.getElementById("postTradeModal").style.display = "none";
}

// Handle form submission (temporary action)
document.getElementById("tradeForm").addEventListener("submit", function (e) {
  e.preventDefault();
  alert("Trade posted successfully!");
  closePostTradeModal();
  this.reset(); // Clear form
});

// Optional: Close when clicking outside the modal
window.onclick = function (event) {
  const modal1 = document.getElementById("messageModal");
  const modal2 = document.getElementById("postTradeModal");

  if (event.target == modal1) {
    modal1.style.display = "none";
  }

  if (event.target == modal2) {
    modal2.style.display = "none";
  }
};
    
    // Messages button
    document.getElementById("messagesBtn").addEventListener("click", function() {
        window.location.href = "messages.php";
    });
</script>



</body>
</html>