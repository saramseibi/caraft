<?php
require_once 'check.inc.php';

if (!isset($_SESSION['id']) || !isset($_POST['product_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$db_id = (int)$_SESSION['id'];
$product_id = (int)$_POST['product_id'];

try {
    $stmt = $conn->prepare("DELETE FROM cart WHERE db_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $db_id, $product_id);
    
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