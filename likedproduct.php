<?php

require_once 'check.inc.php';

// check user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to manage your wishlist";
    header("Location: index.php");
    exit();
}

$db_id = $_SESSION["id"] ?? 1;

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    
    try {
        // Check if product exists
        $product_stmt = $conn->prepare("SELECT id, stock_quantity FROM products WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        
        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            
            // Check if there's stock available
            if ($product['stock_quantity'] > 0) {
                // Check if product already in cart
                $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE db_id = ? AND product_id = ?");
                $check_stmt->bind_param("ii", $db_id, $product_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update quantity
                    $cart_item = $check_result->fetch_assoc();
                    $current_quantity = $cart_item['quantity'];
                    
                    // Make sure we don't exceed available stock
                    $new_quantity = min($current_quantity + 1, $product['stock_quantity']);
                    
                    $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                    $update_stmt->execute();
                } else {
                    // Insert new 
                    $insert_stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, 1)");
                    $insert_stmt->bind_param("ii", $db_id, $product_id);
                    $insert_stmt->execute();
                }
                
                $_SESSION['success'] = "Product added to your cart!";
            } else {
                $_SESSION['error'] = "Sorry, this product is out of stock.";
            }
        } else {
            $_SESSION['error'] = "Product not found.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding to cart: " . $e->getMessage();
    }
}

// Remove from wishlist
if (isset($_POST['remove_from_wishlist'])) {
    $product_id = $_POST['product_id'];
    
    try {
        $delete_stmt = $conn->prepare("DELETE FROM liked_order WHERE db_id = ? AND product_id = ?");
        $delete_stmt->bind_param("ii", $db_id, $product_id);
        $delete_stmt->execute();
        
        if ($delete_stmt->affected_rows > 0) {
            $_SESSION['success'] = "Product removed from your favorites!";
        } else {
            $_SESSION['error'] = "Product was not in your favorites.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error removing from favorites: " . $e->getMessage();
    }
}


header("Location: order.php");
exit();
?>