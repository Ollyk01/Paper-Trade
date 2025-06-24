<?php
session_start();
include('server/connection.php');

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

// Get user details from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    die("User not found");
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Acc</title>
    <link rel="stylesheet" href="acc.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <style>
         #logo {
            width: 250px;
            height: auto;
        }
    </style>
</head>
<body>
     <section class="acc-header">
        <nav>
            <a href ="index.php"><img id="logo" src="IMAGES/Paper_Trade-removebg-preview (1).png"  ></a>
            <div class="nav-bar" id="navBar">
                <i class="fa fa-times" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="messages.php">MESSAGES</a></li>
                    <li><a href="#" id="openWishlistLink">WISHLIST</a></li>
                    <li><a href="#" id="openCartLink">CART</a></li>
                </ul>
            </div>
            <i class="fa fa-bars" onclick="showMenu()"></i>
        </nav>
 
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
    </section>

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

  let cartItems = [];

  // Open cart from menu link
  document.getElementById('openCartLink').addEventListener('click', function(e) {
    e.preventDefault();
    cartSidebar.classList.add('open');
  });

  // Close cart
  closeCart.addEventListener('click', () => {
    cartSidebar.classList.remove('open');
  });

  // Add items from buttons
  document.querySelectorAll('.cart-icon').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();

      const bookId = btn.dataset.bookId;
      const title = btn.dataset.title;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;

      const existingItem = cartItems.find(item => item.bookId === bookId);
      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        cartItems.push({ bookId, title, price, image, quantity: 1 });
      }

      updateCartUI();
      cartSidebar.classList.add('open');
    });
  });

  // Update the cart UI with database items
    function updateCartUI() {
        fetch('server/get_cart.php')
            .then(response => response.json())
            .then(cartItems => {
                const cartContent = document.getElementById('cartContent');
                const cartTotal = document.getElementById('cartTotal');
                
                cartContent.innerHTML = '';
                
                if(cartItems.length === 0) {
                    cartContent.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
                    cartTotal.textContent = '0.00';
                    return;
                }
                
                let total = 0;
                
                cartItems.forEach(item => {
                    // Convert price to number if it's a string
                    const price = typeof item.price === 'string' ? 
                        parseFloat(item.price.replace(/[^0-9.-]/g, '')) : 
                        Number(item.price);
                    
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
  function addToCart(bookId, title, price, image) {
    if (!cart.some(item => item.id === bookId)) {
      cart.push({ id: bookId, title, price, image });
      alert(`Added "${title}" to cart from wishlist.`);
    } else {
      alert(`"${title}" is already in your cart.`);
    }
  }

  document.querySelectorAll('.wishlist-icon').forEach(icon => {
    icon.addEventListener('click', function(e) {
      e.preventDefault();
      const bookId = this.getAttribute('data-book-id');
      const productCard = this.closest('.product-card');
      const title = productCard.querySelector('h3').innerText;
      const price = productCard.querySelector('p').innerText.replace('R', '');
      const image = productCard.querySelector('img').getAttribute('src');

      addToWishlist(bookId, title, price, image);
    });
  });
</script>

 <!-- Welcome Section -->
    <div class="welcome-section">
        <h1>Account Dashboard</h1>
        <p>Welcome to your account dashboard. Here you can manage your profile, orders, payments, and more.</p>
    </div>

    <!-- Main Dashboard -->
    <div class="dashboard-container">
       
        <div class="sidebar">
            <div class="profile-section">
                <h3 class="profile-title">Profile</h3>
                
                <a href="#" class="menu-item" data-section="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile details</span>
                </a>
                
                <a href="#" class="menu-item" data-section="orders">
                    <i class="fas fa-box"></i>
                    <span>Orders</span>
                </a>
                
                <a href="#" class="menu-item" data-section="wishlist">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>

                <a href="#" class="menu-item" data-section="payments">
                    <i class="fas fa-wallet"></i>
                    <span>Payments</span> 
                </a>
                
                <a href="#" class="menu-item" data-section="preferences">
                    <i class="fas fa-bell"></i>
                    <span>Preferences & Settings</span>
                </a>
                
                <a href="#" class="menu-item" data-section="support">
                    <i class="fas fa-credit-card"></i>
                    <span>Support</span>
                </a>
                
                <a href="index.html" class="menu-item logout-item" data-section="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Profile Details Section -->
            <div id="profile" class="content-section active">
                <div class="content-header">
                    <h2>Profile details</h2>
                </div>
                
                <form class="profile-form">

                    <div class="form-group">
                        <label for="name">Username</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($user['username']); ?>" />
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" value="" placeholder="Name" />
                    </div>
                    
                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" id="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" />
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly />
                    </div>

                    <div class="form-group">
                        <label for="student_number">Student Number</label>
                        <input type="text" id="student_number" value="<?php echo htmlspecialchars($user['student_number']); ?>" />
                    </div>                    
                    
                    <div class="form-group">
                        <label for="mobile">Mobile number</label>
                        <input type="tel" id="mobile" value="" placeholder="Phone number"/>
                    </div>


                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" value="" placeholder="Address"/>
                    </div>

                    <div class="form-group">
                        <label for="password">Change Password</label>
                        <input type="password" id="password" placeholder="Enter new password" />
                    </div>
                    
                    <button type="button" class="save-btn" onclick="updateProfile()">Save Changes</button>
                </form>

                <div class="delete-section">
                    <h3>Delete my online profile</h3>
                    <button class="delete-btn">Delete Profile</button>
                    <p>You can request any time for your online profile and related data like your order history to be deleted.</p>
                    <div class="contact-info">
                        <i class="fas fa-envelope"></i>
                        <span>customerservices@tfg.co.za</span>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-phone"></i>
                        <span>Contact us</span>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <div class="content-header">
                    <div style="display: flex; align-items: center; margin-bottom: 2rem;">
                        <i class="fas fa-arrow-left" style="margin-right: 1rem; cursor: pointer;" onclick="showDashboard()"></i>
                        <h2>Orders</h2>
                    </div>
                </div>
                
                <div class="order-item">
                    <div class="order-details">
                        <img src="IMAGES/economics-textbook.jpg" alt="Product" class="order-image">
                        <div class="order-info">
                            <h4>BT1454488-01</h4>
                        </div>
                    </div>
                    <div class="order-meta">
                        <div class="date">23 March 2025</div>
                        <div class="price">R450.00</div>
                    </div>
                </div>

                <div class="pagination">
                    <span>Page 1 of 1</span>
                </div>
            </div>

            <!-- Other Sections -->
            <div id="wishlist" class="content-section">
                <div class="content-header">
                    <h2>Wishlist</h2>
                </div>
                <p>Your saved items will appear here.</p>
            </div>

            <div id="preferences" class="content-section">
                <div class="content-header">
                    <h2>Preferences</h2>
                </div>
                <p>Manage your notification settings and preferences here.</p>
            </div>

            <div id="payment" class="content-section">
                <div class="content-header">
                    <h2>Payments</h2>
                </div>
                <p>Manage your payment methods and account details here.</p>
            </div>

            <div id="logout" class="content-section">
                <div class="content-header">
                    <h2>Logout</h2>
                </div>
                <p>Are you sure you want to logout?</p>
                <button class="save-btn" style="width: auto; padding: 0.75rem 2rem;">Confirm Logout</button>
            </div>
        </div>
    </div>

<!---------JavaScript to handle menu toggling and content loading--------->

<script>
        // Navigation menu toggle
        function showMenu() {
            document.getElementById("navBar").style.right = "0";
        }

        function hideMenu() {
            document.getElementById("navBar").style.right = "-200px";
        }
     
        // Dashboard functionality
        function showSection(sectionId) {
          
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            document.getElementById(sectionId).classList.add('active');
            
            document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
            
            const welcomeTitle = document.getElementById('welcomeTitle');
            const welcomeText = document.getElementById('welcomeText');
            
            if (sectionId === 'profile') {
                welcomeTitle.textContent = 'Hi, Sesethu!';
                welcomeText.textContent = '';
            } else {
                welcomeTitle.textContent = 'Account Dashboard';
                welcomeText.textContent = 'Welcome to your account dashboard. Here you can manage your profile, orders, payments, and more.';
            }
        }

        function showDashboard() {
            showSection('profile');
        }

        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');
                
                if (section === 'logout') {
                    if (confirm('Are you sure you want to logout?')) {
                        
                        alert('Logged out successfully!');
                        
                    }
                } else {
                    showSection(section);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            showSection('profile');
        });

</script>

<script>
    function updateProfile() {
    const formData = {
        username: document.getElementById('name').value,
        surname: document.getElementById('surname').value,
        student_number: document.getElementById('student_number').value,
        password: document.getElementById('password').value
    };

    fetch('server/update_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Profile updated successfully!');
        } else {
            alert(data.message || 'Error updating profile');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update profile');
    });
}
</script>
</body>
</html>