<?php
session_start();
include 'connect.php'; // Database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $customer_id = $_SESSION['user_id'] ?? null;

    if (!$customer_id) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit();
    }

    if ($action === 'add') {
        // Handle Add to Cart
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if ($product_id && is_numeric($quantity) && $quantity > 0) {
            // Check if product already exists in cart
            $check_sql = "SELECT id, quantity FROM carts WHERE customer_id = ? AND product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $customer_id, $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                // Product exists, update quantity
                $row = $check_result->fetch_assoc();
                $new_quantity = $row['quantity'] + $quantity;

                $update_sql = "UPDATE carts SET quantity = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_quantity, $row['id']);
                $update_stmt->execute();
                $update_stmt->close();

                echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
            } else {
                // Product doesn't exist, insert new row
                $insert_sql = "INSERT INTO carts (customer_id, product_id, quantity) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iii", $customer_id, $product_id, $quantity);

                if ($insert_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Item added to cart!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add item.']);
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
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
            $stmt->bind_param("ii", $quantity, $cart_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cart updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid cart data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
}
?>