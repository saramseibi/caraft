<?php
require_once 'check.inc.php';

if (!isset($_SESSION['id']) || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$db_id = (int)$_SESSION['id'];
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

try {
    // Check if product exists in cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE db_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $db_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE db_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $db_id, $product_id);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO cart (db_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $db_id, $product_id, $quantity);
    }
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>