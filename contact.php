<?php
session_start();
// Database connection
include 'connect.php';

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
  <title>Shop Now</title>
  <link rel="stylesheet" href="stylesheet/styles.css">
  <link rel="stylesheet" href="stylesheet/contact.css">
  <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
  <style>
    .fa-cart-shopping {
      border: none;
      font-size: 2vw;
      color: rgb(57, 5, 105);
    }

    .logo h3 {
      color: red;
    }

    .logo span {
      font-size: 2vw;
    }
  </style>
</head>

<body>
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


  <section class="contact-us">
    <div class="all-contact">
      <div class="image-contact">
        <img src="images/contact.jpg" alt="">
      </div>

      <form action="">
        <h1>Get in Touch</h1>
        <p>24/7 we will answer your question and problems</p>

        <input type="text" placeholder="First Name">
        <input type="text" placeholder="Last Name">
        <input type="email" placeholder="Enter Your Email">
        <textarea name="message" placeholder="Describe your issue"></textarea>
        <button class="contact-btn">Send</button>

      </form>

    </div>

  </section>

</body>

</html>