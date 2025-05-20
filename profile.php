<?php
// Include the check.inc.php file which already starts a session and sets up database connection
require_once 'check.inc.php';


$db_id = 1;



$sql = "SELECT * FROM db WHERE id = " . $db_id;
$result = $conn->query($sql);
if (!$result) {
    echo "SQL Error: " . $conn->error . "<br>";
} else {
    
    
    
    $stmt = $conn->prepare("SELECT * FROM db WHERE id = ?");
    if ($stmt === false) {
       
    } else {
       
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
      
    }
}

//  placeholder values
if (!isset($user) || !$user) {
    echo "Using default placeholder values.<br>";
    $user = [
        'id' => 'xx',
        'full_name' => 'xx',
        'email' => 'xx',
        'password' => 'xx'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-wrapper {
            padding: 40px 0;
        }
        
        .title {
            color: #764ba2;
            font-weight: 700;
            margin-bottom: 40px;
        }
        
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .profile-nav-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 20px;
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }
        
        .profile-nav-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(5px);
            color: white;
        }
        
        .bio-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .bio-header {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
        }
        
        .bio-content {
            padding: 30px;
        }
        
        .bio-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }
        
        .info-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .info-label {
            font-weight: 600;
            color: #764ba2;
            width: 140px;
            display: flex;
            align-items: center;
        }
        
        .info-label i {
            margin-right: 10px;
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container profile-wrapper">
        <h2 class="text-center title">WELCOME!</h2>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card text-center">
                    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" alt="Profile" class="profile-img mb-4">
                    <h2 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                    <a href="edit.php" class="profile-nav-btn">
                        <i class="fas fa-edit me-2"></i> Edit Profile
                    </a>
                    <a href="index.php" class="profile-nav-btn">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
            <div class="col-md-8">
                <div class="bio-card">
                    <div class="bio-header">
                        Welcome to your profile page!
                    </div>
                    <div class="bio-content">
                        <h3 class="bio-title">User Information</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-user"></i> Full Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-id-card"></i> User ID</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['id']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-key"></i> Password</span>
                                    <span class="info-value">••••••••</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>