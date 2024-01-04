<?php
session_start();
require_once('includes/db.php');

// Get form data
if (isset($_POST['username']) && isset($_POST['password'])) {
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare SQL statement
$sql = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $sql);

// Check if there is a match in the database
if (mysqli_num_rows($result) == 1) {
  // Set session variables
  $_SESSION['authenticated'] = true;
  $_SESSION['username'] = $username;

  // Redirect to index
  header("Location: dashboard.php");
  exit;
} else {
  $_SESSION['error'] = "Incorrect username or password.";
  header("Location: pages-login.php");
  exit;
}

} else {
  // Redirect back to login page
  header("Location: pages-login.php");
  exit;
}

?>
