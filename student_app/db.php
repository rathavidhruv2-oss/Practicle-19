<?php
// ============================================================
// db.php — Database Connection File
// Purpose : Opens a MySQLi connection and returns it.
//           Every other file includes this file.
// ============================================================

// Database credentials (change if yours differ)
define('DB_HOST', 'localhost');   // XAMPP MySQL host
define('DB_USER', 'root');        // Default XAMPP username
define('DB_PASS', '');            // Default XAMPP password (empty)
define('DB_NAME', 'student_db');  // Database we created in database.sql

// Create the MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection failed and stop execution with a clear message
if ($conn->connect_error) {
    die("<p style='color:red;font-family:sans-serif;'>
         ❌ Database Connection Failed: " . $conn->connect_error . "<br>
         Make sure XAMPP MySQL is running and you have run database.sql.
         </p>");
}

// Set the character encoding to UTF-8 to avoid encoding issues
$conn->set_charset("utf8mb4");
?>
