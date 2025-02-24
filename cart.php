    <?php

    session_start();

    include 'connect.php';  // Include your database connection

    // Handle deleting selected items
    if (isset($_POST['delete_selected']) && !empty($_POST['check'])) {
        // Ensure `$_POST['check']` is treated as an array
        $selected_items = (array) $_POST['check'];

        // Sanitize and delete the selected items
        $ids_to_delete = implode(',', array_map('intval', $selected_items));

        // Delete the selected items
        $delete_sql = "DELETE FROM carts WHERE id IN ($ids_to_delete)";
        if ($conn->query($delete_sql) === TRUE) {
            header("Location: cart.php"); // Reload the page to reflect the changes
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }

    // Handle single item deletion
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $delete_sql = "DELETE FROM carts WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: cart.php"); // Reload the page to reflect the changes
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Handel Form Submission 
    if (isset($_POST['submit_order'])) {
        // Capture user and form details
        $user_name = $conn->real_escape_string($_POST['user_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $mobile_number = $conn->real_escape_string($_POST['mobile_number']);
        $payment_method = $conn->real_escape_string($_POST['payment_method']);

        // Fetch cart items from the database for the logged-in user
        $cart_items_sql = "SELECT c.*, p.item_name, p.product_description, p.item_price, p.item_image 
                           FROM carts c 
                           INNER JOIN products p ON c.product_id = p.id 
                           WHERE c.customer_id = ?";
        $stmt = $conn->prepare($cart_items_sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $cart_items_result = $stmt->get_result();

        $total_amount = 0;

        while ($item = $cart_items_result->fetch_assoc()) {
            $total_amount += $item['item_price'] * $item['quantity'];

            $product_id = $item['product_id'];
            $product_name = $conn->real_escape_string($item['item_name']);
            $product_description = $conn->real_escape_string($item['product_description']);
            $product_price = $item['item_price'];
            $quantity = $item['quantity'];
            $product_img = $item['item_image'];

            $sql = "INSERT INTO myorders (
                        user_name, email, address, mobile_number, 
                        product_id, product_name, product_description, product_price, quantity, product_img, 
                        total_amount, payment_method, order_status, order_date
                    ) VALUES (
                        '$user_name', '$email', '$address', '$mobile_number', 
                        '$product_id', '$product_name', '$product_description', '$product_price', '$quantity', '$product_img',
                        '$total_amount', '$payment_method', 'Pending', NOW()
                    )";

            if ($conn->query($sql) !== TRUE) {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }

        $stmt->close();

        // Clear cart after successful order
        $clear_cart_sql = "DELETE FROM carts WHERE customer_id = ?";
        $stmt = $conn->prepare($clear_cart_sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Order placed successfully!'); window.location.href='cart.php';</script>";
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
                    <a class="two-nav-btn profile" href="#">Profile</a>
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
                    <a class="two-nav-btn" href="signup.php">signup</a>
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()):
                        ?>
                            <div class="product-card">
                                <input type="checkbox" name="check[]" value="<?= $row['id']; ?>">
                                <div class="product-info">
                                    <img src="<?= $row['product_img']; ?>" alt="Product Image" class="product-image">
                                    <div class="product-details">
                                        <h3 class="product-name"><?= $row['product_name']; ?></h3>
                                        <p class="product-description"><?= $row['product_description']; ?></p>
                                    </div>
                                </div>
                                <div class="product-price">
                                    <span class="current-price">NPR <?= number_format($row['total_price'], 2); ?></span>
                                </div>
                                <div class="quantity">
                                    <button type="button" class="decrement" data-cart-id="<?= $row['id']; ?>">-</button>
                                    <span class="quantity-number"><?= $row['quantity']; ?></span>
                                    <button type="button" class="increment" data-cart-id="<?= $row['id']; ?>">+</button>
                                </div>

                                <div class="fa-icon"
                                    style="display: flex;
                        height: 3vw;
                        width: 6vw;
                        position: absolute;
                        right: 40vw;
                        top: 13vw;">
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
                </form>
                <style>
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

            <div class="right_side" id="summary">
                <div class="right_side_header">
                    <h3><span>Location</span></h3>
                    <p><i class="fa-solid fa-location-dot"></i> Your Location.....</p>
                </div>

                <div class="summary">
                    <h4>Order Details</h4>
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
                            height: 15vw;
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
                            background-color: black;
                            color: #fff;
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
                    <form action="?" method="POST">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="user_name" placeholder="Enter your full name" required>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>

                        <label for="address">Address:</label>
                        <input id="address" name="address" placeholder="Enter your full address" required></input>

                        <label for="number">Mobile Number:</label>
                        <input type="tel" name="mobile_number" id="number" pattern="[0-9]{10}" placeholder="e.g., 9800000000" required>
                        <button type="submit" name="submit_order">Submit</button>
                    </form>
                </div>
            </section>
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
                bottom: -13.8vw;
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
                        // alert('Quantity must be between 1 and 10.');
                        return;
                    }

                    // Send AJAX request to cart_update.php
                    fetch('cart_update.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `cart_id=${cartId}&quantity=${newQuantity}`
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                // Update quantity and price on the frontend
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
                    } else {
                        // alert('Minimum quantity is 1.');
                    }
                });

                incrementButton.addEventListener('click', function() {
                    let currentQuantity = parseInt(quantityNumber.textContent);
                    if (currentQuantity < 10) {
                        updateCart(cartId, currentQuantity + 1);
                    } else {
                        // alert('Maximum quantity is 10.');
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

            // Select all checkboxes when the 'Select All' Checkbox is clicked
            document.getElementById('selectAll').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('input[name="check[]"]');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            });

            // Toggle payment methods dropdown
            document.getElementById('payment-btn').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default action
                const paymentOption = document.getElementById('payment_option');
                const summary = document.getElementById('summary');
                if (paymentOption.style.display === 'flex') {
                    paymentOption.style.display = 'none';
                    summary.style.height = '15vw';
                } else {
                    paymentOption.style.display = 'flex';
                    summary.style.height = '28vw';
                }
            });

            var cash_delivery = document.getElementById('cash');
            const summary = document.getElementById('process_form');
            cash_delivery.addEventListener('click', function(event) {
                summary.style.display = "block";
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
        </script>

    </body>

    </html>