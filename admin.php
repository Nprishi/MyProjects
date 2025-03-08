<?php

session_start();

// Database connection
include 'connect.php';
$customer_id = $_SESSION['customer_id'] ?? 0; // Ensure it's not empty

// Assume user_id is being retrieved from session or request
$user_id = $_SESSION['id']; // or $_GET['user_id'] if passed in URL

if ($user_id) {
    // Prepare and execute the query to check for the user ID
    $stmt = $conn->prepare("SELECT * FROM `admin-login` WHERE id = ?");
    $stmt->bind_param("i", $user_id); // assuming id is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user ID exists
    if ($result->num_rows > 0) {
        // User ID exists, redirect to cart.php
    }
} else {
    // User ID does not exists, redirect to login.php
    header("Location: adminout.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'connect.php'; // Include the database connection

    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $item_price = mysqli_real_escape_string($conn, $_POST['item_price']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category']); // Get category name from the form
    $item_image = '';

    //  Check if category exists; if not, insert it
    $query = "SELECT category_id FROM category WHERE category_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    if (!$category) {
        // Insert new category into the category table
        $insert_category = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
        $insert_category->bind_param("s", $category_name);
        if ($insert_category->execute()) {
            $category_id = $insert_category->insert_id; // Get the ID of the newly inserted category
        } else {
            echo "Error: Could not insert category.', error";
            exit();
        }

        $insert_category->close();
    } else {
        $category_id = $category['category_id']; // Fetch existing category_id
    }

    // Handle image upload or URL
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        // Handle image upload from local storage
        $target_dir = __DIR__ . "/images/"; // Absolute path for "images" directory
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["item_image"]["name"])); // Sanitize the file name
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type (only images allowed)
        $valid_types = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $valid_types)) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
                $item_image = "images/" . $file_name; // Store relative path for DB

                // Insert product data into the database
                $stmt = $conn->prepare("INSERT INTO products (item_name, product_description, item_price, item_image, category_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdsi", $item_name, $product_description, $item_price, $item_image, $category_id);

                if ($stmt->execute()) {
                    echo "Product added successfully!', success";
                    header("Location: admin.php"); // Reload the page to reflect the changes
                } else {
                    echo "Error: Could not insert product.', error";
                }
            } else {
                echo "Error uploading the file.', error";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.', error";
        }
    }

    // Check if image URL is provided
    elseif (!empty($_POST['item_image_url'])) {
        $item_image = mysqli_real_escape_string($conn, $_POST['item_image_url']);

        // Insert product data into the database
        $stmt = $conn->prepare("INSERT INTO products (item_name, product_description, item_price, item_image, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsi", $item_name, $product_description, $item_price, $item_image, $category_id);

        if ($stmt->execute()) {
            echo "Product added successfully with URL!', success";
            header("Location: admin.php"); // Reload the page to reflect the changes
        } else {
            echo "Error: Could not insert product.', error";
        }
    } else {
        echo "Error: No image or URL provided.', error";
    }

    $stmt->close();
}

// Fetch all items from the database
$query = "SELECT * FROM products";
$result = $conn->query($query);

$sqls = "SELECT * FROM myorder";
$results = $conn->query($sqls);

// Handle single item deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: admin.php"); // Reload the page to reflect the changes
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
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
        header("Location: admin.php"); // Reload the page to reflect the changes
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// // Close the database connection
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <title>Admin Panel - Add Item</title>

    <style>
        /* Basic styling for the login and add item form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
            overflow: scroll;
        }

        .container {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            /* border: 1px solid green; */
            overflow: hidden;
        }

        .login-container {
            display: block;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            width: 300px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"],
        input[type="url"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 10vw;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        /* Choose Items */
        .chooseItems {
            width: 20vw;
            height: 43vw;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
            padding: 4vw 0;
            box-sizing: border-box;
            box-shadow: 0.1vw 0.1vw 0.5vw 0.1vw #ccc;
            background-color: #1D1B1B;
            position: absolute;
            top: 0;
            left: 0;
        }

        .Descriptions {
            margin-top: -2vw;
            display: block;
            color: #fff;
            font-size: 2vw;
        }

        .addItems {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: start;
            margin: 1vw;
            /* border: 1px solid red; */
        }

        .allItems {
            display: flex;
            flex-direction: column;
        }

        .item-button {
            width: 10vw;
            padding: 0 1vw;
            text-align: start;
            background-color: #1D1B1B;
            font-size: 1.3vw;
        }

        .item-button:hover {
            background-color: #1D1B1B;
            color: grey;
        }

        .addItems a {
            text-decoration: none;
            text-align: start;
            color: #fff;
            margin: 1vw;
        }

        .addItems a:hover {
            color: grey;
        }

        .Items {
            margin: 1vw;
        }

        .Items i {
            color: #fff;
            font-size: 1.5vw;
        }

        .addItems img {
            width: 2vw;
            height: 2vw;
            opacity: 85%;
            margin-right: 1vw;
        }

        .addItems img:hover {
            transform: scale(1.005);
            opacity: 100%;
        }

        /* Hide the add item form initially */
        .add-item-container {
            display: none;
            padding: 4vw;
            width: 72vw;
        }

        /* Success message styling */
        .message {
            display: block;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: green;
        }

        .message.success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .message.error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>

</head>

<body>

    <nav class="navbar">
        <div class="logo">
            <img src="" alt="">
            <a href="#">Gadget4U</a>
        </div>

        <div class="admin-profile">
            <img src="" alt="">
            <a href="#">ADMIN PROFILE</a>
        </div>
    </nav>

    <style>
        .navbar {
            /* border: 1px solid green; */
            display: flex;
            width: 78.4vw;
            margin-left: 19.5vw;
            justify-content: space-between;
            padding: 0.5vw;
            background-color: #fff;
            overflow: hidden;
            border-bottom: 1px solid grey;
        }

        .navbar a {
            text-decoration: none;
        }
    </style>

    <div class="container">
        <!-- Choose Options -->
        <div class="chooseItems" id="chooseItems">
            <h1 class="Descriptions">DASHBOARD</h1>

            <div class="addItems allItems">
                <div class="Items">
                    <i class="fa-solid fa-house"></i>
                    <a href="admin.php">Home</a>
                </div>
                <div class="Items">
                    <i class="fa-solid fa-signal"></i>
                    <a href="#">Transactions</a>
                </div>
                <div class="Items">
                    <i class="fa-solid fa-universal-access"></i>
                    <a href="#">Sales</a>
                </div>
                <div class="Items">
                    <i class="fa-regular fa-folder"></i>
                    <a href="#">Products</a>
                </div>

                <div class="addItems">
                    <i class="fa-solid fa-cart-plus"></i>
                    <button class="item-button" onclick="showAddItemForm()">Add Items</button>
                </div>

                <div class="addItems">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <button class="item-button" onclick="showRemoveItemForm()">Edit Items</button>
                </div>

                <div class="addItems">
                    <i class="fa-solid fa-gear"></i>
                    <button class="item-button" onclick="showSettings()">Settings</button>
                </div>

                <?php if (isset($_SESSION['id'])): ?>
                    <div class="admin-logout">
                        <a class="logout" href="adminout.php"
                            style="
                            text-decoration: none;
                            font-size: 1.5vw; margin:4vw 0 0 4vw;">
                            Logout
                        </a>
                    </div>
                <?php endif; ?>
                <style>
                    .admin-logout a:hover {
                        color: red;
                    }

                    .addItems i {
                        color: #fff;
                        font-size: 1.5vw;
                    }

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

                    .admin-logout {
                        display: flex;
                        /* border: 1px solid red; */
                        width: 20%;
                    }
                </style>

            </div>

        </div>

        <!-- Display after click -->
        <div class="Display">

            <!-- Add Item Form (Initially Hidden) -->
            <div class="add-item-container" id="addItemForm">
                <h2 class="add-title">Add New Item</h2>
                <form action="admin.php" method="POST">
                    <label for="category">Select Category</label><br>
                    <select id="category" name="category">
                        <option value="coats">Coats</option>
                        <option value="watches">Watches</option>
                        <option value="girls clothes">Girls Clothes</option>
                        <option value="boys clothes">Boys Clothes</option>
                        <option value="Others clothes">Others</option>
                    </select><br><br>
                    <label>Choose how to add an image:</label><br>
                    <input type="radio" id="upload_option" name="image_option" value="upload" onclick="toggleImageOption()" checked>
                    <label for="upload_option">Upload from storage</label><br>

                    <input type="radio" id="url_option" name="image_option" value="url" onclick="toggleImageOption()">
                    <label for="url_option">Use Image URL</label><br><br>

                    <!-- Image from Upload -->
                    <div id="upload_section">
                        <label for="item_image">Item Image (Upload from storage):</label>
                        <input type="file" name="item_image" id="item_image"><br><br>
                    </div>

                    <!-- Image from URL -->
                    <div id="url_section" style="display: none;">
                        <label for="item_image_url">Item Image URL:</label>
                        <input type="url" name="item_image_url" id="item_image_url" placeholder="http://example.com/image.jpg" style="padding:1.05vw;"><br><br>
                    </div>

                    <!-- Item Name -->
                    <label for="item_name">Item Name:</label>
                    <input type="text" name="item_name" id="item_name" required><br><br>

                    <!-- Item Description -->
                    <label for="item_description">Item Description:</label>
                    <input type="text" name="product_description" id="item_desc" required style="padding-bottom:4vw;"><br><br>

                    <!-- Item Price -->
                    <label for="item_price">Item Price:</label>
                    <input type="number" name="item_price" id="item_price" step="0.01" required><br><br>

                    <!-- Submit Button -->
                    <button type="submit">Add Item</button>
                </form>
            </div>

            <div class="item-store" id="removeItemForm">
                <form action="?" method="POST">
                    <div class="selected">
                        <!-- Add "Select All" checkbox -->
                        <label><input type="checkbox" id="selectAll"> Select All</label><br><br>
                        <!-- Add "Delete Selected" button -->
                        <button type="submit" name="delete_selected" class="delete_all"><i class="fa-solid fa-trash"></i> Delete</button>
                    </div>
                    <div class="myItems">
                        <?php
                        // Assuming $result contains the query results from the database
                        // Check if items exist
                        if ($result->num_rows > 0) {
                            // Loop through each row in the database result
                            while ($row = $result->fetch_assoc()) {

                                // Fetch data from each row
                                $item_image = $row['item_image'];
                                $item_name = $row['item_name'];
                                $item_price = $row['item_price'];
                                // Display each item dynamicallys
                                echo '
          <div class="single-item">
            <input type="checkbox" name="check[]" value="' . $row['id'] . '">
                <div class="item-image">
                    <img src="' . $item_image . '" alt="' . $item_name . '" class="store-product-images">
                </div>
                <div class="item-details">
                    <figcaption>' . $item_name . '</figcaption>
                    <p class="item-price">Price: NPR ' . number_format($item_price, 2) . '</p>
                </div>
                <div  class="fa-icon">
                <a href="admin.php?delete_id=' . $row['id'] . '" class="delete-icon"><i class="fa-solid fa-trash"></i></a>
                 <a href="admin.php?edit_id=' . $row['id'] . '" class="edit-icon"><i class="fa-solid fa-pen-to-square"></i></a>
                </div>
            </div>';
                            }
                        } else {
                            // No items found in the database
                            echo "<p>No items found in the store.</p>";
                        }
                        ?>
                    </div>
                    <style>
                        .Display {
                            width: 45vw;
                            height: auto;
                            padding: 0;
                            margin: 0 0 0 20vw;

                        }

                        .add-item-container {
                            padding-top: 0.1vw;
                            display: none;
                            background-color: white;
                            border-radius: 5px;
                            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
                            width: 36vw;
                            border-right: 2px solid grey;
                        }

                        #removeItemForm {
                            display: block;
                            justify-content: space-around;
                            align-items: center;
                            width: 45vw;
                            height: 40vw;
                            flex-wrap: wrap;
                            background-color: #ccc;
                            overflow: scroll;
                        }

                        .fa-icon {
                            display: flex;
                            justify-content: space-between;
                            /* border: 1px solid red; */
                            width: 15vw;
                        }

                        .selected {
                            display: flex;
                            justify-content: space-between;
                            background-color: #fff;
                            text-align: center;
                            align-items: center;
                        }

                        .delete_all {
                            padding: 0.5vw;
                            width: 7vw;
                            margin-right: 1.5vw;
                        }

                        .myItems {
                            display: flex;
                            height: 34.4vw;
                        }


                        .single-item {
                            margin: 2vw 2vw;
                            width: 25%;
                            height: 20vw;
                            display: flex;
                            flex-wrap: wrap;
                        }

                        .item-image img {
                            height: 10vw;
                        }


                        .delete-icon:hover {
                            color: red;
                        }
                    </style>
            </div>
            </form>
        </div>

        <!-- Order List -->
        <div class="order-list">
            <div class="order-list-header">
                <h2>Order List</h2>
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
                    </style>
                </section>


            </div>
        </div>

        <style>
            .order-list {
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: #fff;
                box-sizing: border-box;
                height: 40vw;
                width: 34vw;
                overflow: scroll;
            }

            .order-list-header {
                text-align: center;
                border-bottom: 1px solid #000;
                padding: 0;
                height: 4vw;
                margin-top: -1vw;
                border-bottom: 2px solid grey;

            }

            .order-list-header h2 {
                padding: 0;
            }
        </style>

    </div>

    <script>
        function showAddItemForm() {
            // Show the add item form
            var addItemForm = document.getElementById('addItemForm');
            addItemForm.style.display = 'block';
            removeItemForm.style.display = 'none';
        }

        function showRemoveItemForm() {
            // Logic to show remove item form can go here
            var removeItemForm = document.getElementById('removeItemForm');
            removeItemForm.style.display = 'flex';
            addItemForm.style.display = 'none';
        }

        function showSettings() {
            // Logic to show settings form can go here
            alert("Settings functionality to be implemented.");
        }


        // Choose image from URL or Storage

        function toggleImageOption() {
            var uploadSection = document.getElementById('upload_section');
            var urlSection = document.getElementById('url_section');

            if (document.getElementById('upload_option').checked) {
                uploadSection.style.display = 'block';
                urlSection.style.display = 'none';
            } else if (document.getElementById('url_option').checked) {
                uploadSection.style.display = 'none';
                urlSection.style.display = 'block';
            }
        }

        // Show message for 1 second, then hide it
        function showMessage(message, type) {
            var messageDiv = document.getElementById('message');
            messageDiv.innerHTML = message;
            messageDiv.className = 'message ' + type; // Add success or error class
            messageDiv.style.display = 'block'; // Show the message

            // Hide the message after 1 second
            setTimeout(function() {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        // Select all checkboxes when the 'Select All' checkbox is clicked
        document.getElementById('selectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="check[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>

</body>

</html>