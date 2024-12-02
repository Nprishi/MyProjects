<?php

session_start();
include 'connect.php';  // Include the database connection

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
    <link rel="stylesheet" href="stylesheet/about.css">
    <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
    <title>About_us</title>
    <style>
        .fa-cart-shopping {
            border: none;
            font-size: 2vw;
            color: rgb(57, 5, 105);
        }

        .about-page {
            background-image: url("./images/about_bg.jpg");
            height: 13vw;
            background-size: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-repeat: no-repeat;
        }

        .about-page h1 {
            background-color: antiquewhite;
            padding: 1vw;
            font-size: 1vw;
        }

        .about-us-section {
            padding: 1rem;
            width: 100%;
            height: auto;
        }

        .about-our-store-two {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3vw 0 0 0;
        }

        .first {
            width: 50%;
        }

        .first img {
            height: 40vw;
            width: 45vw;
            filter: grayscale(50%);
            cursor: pointer;
        }

        .first img:hover {
            filter: grayscale(0%);
        }

        .first:nth-child(2) {
            padding: 1vw;
        }

        .text-about {
            font-size: 2.5vw;
            color: red;
            margin-bottom: 2vw;
        }

        .two-list-type {
            padding: 2vw 0;
        }

        .one-type {
            padding: 1vw;
        }

        .one-type h5 {
            font-size: 1.2vw;
            margin: 0.5vw 0;
        }

        .text-list {
            font-size: 1vw;
            color: #000;
        }

        .logo h3 {
            color: red;
        }

        .logo span {
            font-size: 2vw;
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
                <a class="two-nav-btn order" href="#"><i class="fa-solid fa-store"></i>Order</a>
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

    <section class="about-us-section">
        <div class="about-page">
        </div>
        <div class="about-our-store-two">

            <div class="first">
                <img src="images/About Us.png" alt="">
            </div>

            <div class="first">
                <h1 class="text-about">About Our Store</h1>
                <p class="text-list">Welcome to our online fashion store, where style meets convenience. Explore the latest trends, timeless classics, and everything in between. Elevate your wardrobe with our curated collection of clothing, accessories, and more. Shop with confidence, knowing that quality and fashion are just a click away. Join us in the world of endless possibilities and express your unique style effortlessly. Happy shopping!</p>
                <div class="two-list-type">
                    <div class="one-type">
                        <h5>Fashion industries leading</h5>
                        <p class="text-list">We are a recognized authority in our field, setting industry standards
                            through expertise and innovation.</p>
                    </div>
                    <div class="one-type">
                        <h5>Express your unique style</h5>
                        <p class="text-list">We believe in fashion as a form of self-expression. Our diverse range of
                            products ensures that you can find pieces that reflect your individual style.</p>
                    </div>
                </div>
                <button class="Subscribe shopnow">Shop Now</button>
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

        .white-section {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 18vh;
            background-color: #fff;
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