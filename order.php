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

// Process checkout form submission
if (isset($_POST['checkout'])) {
    // Validate form data
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Process Credit Card payment if selected
    if ($payment_method === 'Credit Card') {
        $cardholder_name = $_POST['cardholder_name'] ?? '';
        $card_number = $_POST['card_number'] ?? '';
        $expiry_date = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $save_card = isset($_POST['save_card']) ? 1 : 0;
        
        // Basic validation for card details
        if (empty($cardholder_name)) {
            $_SESSION['error'] = "Cardholder name is required";
        } elseif (empty($card_number)) {
            $_SESSION['error'] = "Card number is required";
        } elseif (empty($expiry_date) || !preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
            $_SESSION['error'] = "Valid expiry date is required (MM/YY)";
        } elseif (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $_SESSION['error'] = "Valid CVV is required";
        }
    }
    
    if (empty($shipping_address)) {
        $_SESSION['error'] = "Shipping address is required";
    } elseif (empty($payment_method)) {
        $_SESSION['error'] = "Payment method is required";
    } else {
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Get cart items
            $cart_stmt = $conn->prepare("
                SELECT c.*, p.name, p.price, (c.quantity * p.price) as total 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.db_id = ?
            ");
            
            $cart_stmt->bind_param("i", $db_id);
            $cart_stmt->execute();
            $cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (empty($cart_items)) {
                throw new Exception("Your cart is empty!");
            }
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['total'];
            }
            
            // Calculate tax and shipping
            $shipping = 5.00;
            $tax = round($subtotal * 0.08, 2); // 8% tax
            $total_amount = $subtotal + $shipping + $tax;
            
            // Create new order in the orders table
            $order_stmt = $conn->prepare("
                INSERT INTO `orders` (db_id, order_date, status, shipping_address, payment_method, total_amount, notes) 
                VALUES (?, CURRENT_TIMESTAMP, 'PENDING', ?, ?, ?, ?)
            ");
            
            $order_stmt->bind_param("issds", $db_id, $shipping_address, $payment_method, $total_amount, $notes);
            
            if (!$order_stmt->execute()) {
                throw new Exception("Failed to create order: " . $conn->error);
            }
            
            $order_id = $conn->insert_id; // Get the ID of the new order
            
            // If Credit Card was used and save_card is checked, store card info
            if ($payment_method === 'Credit Card' && isset($save_card) && $save_card == 1) {
                // Create payment_methods table if it doesn't exist
                $conn->query("
                    CREATE TABLE IF NOT EXISTS payment_methods (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        db_id INT(11) NOT NULL,
                        cardholder_name VARCHAR(255) NOT NULL,
                        card_number VARCHAR(255) NOT NULL,
                        expiry_date VARCHAR(10) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        FOREIGN KEY (db_id) REFERENCES users(id) ON DELETE CASCADE
                    )
                ");
                
                // Store card info (in a real system, this would be encrypted)
                $last_four = substr(str_replace(' ', '', $card_number), -4);
                $masked_number = '**** **** **** ' . $last_four;
                
                $save_card_stmt = $conn->prepare("
                    INSERT INTO payment_methods (db_id, cardholder_name, card_number, expiry_date) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $save_card_stmt->bind_param("isss", $db_id, $cardholder_name, $masked_number, $expiry_date);
                $save_card_stmt->execute();
            }
            
            // Insert items into orders_items (table already exists, no need to create)
            $items_stmt = $conn->prepare("
                INSERT INTO orders_items (orders_id, product_id, quantity, price_per_item) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                $items_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                if (!$items_stmt->execute()) {
                    throw new Exception("Failed to add item to order: " . $conn->error);
                }
            }
            
            // Clear cart after successful order
            $clear_cart_stmt = $conn->prepare("DELETE FROM cart WHERE db_id = ?");
            $clear_cart_stmt->bind_param("i", $db_id);
            
            if (!$clear_cart_stmt->execute()) {
                throw new Exception("Failed to clear cart: " . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Order #$order_id placed successfully!";
            header("Location: order.php");
            exit();
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();
            $_SESSION['error'] = "Error processing your order: " . $e->getMessage();
        }
    }
}

// Handle "Buy Again" action
if (isset($_POST['buy_again']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'] ?? 0;
    
    if (empty($order_id)) {
        $_SESSION['error'] = "Invalid order ID";
    } else {
        try {
            // Verify order belongs to current user
            $verify_stmt = $conn->prepare("SELECT id FROM `orders` WHERE id = ? AND db_id = ?");
            $verify_stmt->bind_param("ii", $order_id, $db_id);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows === 0) {
                throw new Exception("Invalid order");
            }
            
            // Get order items
            $items_stmt = $conn->prepare("
                SELECT oi.product_id, oi.quantity, oi.price_per_item 
                FROM orders_items oi
                WHERE oi.orders_id = ?
            ");
            
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (empty($items)) {
                throw new Exception("No items found in this order");
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Add each item to cart
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                
                // Check if product is still available
                $product_stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND active = 1");
                $product_stmt->bind_param("i", $product_id);
                $product_stmt->execute();
                
                if ($product_stmt->get_result()->num_rows === 0) {
                    // Skip unavailable products
                    continue;
                }
                
                // Check if item already exists in cart
                $check_stmt = $conn->prepare("SELECT quantity FROM cart WHERE db_id = ? AND product_id = ?");
                $check_stmt->bind_param("ii", $db_id, $product_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Update existing cart item
                    $cart_item = $result->fetch_assoc();
                    $new_quantity = $cart_item['quantity'] + $quantity;
                    
                    $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE db_id = ? AND product_id = ?");
                    $update_stmt->bind_param("iii", $new_quantity, $db_id, $product_id);
                    
                    if (!$update_stmt->execute()) {
                        throw new Exception("Failed to update cart item");
                    }
                } else {
                    // Insert new cart item
                    $insert_stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, ?)");
                    $insert_stmt->bind_param("iii", $db_id, $product_id, $quantity);
                    
                    if (!$insert_stmt->execute()) {
                        throw new Exception("Failed to add item to cart");
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Items added to your cart!";
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();
            $_SESSION['error'] = "Error adding items to cart: " . $e->getMessage();
        }
    }
}

// Update cart item quantity
if (isset($_POST['update_cart_quantity'])) {
    $cart_id = $_POST['cart_id'] ?? 0;
    $quantity = intval($_POST['quantity'] ?? 0);
    
    if (empty($cart_id)) {
        $_SESSION['error'] = "Invalid cart item";
    } else {
        try {
            // Verify cart item belongs to user
            $verify_stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND db_id = ?");
            $verify_stmt->bind_param("ii", $cart_id, $db_id);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows === 0) {
                throw new Exception("Invalid cart item");
            }
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND db_id = ?");
                $delete_stmt->bind_param("ii", $cart_id, $db_id);
                
                if (!$delete_stmt->execute()) {
                    throw new Exception("Failed to remove cart item");
                }
                
                $_SESSION['info'] = "Item removed from your cart";
            } else {
                // Update quantity
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND db_id = ?");
                $update_stmt->bind_param("iii", $quantity, $cart_id, $db_id);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update cart item");
                }
                
                $_SESSION['info'] = "Cart updated";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating cart: " . $e->getMessage();
        }
    }
}

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'] ?? 0;
    
    if (empty($cart_id)) {
        $_SESSION['error'] = "Invalid cart item";
    } else {
        try {
            // Verify cart item belongs to user
            $verify_stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND db_id = ?");
            $verify_stmt->bind_param("ii", $cart_id, $db_id);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows === 0) {
                throw new Exception("Invalid cart item");
            }
            
            // Remove item
            $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND db_id = ?");
            $delete_stmt->bind_param("ii", $cart_id, $db_id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to remove cart item");
            }
            
            $_SESSION['info'] = "Item removed from your cart";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error removing item: " . $e->getMessage();
        }
    }
}

// Handle adding to cart from favorites
if (isset($_POST['add_to_cart_from_favorite'])) {
    $product_id = $_POST['product_id'] ?? 0;
    
    if (empty($product_id)) {
        $_SESSION['error'] = "Invalid product";
    } else {
        try {
            // Check if product exists and is active
            $product_stmt = $conn->prepare("SELECT id, price FROM products WHERE id = ? AND active = 1");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            
            if ($product_result->num_rows === 0) {
                throw new Exception("Product not available");
            }
            
            // Check if item already exists in cart
            $check_stmt = $conn->prepare("SELECT quantity FROM cart WHERE db_id = ? AND product_id = ?");
            $check_stmt->bind_param("ii", $db_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing cart item
                $cart_item = $result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + 1;
                
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE db_id = ? AND product_id = ?");
                $update_stmt->bind_param("iii", $new_quantity, $db_id, $product_id);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update cart item");
                }
            } else {
                // Insert new cart item
                $insert_stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, 1)");
                $insert_stmt->bind_param("ii", $db_id, $product_id);
                
                if (!$insert_stmt->execute()) {
                    throw new Exception("Failed to add item to cart");
                }
            }
            
            $_SESSION['success'] = "Item added to your cart!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding item to cart: " . $e->getMessage();
        }
    }
}

// Fetch user's cart items
$cart_items = [];
$cart_total = 0;
try {
    $stmt = $conn->prepare("
        SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image, (c.quantity * p.price) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.db_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Calculate cart total
        foreach ($cart_items as $item) {
            $cart_total += $item['total'];
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching cart: " . $e->getMessage();
    $cart_items = [];
}

// Calculate cart summary for checkout form
$subtotal = $cart_total;
$shipping = 5.00;
$tax = round($subtotal * 0.08, 2); // 8% tax
$total = $subtotal + $shipping + $tax;

// Fetch user's orders with items
$orders = [];
try {
    // First get all orders
    $stmt = $conn->prepare("SELECT * FROM `orders` WHERE db_id = ? ORDER BY order_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($order = $result->fetch_assoc()) {
            // For each order, get its items
            $items_stmt = $conn->prepare("
                SELECT oi.*, p.name, p.image 
                FROM orders_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.orders_id = ?
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
    <link rel="stylesheet" href="css/order.css">
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
                        <a href="order.php" class="nav-link-sidebar active">
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
                        <!-- Current Cart -->
                        <div class="cart-card">
                            <div class="cart-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Your Cart</h5>
                                <?php if (!empty($cart_items)): ?>
                                <span class="badge bg-white text-primary"><?php echo count($cart_items); ?> items</span>
                                <?php endif; ?>
                            </div>
                            <div class="cart-body">
                                <?php if (empty($cart_items)): ?>
                                    <div class="text-center py-4">
                                        <div class="empty-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <p class="text-muted">Your cart is empty</p>
                                        <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cart_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($item['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                                alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                                class="me-3"width="50" height="50"
                                                                style="object-fit: cover; border-radius: 5px;"
                                                                onerror="this.src='assets/images/placeholder.jpg'">
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                    <td>
                                                        <form action="" method="post" class="quantity-control">
                                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" name="update_cart_quantity" class="quantity-btn minus" 
                                                                onclick="document.getElementById('qty_<?php echo $item['id']; ?>').value = Math.max(1, parseInt(document.getElementById('qty_<?php echo $item['id']; ?>').value) - 1);">-</button>
                                                            <input type="number" id="qty_<?php echo $item['id']; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                                            <button type="submit" name="update_cart_quantity" class="quantity-btn plus" 
                                                                onclick="document.getElementById('qty_<?php echo $item['id']; ?>').value = parseInt(document.getElementById('qty_<?php echo $item['id']; ?>').value) + 1;">+</button>
                                                        </form>
                                                    </td>
                                                    <td>$<?php echo number_format($item['total'], 2); ?></td>
                                                    <td>
                                                        <form action="" method="post" style="display: inline-block;">
                                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" name="remove_from_cart" class="btn btn-sm btn-outline-danger" title="Remove">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                                    <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <form action="" method="post">
                                            <button type="submit" name="show_checkout" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart me-2"></i> Proceed to Checkout
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Checkout Form (Show only when proceeding to checkout) -->
                        <?php if (!empty($cart_items) && isset($_POST['show_checkout'])): ?>
                            <div class="checkout-form">
                                <div class="form-header">
                                    Complete Your Order
                                </div>
                                
                                <div class="form-body">
                                    <form action="" method="post">
                                        <!-- Shipping Address -->
                                        <div class="form-group">
                                            <label for="shipping_address" class="form-label">Shipping Address</label>
                                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                                        </div>
                                        
                                        <!-- Payment Method Selection -->
                                        <div class="form-group">
                                            <label class="form-label">Payment Method</label>
                                            <div class="payment-methods">
                                                <div class="payment-method selected" onclick="selectPaymentMethod(this, 'Credit Card')">
                                                    <input type="radio" name="payment_method" value="Credit Card" checked>
                                                    <div class="method-content">
                                                        <i class="fas fa-credit-card method-icon"></i>
                                                        <span class="method-name">Credit Card</span>
                                                    </div>
                                                </div>
                                                <div class="payment-method" onclick="selectPaymentMethod(this, 'PayPal')">
                                                    <input type="radio" name="payment_method" value="PayPal">
                                                    <div class="method-content">
                                                        <i class="fab fa-paypal method-icon paypal-icon"></i>
                                                        <span class="method-name">PayPal</span>
                                                    </div>
                                                </div>
                                                <div class="payment-method" onclick="selectPaymentMethod(this, 'Cash on Delivery')">
                                                    <input type="radio" name="payment_method" value="Cash on Delivery">
                                                    <div class="method-content">
                                                        <i class="fas fa-money-bill-wave method-icon cod-icon"></i>
                                                        <span class="method-name">Cash on Delivery</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Card Details (initially visible) -->
                                        <div id="card-details" class="card-details">
                                            <div class="form-group">
                                                <label for="cardholder_name" class="form-label">Cardholder Name</label>
                                                <input type="text" class="form-control" id="cardholder_name" name="cardholder_name" placeholder="Name as displayed on card">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="card_number" class="form-label">Card Number</label>
                                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                            </div>
                                            
                                            <div class="card-row">
                                                <div class="card-col">
                                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                                </div>
                                                <div class="card-col">
                                                    <label for="cvv" class="form-label">CVV</label>
                                                    <div class="cvv-field">
                                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="4">
                                                        <span class="help-tooltip" title="3-digit code on back of card"><i class="fas fa-question-circle"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="save-card">
                                                <input type="checkbox" id="save_card" name="save_card" value="1">
                                                <label for="save_card">Save this card for future purchases</label>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Summary -->
                                        <div class="form-group">
                                            <label class="form-label">Order Summary</label>
                                            <div class="order-summary">
                                                <div class="summary-row">
                                                    <span>Subtotal</span>
                                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Shipping</span>
                                                    <span>$<?php echo number_format($shipping, 2); ?></span>
                                                </div>
                                                <div class="summary-row">
                                                    <span>Tax</span>
                                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                                </div>
                                                <div class="summary-row total-row">
                                                    <span>Total</span>
                                                    <span>$<?php echo number_format($total, 2); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Notes -->
                                        <div class="form-group">
                                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                                            <textarea class="form-control" id="notes" name="notes" placeholder="Special instructions for delivery or preparation"></textarea>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="buttons">
                                            <a href="order.php" class="btn btn-back">Back to Cart</a>
                                            <button type="submit" name="checkout" class="btn btn-primary">
                                                <i class="fas fa-lock"></i> Place Order Securely
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order History -->
                        <?php if (!empty($orders)): ?>
                            <h4 class="section-title">Order History</h4>
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
                                                    <form action="" method="post">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="buy_again" class="btn btn-outline-primary btn-sm">Buy Again</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif (empty($cart_items)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h4>No Orders Yet</h4>
                                <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                                <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
                            </div>
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
                                <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
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
                                                    <form action="" method="post" style="display: inline;">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" name="add_to_cart_from_favorite" class="btn btn-cart">
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
    <script>
    // Function to select payment method
    function selectPaymentMethod(element, method) {
        // Remove selected class from all methods
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add selected class to clicked method
        element.classList.add('selected');
        
        // Check the radio button
        element.querySelector('input[type="radio"]').checked = true;
        
        // Show/hide card details based on selection
        const cardDetails = document.getElementById('card-details');
        if (method === 'Credit Card') {
            cardDetails.style.display = 'block';
        } else {
            cardDetails.style.display = 'none';
        }
    }
    
    // Format credit card number with spaces
    document.getElementById('card_number')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        
        e.target.value = formattedValue;
    });
    
    // Format expiry date with slash
    document.getElementById('expiry_date')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        e.target.value = value;
    });
    
    // Limit CVV to numbers only
    document.getElementById('cvv')?.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
    </script>
</body>
</html>