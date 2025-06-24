<?php
session_start();
include('server/connection.php');

// Check if user is logged in
if(!isset($_SESSION['logged_in'])) {
    header('location: index.php?error=Please login first');
    exit;
}

// Check if user has required role
$allowed_roles = ['seller', 'student', 'admin'];
if(!in_array($_SESSION['role'] ?? '', $allowed_roles)) {
    header('location: index.php?error=Unauthorized access');
    exit;
}


// Verify user_id exists in session
if(!isset($_SESSION['user_id'])) {
    die("Error: User authentication failed. Please login again.");
}

if(isset($_POST['title'])) {
    $name = $_POST['title'];
    $book_author = $_POST['book_author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $pickup_address = $_POST['pickup-add'];
    $user_id = $_SESSION['user_id'];
    $negotiable = $_SESSION['negotiable'];

    // Handle image upload
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $target_dir = "assets/images/Categories/";
    $target_file = $target_dir . basename($image);

    // Validate file upload
    if(!is_uploaded_file($image_tmp)) {
        header('location: my_library.php?error=No file uploaded');
        exit;
    }

    // Create target directory if it doesn't exist
    if(!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Move uploaded file
    // Add this right after session_start()
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

    // Replace your textbook insertion code with:
    if(move_uploaded_file($image_tmp, $target_file)) {
        // Verify user exists
        $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $check_user->bind_param('i', $user_id);
        $check_user->execute();
        $check_user->store_result();
        
        if($check_user->num_rows === 1) {
            $stmt = $conn->prepare("INSERT INTO textbooks 
                (book_title, description, book_image, price, category, user_id, `condition`, Pick_up_Address, book_author, negotiable)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Add a default author if not provided
            $author = $_POST['author'] ?? 'Unknown';
            
            $stmt->bind_param('sssdsssss', 
                $name, 
                $description, 
                $image, 
                $price, 
                $category, 
                $user_id, 
                $condition, 
                $pickup_address,
                $author,
                $negotiable,
            );
            
            if($stmt->execute()) {
                header('location: my_library.php?message=Textbook added successfully');
                exit;
            } else {
                error_log("Insert error: ".$stmt->error);
                header('location: my_library.php?error=Database error');
                exit;
            }
        } else {
            error_log("User not found: ".$user_id);
            header('location: my_library.php?error=User account not found');
            exit;
        }
    }
}

// Fetch books from database (add this after your existing PHP code but before HTML)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM textbooks WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$textbooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library</title>
    <link rel="stylesheet" href="library.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        #logo {
            width: 250px;
            height: auto;
        }
    </style>
</head>
<body>
    <section class="library-header">
        <nav>
            <a href ="index.php"><img id="logo" src="IMAGES/Paper_Trade-removebg-preview (1).png"></a>
            <div class="nav-bar" id="navBar">
                <i class="fa fa-times" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="acc.php">MY ACC</a></li>
                    <li><a href="campus_shelf.php">CAMPUS SHELF</a></li>
                    <li><a href="#">WISHLIST</a></li>
                    <li><a href="#">CART</a></li>
                </ul>
            </div>
            <i class="fa fa-bars" onclick="showMenu()"></i>
        </nav>
        </section>







<section class="mylibrary-header">
    <h3 class="library-title">My Library</h3>
    <div class="library-grid">
        <?php if(empty($textbooks)): ?>
            <p>No textbooks found in your library.</p>
        <?php else: ?>
            <?php foreach($textbooks as $book): ?>
                <div class="book-card" data-id="<?= $book['id'] ?>">
                    <div class="book-badge"><?= $book['condition'] ?></div>
                    <div class="book-image">
                        <img src="assets/images/Categories/<?= htmlspecialchars($book['book_image']) ?>" alt="<?= htmlspecialchars($book['book_title']) ?>">
                        <div class="book-overlay">
                            <button class="quick-view">Quick View</button>
                        </div>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?= htmlspecialchars($book['book_title']) ?></h3>
                        <div class="book-meta">
                            <span class="book-author">By <?= htmlspecialchars($book['book_author'] ?? 'Unknown') ?></span>
                            <div class="book-rating">
                                ★★★★☆ <span class="rating-count">(42)</span>
                            </div>
                        </div>
                        <div class="book-footer">
                            <p class="book-price">R<?= number_format($book['price'], 2) ?></p>
                            <button class="remove-button" onclick="deleteBook(<?= $book['id'] ?>)">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <button id="uploadBtn" class="upload-button">Upload</button>
</section>




<!-----------Toggle menu---------->
        <script>
    var navBar = document.getElementById("navBar");

    function showMenu() {
        navBar.style.right = "0";
    }

    function hideMenu() {
        navBar.style.right = "-200px";
    }

</script>



<!-- Popup Form -->
    <div id="uploadForm" class="popup-form">
        <form id="textbookForm" method="POST" enctype="multipart/form-data">
            <h2>Upload Textbook</h2>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>

            <label for="price">Price (R):</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label>Price Negotiable:</label>
            <div>
                <input type="radio" id="negotiableYes" name="negotiable" value="yes" required>
                <label for="negotiableYes">Yes</label>

                <input type="radio" id="negotiableNo" name="negotiable" value="no">
                <label for="negotiableNo">No</label>
            </div>


            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <label for="pickup-add">Pick-up Address:</label>
            <input type="text" id="pickup-add" name="pickup-add" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">All Courses</option>
                <option value="Humanities">Humanities</option>
                <option value="Engineering and the built environment">Engineering and The Built Environment</option>
                <option value="Health science">Health Science</option>
                <option value="Commerce">Commerce</option>
                <option value="Law">Law</option>
                <option value="Science">Science</option>
                <option value="Education">Education</option>
            </select>

            <label for="condition">Condition:</label>
            <select id="condition" name="condition" required>
                <option value="">Select Condition</option>
                <option value="New">New</option>
                <option value="Used - Like New">Used - Like New</option>
                <option value="Used - Good">Used - Good</option>
                <option value="Used - Acceptable">Used - Acceptable</option>
            </select> 
            <div class="form-buttons">
                <button type="submit">Submit</button>
                <button type="button" id="closeBtn">Cancel</button>
            </div>
        </form>
    </div>


    <script>
        document.getElementById("uploadBtn").addEventListener("click", () => {
            document.getElementById("uploadForm").style.display = "block";
        });

        document.getElementById("closeBtn").addEventListener("click", () => {
            document.getElementById("uploadForm").style.display = "none";
        });

        // The form will now submit via PHP, so we can simplify the JavaScript
        document.getElementById("textbookForm").addEventListener("submit", function(e) {
            // Client-side validation can go here if needed
            const selectedCategory = document.getElementById("category").value;
            if (!selectedCategory) {
                e.preventDefault();
                alert("Please select a course category.");
                return;
            }
        });
    </script>



<!-- <script>
document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".delete-button");

  deleteButtons.forEach(button => {
    button.addEventListener("click", function () {
      const bookCard = this.closest(".book-card");
      const bookId = bookCard.getAttribute("data-id");

      if (confirm("Are you sure you want to delete this book?")) {
        fetch("delete_book.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "id=" + encodeURIComponent(bookId)
        })
        .then(response => response.text())
        .then(result => {
          if (result.trim() === "success") {
            bookCard.remove();
            alert("Book successfully deleted.");
          } else {
            alert("Failed to delete book: " + result);
          }
        })
        .catch(error => {
          console.error("Error:", error);
          alert("An error occurred while trying to delete the book.");
        });
      }
    });
  });
});
</script> -->

<?php include('layout/footer.php');?>

    </body>
</html>