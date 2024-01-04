<?php

// Define database connection constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'inventory_dw');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check if connection was successful
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

?>
