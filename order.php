<?php

session_start();
// Database connection
include 'connect.php';
$customer_id = $_SESSION['customer_id'] ?? 0; // Ensure it's not empty

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$profile = $_SESSION['user_name'] ?? 'Guest';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="stylesheet/styles.css">
    <title>Document</title>
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
            <li class="nav-list"><a href="contact.php" class=" mainbtn nav-anchor ">CONTACT</a></li>
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

    <section class="odSection">

        <div class="display-orders">
            <?php
            // Fetch all order items for the logged-in customer
            $sql = "SELECT o.id AS order_id, o.product_id, o.quantity, o.customer_id, o.payment_id, 
               o.total_amount, o.address, o.cart_id,
               p.item_name, p.item_image, p.product_description, p.item_price
        FROM myorder o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.customer_id = ?
        ORDER BY o.id DESC";  // Latest orders first

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Display all orders
            if ($result->num_rows > 0) {
                echo "<h2>My Orders</h2>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<tr>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
          </tr>";

                while ($row = $result->fetch_assoc()) {
                    $total_price = $row['item_price'] * $row['quantity'];
                    echo "<tr>";
                    echo "<td><img src='" . htmlspecialchars($row['item_image']) . "' alt='Product Image' width='50'></td>";
                    echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td>NPR " . number_format($row['item_price'], 2) . "</td>";
                    echo "<td>NPR " . number_format($total_price, 2) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No orders found.</p>";
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
            ?>
        </div>

        <form action="?" method="POST">
            <input type="hidden" name="id" value="<?= $order_id ?>">
        </form>
        <style>
            .display-orders {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                justify-content: center;
                width: 50vw;
            }
        </style>s
    </section>

</body>

</html>