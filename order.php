<?php
session_start();
// Database connection
include 'connect.php';

// // Assume user_id is being retrieved from session or request
// $user_id = $_SESSION['user_id']; // or $_GET['user_id'] if passed in URL

// if ($user_id) {
//     // Prepare and execute the query to check for the user ID
//     $stmt = $conn->prepare("SELECT * FROM registration WHERE id = ?");
//     $stmt->bind_param("i", $user_id); // assuming id is an integer
//     $stmt->execute();
//     $result = $stmt->get_result();

//     // Check if user ID exists
//     if ($result->num_rows > 0) {
//         // User ID exists, redirect to cart.php
//     } else {
//         // User ID does not exists, redirect to login.php
//         header("Location: login.php");
//         exit();
//     }
// }

// Fetch all items from the database
$query = "SELECT * FROM myOrders";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now</title>
    <link rel="stylesheet" href="stylesheet/carousel.css">
    <link rel="stylesheet" href="stylesheet/styles.css">
    <link rel="stylesheet" href="stylesheet/shop.css">
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <style>
        .logo h3 {
            font-weight: bold;
            font-size: 1.51vw;
        }

        .fa-cart-shopping {
            border: none;
            font-size: 2vw;
            color: rgb(57, 5, 105);
        }

        .fa-star {
            border: none;
            margin-bottom: 2vw;

        }

        .all-items {
            margin-bottom: 6vw;
        }

        .shop-product-images {
            width: 15vw;
            height: 20vw;
        }

        .add_items {
            width: 15vw;
        }

        /* .search-input {
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
        } */
    </style>

</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <h3>Gadget<span>4</span>U</h3>
        </div>

        <nav>
            <li class="nav-list"><a href="index.php" class="nav-anchor">HOME</a></li>
            <li class="nav-list"><a href="about.php" class="nav-anchor">ABOUT</a></li>
            <li class="nav-list"><a href="shop.php" class="nav-anchor">SHOP</a></li>
            <li class="nav-list"><a href="contact.php" class=" mainbtn nav-anchor ">CONTACT</a></li>
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

    <section class="first-container">
        <div class="oreder_container">
            <?php
            // Check if there are items
            if ($result->num_rows > 0) {
                // Start HTML output
                echo '<div class="item-container">';

                // Loop through each item and display in a card
                while ($row = $result->fetch_assoc()) {
                    // Display item card
                    echo '<div class="item-card">';
                    echo $row['order_image'] . '" alt="' . htmlspecialchars($row['order_name']) . '" class="item-image">';
                    echo '<h3 class="item-name">' . htmlspecialchars($row['order_name']) . '</h3>';
                    echo '<p class="item-des">' . htmlspecialchars($row['order_des']) . '</p>';
                    echo '<p class="item-price">$' . number_format($row['order_price'], 2) . '</p>';
                    echo '<p class="item-qty">Quantity: ' . htmlspecialchars($row['order_qty']) . '</p>';
                    echo '</div>';
                }

                echo '</div>'; // Close item container
            } else {
                echo "No items found.";
            }
            ?>

            <!-- <div class="summary_container">
                <?php
                // Example: Display the order summary
                echo "<h1>Order Summary</h1>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                echo "<p><strong>Item:</strong> " . htmlspecialchars($item) . "</p>";
                echo "<p><strong>Quantity:</strong> " . htmlspecialchars($quantity) . "</p>";
                ?>
            </div> -->
        </div>
        </div>
    </section>

    <section class="second_container">
        <!-- <div class="right_side_header">
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
        </div> -->

        <!-- <div class="Paymnet Id/Password">
        <h1>Try Esewa Account</h1>
         <p>eSewa ID: 9806800001/2/3/4/5</p>
       <p> Password: Nepal@123 MPIN: 1122 (for application only)</p>
        <p>Token:123456</P>
        </div> -->
    </section>

    <style>
        body {
            min-height: 100vh;
            background-size: cover;
            font-family: 'Lato', sans-serif;
            color: rgba(116, 116, 116, 0.667);

        }

        .item-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            /* Center items */
            padding: 20px;
        }

        .item-card {
            width: 80vw;
            /* Card width */
            max-width: 300px;
            /* Optional: Max width for responsiveness */
            border: 1px solid #ccc;
            /* Border around the card */
            border-radius: 8px;
            /* Rounded corners */
            margin: 10px;
            /* Space between cards */
            padding: 15px;
            /* Inner padding */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            /* Shadow for card */
            text-align: center;
            /* Center text */
            background-color: #fff;
            /* Background color */
        }

        .item-image {
            width: 15vw;
            /* Image width */
            height: 15vw;
            /* Image height */
            max-width: 150px;
            /* Optional: Limit max size */
            max-height: 150px;
            /* Optional: Limit max size */
            object-fit: cover;
            /* Keep aspect ratio */
            border-radius: 4px;
            /* Rounded image corners */
        }

        .item-name {
            font-size: 1.5em;
            /* Title size */
            margin: 10px 0;
            /* Space around title */
        }

        .item-des {
            font-size: 1em;
            /* Description size */
            color: #555;
            /* Description color */
        }

        .item-price {
            font-size: 1.2em;
            /* Price size */
            color: #007BFF;
            /* Price color */
        }

        .item-qty {
            font-size: 1em;
            /* Quantity size */
            color: #333;
            /* Quantity color */
        }
    </style>

    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <body>
       
    </body>

</html>