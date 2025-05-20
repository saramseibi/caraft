<?php

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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $fullName = $conn->real_escape_string($_POST['fullName']);
    $email = $conn->real_escape_string($_POST['signupEmail']);
    $password = $_POST['signupPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: index.php");
        exit;
    }
    
    
    if (strlen($password) < 6) {
        $_SESSION['signup_error'] = "Password must be at least 6 characters.";
        header("Location: index.php");
        exit;
    }
    
    
    if ($password !== $confirmPassword) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: index.php");
        exit;
    }
    
  
    $check_email = "SELECT id FROM db WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        $_SESSION['signup_error'] = "Email already exists. Please use a different email.";
        header("Location: index.php");
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // add new user
    $sql = "INSERT INTO db (full_name, email, password) VALUES ('$fullName', '$email', '$hashed_password')";
    
    if ($conn->query($sql) === TRUE) {
       
        $_SESSION['signup_success'] = "Account created successfully! You can now log in.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['signup_error'] = "Error: " . $sql . "<br>" . $conn->error;
        header("Location: index.php");
        exit;
    }
    
   
    $conn->close();
} else {
    
    header("Location: index.php");
    exit;
}
?>