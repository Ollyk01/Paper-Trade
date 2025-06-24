<?php
session_start();
include('server/connection.php');

// Destroy the session
session_destroy();
// ===== END SESSION DESTRUCTION ===== //

// Redirect to login page
header('Location: login.php');
exit;
?>