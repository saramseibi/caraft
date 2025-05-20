<?php
// store user 
session_start();
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "caraft";  

// Create con
$conn = new mysqli($servername, $username, $password, $dbname);

// Check con
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    //  check if user exists
    $sql = "SELECT id, full_name, email, password FROM db WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // User found, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['email'];
            
         
            header("Location: index.php");
            exit;
        } else {
            
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: index.php");
            exit;
        }
    } else {
        
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: index.php");
        exit;
    }
    
    
    $conn->close();
} else {
    
    header("Location: index.php");
    exit;
}
?>