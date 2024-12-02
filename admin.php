<?php
session_start();

// Database connection
include 'connect.php';

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
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $item_price = mysqli_real_escape_string($conn, $_POST['item_price']);
    $item_image = '';

    // Check if file was uploaded
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

                // Display success message
                echo "<script>showMessage('Item added successfully!', 'success');</script>";
            } else {
                echo "<script>showMessage('Error uploading the file.', 'error');</script>";
            }
        } else {
            echo "<script>showMessage('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.', 'error');</script>";
        }
    } // Check if image URL is provided
    elseif (!empty($_POST['item_image_url'])) {
        // Escape and store the image URL
        $item_image = mysqli_real_escape_string($conn, $_POST['item_image_url']);

        // Insert item data into the database
        $stmt = $conn->prepare("INSERT INTO shop_items (item_name, item_price, item_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $item_name, $item_price, $item_image);

        // Execute and check for errors
        if ($stmt->execute()) {
            echo "<script>showMessage('Item added successfully with URL!', 'success');</script>";
        } else {
            echo "<script>showMessage('Error: Could not insert item.', 'error');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>showMessage('Error: No image URL provided.', 'error');</script>";
    }
}

// Fetch all items from the database
$query = "SELECT * FROM shop_items";
$result = $conn->query($query);

// Handle single item deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM shop_items WHERE id = ?";
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
        header("Location: cart.php"); // Reload the page to reflect the changes
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
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
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-container,
        .add-item-container {
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
            width: 80vw;
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            padding: 4vw 0;
            box-sizing: border-box;
            box-shadow: 0.1vw 0.1vw 0.5vw 0.1vw #ccc;
        }

        .Descriptions {
            display: block;
            position: absolute;
            top: 1vw;
            color: green;
        }

        .addItems {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .item-button {
            width: 10vw;
        }

        .addItems img {
            width: 15vw;
            height: 15vw;
            opacity: 85%;
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
    <?php if (isset($_SESSION['id'])): ?>
        <div class="admin-logout">
            <a class="logout" href="adminout.php">Logout</a>
            <i class="fa-solid fa-right-from-bracket exit"></i>
        </div>
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

            .admin-logout {
                display: flex;
                border: 1px solid red;
                width: 20%;
            }

            .logout {
                position: absolute;
                top: 2vw;
                right: 5vw;
                text-decoration: none;
                font-size: 1.5vw;
            }

            .logout:hover {
                color: red;
            }

            .exit {
                position: absolute;
                top: 2.2vw;
                right: 3vw;
                text-decoration: none;
                font-size: 1.3vw;
            }
        </style>
        </div>
    <?php endif; ?>
    <!-- Choose Options -->
    <div class="chooseItems" id="chooseItems">
        <h1 class="Descriptions">Admin Panel</h1>
        <div class="addItems">
            <img src="images/addItem.png" alt="Add Items">
            <button class="item-button" onclick="showAddItemForm()">Add Items</button>
        </div>

        <div class="addItems">
            <img src="images/removeItem.png" alt="Remove Items">
            <button class="item-button" onclick="showRemoveItemForm()">Remove Items</button>
        </div>

        <div class="addItems">
            <img src="images/settings.png" alt="Add Items">
            <button class="item-button" onclick="showSettings()">Settings</button>
        </div>
    </div>

    <!-- Add Item Form (Initially Hidden) -->
    <div id="message" class="message"></div> <!-- Success/Error message div -->
    <div class="add-item-container" id="addItemForm">
        <h2>Add New Item</h2>
        <form action="?" method="POST">
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
                <input type="url" name="item_image_url" id="item_image_url" placeholder="http://example.com/image.jpg"><br><br>
            </div>

            <!-- Item Name -->
            <label for="item_name">Item Name:</label>
            <input type="text" name="item_name" id="item_name" required><br><br>

            <!-- Item Price -->
            <label for="item_price">Item Price:</label>
            <input type="number" name="item_price" id="item_price" step="0.01" required><br><br>

            <!-- Submit Button -->
            <button type="submit">Add Item</button>
        </form>
    </div>

    <form action="?" method="POST">
        <div class="item-store" id="removeItemForm">
            <div class="selected">
                <!-- Add "Select All" checkbox -->
                <label><input type="checkbox" id="selectAll"> Select All</label><br><br>
                <!-- Add "Delete Selected" button -->
                <button type="submit" name="delete_selected" class="delete_all"><i class="fa-solid fa-trash"></i> Delete</button>
            </div>
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

                    // Display each item dynamically
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
                </div>
            </div>';
                }
            } else {
                // No items found in the database
                echo "<p>No items found in the store.</p>";
            }
            ?>
            <style>
                #removeItemForm {
                    display: none;
                    justify-content: space-around;
                    align-items: center;
                    width: 80vw;
                    height: auto;
                    margin-top: 1vw;
                    flex-direction: row;
                    flex-wrap: wrap;
                    background-color: #ccc;
                }

                .single-item {
                    margin: 2vw 2vw;
                }

                .single-item img {
                    width: 15vw;
                    height: 20vw;
                }

                .delete-icon:hover {
                    color: red;
                }
            </style>

        </div>
    </form>

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