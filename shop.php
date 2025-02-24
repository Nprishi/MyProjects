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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $product_id = $_POST['product_id'];
  $product_name = $_POST['product_name'];
  $product_price = $_POST['product_price'];
  $quantity = $_POST['quantity'];
  $product_img = $_POST['product_img'];

  if (!$customer_id) {
    header("Location: login.php");
    exit();
  }

  $_SESSION['user_name'] = $user['firstName']; // Store the user's name in the session
  $profile = $user['firstName'];
  // Check if the customer exists
  $check_customer = $conn->prepare("SELECT id FROM customer WHERE id = ?");
  $check_customer->bind_param("i", $customer_id);
  $check_customer->execute();
  $customer_result = $check_customer->get_result();

  if ($customer_result->num_rows === 0) {
    die("Invalid customer ID.");
  }

  // Check if the product is already in the cart
  $check_cart = $conn->prepare("SELECT * FROM carts WHERE product_id = ?");
  $check_cart->bind_param("i", $product_id);
  $check_cart->execute();
  $result = $check_cart->get_result();

  if ($result->num_rows > 0) {
    // Fetch the current quantity in the cart
    $row = $result->fetch_assoc();
    $current_quantity = $row['quantity'];
    $total_price = $row['product_price'];

    // Calculate the total price for the selected quantity
    $total_price = $product_price * $quantity;

    // Check if the total quantity will exceed 10
    if (($current_quantity + $quantity) <= 10) {
      // Update the quantity and total price in the cart
      $new_quantity = $current_quantity + $quantity;
      $new_total_price = $total_price * $new_quantity;
      $update_cart = $conn->prepare("UPDATE carts SET quantity = ?, product_price = ? WHERE product_id = ?");
      $update_cart->bind_param("idi", $new_quantity, $new_total_price, $product_id);
      $update_cart->execute();
    } else {
      // If the total quantity exceeds 10, set it to 10
      $new_quantity = 10;
      $new_total_price = $total_price * $new_quantity;

      $update_cart = $conn->prepare("UPDATE carts SET quantity = ?, product_price = ? WHERE product_id = ?");
      $update_cart->bind_param("idi", $new_quantity, $new_total_price, $product_id);
      $update_cart->execute();
    }
  } else {
    // If the product is not in the cart, add it
    $add_cart = $conn->prepare("INSERT INTO carts (product_id, product_name, product_price, product_img, quantity) VALUES (?, ?, ?, ?, ?)");
    $add_cart->bind_param("isdss", $product_id, $product_name, $product_price, $product_img, $quantity);
    $add_cart->execute();
  }

  // Redirect back to the product page or Home Page
  header("Location:shop.php");
  exit();
}

// Fetch all items from the database

// Fetch all items from the database
$search = "SELECT * FROM products";
$result = $conn->query($search);

$search_query = '';
$has_result = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search_query = $_GET['search'];

  // SQL query to search for products by search_name
  $stmt = $conn->prepare("SELECT * FROM products WHERE search_name LIKE ?");
  $search_term = "%" . $search_query . "%";  // Add wildcards for partial matching
  $stmt->bind_param("s", $search_term);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $has_result = true;
  } else {
    $has_result = false;
  }
}

// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Now</title>
  <link rel="stylesheet" href="stylesheet/carousel.css">
  <link rel="stylesheet" href="stylesheet/styles.css">
  <link rel="stylesheet" href="stylesheet/shop.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
  <style>
    /* Carousel CSS */
    #demo {
      width: 90vw;
      margin-left: 4vw;
      /* border: 1px solid red; */
    }

    .logo h3 {
      font-weight: bold;
      font-size: 1.51vw;
    }

    .fa-cart-shopping {
      border: none;
      font-size: 2vw;
      color: rgb(57, 5, 105);
    }

    .buy-product {
      position: absolute;
      top: 0;
      right: 0;
      text-decoration: none;
      color: #f9f9f9;
      background-color: #fc5f49;
      padding: 0.5vw 1vw;
      text-transform: capitalize;
      opacity: 0;
      transition: 0.3s all ease-in-out;
    }

    .cart-product {
      position: absolute;
      right: 0;
      bottom: -1.5vw;
      padding: 0.4vw;
      background-color: #ff2200;
      color: #fff;
      border: none;
      cursor: pointer;
      font-size: 1vw;
    }

    .fa-star {
      border: none;
      margin-bottom: 2vw;

    }

    .second-section {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .shop-section {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 90vw;
      flex-wrap: wrap;
      border: 1px solid #fff;
    }

    .all-items {
      margin-bottom: 6vw;
    }

    .shop-product-images {
      width: 15vw;
      height: 20vw;
    }

    .add_items {
      width: 15vw;
    }

    .search-input {
      width: 20vw;
      padding: 0.5vw;
      font-size: 1vw;
      border-radius: 0.5vw;
      outline: none;
      border: none;
      margin-right: 0.5vw;
      border-top: 1px solid #bdbdbd;
      border-bottom: 1px solid #bdbdbd;
      border-left: 1px solid #bdbdbd;
    }

    .search-btn {
      padding: 0.5vw 1vw;
      font-size: 1vw;
      background-color: #1D1B1B;
      color: white;
      border: none;
      border-radius: 0 0.5vw 0.5vw 0;
      cursor: pointer;
      outline: none;
      margin-left: -3.5vw;
    }

    .search-btn:hover {
      background-color: #000;
    }
  </style>

</head>

<body>
  <!-- Header -->
  <header>
    <div class="logo">
      <h3>Gadget<span>4</span>U</h3>
    </div>

    <!-- Search Form -->
    <form action="?" method="GET">
      <div class="search_product">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="search-input">
        <button type="submit" class="search-btn"><i class="fa-brands fa-searchengin"></i></button>
      </div>
    </form>

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

  <div class="shop-now">
    <!-- Carousel -->
    <div id="demo" class="carousel slide" data-bs-ride="carousel">
      <!-- Indicators/dots -->
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#demo" data-bs-slide-to="3"></button>
        <button type="button" data-bs-target="#demo" data-bs-slide-to="4"></button>
        <button type="button" data-bs-target="#demo" data-bs-slide-to="5"></button>
      </div>

      <!-- The slideshow/carousel -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="Corusal/image (1).png" alt="" class="d-block">
        </div>
        <div class="carousel-item">
          <img src="Corusal/image (2).png" alt="" class="d-block">
        </div>
        <div class="carousel-item">
          <img src="Corusal/image (3).png" alt="" class="d-block">
        </div>
        <div class="carousel-item">
          <img src="Corusal/image (4).png" alt="" class="d-block">
        </div>
        <div class="carousel-item">
          <img src="Corusal/image (5).png" alt="" class="d-block">
        </div>
        <div class="carousel-item">
          <img src="Corusal/image (6).jpg" alt="" class="d-block">
        </div>
      </div>

      <!-- Left and right controls/icons -->
      <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
  </div>

  <!-- Search Section -->
  <section class="watches-product-section first-section" id="search_section">
    <div class="sh_nav">
      <h4 class="search_heading">Recent Search</h4>
      <button class="sh-btn" onclick="sectionClose()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="watches-section shop-section">
      <?php
      // Assuming you have already connected to the database and executed the query
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $name = isset($row['search_name']) ? htmlspecialchars($row['search_name']) : 'No Name Available';
          $price = isset($row['search_price']) ? number_format($row['search_price'], 2) : '0.00';
          $image = isset($row['search_image']) ? htmlspecialchars($row['search_image']) : 'default_image.jpg'; // Use a default image if none found

          echo '
                    <div class="all-items" style="margin-right:2vw;">
                        <div class="watch-images">
                            <img src="' . $image . '" alt="' . $name . '" class="watch-product-images">
                        </div>
                        <figcaption>' . $name . '</figcaption>
                        <div class="watch-star">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star-half-stroke"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <p class="watch-price">NPR. ' . $price . '</p>
                        <a href="#" class="buy-product">Buy</a>

                        <!-- Form for Adding to Cart -->
                        <form action="?" method="POST">
                            <input type="hidden" name="product_id" value="' . $row['id'] . '"> <!-- Assuming id is used here -->
                            <input type="hidden" name="product_name" value="' . $name . '">
                            <input type="hidden" name="product_price" value="' . $row['search_price'] . '">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="product_img" value="' . $image . '">
                            <input type="submit" class="cart-product" value="Add Cart">
                        </form>
                    </div>';
        }
      } else {
        echo '<p>No products found.</p>';
      }
      ?>
    </div>
    <style>
      .first-section {
        border: 1px solid red;
        width: 60vw;
        margin-left: 20vw;
        justify-content: flex-start;
        align-items: flex-starts;
        display: flex;
        flex-wrap: wrap;
        border: 1px solid grey;
        box-shadow: 0.5vw 0.1vw 0.5vw 0.1vw #5c5b5b;
        background-color: #f9f9f9;
        overflow: scroll;
      }

      .search_items {
        display: flex;
        justify-content: space-around;
      }

      #search_items {
        border: 1px solid red;
        display: flex;
        justify-content: space-around;
        align-items: center;
        width: 80vw;
        z-index: 1000;
        overflow-y: auto;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        background-color: rgba(255, 255, 255, 0.9);

      }

      .sh_nav {
        display: flex;
        justify-content: space-between;
        margin-left: 2vw;
        position: absolute;
        top: 6vw;
        width: 55vw;
      }

      .search_heading {
        color: red;
      }

      .sh-btn {
        border: none;
        outline: none;
      }

      .fa-xmark {
        font-size: 2vw;
        border: none;
        position: absolute;
      }
    </style>
  </section>

  <!-- Shops Section 1 -->
  <section class="watches-product-section second-section">
    <div class="watch-text-sec">
      <h1 class="watch-h">Shop Here</h1>
      <p class="watch-description" style="width:100%;">You can purchase different item from here as you required.</p>
    </div>

    <div class="watches-section shop-section">
      <!-- Product 1 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/shop (1).jpg" alt="" class="shop-product-images">
        </div>
        <figcaption>Coat</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
        </div>
        <p class="watch-price">NPR.9890</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 1 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="201">
          <input type="hidden" name="product_name" value="Coat 1">
          <input type="hidden" name="product_price" value="9890">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/shop (1).jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 2 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/shop (2).jpg" alt="" class="shop-product-images">
        </div>
        <figcaption>Coat</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
        </div>
        <p class="watch-price">NPR.7255</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 2 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="202">
          <input type="hidden" name="product_name" value="Coat 2">
          <input type="hidden" name="product_price" value="7255">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/shop (2).jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 3 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/shop (3).jpg" alt="" class="shop-product-images">
        </div>
        <figcaption>Coat</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.8150</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 3 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="203">
          <input type="hidden" name="product_name" value="Coat 3">
          <input type="hidden" name="product_price" value="8150">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/shop (3).jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 4 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/shop (4).jpg" alt="" class="shop-product-images">
        </div>
        <figcaption>Coat</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.6550</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 4 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="204">
          <input type="hidden" name="product_name" value="Coat 4">
          <input type="hidden" name="product_price" value="6550">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/shop (4).jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 5 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/shop (5).jpg" alt="" class="shop-product-images">
        </div>
        <figcaption>Coat</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.6550</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 5 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="204">
          <input type="hidden" name="product_name" value="Coat 4">
          <input type="hidden" name="product_price" value="6550">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/shop (5).jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Display each item dynamically -->
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $item_id = $row['id'];
          $item_name = $row['item_name'];
          $item_price = isset($row['item_price']) ? $row['item_price'] : 0;
          $item_image = $row['item_image'];

          echo '
        <div class="all-items add_items">  
            <div class="watch-images">
                <img src="' . $item_image . '" alt="' . $item_name . '" class="shop-product-images">
            </div>
            <figcaption>' . $item_name . '</figcaption>
            <div class="watch-star">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star-half-stroke"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p class="watch-price">NPR. ' . number_format($item_price, 2) . '</p>
            <a href="#" class="buy-product">Buy</a>

            <form action="?" method="POST">
                <input type="hidden" name="product_id" value="' . $item_id . '">
                <input type="hidden" name="product_name" value="' . $item_name . '">
                <input type="hidden" name="product_price" value="' . $item_price . '">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="product_img" value="' . $item_image . '">
                <input type="submit" class="cart-product" value="Add Cart">
            </form>
        </div>';
        }
      } else {
        echo '<p>No items available.</p>';
      }
      ?>
    </div>
  </section>

  <!-- Add_Items Section 1 -->
  <section class="watches-product-section third-section">
    <div class="watches-section shop-section">
      <!-- Display each item dynamically -->
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $item_id = $row['id'];
          $item_name = $row['item_name'];
          $item_price = isset($row['item_price']) ? $row['item_price'] : 0;
          $item_image = $row['item_image'];

          echo '
        <div class="all-items add_items">  
            <div class="watch-images">
                <img src="' . $item_image . '" alt="' . $item_name . '" class="shop-product-images">
            </div>
            <figcaption>' . $item_name . '</figcaption>
            <div class="watch-star">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star-half-stroke"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p class="watch-price">NPR. ' . number_format($item_price, 2) . '</p>
            <a href="#" class="buy-product">Buy</a>

            <form action="?" method="POST">
                <input type="hidden" name="product_id" value="' . $item_id . '">
                <input type="hidden" name="product_name" value="' . $item_name . '">
                <input type="hidden" name="product_price" value="' . $item_price . '">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="product_img" value="' . $item_image . '">
                <input type="submit" class="cart-product" value="Add Cart">
            </form>
        </div>';
        }
      } else {
        echo '<p>No items available.</p>';
      }
      ?>
    </div>
  </section>

  <!-- Footer Section here -->
  <footer>
    <div class="black-section">
      <div class="icon">
        <i class="fa-solid fa-angles-right"></i>
      </div>
      <div class="first-black">
        <h1>About</h1>
        <ul>
          <a class="footer-list" href="">Home</a>
          <a class="footer-list" href="">Get in touch</a>
          <a class="footer-list" href="">fAQs</a>
          <a class="footer-list" href="">Term and Condition</a>
        </ul>
      </div>
      <div class="first-black">
        <h1>Product</h1>
        <ul>
          <a class="footer-list" href="">Testimonials</a>
          <a class="footer-list" href="">Recent Blog</a>
          <a class="footer-list" href="">How it Works</a>
          <a class="footer-list" href="">Member Discount</a>
        </ul>
      </div>
      <div class="first-black">
        <h1 style="margin-left:-0.1vw" ;>Not Quite Ready for Gadget<span>4</span>U</h1>
        <div class="footer-input">
          <p>Join our team.</p>
          <div class="footer-applied">
            <input class="mail" type="email" placeholder="Enter Your Email">
            <input type="submit" value="Subscribe">
          </div>
        </div>
      </div>
    </div>
    <div class="process">
      <div class="address-link">
        <p><span style="color:#fff" ;>Email: </span>gadgetforyou44@gmail.com</p>
        <p><span style="color:#fff" ;>Address: </span>Greenland, Kathmandu</p>
      </div>
      <div class="social-link">
        <a href="https://www.facebook.com/"><i class="fa fa-facebook"></i></a>
        <a href="https://www.instagram.com/"><i class="fa fa-instagram"></i></a>
        <a href="https://www.twitter.com/"><i class="fa-brands fa-square-x-twitter"></i></a>
        <a href="https://www.linkedin.com/"><i class="fa fa-linkedin"></i></a>
      </div>
    </div>
  </footer>

  <!-- Footer CSS Here  -->
  <style>
    footer {
      width: 100%;
      height: auto;
      /* margin-top:-10vw; */
    }

    footer h1,
    p {
      color: #fff;
    }

    .middle-section {
      width: 88vw;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: rgb(162, 230, 253);
      position: absolute;
      bottom: -70%;
      padding: 5vw;
      border-radius: 2vw;
      background-image: url(images/background.jpg);
      background-size: cover;
      border-bottom: 4px solid rgb(60, 60, 60);
    }

    .text-footer span {
      color: #048f3b;
    }

    .first-button {
      padding: 1vw;
      border-radius: 0.5vw;
      outline: none;
      border: none;
      color: #fff;
      background-color: rgb(2, 2, 121);
      cursor: pointer;
    }

    .black-section {
      display: flex;
      justify-content: space-around;
      align-items: flex-start;
      padding-top: 10vw;
      height: 25vw;
      background-color: #1D1B1B;
    }

    .icon i {
      color: #048f3b;
      align-items: center;
      margin-top: 5vw;
      border: none;
    }

    .first-black:nth-child(4) {
      width: 30%;
    }

    .first-black h1 {
      color: rgb(163, 163, 163);
      font-size: 1.5vw;
      margin-bottom: 1vw;
      /* border: 1px solid red; */
      margin-left: 2.5vw;
    }

    .first-black ul {
      display: flex;
      flex-direction: column;
      align-items: start;
      justify-content: start;
    }

    .first-black a {
      color: #bdbdbd;
    }

    .first-black a:hover {
      color: #5c5b5b;
    }

    .first-black span {
      font-size: 2vw;
    }

    .first-black p {
      color: #fff;
    }

    .footer-list {
      text-decoration: none;
      margin: 0.5vw 0;
    }

    .footer-applied input {
      padding: 1vw;
    }

    .mail {
      width: 60%;
      border-top-left-radius: 1vw;
      border-bottom-left-radius: 1vw;
      border: none;
    }

    input[type='email'] {
      width: 60%;
      padding: 1.1vw;
    }

    .footer-applied input:nth-child(2) {
      background-color: #048f3b;
      outline: none;
      border: none;
      padding: 1.1vw;
      color: #fff;
      cursor: pointer;
    }

    .process {
      height: 2vw;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #bdbdbd;
    }

    .address-link {
      color: rgb(102, 100, 100);
      font-size: 0.9vw;
      display: flex;
      padding: 1vw;
      margin: 1vw 4vw 0 0;
    }

    .address-link span {
      color: #c9c9c9;
      margin-left: 2vw;
    }

    .address-link p {
      color: grey;
    }

    .address-link .social-link {
      margin-right: 4vw;
      display: flex;
      justify-content: space-around;
      padding: auto;
      width: 10vw;
    }

    .social-link {
      margin-right: 4vw;
    }

    .social-link i {
      font-size: 1vw;
      padding: 0;
      margin-right: 1vw;

    }

    .fa-square-x-twitter {
      color: #000;
      background-color: #fff;
    }

    .process a:nth-child(2) {
      color: rgb(90, 90, 245);
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchBtn = document.querySelector('.search-btn');
      const searchSection = document.getElementById('search_section');
      const searchInput = document.querySelector('.search-input');

      // Check if the search section should be hidden on page load
      const isSearchSectionHidden = localStorage.getItem('searchSectionHidden');

      if (isSearchSectionHidden === 'true') {
        searchSection.style.display = 'none';
      } else {
        searchSection.style.display = 'flex'; // Adjust display style as needed
      }

      // Listen for search button click
      searchBtn.addEventListener('click', function() {
        const searchValue = searchInput.value.trim();

        if (searchValue === '') {
          // If the input is empty, hide the section
          searchSection.style.display = 'none';
          localStorage.setItem('searchSectionHidden', 'true'); // Save state in localStorage
        } else {
          // Show the section and clear the hidden state in localStorage
          searchSection.style.display = 'flex';
          localStorage.setItem('searchSectionHidden', 'false'); // Save state in localStorage
        }
      });
    });

    // Function to close the section and save state
    function sectionClose() {
      const searchSection = document.getElementById('search_section');
      searchSection.style.display = 'none';
      localStorage.setItem('searchSectionHidden', 'true');
    }
  </script>

</body>

</html>