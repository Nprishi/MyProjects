<?php
session_start();
include 'connect.php';  // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$profile = $_SESSION['user_name'] ?? 'Guest';
$result = false; // Initialize $result to prevent "undefined variable" error

$query = "SELECT carts.*, products.item_price, products.item_image, products.item_name, products.product_description 
          FROM carts 
          INNER JOIN products ON carts.product_id = products.id 
          WHERE carts.customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Handle deleting selected items
// Handle deleting selected items
if (isset($_POST['delete_selected']) && !empty($_POST['check'])) {
    // Ensure `$_POST['check']` is treated as an array
    $selected_items = (array) $_POST['check'];

    // Sanitize and delete the selected items
    $ids_to_delete = implode(',', array_map('intval', $selected_items));

    // Delete the selected items from the cart
    $delete_sql = "DELETE FROM carts WHERE id IN ($ids_to_delete) AND customer_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $customer_id);

    if ($delete_stmt->execute()) {
        header("Location: cart.php"); // Reload the page to reflect the changes
        exit();
    } else {
        echo "Error: " . $delete_stmt->error;
    }
}


// Handle single item deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM carts WHERE id = ? AND customer_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $delete_id, $customer_id);

    if ($stmt->execute()) {
        header("Location: cart.php"); // Reload the page to reflect the changes
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle order form submission
if (isset($_POST['submit_order'])) {
    // Capture user and form details
    $user_name = $conn->real_escape_string($_POST['user_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $mobile_number = $conn->real_escape_string($_POST['mobile_number']);
    $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : '';
    $total_amount = 0;

    // Fetch cart items again during order placement
    $query = "SELECT c.id, c.product_id, c.quantity, p.item_price 
    FROM carts c
    JOIN products p ON c.product_id = p.id 
    WHERE c.customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $product_id = $item['product_id'];  // Get product_id from the cart
        $quantity = $item['quantity'];  // Get quantity from the cart
        $cart_id = $item['id'];  // Assuming 'cart_id' exists in the cart table
        $total_price = $item['item_price'] * $quantity; // Calculate total price
        $total_amount += $total_price; // Add it to the total amount

        // Insert into myorders table (Only required fields)
        $sql = "INSERT INTO myorder(
                product_id, quantity, customer_id, payment_id, address, total_amount, cart_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $order_stmt = $conn->prepare($sql);
        $order_stmt->bind_param(
            "iiissdi",
            $product_id,
            $quantity,
            $customer_id,
            $payment_id,
            $address,
            $total_price,
            $cart_id
        );

        if ($order_stmt->execute()) {
            // Order inserted successfully
        } else {
            echo "<script>alert('Error: " . $order_stmt->error . "');</script>";
        }
    }

    // Clear cart after successful order
    $clear_cart_sql = "DELETE FROM carts WHERE customer_id = ?";
    $clear_stmt = $conn->prepare($clear_cart_sql);
    $clear_stmt->bind_param("i", $customer_id);
    if ($clear_stmt->execute()) {
        echo "<script>alert('Order placed successfully!'); window.location.href='order.php';</script>";
    } else {
        echo "<script>alert('Error: Could not clear cart.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="stylesheet/styles.css">
    <title>Product Cart</title>

    <style>
        body {
            background-color: #F4F4F4;
            overflow: scroll;
        }

        .cart_section {
            display: flex;
            justify-content: space-between;
        }

        .left_side {
            width: 65vw;

            /* border: 1px solid red; */
        }

        .right_side {
            width: 25vw;
            height: 20vw;
            margin: 1vw 5vw;
            box-sizing: border-box;
            background-color: #fff;
            border-radius: 1vw;
            /* border: 1px solid green; */
        }

        /* Add styles for the delete icon */
        .delete-icon {
            font-size: 1.5vw;
            cursor: pointer;
            margin-left: 2vw;
            margin-top: 10vw;
        }

        .delete-icon:hover {
            color: red;
        }

        .fa-heart {
            font-size: 1.5vw;
            cursor: pointer;
            margin-left: 2vw;
            margin-top: 10vw;
            color: red;
        }

        .fa-regular {
            color: black;
        }

        .fa-cart-shopping {
            font-size: 2vw;
        }

        /* Other existing styles */
        .product-card {
            width: 60vw;
            height: 15vw;
            background-color: white;
            display: flex;
            align-items: center;
            padding: 2vw;
            border-radius: 1vw;
            box-shadow: 0vw 0.2vw 1vw rgba(0, 0, 0, 0.1);
            margin: 2vw 0 0 3vw;
            justify-content: space-between;
            /* Align items properly */
        }

        .quantity button {
            width: 2.5vw;
            height: 2.5vw;
            border: none;
            background-color: #ddd;
            border-radius: 0.5vw;
            cursor: pointer;
            font-size: 1.6vw;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-number {
            font-size: 1.6vw;
            margin: 0 1vw;
        }

        .selected {
            position: sticky;
            top: 4.8vw;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 3vw;
            background-color: #fff;
            width: 60vw;
            border-top: 1vw solid #F4F4F4;
        }

        #selectAll {
            margin: 0 1vw;
        }

        .delete_all {
            color: #fff;
            background-color: blue;
            padding: 0.65vw;
            outline: none;
            border: none;
        }

        .delete_all:hover {
            background-color: red;

        }

        input[type="submit"] {
            background-color: #000;
            color: #fff;
            border: none;
            outline: none;
            padding: 1vw;
            width: 25vw;
            font-size: 1.3vw;
        }

        .right_side_header {
            margin-left: 1vw;
            margin-top: 1vw;
        }

        .summary {
            margin-left: 1vw;
            display: none;
        }

        .vouchar {
            margin-left: 1vw;
        }

        .search-input {
            width: 20vw;
            padding: 0.5vw;
            font-size: 1vw;
            border-radius: 0.5vw;
            outline: none;
            border: none;
            margin-right: 0.5vw;
            border-top: 1px solid #bdbdbd;
            border-bottom: 1px solid #bdbdbd;
            border-left: 1px solid #bdbdbd;
        }

        .search-btn {
            padding: 0.5vw 1vw;
            font-size: 1vw;
            background-color: #1D1B1B;
            color: white;
            border: none;
            border-radius: 0 0.5vw 0.5vw 0;
            cursor: pointer;
            outline: none;
            margin-left: -3.5vw;
        }

        .search-btn:hover {
            background-color: #000;
        }

        .empty_product {
            margin: 2vw;
            font-size: 1.5vw;
            color: red;
        }
    </style>

</head>

<body>

    <!-- Header -->
    <header>
        <div class="logo">
            <h3>Gadget<span>4</span>U</h3>
        </div>

        <!-- Search bar form -->
        <div class="search_product">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="search-input">
            <button type="submit" class="search-btn"><i class="fa-brands fa-searchengin"></i></button>
        </div>

        <nav>
            <li class="nav-list"><a href="index.php" class="nav-anchor">HOME</a></li>
            <li class="nav-list"><a href="about.php" class="nav-anchor">ABOUT</a></li>
            <li class="nav-list"><a href="shop.php" class="nav-anchor">SHOP</a></li>
            <li class="nav-list"><a href="contact.php" class="mainbtn nav-anchor">CONTACT</a></li>
        </nav>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="nav-icon">
                <a class="two-nav-btn profile" href="#">
                    <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : "Profile"; ?>
                </a>
                <a class="two-nav-btn order" href="order.php"><i class="fa-solid fa-store"></i>Order</a>
                <a class="two-nav-btn logout" href="logout.php">Logout</a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <style>
                    .order {
                        background-color: rgb(57, 5, 105);
                    }

                    .order:hover {
                        background-color: rgb(70, 10, 110);
                    }

                    .profile:hover {
                        border: 1px solid #1D1B1B;
                        color: #ff2200;
                    }

                    .logout:hover {
                        background-color: #1D1B1B;
                    }

                    .fa-store {
                        margin-right: 1vw;
                        border: none;
                    }
                </style>
            </div>
        <?php else: ?>
            <div class="nav-icon">
                <a class="two-nav-btn" href="login.php">Login</a>
                <a class="two-nav-btn" href="signup.php">Signup</a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        <?php endif; ?>

    </header>

    <section class="cart_section">
        <div class="left_side">
            <form action="?" method="POST" id="cart-form">
                <div class="selected">
                    <!-- Add "Select All" checkbox -->
                    <label><input type="checkbox" id="selectAll"> Select All</label><br><br>
                    <!-- Add "Delete Selected" button -->
                    <button type="submit" name="delete_selected" class="delete_all"><i class="fa-solid fa-trash"></i> Delete</button>
                </div>

                <!-- Display the product cards -->
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <input type="checkbox" name="check[]" value="<?= $row['id']; ?>">
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($row['item_image']); ?>" alt="Product Image" class="product-image">
                                <div class="product-details">
                                    <h3 class="product-name"><?= htmlspecialchars($row['item_name']); ?></h3>
                                    <p class="product-description"><?= htmlspecialchars($row['product_description']); ?></p>
                                </div>
                            </div>
                            <div class="product-price">
                                <?php $calculated_price = $row['quantity'] * $row['product_price']; ?>
                                <span class="current-price">NPR <?= number_format($calculated_price, 2); ?></span>
                            </div>
                            <div class="quantity">
                                <button type="button" class="decrement" data-cart-id="<?= $row['id']; ?>">-</button>
                                <span class="quantity-number"><?= $row['quantity']; ?></span>
                                <button type="button" class="increment" data-cart-id="<?= $row['id']; ?>">+</button>
                            </div>
                            <div class="fa-icon" style="height:5vw; width:6vw; display: flex; margin-top:-5vw;">
                                <i class="fa-regular fa-heart"></i>
                                <a href="cart.php?delete_id=<?= $row['id']; ?>" class="delete-icon">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty_product">No products in the cart.</p>
                <?php endif; ?>

                <?php

                // Assuming you have the cart items stored in $result variable

                if ($result && $result->num_rows > 0) {
                    $cart_items_count = $result->num_rows;
                    $summary_display = 'block';  // Show summary if items exist
                } else {
                    $cart_items_count = 0;
                    $summary_display = 'none';  // Hide summary if cart is empty
                }
                ?>

            </form>
            <style>
                .selected {
                    border-bottom: 1px solid grey;
                }

                .product-image {
                    width: 10vw;
                    height: 10vw;
                    object-fit: cover;
                    margin: 0 2vw;
                    border-radius: 0.5vw;
                }

                .product-details {
                    border-right: 1px solid grey;
                    width: 20vw;
                    height: 10vw;
                }

                .product-name {
                    font-size: 1.8vw;
                    font-weight: bold;
                }

                .product-description {
                    font-size: 1.2vw;
                    text-align: left;
                    color: #666;
                }

                .product-price {
                    display: flex;
                    justify-content: center;
                    text-align: left;
                }

                .product-info {
                    display: flex;
                    align-items: center;
                    flex: 1;
                }

                .current-price {
                    font-size: 1.6vw;
                    font-weight: bold;
                    color: orange;
                    display: block;
                }

                .quantity {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    width: 8vw;
                    margin-left: 2vw;
                }
            </style>
        </div>

        <div class="right_side" id="summary" style="display: <?= $summary_display; ?>;">
            <div class="right_side_header">
                <h3><span>Location</span></h3>
                <p id="googleMap" style="cursor: pointer; font-weight:bolder;"><i class="fa-solid fa-location-dot" style="color: red;"></i> <span id="state"></span> </p>
            </div>

            <div class="mysummary">
                <h4 class="Details-Head">Order Details</h4>
                <p>Subtotal:</p>
                <p>Shipping Fee:</p>
                <input type="text" name="vouchar" class="vouchar" placeholder="Enter Your Token">
                <button name="apply" class="apply">Apply</button>
            </div>

            <div class="right_side_footer">
                <p>Total:</p>
            </div>

            <div class="payment_method">
                <button class="submit" name="payment" id="payment-btn">Place Order</button>
            </div>
            </form>

            <section class="payment" id="payment_option">

                <div class="cash_delivery">
                    <input type="hidden" name="payment_method" value="Cash on Delivery">
                    <button type="submit" name="submit_order" class="submit" id="cash">Cash on Delivery</button>
                </div>

                <div class="eSewa">
                    <!-- <div class="Paymnet Id/Password">
                        <h1>Try Esewa Account</h1>
                        <p>eSewa ID: 9806800001/2/3/4/5</p>
                        <p> Password: Nepal@123 MPIN: 1122 (for application only)</p>
                        <p>Token:123456</P>
                        </div> -->
                    <form action="https://uat.esewa.com.np/epay/main" method="POST">
                        <!-- Total Amount -->
                        <input type="hidden" name="tAmt" value="1000">
                        <!-- Actual Amount -->
                        <input type="hidden" name="amt" value="1000">
                        <!-- Tax Amount (if any) -->
                        <input type="hidden" name="txAmt" value="0">
                        <!-- Service Charge (if any) -->
                        <input type="hidden" name="psc" value="0">
                        <!-- Delivery Charge (if any) -->
                        <input type="hidden" name="pdc" value="0">
                        <!-- Merchant Code (Test) -->
                        <input type="hidden" name="scd" value="EPAYTEST">
                        <!-- Transaction Reference ID (Unique for each payment) -->
                        <input type="hidden" name="pid" value="<?php echo uniqid('test-invoice-'); ?>">
                        <!-- Success URL (Publicly accessible) -->
                        <input type="hidden" name="su" value="http://xyz.ngrok.io/esewa_success.php?q=su">
                        <!-- Failure URL (Publicly accessible) -->
                        <input type="hidden" name="fu" value="http://xyz.ngrok.io/esewa_failure.php?q=fu">
                        <!-- Submit Button -->
                        <input id="esewa" class="submit" type="submit" value="eSewa" style="width: 9vw; padding:0.5vw; background-color:green; font-size:1vw;">
                    </form>
                </div>

                <div class="khalti">
                    <button class="submit" name="khalti" id="khalti">Khalti</button>
                </div>

                <div class="eBanking">
                    <button class="submit" name="eBanking" id="eBanking">eBanking</button>
                </div>

                <style>
                    #summary {
                        display: flex;
                        flex-direction: column;
                        height: 25vw;
                    }

                    .Details-Head{
                        margin-left: 1vw;
                        color: green;
                    }

                    .form_section {
                        border: 1px solid red;
                        width: 30vw;
                        display: none;
                    }

                    .form_section form {
                        display: flex;
                        flex-direction: column;
                        margin-top: 2vw;
                    }

                    .payment {
                        display: none;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        width: 10vw;
                        margin-top: 1vw;
                        /* border: 1px solid red; */
                    }

                    #payment-btn {
                        background-color: red;
                        color: #fff;
                        margin-left: 1vw;
                    }

                    .submit {
                        padding: 0.5vw;
                        color: #fff;
                        width: 9vw;
                        background-color: green;
                        outline: none;
                        border: none;
                        border-radius: 0.5vw;
                        margin-top: 1vw;
                    }
                </style>

            </section>

        </div>

        <!-- Receiver Details -->
        <section class="form_section" id="process_form">
            <div class="order-form">
                <h4>Enter Receiver Details</h4>
                <form action="?" method="POST" class="form_submit">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="user_name" placeholder="Enter your full name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>

                    <label for="address">Address:</label>
                    <input id="address" name="address" placeholder="Enter your full address" required></input>

                    <label for="number">Mobile Number:</label>
                    <input type="tel" name="mobile_number" id="number" pattern="[0-9]{10}" placeholder="e.g., 9800000000" required>
                    <button type="submit" name="submit_order" class="apply-form">Submit</button>
                </form>
            </div>
        </section>
        <style>
            .form_section {
                width: 30vw;
                height: 38vw;
                align-items: center;
                background-color: #fff;
                margin-left: 3vw;
                margin-top: 1vw;
                justify-content: center;
                right: 2vw;
                border: none;
            }
            .form_submit input{
                margin-bottom: 2vw;
                width: 80%;
            }
            .form_submit{
                margin-left: 2vw;
            }
            .order-form{
                margin-top: 2vw;
            }
            .order-form h4{
            text-align: center;
            color:green;
            }
            .apply-form{
                background-color: red;
                color: white;
                width: 10vw;
                padding: 0.5vw;
                outline: none;
                border: none;
            }
        </style>
    </section>

    <!-- Footer Section here -->
    <footer>
        <div class="process">
            <div class="address-link">
                <p><span style="color:#fff" ;>Email: </span>gadgetforyou44@gmail.com</p>
                <p><span style="color:#fff" ;>Address: </span>Bhaktapur, Kathmandu</p>
            </div>
            <div class="social-link">
                <a href="https://www.facebook.com/"><i class="fa fa-facebook"></i></a>
                <a href="https://www.instagram.com/"><i class="fa fa-instagram"></i></a>
                <a href="https://www.twitter.com/"><i class="fa-brands fa-square-x-twitter"></i></a>
                <a href="https://www.linkedin.com/"><i class="fa fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <!-- Footer CSS Here  -->
    <style>
        footer {
            position: relative;
            bottom: -22.8vw;
            width: 95vw;
            margin-left: 2vw;
            margin-top: 1vw;
            border-top: 2vw #F4F4F4;
        }

        .process {
            height: 2vw;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #bdbdbd;
        }

        .address-link {
            color: rgb(102, 100, 100);
            font-size: 0.9vw;
            display: flex;
            padding: 1vw;
            margin-left: 4vw;
        }

        .address-link span {
            color: #c9c9c9;
            margin-left: 2vw;
        }

        .address-link p {
            color: grey;
        }

        .address-link .social-link {
            margin-right: 4vw;
            display: flex;
            justify-content: space-around;
            padding: auto;
            width: 10vw;
        }

        .social-link {
            margin-right: 4vw;
        }

        .social-link i {
            font-size: 1vw;
            padding: 0;
            margin-right: 1vw;

        }

        .fa-square-x-twitter {
            color: #000;
            background-color: #fff;
        }

        .process a:nth-child(2) {
            color: rgb(90, 90, 245);
        }
    </style>

    <!-- Javascript Here  -->
    <script>
        // Functionality for incrementing and decrementing the quantity
        document.querySelectorAll('.quantity').forEach(function(quantityContainer) {
            const decrementButton = quantityContainer.querySelector('.decrement');
            const incrementButton = quantityContainer.querySelector('.increment');
            const quantityNumber = quantityContainer.querySelector('.quantity-number');
            const currentPrice = quantityContainer.closest('.product-card').querySelector('.current-price');
            const cartId = decrementButton.dataset.cartId; // Same for incrementButton

            const updateCart = (cartId, newQuantity) => {
                if (newQuantity < 1 || newQuantity > 10) {
                    return;
                }

                // Send AJAX request to update cart quantity
                fetch('cart_update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=update&cart_id=${cartId}&quantity=${newQuantity}` // ✅ Fix added here
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            // Update quantity and price in the UI
                            quantityNumber.textContent = newQuantity;
                            currentPrice.textContent = `NPR ${data.total_price}`;
                            updateSubtotal();
                        } else {
                            alert(data.message || 'Failed to update the cart.');
                        }
                    })
                    .catch((error) => console.error('Error:', error));
            };

            const updateSubtotal = () => {
                let subtotal = 0;
                document.querySelectorAll('.current-price').forEach((priceElement) => {
                    subtotal += parseFloat(priceElement.textContent.replace('NPR ', '').replace(',', ''));
                });
                document.querySelector('#subtotal').textContent = `NPR ${subtotal.toFixed(2)}`;
            };

            decrementButton.addEventListener('click', function() {
                let currentQuantity = parseInt(quantityNumber.textContent);
                if (currentQuantity > 1) {
                    updateCart(cartId, currentQuantity - 1);
                }
            });

            incrementButton.addEventListener('click', function() {
                let currentQuantity = parseInt(quantityNumber.textContent);
                if (currentQuantity < 10) {
                    updateCart(cartId, currentQuantity + 1);
                }
            });
        });


        // Toggle heart icon color on click
        document.querySelectorAll('.fa-heart').forEach((icon) => {
            icon.addEventListener('click', function() {
                // Toggle between 'fa-regular' and 'fa-solid'
                if (this.classList.contains('fa-regular')) {
                    this.classList.remove('fa-regular');
                    this.classList.add('fa-solid');
                } else {
                    this.classList.remove('fa-solid');
                    this.classList.add('fa-regular');
                }
            });
        });

        // Select All Functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="check[]"]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        // Submit Form and Delete Selected Items
        document.getElementById('cart-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Check if the delete_selected button was pressed
            if (document.querySelector('[name="delete_selected"]')) {
                var selectedCheckboxes = document.querySelectorAll('input[name="check[]"]:checked');

                if (selectedCheckboxes.length > 0) {
                    this.submit(); // Submit the form if checkboxes are selected
                } else {
                    alert("Please select items to delete.");
                }
            }
        });

        // Toggle payment methods dropdown
        document.getElementById('payment-btn').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default action
            const paymentOption = document.getElementById('payment_option');
            const summary = document.getElementById('summary');
            if (paymentOption.style.display === 'flex') {
                paymentOption.style.display = 'none';
                summary.style.height = '25vw';
            } else {
                paymentOption.style.display = 'flex';
                summary.style.height = '38vw';
            }
        });

        var cash_delivery = document.getElementById('cash');
        const process = document.getElementById('process_form');
        cash_delivery.addEventListener('click', function(event) {
            process.style.display = "block";
            process.style.position ="absolute";
        });

        // // Select all payment method buttons (cash, esewa, khalti)
        // var paymentButtons = document.querySelectorAll('#cash, #esewa, #khalti, #eBanking');

        // // Check Item Select or not (payment button)
        // paymentButtons.forEach(function(button) {
        //     button.addEventListener('click', function(event) {
        //         // var checkboxes = document.querySelectorAll('input[name="check[]"]:checked');

        //         // if (checkboxes.length > 0) {
        //         // If at least one item is checked, show the payment process div
        //         document.getElementById('process').style.display = 'block';
        //         event.preventDefault(); // Prevent the default action
        //         // } else {
        //         // If no items are checked, show an alert
        //         alert("Please select an item.");
        //         event.preventDefault(); // Prevent the default action
        //         // }
        //     });
        // });

        // }

        //Get Current Location 
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                document.getElementById("state").innerText = "Geolocation is not supported.";
            }
        }

        function showPosition(position) {
            let lat = position.coords.latitude;
            let lon = position.coords.longitude;

            // Use a reverse geocoding API (Example: OpenStreetMap)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                .then(response => response.json())
                .then(data => {
                    let state = data.address.state || "Unknown";
                    document.getElementById("state").innerText = state;
                })
                .catch(error => console.error("Error fetching location data:", error));
        }

        function showError(error) {
            document.getElementById("state").innerText = "Unable to retrieve location.";
        }

        getLocation(); // Call function on page load

        document.getElementById("googleMap").addEventListener('click', function() {
            window.open("https://www.google.com/maps", "_blank");
        });

        //Cart Empty Managemant
        document.addEventListener("DOMContentLoaded", function() {
            // Function to check if the cart has items
            function updateCartSummaryVisibility() {
                const cartItems = document.querySelectorAll(".product-card"); // Assuming each product has the class "product-card"
                const summaryDiv = document.getElementById("summary");

                if (cartItems.length === 0) {
                    summaryDiv.style.display = "none"; // Hide if no items in cart
                } else {
                    summaryDiv.style.display = "block"; // Show if there are items in the cart
                }
            }

            // Call the function on page load to check if cart has items
            updateCartSummaryVisibility();

            // If you have AJAX or actions where items are added/removed, call the function again
            document.querySelectorAll(".decrement, .increment").forEach(button => {
                button.addEventListener("click", updateCartSummaryVisibility);
            });

            // On form submission or AJAX request to delete selected items
            document.getElementById("cart-form").addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent form from submitting normally

                // Perform the deletion logic (e.g., via AJAX or form submission)

                // After deletion, check cart status and update summary visibility
                updateCartSummaryVisibility();
            });

        });
    </script>

</body>

</html>