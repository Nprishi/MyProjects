<?php
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include 'connect.php';
  // Get user input
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Validate input fields
  if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Please Enter Your Email And Password.";
  } elseif (empty($email)) {
    $_SESSION['login_error'] = "Please Enter Your Email";
  } elseif (empty($password)) {
    $_SESSION['login_error'] = "Please Enter Your Password";
  } else {
    // Prepare and execute the SQL query
    $sql = "SELECT * FROM `registration` WHERE email = '$email' AND pass = '$password'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
      // User exists, set session variables
      $_SESSION['user_id'] = $user['id']; // Store the user's ID in the session
      $_SESSION['user_name'] = $user['firstName']; // Store the user's name in the session

      // Redirect to the dashboard or protected page
      header("Location: index.php");
      exit();
    } else {
      // Login failed
      $_SESSION['login_error'] = "Your Username and Password is incorrect.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Now - Login</title>
  <link rel="stylesheet" href="stylesheet/styles.css">
  <link rel="stylesheet" href="stylesheet/contact.css">
  <style>
    .error-message {
      color: red;
      text-align: center;
      padding-top: 1vw;
      opacity: 1;
      transition: opacity 1s ease-in-out;
    }

    .register {
      color: blue;
      text-align: center;
      padding-top: 1vw;
      opacity: 1;
      transition: opacity 1s ease-in-out;
    }

    .fade-out {
      opacity: 0;
    }
  </style>
</head>

<body>
  
  <?php
  if (isset($_SESSION['message'])) {
    echo '<h5 class="register">' . $_SESSION['message'] . '</h5>';
    unset($_SESSION['message']);
  }
  ?>

  <section class="contact-us">
    <div class="all-contact">
      <div class="image-contact">
        <img src="images/contact.jpg" alt="">
      </div>
      <form action="?" method="POST">
        <h1 style="width: 100%; text-align: center;">Log in</h1>
        <?php
        if (isset($_SESSION['message'])) {
          echo '<h3 class="register">' . $_SESSION['message'] . '</h3>';
          unset($_SESSION['message']);
        }
        if (isset($_SESSION['login_error'])) {
          echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
          unset($_SESSION['login_error']);
        }
        ?>
        <input type="email" placeholder="Enter Your Email" name="email">
        <input type="password" placeholder="Enter Your Password" name="password">
        <a href="signup.php" class="already">Create a new account</a>
        <button class="contact-btn">Log in</button>
      </form>
    </div>
  </section>

  <footer>
    <div class="social-media">
    </div>
  </footer>

  <script>
    // Fade out the registration message after 2 second
    setTimeout(function() {
      var messageElement = document.querySelector('.register');
      if (messageElement) {
        messageElement.classList.add('fade-out');

        // Completely remove the message after the fade-out transition
        setTimeout(function() {
          messageElement.style.display = 'none';
        }, 1000); // Wait for the fade-out transition to complete
      }
    }, 2000); // Initial 1-second delay before starting the fade

    // Fade out the error message after 3 seconds
    setTimeout(function() {
      var errorMessageElement = document.querySelector('.error-message');
      if (errorMessageElement) {
        errorMessageElement.classList.add('fade-out');

        // Completely remove the error message after the fade-out transition
        setTimeout(function() {
          errorMessageElement.style.display = 'none';
        }, 1000); // Wait for the fade-out transition to complete
      }
    }, 3000); // Initial 3-second delay before starting the fade
  </script>
</body>

</html>