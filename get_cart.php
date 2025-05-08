<?php
require_once 'check.inc.php';

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$db_id = (int)$_SESSION['id'];

try {
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, (c.quantity * p.price) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.db_id = ?
    ");
    $stmt->bind_param("i", $db_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['items' => $items]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>