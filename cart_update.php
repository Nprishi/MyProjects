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
            // Fetch product price
            $product_sql = "SELECT item_price FROM products WHERE id = ?";
            $product_stmt = $conn->prepare($product_sql);
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();

            if ($product_result->num_rows > 0) {
                $product_data = $product_result->fetch_assoc();
                $product_price = $product_data['item_price'];
                $total_price = $product_price * $quantity;

                // Check if product already exists in cart
                $check_sql = "SELECT id, quantity FROM carts WHERE customer_id = ? AND product_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $customer_id, $product_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Product exists, update quantity and total price
                    $row = $check_result->fetch_assoc();
                    $new_quantity = $row['quantity'] + $quantity;
                    $new_total_price = $product_price * $new_quantity;

                    $update_sql = "UPDATE carts SET quantity = ?, product_price = ?, total_price = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("idii", $new_quantity, $product_price, $new_total_price, $row['id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
                } else {
                    // Insert new product in cart
                    $insert_sql = "INSERT INTO carts (customer_id, product_id, quantity, product_price, total_price) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iiidd", $customer_id, $product_id, $quantity, $product_price, $total_price);

                    if ($insert_stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Item added to cart!']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add item.']);
                    }
                    $insert_stmt->close();
                }

                $check_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
            }

            $product_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product data.']);
        }
    } elseif ($action === 'update') {
        // Handle Update Cart Quantity
        if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
            $cart_id = $_POST['cart_id'];
            $quantity = $_POST['quantity'];

            // Get product price
            $sql = "SELECT product_price FROM carts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->bind_result($product_price);
            $stmt->fetch();
            $stmt->close();

            if ($product_price) {
                $total_price = $product_price * $quantity;

                // Update cart
                $update_sql = "UPDATE carts SET quantity = ?, total_price = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("idi", $quantity, $total_price, $cart_id);
                if ($update_stmt->execute()) {
                    echo json_encode(['success' => true, 'total_price' => number_format($total_price, 2)]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
                }
                $update_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
            }

            $cart_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid cart data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
}
