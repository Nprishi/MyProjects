<?php

session_start();
include 'connect.php';  // Include your database connection

// Assume user_id is being retrieved from session or request
 $user_id = $_SESSION['user_id']; // or $_GET['user_id'] if passed in URL

if ($user_id) {
    // Prepare and execute the query to check for the user ID
    $stmt = $conn->prepare("SELECT * FROM registration WHERE id = ?");
    $stmt->bind_param("i", $user_id); // assuming id is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user ID exists
    if ($result->num_rows > 0) {
        // User ID exists, redirect to cart.php
    } 
}
else {
    // User ID does not exists, redirect to login.php
    session_destroy();
    session_unset();  // Unset all session variables
    header("Location: login.php");
    exit();
}

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

// Fetch all items from the carts table
$sql = "SELECT * FROM shop_items"; // Assuming your table name is 'carts'
$SQL = "SELECT * FROM carts"; // Assuming your table name is 'carts'
$result = $conn->query($sql);
$result = $conn->query($SQL);
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

        .fa-trash {
            margin-right: 0.5vw;
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

        .product-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .product-image {
            width: 10vw;
            height: 10vw;
            object-fit: cover;
            margin: 0 2vw;
            border-radius: 0.5vw;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-size: 1.8vw;
            font-weight: bold;
        }

        .product-description {
            font-size: 1.4vw;
            color: #666;
        }

        .product-price {
            text-align: right;
            margin-left: 2vw;
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
                <a class="two-nav-btn order" href="#"><i class="fa-solid fa-store"></i>Order</a>
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
                <?php
                if ($result->num_rows > 0) {
                    // Loop through each product in the cart and display it
                    while ($row = $result->fetch_assoc()) {
                        echo '
            <div class="product-card">
             <input type="checkbox" name="check[]" value="' . $row['id'] . '">
                <div class="product-info">
                    <img src="' . $row['product_img'] . '" alt="Product Image" class="product-image">
                    <div class="product-details">
                        <h3 class="product-name">' . $row['product_name'] . '</h3>
                        <p class="product-description">Product Description Here</p>
                    </div>
                </div>
                <div class="product-price">
                    <span class="current-price">NPR ' . $row['product_price'] . '</span>
                </div>

                <div class="quantity">
                    <button type="button" class="decrement">-</button>
                    <span class="quantity-number">' . $row['quantity'] . '</span>
                    <button type="button" class="increment">+</button>
                </div>

                <div  class="fa-icon">
                <i class="fa-regular fa-heart"></i>
                <a href="cart.php?delete_id=' . $row['id'] . '" class="delete-icon"><i class="fa-solid fa-trash"></i></a>
                </div>
    
            </div>';
                    }
                } else {
                    echo '<p class="empty_product">No products in the cart.</p>';
                }
                ?>
            </form>
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
        </div>

        <!-- Receiver Details -->
        <section class="form_section" id="process_form">
            <div class="order-form">
                <h4>Enter Receiver Details</h4>
                <form action="?" method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="address">Address:</label>
                    <input type="address" id="address" name="address" required>

                    <label for="number">Mobile Number:</label>
                    <input type="number" name="number" id="'number" required>

                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1" required>
                </form>
            </div>
            <section class="payment" id="payment_option">
                <div class="cash_delivery">
                    <button class="submit" name="cash" id="cash">Cash on Delivery</button>
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
                        display: block;
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
                        display: block;
                        justify-content: space-around;
                        align-items: center;
                        width: 50vw;
                        margin-top: 1vw;
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
        document.querySelectorAll('.increment').forEach((button, index) => {
            button.addEventListener('click', function() {
                const quantityElement = document.querySelectorAll('.quantity-number')[index];
                let quantity = parseInt(quantityElement.textContent);
                if (quantity < 10) {
                    quantity++;
                    quantityElement.textContent = quantity;
                }
            });
        });

        document.querySelectorAll('.decrement').forEach((button, index) => {
            button.addEventListener('click', function() {
                const quantityElement = document.querySelectorAll('.quantity-number')[index];
                let quantity = parseInt(quantityElement.textContent);
                if (quantity > 1) {
                    quantity--;
                    quantityElement.textContent = quantity;
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

        // Select all checkboxes when the 'Select All' checkbox is clicked
        document.getElementById('selectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="check[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Toggle payment methods dropdown when "Place Order" button is clicked
        document.getElementById('payment-btn').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('input[name="check[]"]:checked');
            if (checkboxes.length > 0) {
                // If at least one item is checked, show the payment process div
                document.getElementById('process_form').style.display = 'block';
                document.getElementById('summary').style.display = 'none';
                event.preventDefault(); // Prevent the default action
            } else {
                // If no items are checked, show an alert
                alert("Please select an item.");
                event.preventDefault(); // Prevent the default action
            }
        });

        // Select all payment method buttons (cash, esewa, khalti)
        var paymentButtons = document.querySelectorAll('#cash, #esewa, #khalti, #eBanking');

        // Check Item Select or not (payment button)
        paymentButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                var checkboxes = document.querySelectorAll('input[name="check[]"]:checked');

                if (checkboxes.length > 0) {
                    // If at least one item is checked, show the payment process div
                    document.getElementById('process').style.display = 'block';
                    event.preventDefault(); // Prevent the default action
                } else {
                    // If no items are checked, show an alert
                    alert("Please select an item.");
                    event.preventDefault(); // Prevent the default action
                }
            });
        });
    </script>

</body>

</html>