<?php
session_start();
include 'connect.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $product_id = $_POST['product_id'];
  $product_name = $_POST['product_name'];
  $product_price = $_POST['product_price'];
  $quantity = $_POST['quantity'];
  $product_img = $_POST['product_img'];

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
  header("Location:index.php");
  exit();
}


// Fetch all items from the database
$search = "SELECT * FROM search";
$result = $conn->query($search);

$search_query = '';
$has_result = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search_query = $_GET['search'];

  // SQL query to search for products by search_name
  $stmt = $conn->prepare("SELECT * FROM search WHERE search_name LIKE ?");
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="stylesheet/styles.css">
  <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
  <title>Document</title>
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

    .section-for-three {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 4vw 2vw;
    }

    .all-image-section {
      display: flex;
      justify-content: end;
      flex-direction: column;
      align-items: center;
      height: 35vw;
      width: 25%;
      border-radius: 0.5vw;
      background-image: url("images/pexels-hiago-italo-1808785.jpg");
      background-size: 100%;
      background-repeat: no-repeat;

    }

    .all-image-section:nth-child(2) {
      background-image: url("images/pexels-jk-films-15345106.jpg");
    }

    .all-image-section:nth-child(3) {
      background-image: url("images/pexels-tuấn-kiệt-jr-1468379.jpg");
    }

    .all-image-section figcaption {
      background-color: #00000074;
      font-size: 1vw;
      color: #e8e8e8;
      padding: 1vw;
      text-transform: uppercase;
      border-radius: 0.2vw;
      font-weight: 700;
      margin: 0 0 1vw 0;

    }

    body p {
      color: #000;
      padding: 1vw;
    }

    .watches-product-section .watches-section .all-items .watch-price {
      color: #ff2200;
      background-color: #fff;
      font-size: 1vw;
      text-align: center;
      border: none;
      position: absolute;
      bottom: -4vw;
      left: 0;
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
      bottom: -3vw;
      right: 0;
      padding: 0.5vw;
      background-color: #ff2200;
      color: #fff;
      border: none;
      cursor: pointer;
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

    <!-- Search bar form -->
    <div class="search_product">
      <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="search-input">
      <button type="submit" class="search-btn"><i class="fa-brands fa-searchengin"></i></button>
    </div>
    <nav>
      <li class="nav-list"><a href="index.php" class="nav-anchor">HOME</a></li>
      <li class="nav-list"><a href="about.php" class="nav-anchor">ABOUT</a></li>
      <li class="nav-list"><a href="shop.php" class="nav-anchor">SHOP</a></li>
      <li class="nav-list"><a href="contact.php" class=" mainbtn nav-anchor ">CONTACT</a></li>
    </nav>

    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="nav-icon">
        <a class="two-nav-btn profile" href="#">Profile</a>
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
        <a class="two-nav-btn" href="signup.php">signup</a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
      </div>
    <?php endif; ?>

  </header>

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

  <!-- Main Section -->
  <section class="main-landing-page">
    <div class="two-main-div">
      <h1>Discover and find Your Own <span class="home-span" style="color: #ff2200;">Fashion !</span> </h1>
      <p>
        we are more than just an online shopping destination; we're your personal gateway to a world of fashion, trendsetting styles, and unparalleled convenience. We believe that fashion is a reflection of your individuality, and our mission is to make every shopping experience with us.
      </p>
      <button class="home-btn">Shop Now</button>
    </div>
  </section>

  <section class="section-for-three">
    <div class="all-image-section">
      <figcaption>30% off all order</figcaption>
      <button class="three-btn">shop now</button>
    </div>
    <div class="all-image-section">
      <figcaption>30% off all order</figcaption>
      <button class="three-btn">shop now</button>

    </div>
    <div class="all-image-section">
      <figcaption>30% off all order</figcaption>
      <button class="three-btn">shop now</button>
    </div>
  </section>

  <!-- Watches Section -->
  <section class="watches-product-section">
    <div class="watch-text-sec">
      <h1 class="watch-h">Watch</h1>
    </div>

    <div class="watches-section">
      <!-- Product 1 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/watch-shop-10.webp" alt="" class="watch-product-images">
        </div>
        <figcaption>Watch</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
        </div>
        <p class="watch-price">NPR.3200</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 1 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="102">
          <input type="hidden" name="product_name" value="Watch">
          <input type="hidden" name="product_price" value="3200">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/watch-shop-10.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 2 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/watch-shop-8.webp" alt="" class="watch-product-images">
        </div>
        <figcaption>Watch</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price" style="color:#ff2200;">NPR.2999</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 2 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="103">
          <input type="hidden" name="product_name" value="Watch">
          <input type="hidden" name="product_price" value="2999">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/watch-shop-8.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 3 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/watch-shop-7.webp" alt="" class="watch-product-images">
        </div>
        <figcaption>Watch</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.3900</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 3 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="104">
          <input type="hidden" name="product_name" value="Watch">
          <input type="hidden" name="product_price" value="3900">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/watch-shop-7.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 4 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/watch-shop-11.webp" alt="" class="watch-product-images">
        </div>
        <figcaption>Watch</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
        </div>
        <p class="watch-price">NPR.2599</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Form for Product 4 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="105">
          <input type="hidden" name="product_name" value="Watch">
          <input type="hidden" name="product_price" value="2599">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/watch-shop-11.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

    </div>
  </section>

  <!-- Coats Section -->
  <section class="watches-product-section">
    <div class="watch-text-sec">
      <h1 class="watch-h">Coat</h1>
      <p class="watch-description">A coat is a long outer garment worn to protect the body from cold weather or as a fashion statement. It typically extends below the waist, covering the upper body and arms.</p>
    </div>

    <div class="watches-section">
      <!-- Product 1 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/fashion-1-shop-1-1.webp" alt="" class="watch-product-images">
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
          <input type="hidden" name="product_img" value="images/fashion-1-shop-1-1.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 2 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/fashion-1-shop-2-1.webp" alt="" class="watch-product-images">
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
          <input type="hidden" name="product_img" value="images/fashion-1-shop-2-1.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 3 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/fashion-1-shop-4-1.webp" alt="" class="watch-product-images">
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
          <input type="hidden" name="product_img" value="images/fashion-1-shop-4-1.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 4 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/fashion-1-shop-5.webp" alt="" class="watch-product-images">
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
          <input type="hidden" name="product_img" value="images/fashion-1-shop-5.webp">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>
    </div>
  </section>

  <!-- Girls Product Section -->
  <section class="watches-product-section">
    <div class="watch-text-sec">
      <h1 class="watch-h">Girls Clothes</h1>
      <p class="watch-description">
        A thin shirt typically refers to a garment made from lightweight and breathable fabric. These shirts are designed to be comfortable and suitable for warm weather or as a layering piece under other clothing.
      </p>
    </div>
    <div class="watches-section">

      <!-- Product 1 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/girls3.jpg" alt="" class="watch-product-images">
        </div>
        <figcaption>Girls Product</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
        </div>
        <p class="watch-price">NPR.1700</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Add Cart Form for Product 1 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="404">
          <input type="hidden" name="product_name" value="Girls Product 1">
          <input type="hidden" name="product_price" value="1700">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/girls3.jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 2 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/girls4.jpg" alt="" class="watch-product-images">
        </div>
        <figcaption>Girls Product</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
        </div>
        <p class="watch-price">NPR.2200</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Add Cart Form for Product 2 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="405">
          <input type="hidden" name="product_name" value="Girls Product 2">
          <input type="hidden" name="product_price" value="2200">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/girls4.jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 3 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/girls2.jpg" alt="" class="watch-product-images">
        </div>
        <figcaption>Girls Product</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.1850</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Add Cart Form for Product 3 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="106"> <!-- Generates random product ID -->
          <input type="hidden" name="product_name" value="Girls Product 3">
          <input type="hidden" name="product_price" value="1850">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/girls2.jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>

      <!-- Product 4 -->
      <div class="all-items">
        <div class="watch-images">
          <img src="images/girls1.jpg" alt="" class="watch-product-images">
        </div>
        <figcaption>Girls Product</figcaption>
        <div class="watch-star">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <p class="watch-price">NPR.1600</p>
        <a href="#" class="buy-product">Buy</a>

        <!-- Add Cart Form for Product 4 -->
        <form action="?" method="POST">
          <input type="hidden" name="product_id" value="407">
          <input type="hidden" name="product_name" value="Girls Product 4">
          <input type="hidden" name="product_price" value="1600">
          <input type="hidden" name="quantity" value="1">
          <input type="hidden" name="product_img" value="images/girls1.jpg">
          <input type="submit" class="cart-product" value="Add Cart">
        </form>
      </div>
    </div>
  </section>

  <section class="subsc">
    <div class="subscribe-div">
      <h3>Subscribe newsletter</h3>
      <p>Join the style revolution and subscribe to our <br> fashion shop </p>
      <div class="twoinput">
        <input class="inputtext" type="text" placeholder="Enter Your Email address...">
        <button class="Subscribe">Subscribe</button>
      </div>
    </div>
  </section>

  <!-- Footer Section here -->
  <footer>
    <div class="black-section">
      <div class="logo">
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
        <h1>Not Quite Ready for Gadget<span>4</span>U</h1>
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

    .logo i {
      color: #048f3b;
      align-items: center;
      margin-top: 5vw;
    }

    .first-black:nth-child(4) {
      width: 30%;
    }

    .first-black h1 {
      color: rgb(163, 163, 163);
      font-size: 1.5vw;
      margin-bottom: 1vw;
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
      margin-left: 4vw;
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

  <!-- JavaScript to handle section closing and persistence -->
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