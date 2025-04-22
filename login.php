<?php
// Start session to store user data
session_start();
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "caraft";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // SQL query to check if user exists
    $sql = "SELECT id, full_name, email, password FROM db WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // User found, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['email'];
            
            // Redirect to home page
            header("Location: index.php");
            exit;
        } else {
            // Password is incorrect
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: index.php");
            exit;
        }
    } else {
        // User not found
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: index.php");
        exit;
    }
    
    // Close connection
    $conn->close();
} else {
    // Not a POST request, redirect to home page
    header("Location: index.php");
    exit;
}
?>