<?php
session_start();
include 'connect.php';

// Handle AJAX validation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_validate'])) {
    $response = [];
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');

    // Field validation logic
    switch ($field) {
        case 'firstName':
        case 'lastName':
            if (empty($value) || !preg_match('/^[a-zA-Z]+$/', $value)) {
                $response['error'] = "Please enter a valid $field.";
            }
            break;

        case 'email':
            if (empty($value) || !filter_var($value, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $value)) {
                $response['error'] = "Please enter a valid Gmail address.";
            } else {
                // Check if the email is already registered
                $stmt = $conn->prepare("SELECT email FROM customer WHERE email = ?");
                $stmt->bind_param("s", $value);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $response['error'] = "This email is already registered.";
                }
                $stmt->close();
            }
            break;

        case 'pass':
            if (empty($value) || !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value)) {
                $response['error'] = "Password must meet the required complexity.";
            }
            break;

        case 'phoneNumber':
            if (empty($value) || !preg_match('/^[0-9]{10}$/', $value)) {
                $response['error'] = "Please enter a valid 10-digit phone number.";
            }
            break;

        default:
            $response['error'] = "Invalid field.";
    }

    echo json_encode($response);
    exit();
}

// Handle standard form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_validate'])) {
    // Collect form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = md5($_POST['pass']);
    $phoneNumber = trim($_POST['phoneNumber']);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO customer (firstName, lastName, email, pass, phoneNumber) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $phoneNumber);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Registration successful!";
        header("Location: login.php"); // Redirect to the login page
        exit();
    } else {
        $_SESSION['registration_error'] = "Database error: " . $stmt->error;
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now</title>
    <link rel="stylesheet" href="stylesheet/styles.css">
    <link rel="stylesheet" href="stylesheet/contact.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <header></header>
    <section class="contact-us">
        <div class="all-contact">
            <div class="image-contact">
                <img src="images/contact.jpg" alt="">
            </div>
            <form action="?" method="POST">
                <h1>Sign up Here</h1>
                <p>Create an account here</p>
                <input type="text" placeholder="First Name" name="firstName">
                <input type="text" placeholder="Last Name" name="lastName">
                <input type="email" placeholder="Enter Your Email" name="email">
                <input type="password" placeholder="Enter Password" name="pass">
                <input type="number" placeholder="Enter Your Number" name="phoneNumber">
                <a href="login.php" class="already">Already Have an Account</a>
                <button type="submit" class="contact-btn">Sign Up</button>
            </form>


        </div>
    </section>
    <footer>
        <div class="social-media"></div>
    </footer>

    <script>
        $(document).ready(function() {
            // Function to validate a single field
            function validateField(field, value) {
                return $.ajax({
                    url: 'signup.php',
                    type: 'POST',
                    data: {
                        ajax_validate: true,
                        field: field,
                        value: value
                    }
                });
            }

            // Form submission handling
            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent form submission
                let hasError = false;

                // Get all input fields
                const fields = ['firstName', 'lastName', 'email', 'pass', 'phoneNumber'];
                const promises = [];

                // Validate each field
                fields.forEach((field) => {
                    const input = $('input[name="' + field + '"]');
                    const value = input.val();
                    const errorSpan = input.next('.error-message');

                    // Perform AJAX validation for each field
                    const promise = validateField(field, value).then((response) => {
                        const res = JSON.parse(response);

                        if (res.error) {
                            hasError = true;
                            if (!errorSpan.length) {
                                $('<span class="error-message" style="color: red;">' + res.error + '</span>').insertAfter(input);
                            } else {
                                errorSpan.text(res.error);
                            }
                        } else {
                            errorSpan.remove();
                        }
                    });

                    promises.push(promise);
                });

                // Wait for all validations to complete
                Promise.all(promises).then(() => {
                    if (!hasError) {
                        // Submit the form if no errors
                        e.currentTarget.submit();
                    }
                });
            });
        });
    </script>

</body>

</html>