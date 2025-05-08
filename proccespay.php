<?php
// Include database connection and session management
require_once 'check.inc.php';

// Make sure user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to complete your purchase";
    header("Location: index.php");
    exit();
}

$db_id = $_SESSION["id"] ?? 1; // Get user ID from session

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
            header("Location: order.php");
            exit();
        }
        
        if (empty($card_number)) {
            $_SESSION['error'] = "Card number is required";
            header("Location: order.php");
            exit();
        }
        
        if (empty($expiry_date) || !preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
            $_SESSION['error'] = "Valid expiry date is required (MM/YY)";
            header("Location: order.php");
            exit();
        }
        
        if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $_SESSION['error'] = "Valid CVV is required";
            header("Location: order.php");
            exit();
        }
    }
    
    if (empty($shipping_address)) {
        $_SESSION['error'] = "Shipping address is required";
        header("Location: order.php");
        exit();
    }
    
    if (empty($payment_method)) {
        $_SESSION['error'] = "Payment method is required";
        header("Location: order.php");
        exit();
    }
    
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
        
        // Insert order items into orders_items table
        // Create the table if it doesn't exist
        $conn->query("
            CREATE TABLE IF NOT EXISTS orders_items (
                id INT(11) NOT NULL AUTO_INCREMENT,
                orders_id INT(11) NOT NULL,
                product_id INT(11) NOT NULL,
                quantity INT(11) NOT NULL,
                price_per_item DECIMAL(10,2) NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (orders_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");
        
        // Insert items into orders_items
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
        header("Location: order.php");
        exit();
    }
} else {
    // If accessed directly without form submission
    $_SESSION['error'] = "Invalid access";
    header("Location: order.php");
    exit();
}