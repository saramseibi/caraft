<?php
// Include the check.inc.php file which already starts a session and sets up database connection
require_once 'check.inc.php';

// Make sure user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to view your orders";
    header("Location: index.php");
    exit();
}

$db_id = $_SESSION["id"] ?? 1; // Get user ID from session, or use 1 for testing

// Fetch user's orders with items
$orders = [];
try {
    // First get all orders - note that 'order' is a reserved keyword in SQL, so we need to use backticks
    $stmt = $conn->prepare("SELECT * FROM `order` WHERE db_id = ? ORDER BY order_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($order = $result->fetch_assoc()) {
            // For each order, get its items
            $items_stmt = $conn->prepare("
                SELECT oi.*, p.name, p.image 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            
            if ($items_stmt) {
                $items_stmt->bind_param("i", $order['id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                $order['items'] = [];
                while ($item = $items_result->fetch_assoc()) {
                    $order['items'][] = $item;
                }
                
                $orders[] = $order;
            }
        }
    }
} catch (Exception $e) {
    // Handle exception
    $_SESSION['error'] = "Error fetching orders: " . $e->getMessage();
    $orders = [];
}

// Fetch user's liked products
$liked_products = [];
try {
    $stmt = $conn->prepare("
        SELECT p.* FROM products p 
        JOIN liked_order lo ON p.id = lo.product_id 
        WHERE lo.db_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $liked_products[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching liked products: " . $e->getMessage();
    $liked_products = [];
}

// Get user information
$user_name = $_SESSION["full_name"] ?? "User";
$user_email = $_SESSION["email"] ?? "user@example.com";
$user_profile = $_SESSION["profile"] ?? "https://bootdey.com/img/Content/avatar/avatar3.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Favorites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .orders-wrapper {
            padding: 40px 0;
        }
        
        .title {
            color: #764ba2;
            font-weight: 700;
            margin-bottom: 40px;
        }
        
        .order-card, .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .order-card:hover, .product-card:hover {
            transform: translateY(-5px);
        }
        
        .order-header {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            padding: 15px 20px;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
        }
        
        .order-status.completed {
            background-color: #10b981;
        }
        
        .order-status.processing {
            background-color: #f59e0b;
        }
        
        .order-status.pending {
            background-color: #6366f1;
        }
        
        .order-status.cancelled {
            background-color: #ef4444;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        
        .product-body {
            padding: 20px;
        }
        
        .product-price {
            font-weight: 700;
            color: #764ba2;
        }
        
        .product-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .btn-like {
            color: #e11d48;
        }
        
        .btn-cart {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
        }
        
        .tab-content {
            padding-top: 30px;
        }
        
        .nav-tabs .nav-link {
            color: #6b7280;
            border: none;
            padding: 10px 20px;
            border-radius: 0;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: #764ba2;
            font-weight: 600;
            background-color: transparent;
        }
        
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-icon {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .sidebar {
            position: sticky;
            top: 20px;
        }
        
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.3);
            margin-bottom: 15px;
        }
        
        .nav-link-sidebar {
            display: block;
            padding: 15px;
            color: #4b5563;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-link-sidebar:hover, .nav-link-sidebar.active {
            background-color: #f3f4f6;
            color: #764ba2;
        }
        
        .nav-link-sidebar i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container orders-wrapper">
        <!-- Display any success or error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['info']); 
                    unset($_SESSION['info']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <div class="profile-card text-center">
                        <img src="<?php echo htmlspecialchars($user_profile); ?>" alt="Profile" class="profile-img">
                        <h5 class="mb-1"><?php echo htmlspecialchars($user_name); ?></h5>
                        <p class="mb-0 small"><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    
                    <nav class="nav flex-column mt-4">
                        <a href="profile.php" class="nav-link-sidebar">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="orders.php" class="nav-link-sidebar active">
                            <i class="fas fa-shopping-bag"></i> Orders & Favorites
                        </a>
                        <a href="settings.php" class="nav-link-sidebar">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="logout.php" class="nav-link-sidebar text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <h2 class="title">My Orders & Favorites</h2>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="ordersTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="true">
                            My Orders
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="favorites-tab" data-bs-toggle="tab" href="#favorites" role="tab" aria-controls="favorites" aria-selected="false">
                            Favorites
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="ordersTabContent">
                    <!-- Orders Tab -->
                    <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h4>No Orders Yet</h4>
                                <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                                <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Order #<?php echo htmlspecialchars($order['id']); ?></h5>
                                            <small>Placed on <?php echo date('M d, Y', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <span class="order-status <?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="order-body">
                                        <?php if (!empty($order['items'])): ?>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <div class="order-item d-flex justify-content-between">
                                                    <div>
                                                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <p class="text-muted mb-0">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                                    </div>
                                                    <div class="text-end">
                                                        <h6>$<?php echo htmlspecialchars(number_format($item['price_per_item'] * $item['quantity'], 2)); ?></h6>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="order-item">
                                                <p class="text-muted">No items found for this order.</p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3">
                                            <p class="text-end mb-3"><strong>Total: $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></strong></p>
                                            <div class="d-flex justify-content-between">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-secondary btn-sm">View Details</a>
                                                <?php if (strtolower($order['status']) == 'completed'): ?>
                                                    <form action="process_orders.php" method="post">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="buy_again" class="btn btn-outline-primary btn-sm">Buy Again</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Favorites Tab -->
                    <div class="tab-pane fade" id="favorites" role="tabpanel" aria-labelledby="favorites-tab">
                        <?php if (empty($liked_products)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h4>No Favorites Yet</h4>
                                <p class="text-muted">You haven't added any products to your favorites yet.</p>
                                <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($liked_products as $product): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="product-card">
                                            <img src="<?php echo htmlspecialchars($product['image'] ?? 'assets/images/placeholder.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-img"
                                                 onerror="this.src='assets/images/placeholder.jpg'">
                                            <div class="product-body">
                                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                                <p class="text-muted small">
                                                    <?php 
                                                        $desc = $product['description'] ?? '';
                                                        echo htmlspecialchars(substr($desc, 0, 60) . (strlen($desc) > 60 ? '...' : ''));
                                                    ?>
                                                </p>
                                                <p class="product-price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                                                <div class="product-actions">
                                                    <form action="process_wishlist.php" method="post" style="display: inline;">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" name="remove_from_wishlist" class="btn btn-like">
                                                            <i class="fas fa-heart"></i>
                                                        </button>
                                                    </form>
                                                    <form action="process_wishlist.php" method="post" style="display: inline;">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" name="add_to_cart" class="btn btn-cart">
                                                            <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>