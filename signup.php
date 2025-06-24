<?php
 

session_start();
include('server/connection.php');

// At the top of your file, initialize both error arrays:
$login_errors = [];
$register_errors = [];

 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Register'])) {

  $student_number = $_POST['student_number'];
  $username = $_POST['username'];
  $surname = $_POST['surname'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $password2 = $_POST['password2'];

  // Check if passwords don't match
  if ($password !== $password2) {
    $register_errors[] = 'Passwords do not match';

  // Check password length
  } elseif (strlen($password) < 6) {
    $register_errors[] = 'Password must be at least 6 characters';

  } else {
    $stmt1 = $conn->prepare("SELECT count(*) FROM users WHERE email=?");

    if (!$stmt1) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt1->bind_param('s', $email);
    $stmt1->execute();
    $stmt1->bind_result($num_rows);
    $stmt1->store_result();
    $stmt1->fetch();

    if ($num_rows != 0) {
      $register_errors[] = 'A user with this email already exists';

    } else {
      // Register new user
      $stmt = $conn->prepare("INSERT INTO users (student_number, username, surname, email, password, roles) VALUES (?, ?, ?, ?, ?, 'student')");

      if (!$stmt) {
          die("Prepare failed: " . $conn->error);
      }

      $stmt->bind_param('sssss', $student_number, $username, $surname, $email, md5($password));


      if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['student_number']= $student_number;
        $_SESSION['username'] = $username;
        $_SESSION['surname'] = $surname;
        $_SESSION['email'] = $email;
        $_SESSION['logged_in'] = true;
 
        $success = 'You are successfully registered.';
 


        header('Location: index.php?success=' . urlencode($success));
        exit;
      } else {
        $register_errors[] = 'Could not create account at the moment';
      }
    }
  }

  
}elseif(isset($_SESSION['logged_in'])){
  
  header('Location: index.php');
  exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Sign Up</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <h2>Welcome!</h2>
  <p>Please fill in the form to create an account</p>
  <?php if (!empty($register_errors)): ?>
                  <div class="alert alert-danger">
                      <?php foreach ($register_errors as $error): ?>
                          <div><?php echo htmlspecialchars($error); ?></div>
                      <?php endforeach; ?>
                  </div>
              <?php endif; ?>

  <form class="signup-form" action="signup.php" method="POST">
    <input type="text" name="student_number" placeholder="STUDENT NUMBER" required>
    <input type="text" name="username" placeholder="USERNAME" required>
    <input type="text" name="surname" placeholder="SURNAME" required>
    <input type="email" name="email" placeholder="EMAIL" required>
    <input type="password" name="password" placeholder="PASSWORD" required>
    <input type="password" name="password2" placeholder="CORNFIRM PASSWORD" required>

    <button class="signup-button" type="submit" name="Register">SIGN UP</button>

  </form>

  <p>Already have an account? <a href="login.php">Log in here</a></p>
  <p>By signing up, you agree to our <a href="terms.html">Terms of Service</a> and <a href="privacy.html">Privacy Policy</a>.</p>
  <p><a href="forgot-password.html" class="forgot-link">Forgot your password?</a></p>
</body>
</html>
