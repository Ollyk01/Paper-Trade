<?php
session_start();
include('server/connection.php');

$login_errors = [];


// Login Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_btn'])) {
    $student_number = $_POST['student_number'];
    $password = md5($_POST['password']);

    // Modified query to include id (user_id)
    $stmt = $conn->prepare("SELECT id, student_number, username, email, password, roles FROM users WHERE student_number=? AND password=? LIMIT 1");
    $stmt->bind_param('ss', $student_number, $password);

    if($stmt->execute()){
        $stmt->bind_result($user_id, $student_number, $username, $email, $password, $role);
        $stmt->store_result();

        if($stmt->num_rows() != 1){
            $login_errors[] = 'Invalid student number or password';
        }      

        if($stmt->num_rows() == 1){
            $stmt->fetch();
            
            // Set all session variables including user_id
            $_SESSION['user_id'] = $user_id;
            $_SESSION['student_number'] = $student_number;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = $role;

            if ($role === 'admin') {
                header('Location: admin/dashboard.php?message=Admin login successful');
            } else {
                header('Location: index.php?message=Logged in successfully');
            }
            exit;
        }
    }


}
elseif (isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;

}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h2>Student Login</h2>
  <div class="login-form">
  <?php if (!empty($login_errors)): ?>
      <div class="alert alert-danger">
          <?php foreach ($login_errors as $error): ?>
              <div><?php echo htmlspecialchars($error); ?></div>
          <?php endforeach; ?>
      </div>
  <?php endif; ?> 
  <form action="" method="post">
    <label>STUDENT NUMBER:</label><br>
    <input type="text" name="student_number" required><br><br>

    <label>PASSWORD:</label><br>
    <input type="password" name="password" required><br><br>

    <input class="signup-button" type="submit" value="Login" name="login_btn">

  </div>
  </form>

  <p>Don't have an account? <a href="signup.php">Register here</a></p>


</body>
</html>