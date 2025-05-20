<?php

require_once 'check.inc.php';

$db_id = 1;

// Fetch current user 
$stmt = $conn->prepare("SELECT * FROM db WHERE id = ?");
if ($stmt === false) {
    echo "SQL Error in prepare: " . $conn->error;
    exit;
}
$stmt->bind_param("i", $db_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        
        
        $update_query = "UPDATE db SET 
                        full_name = ?, 
                        email = ?";
        
        $params = [$full_name, $email];
        $param_types = "ss";
        
        $update_query .= " WHERE id = ?";
        $params[] = $db_id;
        $param_types .= "i";
        
        $stmt = $conn->prepare($update_query);
        if ($stmt === false) {
            throw new Exception("SQL Error: " . $conn->error);
        }
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        
        //  password change
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Get the stored hashed password
            $stmt = $conn->prepare("SELECT password FROM db WHERE id = ?");
            if ($stmt === false) {
                throw new Exception("SQL Error: " . $conn->error);
            }
            $stmt->bind_param("i", $db_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $pw_check = $result->fetch_assoc();
            $stored_hashed_password = $pw_check['password'];
            
            //  check if the entered password matches the stored hash
            if (password_verify($current_password, $stored_hashed_password)) {
                
                if ($new_password === $confirm_password) {
                    
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE db SET password = ? WHERE id = ?");
                    if ($stmt === false) {
                        throw new Exception("SQL Error: " . $conn->error);
                    }
                    $stmt->bind_param("si", $hashed_new_password, $db_id);
                    $stmt->execute();
                } else {
                    throw new Exception("New passwords do not match.");
                }
            } else {
                throw new Exception("Current password is incorrect.");
            }
        }
        
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .edit-wrapper {
            padding: 40px 0;
        }
        
        .edit-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #444;
        }
        
        .password-section {
            border-top: 2px solid #f1f1f1;
            margin-top: 30px;
            padding-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container edit-wrapper">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="edit-card">
                    <h2 class="text-center mb-4">Edit Profile</h2>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="password-section">
                            <h4 class="mb-4">Change Password</h4>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary me-3">Save Changes</button>
                            <a href="profile.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>