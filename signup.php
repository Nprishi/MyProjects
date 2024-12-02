<?php
session_start(); // Start the session at the beginning

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'connect.php';

    // Collect form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['pass']);
    $phoneNumber = trim($_POST['phoneNumber']);

    // If input field will be empty 
    if (empty($firstName)) {
        $_SESSION['registration_error'] = "Please enter your First Name.";
    } elseif (empty($lastName)) {
        $_SESSION['registration_error'] = "Please enter your Last Name.";
    } elseif (empty($email)) {
        $_SESSION['registration_error'] = "Please enter your Email.";
    } elseif (empty($password)) {
        $_SESSION['registration_error'] = "Please enter your Password.";
    } elseif (empty($phoneNumber)) {
        $_SESSION['registration_error'] = "Please enter your Phone Number.";
    } elseif (!preg_match('/^[a-z0-9._%+-]+@gmail\.com$/', $email)) {
        $_SESSION['registration_error'] = "Please enter a valid email addresss";
    } elseif (!preg_match('/^[0-9]{10}$/', $phoneNumber)) {
        // Check if phone number is valid (10 digits)
        $_SESSION['registration_error'] = "Please enter a valid 10-digit phone number.";
    } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phoneNumber)) {
        $_SESSION['registration_error'] = "Please fill all the details.";
    } else {
        // Prepare the SQL query
        $sql = "INSERT INTO registration (firstName, lastName, email, pass, phoneNumber) 
                VALUES ('$firstName', '$lastName', '$email', '$password', '$phoneNumber')";

        // Execute the query
        if (mysqli_query($conn, $sql)) {
            // Store the success message in a session variable
            $_SESSION['message'] = "Registration successful!";

            // Redirect to the login page
            header("Location: login.php");
            exit();
        } else {
            // Handle SQL error
            die("Error: " . mysqli_error($conn));
        }
    }
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

                <?php
                if (isset($_SESSION['registration_error'])) {
                    echo '<p class="error-message" style="color: red;">' . $_SESSION['registration_error'] . '</p>';
                    unset($_SESSION['registration_error']);
                }
                ?>

                <input type="text" placeholder="First Name" name="firstName">
                <input type="text" placeholder="Last Name" name="lastName">
                <input type="email" placeholder="Enter Your Email" name="email">
                <input type="password" placeholder="Enter Password" name="pass">
                <input type="number" placeholder="Enter Your Number" name="phoneNumber">
                <a href="login.php" class="already">Already Have an Account</a>
                <button class="contact-btn">Sign Up</button>
            </form>
        </div>
    </section>
    <footer>
        <div class="social-media"></div>
    </footer>

    <script>
        // Automatically hide error messages after 1 second
        const errorMessageElement = document.querySelector('.error-message');
        if (errorMessageElement) {
            setTimeout(() => {
                errorMessageElement.style.display = 'none'; // Hide after 1 second
            }, 3000);
        }
    </script>
</body>

</html>