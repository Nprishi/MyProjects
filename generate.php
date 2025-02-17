<?php
// Set the content type to image/jpeg (or image/png if you use another service)
header('Content-Type: image/jpeg');

// Define the image size
$width = 1270;  // Width in pixels
$height = 200; // Height in pixels

// Construct the URL for the random image from Lorem Picsum
$imageUrl ="https://loremflickr.com/{$width}/{$height}/clothes";

// Fetch the image from the URL
$imageContent = file_get_contents($imageUrl);

// Check if the image content was retrieved successfully
if ($imageContent === false) {
    http_response_code(500);
    echo "Error fetching image.";
    exit;
}

// Output the image content
echo $imageContent;
?>
