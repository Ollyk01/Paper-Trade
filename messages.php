<?php
session_start();
include('server/connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get textbook_id from URL if it exists
$textbook_id = isset($_GET['textbook_id']) ? (int)$_GET['textbook_id'] : null;

// If textbook_id is provided, find or create conversation
if ($textbook_id) {
    // Get textbook details
    $stmt = $conn->prepare("SELECT * FROM textbooks WHERE id = ?");
    $stmt->bind_param("i", $textbook_id);
    $stmt->execute();
    $textbook = $stmt->get_result()->fetch_assoc();
    
    if ($textbook) {
        $seller_id = $textbook['user_id'];
        
        // Check if conversation already exists
        $stmt = $conn->prepare("SELECT id FROM conversations 
                               WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
                               AND textbook_id = ?");
        $stmt->bind_param("iiiii", $user_id, $seller_id, $seller_id, $user_id, $textbook_id);
        $stmt->execute();
        $conversation = $stmt->get_result()->fetch_assoc();
        
        if ($conversation) {
            // Redirect to existing conversation
            header("Location: messages.php?conversation_id=" . $conversation['id']);
            exit;
        } else {
            // Create new conversation only if one doesn't exist
            $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id, textbook_id) 
                                   VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $seller_id, $textbook_id);
            $stmt->execute();
            $conversation_id = $conn->insert_id;
            
            // Add initial message
            $initial_message = "Hello, I'm interested in this book: " . $textbook['book_title'] . " and would like to chat.";
            $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, content) 
                                   VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $conversation_id, $user_id, $initial_message);
            $stmt->execute();
            
            // Redirect to the newly created conversation
            header("Location: messages.php?conversation_id=" . $conversation_id);
            exit;
        }
    } else {
        // Textbook not found
        $_SESSION['error'] = "Textbook not found";
        header("Location: messages.php");
        exit;
    }
}



// Get all conversations for the user
$stmt = $conn->prepare("
    SELECT c.*, 
           u1.username AS user1_name, 
           u2.username AS user2_name,
           t.book_title,
           t.book_image,
           (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.is_read = FALSE) AS unread_count,
           (SELECT content FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message,
           (SELECT created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message_time
    FROM conversations c
    JOIN users u1 ON c.user1_id = u1.id
    JOIN users u2 ON c.user2_id = u2.id
    LEFT JOIN textbooks t ON c.textbook_id = t.id
    WHERE c.user1_id = ? OR c.user2_id = ?
    ORDER BY last_message_time DESC
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get current conversation if specified
$current_conversation = null;
$current_conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : null;

if ($current_conversation_id) {
    foreach ($conversations as $conv) {
        if ($conv['id'] == $current_conversation_id) {
            $current_conversation = $conv;
            break;
        }
    }
    
    // Mark messages as read
    if ($current_conversation) {
        $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE 
                               WHERE conversation_id = ? AND sender_id != ?");
        $stmt->bind_param("ii", $current_conversation_id, $user_id);
        $stmt->execute();
    }
}

// Get messages for current conversation
$messages = [];
if ($current_conversation) {
    $stmt = $conn->prepare("
        SELECT m.*, u.username AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("i", $current_conversation_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Paper Trade</title>
    <link rel="stylesheet" href="message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
   <div class="chat-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-profile">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div>
                    <div style="font-weight: 600;"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Online</div>
                </div>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Search conversations..." id="searchInput">
        </div>

        <div class="chat-list" id="chatList">
            <?php foreach ($conversations as $conv): 
                $other_user_id = $conv['user1_id'] == $user_id ? $conv['user2_id'] : $conv['user1_id'];
                $other_user_name = $conv['user1_id'] == $user_id ? $conv['user2_name'] : $conv['user1_name'];
            ?>
                <div class="chat-item <?= $current_conversation && $current_conversation['id'] == $conv['id'] ? 'active' : '' ?>" 
                     onclick="window.location.href='messages.php?conversation_id=<?= $conv['id'] ?>'">
                    <div class="chat-item-content">
                        <div class="contact-avatar"><?= strtoupper(substr($other_user_name, 0, 1)) ?></div>
                        <div class="contact-info">
                            <div class="contact-name"><?= htmlspecialchars($other_user_name) ?></div>
                            <div class="last-message">
                                <?php if ($conv['textbook_id']): ?>
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($conv['book_title']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars(substr($conv['last_message'], 0, 30)) . (strlen($conv['last_message']) > 30 ? '...' : '') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="message-time"><?= format_time($conv['last_message_time']) ?></div>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <div class="unread-badge"><?= $conv['unread_count'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <?php if ($current_conversation): 
            $other_user_id = $current_conversation['user1_id'] == $user_id ? $current_conversation['user2_id'] : $current_conversation['user1_id'];
            $other_user_name = $current_conversation['user1_id'] == $user_id ? $current_conversation['user2_name'] : $current_conversation['user1_name'];
        ?>
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="header-avatar"><?= strtoupper(substr($other_user_name, 0, 1)) ?></div>
                    <div class="header-details">
                        <h3 id="currentChatName"><?= htmlspecialchars($other_user_name) ?></h3>
                        <div class="online-status" id="currentChatStatus">Online</div>
                    </div>
                </div>
                <div class="chat-actions">
                    <div class="menu-container">
                        <button class="menu-button" id="menuToggle">⋮</button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <div class="dropdown-item">Mark as Read</div>
                            <div class="dropdown-item">Delete Chat</div>
                            <div class="dropdown-item">Block User</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                <?php if ($current_conversation['textbook_id']): ?>
                    <div class="book-reference">
                        <img src="assets/images/Categories/<?= htmlspecialchars($current_conversation['book_image']) ?>" 
                             alt="<?= htmlspecialchars($current_conversation['book_title']) ?>">
                        <div>
                            <h4><?= htmlspecialchars($current_conversation['book_title']) ?></h4>
                            <p>This conversation is about this textbook</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= $message['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                        <div class="message-bubble">
                            <?= htmlspecialchars($message['content']) ?>
                            <div class="message-time-stamp"><?= format_time($message['created_at']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="message-input-container">
                <form id="messageForm">
                    <input type="hidden" name="conversation_id" value="<?= $current_conversation_id ?>">
                    <div class="message-input-wrapper">
                        <div class="input-actions">
                            <button type="button" class="input-btn" id="emojiBtn">😊</button>
                        </div>
                        <input type="text" class="message-input" name="message" placeholder="Type a message here" id="messageInput" required>
                        <button type="submit" class="send-btn" id="sendBtn">
                            <span>➤</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="no-conversation-selected">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose an existing conversation or start a new one</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (menuToggle && dropdownMenu) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', function() {
            dropdownMenu.style.display = 'none';
        });
    }
    
   
    
    // Auto-scroll to bottom of messages
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach(item => {
                const name = item.querySelector('.contact-name').textContent.toLowerCase();
                const message = item.querySelector('.last-message').textContent.toLowerCase();
                
                item.style.display = (name.includes(searchTerm) || message.includes(searchTerm)) ? 'flex' : 'none';
            });
        });
    }
});
</script>

<script>
// Track the last message ID we've processed
let lastProcessedMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

// Helper function to format time
function formatTime(timestamp) {
    const now = new Date();
    const messageTime = new Date(timestamp);
    const diffMinutes = Math.floor((now - messageTime) / 60000);
    
    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)}h ago`;
    return messageTime.toLocaleDateString();
}

// Function to check for and display new messages
function checkForNewMessages() {
    if (!<?= $current_conversation_id ?>) return;
    
    fetch(`server/get_new_messages.php?conversation_id=<?= $current_conversation_id ?>&last_message_id=${lastProcessedMessageId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.messages.length > 0) {
            const messagesContainer = document.getElementById('messagesContainer');
            let newMessagesAdded = false;
            
            data.messages.forEach(message => {
                // Only add if this message doesn't already exist
                if (!document.querySelector(`[data-message-id="${message.id}"]`)) {
                    const isSent = message.sender_id == <?= $user_id ?>;
                    const newMessage = document.createElement('div');
                    newMessage.className = `message ${isSent ? 'sent' : 'received'}`;
                    newMessage.dataset.messageId = message.id;
                    newMessage.innerHTML = `
                        <div class="message-bubble" data-message-id="<?= $message['id'] ?>">
                            ${message.content}
                            <div class="message-time-stamp">${formatTime(message.created_at)}</div>
                        </div>
                    `;
                    messagesContainer.appendChild(newMessage);
                    newMessagesAdded = true;
                }
            });

            // Update last processed ID
            lastProcessedMessageId = data.messages[data.messages.length - 1].id;
            
            // Scroll to bottom if new messages were added
            if (newMessagesAdded) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
    })
    .catch(error => console.error('Error checking for new messages:', error));
}

// Handle message form submission
function setupMessageForm() {
    const messageForm = document.getElementById('messageForm');
    if (!messageForm) return;

    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageInput = document.getElementById('messageInput');
        const messageContent = messageInput.value.trim();
        const messagesContainer = document.getElementById('messagesContainer');
        
        if (!messageContent) return;

        // Disable send button during submission
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;

        fetch('server/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update last processed ID
                if (data.message_id) {
                    lastProcessedMessageId = data.message_id;
                }
                
                // Add message to UI immediately
                const newMessage = document.createElement('div');
                newMessage.className = 'message sent';
                newMessage.dataset.messageId = data.message_id;
                newMessage.innerHTML = `
                    <div class="message-bubble">
                        ${messageContent}
                        <div class="message-time-stamp">Just now</div>
                    </div>
                `;
                messagesContainer.appendChild(newMessage);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Clear input
                messageInput.value = '';
            } else {
                alert('Failed to send message: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send message');
        })
        .finally(() => {
            sendBtn.disabled = false;
        });
    });
}

// Setup menu toggle functionality
function setupMenuToggle() {
    const menuToggle = document.getElementById('menuToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (menuToggle && dropdownMenu) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', function() {
            dropdownMenu.style.display = 'none';
        });
    }
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const chatItems = document.querySelectorAll('.chat-item');
        
        chatItems.forEach(item => {
            const name = item.querySelector('.contact-name').textContent.toLowerCase();
            const message = item.querySelector('.last-message').textContent.toLowerCase();
            item.style.display = (name.includes(searchTerm) || message.includes(searchTerm)) ? 'flex' : 'none';
        });
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupMenuToggle();
    setupMessageForm();
    setupSearch();
    
    // Auto-scroll to bottom of messages
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Start polling for new messages every 3 seconds
    setInterval(checkForNewMessages, 3000);
});
</script>

<?php
function format_time($datetime) {
    if (!$datetime) return '';
    
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    } else {
        return date('M j', $time);
    }
}
?>
</body>
</html>