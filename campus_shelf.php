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

// Handle message seller request
if (isset($_GET['message_seller'])) {
    $textbook_id = (int)$_GET['message_seller'];
    $user_id = $_SESSION['user_id'];
    
    // Get textbook details
    $stmt = $conn->prepare("SELECT * FROM textbooks WHERE id = ?");
    $stmt->bind_param("i", $textbook_id);
    $stmt->execute();
    $textbook = $stmt->get_result()->fetch_assoc();
    
    if ($textbook) {
        $seller_id = $textbook['user_id'];
        
        // Check if ANY conversation already exists between these users (regardless of textbook)
        $stmt = $conn->prepare("SELECT id FROM conversations 
                               WHERE (user1_id = ? AND user2_id = ?) 
                               OR (user1_id = ? AND user2_id = ?)
                               ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("iiii", $user_id, $seller_id, $seller_id, $user_id);
        $stmt->execute();
        $conversation = $stmt->get_result()->fetch_assoc();
        
        if ($conversation) {
            // Redirect to existing conversation
            header("Location: messages.php?conversation_id=" . $conversation['id']);
            exit;
        } else {
            // Create new conversation only if no conversation exists between these users
            $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id) 
                                   VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $seller_id);
            $stmt->execute();
            $conversation_id = $conn->insert_id;
            
            // Add initial message
            $initial_message = "Hello, I'm interested in your book: " . $textbook['book_title'];
            $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, content) 
                                   VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $conversation_id, $user_id, $initial_message);
            $stmt->execute();
            
            // Redirect to the new conversation
            header("Location: messages.php?conversation_id=" . $conversation_id);
            exit;
        }
    } else {
        // Textbook not found
        $_SESSION['error'] = "Textbook not found";
        header("Location: campus_shelf.php");
        exit;
    }
}

// Build the base query
$query = "SELECT * FROM textbooks WHERE 1=1";
$params = [];
$types = "";

// Apply filters if they exist
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

if (isset($_GET['condition']) && !empty($_GET['condition'])) {
    $query .= " AND `condition` = ?";
    $params[] = $_GET['condition'];
    $types .= "s";
}

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $query .= " AND price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $query .= " AND price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

if (isset($_GET['negotiable']) && $_GET['negotiable'] == 'yes') {
    $query .= " AND negotiable = 'yes'";
}

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$textbooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Shelf</title>
    <link rel="stylesheet" href="camp.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
         #logo {
            width: 250px;
            height: auto;
        }
    </style>
</head>
<body>
    <section class="camp-header">
        <nav>
            <a href ="index.php"><img id="logo" src="IMAGES/Paper_Trade-removebg-preview (1).png"  ></a>
            <div class="nav-bar" id="navBar">
                <i class="fa fa-times" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="acc.php">MY ACC</a></li>
                    <li><a href="messages.php">MESSAGES</a></li>
                    <li><a href="#" id="openWishlistLink">WISHLIST</a></li>
                    <li><a href="#" id="openCartLink">CART</a></li>
                </ul>
            </div>
            <i class="fa fa-bars" onclick="showMenu()"></i>
        </nav> 
 
        <div class="intro-box">
            <h1>Welcome to Paper Trade</h1>
            <h2>Where Safe. Simple. Seamless. is our motto.</h2>
            <p>At Paper Trade, your safety and comfort come first. You'll never have to spend hundreds on Uber rides or leave the comfort of your home to meet strangers — we’ve got it all covered. Our integrated local courier system handles the entire exchange process, from pickup to delivery. We take care of the logistics so you can relax, save money, and focus on your studies.</p>
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

<div class="content-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Browse by</h2>
        <ul>
            <li><a href="barter_buddy.php">Barter Buddy</a></li>
            <li><a href="my_library.php">My Library</a></li>
            <li><a  href="messages.php">Messages</a></li>
            <li><a href="logout.php">Sign Out</a></li>
        </ul>

        <h2>Filter by</h2>
        <form id="filterForm" method="GET">
            <div class="filter-section">
                <h3>Price Range <span>+</span></h3>
                <div class="price-range">
                    <input type="range" min="50" max="10000" value="<?= isset($_GET['max_price']) ? $_GET['max_price'] : '10000' ?>" step="50" class="price-slider" id="priceRange" name="max_price">
                    <div class="price-values">
                        <span id="minValue">R50</span> - <span id="priceValue">R<?= isset($_GET['max_price']) ? $_GET['max_price'] : '10000' ?></span>
                    </div>
                    <input type="hidden" name="min_price" value="50">
                </div>
            </div>

            <div class="filter-section">
                <h3>Courses <span>+</span></h3>
                <div class="filter-dropdown">
                    <select name="category">
                        <option value="">All Courses</option>
                        <option value="Humanities" <?= (isset($_GET['category']) && $_GET['category'] == 'Humanities') ? 'selected' : '' ?>>Humanities</option>
                        <option value="Engineering and the built environment" <?= (isset($_GET['category']) && $_GET['category'] == 'Engineering and the built environment') ? 'selected' : '' ?>>Engineering and The Built Environment</option>
                        <option value="Health science" <?= (isset($_GET['category']) && $_GET['category'] == 'Health science') ? 'selected' : '' ?>>Health Science</option>
                        <option value="Commerce" <?= (isset($_GET['category']) && $_GET['category'] == 'Commerce') ? 'selected' : '' ?>>Commerce</option>
                        <option value="Law" <?= (isset($_GET['category']) && $_GET['category'] == 'Law') ? 'selected' : '' ?>>Law</option>
                        <option value="Science" <?= (isset($_GET['category']) && $_GET['category'] == 'Science') ? 'selected' : '' ?>>Science</option>
                        <option value="Education" <?= (isset($_GET['category']) && $_GET['category'] == 'Education') ? 'selected' : '' ?>>Education</option>
                    </select>
                </div>
            </div>


            <div class="filter-section">
                <h3>Condition <span>+</span></h3>
                <div class="filter-dropdown">
                    <select name="condition">
                        <option value="">All Conditions</option>
                        <option value="New" <?= (isset($_GET['condition']) && $_GET['condition'] == 'New') ? 'selected' : '' ?>>New</option>
                        <option value="Used - Like New" <?= (isset($_GET['condition']) && $_GET['condition'] == 'Used - Like New') ? 'selected' : '' ?>>Like New</option>
                        <option value="Used - Good" <?= (isset($_GET['condition']) && $_GET['condition'] == 'Used - Good') ? 'selected' : '' ?>>Good</option>
                        <option value="Used - Acceptable" <?= (isset($_GET['condition']) && $_GET['condition'] == 'Used - Acceptable') ? 'selected' : '' ?>>Acceptable</option>
                    </select>
                </div>
            </div>

            <div class="filter-section">
                <h3>Price Negotiable <span>+</span></h3>
                <div class="checkbox-filter">
                    <input type="checkbox" id="priceNegotiable" name="negotiable" value="yes" <?= (isset($_GET['negotiable']) && $_GET['negotiable'] == 'yes') ? 'checked' : '' ?>>
                    <label for="priceNegotiable">Yes</label>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="apply-filters">Apply Filters</button>
                <button type="button" class="reset-filters">Reset</button>
            </div>
        </form>
    </aside>
 
    <main class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.html">Home</a>
            <span>></span>
            <a href="#">Campus Shelf</a>
        </div>
 
        <div class="banner">
            <div class="banner-content">
                <h1>Campus Shelf</h1>
                <p>Looking for textbooks without breaking the bank? You’re in the right place. From popular titles to hidden gems, we’ve got something for every course and budget. Just scroll, filter by what you need, and we’ll handle the rest—including pickup and delivery. No awkward meetups, no stress—just smooth, safe trades.</p>
            </div>
        </div>
 
        <div class="products-header">
            <div class="sort-by">
                <span>Sort by:</span>
                <select>
                    <option>Recommended</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Newest</option>
                </select>
            </div>
        </div>

         <div class="search-bar">
    <input type="text" placeholder="Search..." class="search-input">
    <button class="search-button">Search</button>
  </div>
 
        <!-- Product Grid -->
        <div class="product-grid">
        
            <!---------Product 1-------->
            <?php if(empty($textbooks)): ?>
                <p>No textbooks found in your library.</p>
            <?php else: ?>
                <?php foreach($textbooks as $book): ?>
            <div class="product-card" data-id="<?= $book['id'] ?>">

                <div class="product-image">
                    <img src="assets/images/Categories/<?= htmlspecialchars($book['book_image']) ?>" alt="<?= htmlspecialchars($book['book_title']) ?>">
                    <a href="#" class="wishlist-icon" data-id="<?= $book['id'] ?>"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($book['book_title']) ?></h3>
                    <p>R<?= number_format($book['price'], 2) ?></p>
                    <a href="#" class="cart-icon" 
                    data-id="<?= $book['id'] ?>"
                    data-title="<?= htmlspecialchars($book['book_title']) ?>"
                    data-price="<?= number_format($book['price'], 2) ?>"
                    data-image="assets/images/Categories/<?= htmlspecialchars($book['book_image']) ?>">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i></a>
                    <!-- In your product card loop -->
                    <button class="message-seller-btn" 
                            onclick="window.location.href='campus_shelf.php?message_seller=<?= $book['id'] ?>'">
                        💬 Message Seller
                    </button>                  
                </div>
   
            </div>
 
             <?php endforeach; ?>
            <?php endif; ?>

    </main>
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
 
    // Filter sections toggle
    document.addEventListener('DOMContentLoaded', function() {
        const filterHeadings = document.querySelectorAll('.filter-section h3');
       
        filterHeadings.forEach(heading => {
            heading.addEventListener('click', function() {
                // Find the next sibling which contains the filter content
                const filterContent = this.nextElementSibling;
               
                if (filterContent.style.display === 'none') {
                    filterContent.style.display = 'block';
                    this.querySelector('span').textContent = '-';
                } else {
                    filterContent.style.display = 'none';
                    this.querySelector('span').textContent = '+';
                }
            });
           
            
            const filterContent = heading.nextElementSibling;
            filterContent.style.display = 'none';
        });
 
        // Price range slider functionality
        const priceSlider = document.getElementById('priceRange');
        const priceValue = document.getElementById('priceValue');
       
        if (priceSlider && priceValue) {
            priceSlider.addEventListener('input', function() {
                // Update displayed price value
                priceValue.textContent = 'R' + this.value;
            });
        }
    });
</script>


<div id="cartSidebar" class="cart-sidebar">
  <div class="cart-header">
    <span>Cart</span>
    <button id="closeCart">&times;</button>
  </div>
  <div id="cartContent" class="cart-content">
    <p class="empty-cart">Your cart is empty</p>
  </div>
  <div class="cart-footer">
    <div class="cart-total">Total: R<span id="cartTotal">0.00</span></div>
    <button class="checkout-btn">Checkout</button>
  </div>
</div>

<div id="wishlistSidebar" class="wishlist-sidebar">
  <div class="wishlist-header">
    <span>Wishlist</span>
    <button id="closeWishlist">&times;</button>
  </div>
  <div id="wishlistContent" class="wishlist-content">
    <p class="empty-wishlist">Your wishlist is empty</p>
  </div>
</div>

<script>
  const cartSidebar = document.getElementById('cartSidebar');
  const closeCart = document.getElementById('closeCart');
  const cartContent = document.getElementById('cartContent');
  const cartTotal = document.getElementById('cartTotal');

  // Open cart from menu link
  document.getElementById('openCartLink').addEventListener('click', function(e) {
    e.preventDefault();
    updateCartUI();
    cartSidebar.classList.add('open');
  });

  // Close cart
  closeCart.addEventListener('click', () => {
    cartSidebar.classList.remove('open');
  });

  // Update the cart UI with database items
function updateCartUI() {
    fetch('server/get_cart.php')
        .then(response => response.json())
        .then(cartItems => {
            cartContent.innerHTML = '';
            
            if(cartItems.length === 0) {
                cartContent.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
                cartTotal.textContent = '0.00';
                return;
            }
            
            let total = 0;
            
            cartItems.forEach(item => {
                // Ensure price is treated as a number
                const price = parseFloat(item.price);
                total += price * item.quantity;
                
                const itemDiv = document.createElement('div');
                itemDiv.classList.add('cart-item');
                itemDiv.innerHTML = `
                    <img src="assets/images/Categories/${item.book_image}" alt="${item.book_title}">
                    <div class="cart-item-details">
                        <div class="cart-item-title">${item.book_title}</div>
                        <div class="cart-item-seller">Seller: ${item.seller_name}</div>
                        <div class="cart-item-price">R${price.toFixed(2)}</div>
                        <div class="quantity-controls">
                            <button class="decrease" data-id="${item.textbook_id}">-</button>
                            <span>${item.quantity}</span>
                            <button class="increase" data-id="${item.textbook_id}">+</button>
                        </div>
                    </div>
                    <button class="remove-btn" data-id="${item.textbook_id}">&times;</button>
                `;
                
                cartContent.appendChild(itemDiv);
            });
            
            cartTotal.textContent = total.toFixed(2);
              
              // Add event listeners
              document.querySelectorAll('.remove-btn').forEach(btn => {
                  btn.addEventListener('click', (e) => {
                      e.stopPropagation();
                      removeCartItem(btn.dataset.id);
                  });
              });
              
              document.querySelectorAll('.increase').forEach(btn => {
                  btn.addEventListener('click', (e) => {
                      e.stopPropagation();
                      updateCartItemQuantity(btn.dataset.id, 1); // Increase by 1
                  });
              });
              
              document.querySelectorAll('.decrease').forEach(btn => {
                  btn.addEventListener('click', (e) => {
                      e.stopPropagation();
                      updateCartItemQuantity(btn.dataset.id, -1); // Decrease by 1
                  });
              });

              document.querySelector('.checkout-btn').addEventListener('click', function(e) {
                  e.preventDefault();
                window.location.href = 'checkout.php';
              });              
          });
  }

  // Function to add item to cart
  function addToCart(textbookId) {
      fetch('server/add_to_cart.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ textbook_id: textbookId })
      })
      .then(response => response.json())
      .then(data => {
          if(data.success) {
              updateCartUI();
              cartSidebar.classList.add('open');
          } else {
              alert(data.message || 'Error adding to cart');
          }
      });
  }

  // Function to remove item from cart
  function removeCartItem(textbookId) {
      fetch('server/remove_from_cart.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ textbook_id: textbookId })
      })
      .then(response => response.json())
      .then(data => {
          if(data.success) {
              updateCartUI();
          } else {
              alert(data.message || 'Error removing item');
          }
      });
  }

  // Function to update item quantity
  function updateCartItemQuantity(textbookId, change) {
      fetch('server/update_cart_quantity.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ 
              textbook_id: textbookId,
              change: change
          })
      })
      .then(response => response.json())
      .then(data => {
          if(data.success) {
              updateCartUI();
          } else {
              alert(data.message || 'Error updating quantity');
          }
      });
  }

  // Initialize cart on page load
  document.addEventListener('DOMContentLoaded', () => {
      updateCartUI();
      
      // Update cart icons to use database functions
      document.querySelectorAll('.cart-icon').forEach(icon => {
          icon.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              const textbookId = icon.dataset.id;
              addToCart(textbookId);
          });
      });
  });
</script>

<script>
  function getCurrentUserId() {
    return "buyer123"; // Replace with real session user ID
  }

  function messageSeller(bookId, sellerId) {
    const buyerId = getCurrentUserId();

    fetch('/api/conversations/initiate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        buyer_id: buyerId,
        seller_id: sellerId,
        book_id: bookId
      })
    })
    .then(res => res.json())
    .then(data => {
      window.location.href = `/chat.php?conversationId=${data.conversation_id}`;
    })
    .catch(err => {
      alert("Something went wrong.");
      console.error(err);
    });
  }
</script>

<script>
  const wishlistSidebar = document.getElementById("wishlistSidebar");
  const wishlistContent = document.getElementById("wishlistContent");
  const openWishlistLink = document.getElementById("openWishlistLink");
  const closeWishlistBtn = document.getElementById("closeWishlist");

  openWishlistLink.addEventListener("click", (e) => {
    e.preventDefault();
    wishlistSidebar.classList.add("active");
    fetchWishlistItems();
  });

  closeWishlistBtn.addEventListener("click", () => {
    wishlistSidebar.classList.remove("active");
  });

  // Fetch wishlist items from server
  function fetchWishlistItems() {
    fetch('server/get_wishlist.php')
      .then(response => response.json())
      .then(wishlistItems => {
        renderWishlist(wishlistItems);
      })
      .catch(error => {
        console.error('Error fetching wishlist:', error);
      });
  }

  // Render wishlist items
  function renderWishlist(wishlistItems) {
    wishlistContent.innerHTML = "";

    if (!wishlistItems || wishlistItems.length === 0) {
      wishlistContent.innerHTML = "<p class='empty-wishlist'>Your wishlist is empty</p>";
      return;
    }

    wishlistItems.forEach(item => {
      const div = document.createElement("div");
      div.className = "cart-item";
      div.innerHTML = `
        <div class="item-details">
          <img src="assets/images/Categories/${item.book_image}" alt="${item.book_title}" style="width: 50px; height: 50px; object-fit: cover;">
          <div>
            <h4>${item.book_title}</h4>
            <p>R${item.price.toFixed(2)}</p>
            <button class="add-to-cart-btn" data-id="${item.book_id}">🛒 Add to Cart</button>
          </div>
        </div>
      `;
      wishlistContent.appendChild(div);
    });

    // Add event listeners to all Add to Cart buttons in wishlist
    document.querySelectorAll('#wishlistContent .add-to-cart-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const textbookId = this.dataset.id;
        addToCart(textbookId);
      });
    });
  }

  // Update wishlist icon click handler
  document.querySelectorAll('.wishlist-icon').forEach(icon => {
    icon.addEventListener('click', function(e) {
      e.preventDefault();
      const bookId = this.dataset.id;
      
      fetch('server/wishlist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'toggle',
          book_id: bookId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const iconElement = this.querySelector('i');
          iconElement.className = data.action === 'added' ? 'fa fa-heart' : 'fa fa-heart-o';
          alert(data.message);
        } else {
          alert(data.message || 'Error updating wishlist');
        }
      });
    });
  });
</script>

<script>
// Function to create and display the product popup
function showProductPopup(productId) {
    // Find the product data from your textbooks array
    const product = <?php echo json_encode($textbooks); ?>.find(p => p.id == productId);
    
    if (!product) return;
    
    // Create popup container
    const popup = document.createElement('div');
    popup.className = 'product-popup';
    popup.innerHTML = `
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <button class="close-popup">&times;</button>
            
            <div class="product-popup-grid">
                <div class="product-popup-image">
                    <img src="assets/images/Categories/${product.book_image}" alt="${product.book_title}">
                </div>
                
                <div class="product-popup-details">
                    <h2>${product.book_title}</h2>
                    <div class="product-price">R${parseFloat(product.price).toFixed(2)}</div>
                    
                    <div class="product-meta">
                        <div><strong>Author:</strong> ${product.book_author || 'Not specified'}</div>
                        <div><strong>Edition:</strong> ${product.edition || 'Not specified'}</div>
                        <div><strong>Condition:</strong> ${product.condition || 'Not specified'}</div>
                        <div><strong>Category:</strong> ${product.category || 'Not specified'}</div>
                        <div><strong>Description:</strong> ${product.description || 'No description available'}</div>
                    </div>
                    
                    <div class="product-popup-actions">
                        <button class="buy-now-btn">Buy Now</button>
                        <button class="add-to-cart-btn" data-id="${product.id}">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="add-to-wishlist-btn" data-id="${product.id}">
                            <i class="fa fa-heart-o"></i> Add to Favorites
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(popup);
    document.body.style.overflow = 'hidden'; // Prevent scrolling
    
    // Close button functionality
    popup.querySelector('.close-popup').addEventListener('click', () => {
        document.body.removeChild(popup);
        document.body.style.overflow = '';
    });
    
    // Overlay click to close
    popup.querySelector('.popup-overlay').addEventListener('click', () => {
        document.body.removeChild(popup);
        document.body.style.overflow = '';
    });
    
    // Add to cart functionality
    popup.querySelector('.add-to-cart-btn').addEventListener('click', function() {
        const bookId = this.dataset.id;
        addToCart(bookId);
        document.body.removeChild(popup);
        document.body.style.overflow = '';
    });

    // Buy now functionality
    popup.querySelector('.buy-now-btn').addEventListener('click', function() {
        const bookId = product.id;
        
        // First clear the cart
        fetch('server/clear_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(() => {
            // Then add this single item
            addToCart(bookId);
            document.body.removeChild(popup);
            document.body.style.overflow = '';
        });
    });
    
    // Add to wishlist functionality
    popup.querySelector('.add-to-wishlist-btn').addEventListener('click', function() {
        const bookId = this.dataset.id;
        const title = product.book_title;
        const price = product.price;
        const image = `assets/images/Categories/${product.book_image}`;
        
        // Add to wishlist logic (use your existing wishlist implementation)
        if (!wishlist.some(item => item.id === bookId)) {
            wishlist.push({ id: bookId, title, price, image });
            alert(`Added "${title}" to wishlist.`);
        } else {
            alert(`"${title}" is already in wishlist.`);
        }
    });
    
    // Buy now functionality
    popup.querySelector('.buy-now-btn').addEventListener('click', function() {
        const bookId = product.id;
        const title = product.book_title;
        const price = product.price;
        const image = `assets/images/Categories/${product.book_image}`;
        
        // Clear cart and add this single item
        cartItems = [{ bookId, title, price, image, quantity: 1 }];
        updateCartUI();
        
        // Open cart sidebar and proceed to checkout
        cartSidebar.classList.add('open');
    });
}

// Add click event to all product cards
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on wishlist icon or cart icon
            if (e.target.closest('.wishlist-icon') || e.target.closest('.cart-icon') || e.target.closest('.message-seller-btn')) {
                return;
            }
            
            const productId = this.dataset.id;
            showProductPopup(productId);
        });
    });
});
</script>

</body>
</html>