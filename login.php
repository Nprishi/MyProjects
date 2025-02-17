<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Please enter your email and password.";
    header("Location: login.php");
    exit();
  }

  $stmt = $conn->prepare("SELECT id, firstName, pass FROM customer WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['pass'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['firstName'];

    header("Location: index.php");
    exit();
  } else {
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
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