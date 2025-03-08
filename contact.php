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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Retrieve form data
  $customer_id = $_SESSION['user_id'] ?? null; // Get logged-in user ID
  $first_name = trim($_POST['first_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $message = trim($_POST['message']);

  // Validate required fields
  if (empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
      die("All fields are required.");
  }

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      die("Invalid email format.");
  }

  // Check if customer exists in the database
  if ($customer_id) {
      $checkCustomer = $conn->prepare("SELECT customer_id FROM customer WHERE customer_id = ?");
      $checkCustomer->bind_param("i", $customer_id);
      $checkCustomer->execute();
      $checkCustomer->store_result();

      if ($checkCustomer->num_rows === 0) {
          die("Error: Customer does not exist.");
      }
  }

  // Insert data into the contact table
  $stmt = $conn->prepare("INSERT INTO contact (customer_id, first_name, last_name, email, message) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("issss", $customer_id, $first_name, $last_name, $email, $message);

  if ($stmt->execute()) {
      echo "<script>alert('Message sent successfully!'); window.location.href='contact.php';</script>";
  } else {
      echo "Error: " . $stmt->error;
  }

  // Close statement and connection
  $stmt->close();
  $conn->close();
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

      <form action="?" method="POST">
        <h1>Get in Touch</h1>
        <p>24/7 we will answer your questions and problems</p>

        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Enter Your Email" required>
        <textarea name="message" placeholder="Describe your issue" required></textarea>

        <button type="submit" name="submit" class="contact-btn">Send</button>
      </form>

    </div>

  </section>

</body>

</html>