<?php
// Initialize the session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'caraft'; 
$username = 'root';
$password = '';

try {
    global $conn; // Make $conn globally accessible
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to get user's name if logged in
function getUserName() {
    if (isLoggedIn() && isset($_SESSION["name"])) {
        return $_SESSION["name"];
    }
    return false;
}

// Function to get user's email if logged in
function getUserEmail() {
    if (isLoggedIn() && isset($_SESSION["email"])) {
        return $_SESSION["email"];
    }
    return false;
}
?>