<?php
session_start();
include 'connect.php'; // Ensure this file connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Fetch data from AJAX
  $product_id = $_POST['product_id'];
  $product_name = $_POST['product_name'];
  $product_price = $_POST['product_price'];
  $product_description = $_POST['product_description'];
  $product_img = $_POST['product_img'];
  $quantity = $_POST['quantity'];
  $customer_id = $_SESSION['user_id'] ?? null; // Use session user ID

  // Check if the user is logged in
  if (!$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to the cart.']);
    exit;
  }

  // Check if the product already exists in the cart
  $stmt = $conn->prepare("SELECT * FROM carts WHERE product_id = ? AND customer_id = ?");
  $stmt->bind_param("ii", $product_id, $customer_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Update quantity if product exists
    $stmt = $conn->prepare("UPDATE carts SET quantity = quantity + ?, product_price = product_price + ? WHERE product_id = ? AND customer_id = ?");
    $stmt->bind_param("idii", $quantity, $product_price, $product_id, $customer_id);
    $stmt->execute();
  } else {
    // Add new product to the cart
    $stmt = $conn->prepare("INSERT INTO carts (product_id, product_name, product_price, product_description, product_img, quantity, customer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdii", $product_id, $product_name, $product_price, $product_description, $product_img, $quantity, $customer_id);
    $stmt->execute();
  }

  echo json_encode(['success' => true, 'message' => 'Product added to cart!']);
  exit;
}
?>
