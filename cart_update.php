<?php
session_start();
include 'connect.php'; // Include database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Handle Add to Cart
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if ($product_id && is_numeric($quantity) && $quantity > 0) {
            // Insert into cart table
            $sql = "INSERT INTO carts (product_id, quantity) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $product_id, $quantity);
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Item added to cart!',
                        'cart_id' => $stmt->insert_id
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add item.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product data.']);
        }
    } elseif ($action === 'update') {
        // Handle Update Cart Quantity
        $cart_id = $_POST['cart_id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        if ($cart_id && is_numeric($quantity) && $quantity > 0) {
            $sql = "UPDATE carts SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $quantity, $cart_id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Cart updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid cart data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
}
