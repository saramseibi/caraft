<?php
// Contact form processing with SMTP configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Define variables and set to empty values
$name = $phone = $email = $message = "";
$nameErr = $phoneErr = $emailErr = $messageErr = "";
$success = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed";
        }
    }
    
    // Validate phone
    if (empty($_POST["phone"])) {
        $phoneErr = "Phone number is required";
    } else {
        $phone = test_input($_POST["phone"]);
        // Check if phone number format is valid
        if (!preg_match("/^[0-9+ -]*$/", $phone)) {
            $phoneErr = "Invalid phone number format";
        }
    }
    
    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        // Check if email address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }
    
    // Validate message
    if (empty($_POST["message"])) {
        $messageErr = "Message is required";
    } else {
        $message = test_input($_POST["message"]);
    }
    
    // If no errors, process the form
    if (empty($nameErr) && empty($phoneErr) && empty($emailErr) && empty($messageErr)) {
        try {
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';         // SMTP server 
            $mail->SMTPAuth   = true;                     // Enable SMTP authentication
            $mail->Username   = 'sarramseibi2@gmail.com'; // SMTP username
            $mail->Password   = 'atgy onxw gtup airq';    // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port       = 587;                      // TCP port 
            
        
            $mail->setFrom('sarramseibi2@gmail.com', 'Contact Form');
            $mail->addAddress('sarramseibi2@gmail.com');   
            $mail->addReplyTo($email, $name);             
            
           
            $mail->isHTML(true);
            $mail->Subject = 'New Contact Form Submission';
            
            // Email body 
            $mail->Body = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br($message) . "</p>
            ";
            
            // Plain text version of email body
            $mail->AltBody = "
                New Contact Form Submission
                
                Name: {$name}
                Phone: {$phone}
                Email: {$email}
                Message:
                {$message}
            ";
            
            // Send email
            $mail->send();
            $success = "Thank you for contacting us. We will get back to you soon!";
            
            // Clear form fields 
            $name = $phone = $email = $message = "";
            
          
            header("Location: contact.php?success=1");
            exit();
            
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
          
            header("Location: contact.php?error=" . urlencode($error));
            exit();
        }
    } else {
        
        session_start();
        $_SESSION['form_errors'] = array(
            'nameErr' => $nameErr,
            'phoneErr' => $phoneErr,
            'emailErr' => $emailErr,
            'messageErr' => $messageErr,
            'form_data' => array(
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'message' => $message
            )
        );
        
        
        header("Location: contact.php?form_error=1");
        exit();
    }
}

// Function to sanitize input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>