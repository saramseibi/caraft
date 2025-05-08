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

// Function to get user's ID if logged in
function getCurrentUserId() {
    if (isLoggedIn() && isset($_SESSION["id"])) {
        return $_SESSION["id"];
    }
    return null;
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

// Function to require login (redirect if not logged in)
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please log in to continue";
        header("Location: login.php");
        exit();
    }
}

// Function to display error/success messages
function displayMessage() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
}