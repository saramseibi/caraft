<?php
// Include the check.inc.php file which starts a session and sets up database connection
require_once 'check.inc.php';

// Make sure user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to manage your orders";
    header("Location: index.php");
    exit();
}

$db_id = $_SESSION["id"] ?? 1;

// Process "Buy Again" action
if (isset($_POST['buy_again'])) {
    $order_id = $_POST['order_id'];
    
    // Get items from the original order
    try {
        $items_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $result = $items_stmt->get_result();
        $added_count = 0;
        
        // Add each item to cart
        while ($item = $result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Check if product already in cart
            $check_stmt = $conn->prepare("SELECT id FROM cart WHERE db_id = ? AND product_id = ?");
            $check_stmt->bind_param("ii", $db_id, $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update quantity
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE db_id = ? AND product_id = ?");
                $update_stmt->bind_param("iii", $quantity, $db_id, $product_id);
                $update_stmt->execute();
                $added_count++;
            } else {
                // Insert new item
                $insert_stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iii", $db_id, $product_id, $quantity);
                $insert_stmt->execute();
                $added_count++;
            }
        }
        
        if ($added_count > 0) {
            $_SESSION['success'] = "Items from your previous order have been added to cart!";
        } else {
            $_SESSION['info'] = "No items were found in this order to add to cart.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding items to cart: " . $e->getMessage();
    }
}

// Redirect back to orders page
header("Location: order.php");
exit();
?>