<?php
require_once 'check.inc.php';

// Debug
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = 'Please login first';
    header('Location: login.php');
    exit;
}

if (!isset($_GET['product_id']) || !isset($_GET['quantity'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: products.php');
    exit;
}

$product_id = (int)$_GET['product_id'];
$quantity = (int)$_GET['quantity'];
$db_id = (int)$_SESSION['id'];

try {
    // Verify product exists and get price
    $stmt = $conn->prepare("SELECT id, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        throw new Exception("Product $product_id doesn't exist");
    }

    // Check if already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE db_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $db_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE db_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $db_id, $product_id);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $db_id, $product_id, $quantity);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $_SESSION['success'] = 'Product added to cart';
    
} catch (Exception $e) {
    error_log("Cart Error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to add to cart: " . $e->getMessage();
}

header('Location: products.php');
exit;