<?php

session_start();

// Database connection
include 'connect.php';

// Function to display messages

function showMessage($message, $type)
{
    echo "<script>alert('{$message}');</script>"; // Basic alert for message display
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_name = mysqli_real_escape_string($conn, $_POST['search_name']);
    $search_price = mysqli_real_escape_string($conn, $_POST['search_price']);
    $search_image = '';

    // Check if file was uploaded
    if (isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0) {
        // Handle image upload from local storage
        $target_dir = __DIR__ . "/images/"; // Absolute path for "images" directory
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["search_image"]["name"])); // Sanitize the file name
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type (only images allowed)
        $valid_types = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $valid_types)) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["search_image"]["tmp_name"], $target_file)) {
                $search_image = "images/" . $file_name; // Store relative path for DB

                // Prepare to insert item data into the database
                $stmt = $conn->prepare("INSERT INTO search (search_name, search_price, search_image) VALUES (?, ?, ?)");
                $stmt->bind_param("sds", $search_name, $search_price, $search_image);

                // Execute and check for errors
                if ($stmt->execute()) {
                    showMessage('Item added successfully!', 'success');
                } else {
                    showMessage('Error: Could not insert item.', 'error');
                }
                $stmt->close();
            } else {
                showMessage('Error uploading the file.', 'error');
            }
        } else {
            showMessage('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.', 'error');
        }
    }
    // Check if image URL is provided
    elseif (!empty($_POST['search_image_url'])) {
        // Escape and store the image URL
        $search_image = mysqli_real_escape_string($conn, $_POST['search_image_url']);

        // Prepare to insert item data into the database
        $stmt = $conn->prepare("INSERT INTO search (search_name, search_price, search_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $search_name, $search_price, $search_image);

        // Execute and check for errors
        if ($stmt->execute()) {
            showMessage('Item added successfully with URL!', 'success');
        } else {
            showMessage('Error: Could not insert item.', 'error');
        }
        $stmt->close();
    } else {
        showMessage('Error: No image URL provided.', 'error');
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
    <title>Document</title>
</head>

<body>
    <h1>Add Item</h1>
    <form id="itemForm" action="?" method="POST">
        <label for="search_name">Item Name:</label>
        <input type="text" id="search_name" name="search_name" required>

        <label for="search_price">Item Price:</label>
        <input type="number" id="search_price" name="search_price" step="0.01" required>

        <div>
            <input type="radio" id="upload_option" name="image_option" value="upload" onclick="toggleImageOption()" checked>
            <label for="upload_option">Upload Image</label>
            <input type="radio" id="url_option" name="image_option" value="url" onclick="toggleImageOption()">
            <label for="url_option">Image URL</label>
        </div>

        <div id="upload_section">
            <label for="search_image">Choose Image:</label>
            <input type="file" id="search_image" name="search_image" accept="image/*" onchange="previewImage()">
        </div>

        <div id="url_section" style="display: none;">
            <label for="search_image_url">Enter Image URL:</label>
            <input type="url" id="search_image_url" name="search_image_url" placeholder="http://example.com/image.jpg" oninput="updatePreview()">
        </div>

        <div>
            <img id="image_preview" class="preview" alt="Image Preview">
        </div>

        <button type="submit">Add Item</button>
    </form>

    <script>
        // Toggle between uploading an image or entering a URL
        function toggleImageOption() {
            var uploadSection = document.getElementById('upload_section');
            var urlSection = document.getElementById('url_section');

            if (document.getElementById('upload_option').checked) {
                uploadSection.style.display = 'block';
                urlSection.style.display = 'none';
                document.getElementById('search_image_url').value = ''; // Clear URL input
            } else if (document.getElementById('url_option').checked) {
                uploadSection.style.display = 'none';
                urlSection.style.display = 'block';
                document.getElementById('search_image').value = ''; // Clear file input
            }
        }
    </script>

</body>

</html>