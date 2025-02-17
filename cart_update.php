<?php
session_start();
include 'connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    if (isset($cart_id, $quantity) && is_numeric($quantity) && $quantity > 0) {
        // Update cart quantity in the database
        $sql = "UPDATE carts SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $cart_id);
        $stmt->execute();
        $stmt->close();

        // Fetch updated total price for the item
        $sql = "SELECT 
                    p.item_price AS product_price, 
                    (p.item_price * c.quantity) AS total_price 
                FROM carts c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Send updated prices back as JSON
        echo json_encode([
            'success' => true,
            'unit_price' => number_format($result['product_price'], 2),
            'total_price' => number_format($result['total_price'], 2),
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    }
}
