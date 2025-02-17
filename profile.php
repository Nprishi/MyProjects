<?php
session_start();
include 'connect.php';

// Assume user_id is being retrieved from session or request
$user_id = $_SESSION['user_id']; // or $_GET['user_id'] if passed in URL

if ($user_id) {
    // Prepare and execute the query to check for the user ID
    $stmt = $conn->prepare("SELECT * FROM registration WHERE id = ?");
    $stmt->bind_param("i", $user_id); // assuming id is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user ID exists
    if ($result->num_rows > 0) {
        // User ID exists, redirect to profile page
    } else {
        // User ID does not exists, redirect to login.php
        header("Location: login.php");
        exit();
    }
}

// Fetch user data based on user_id
$stmt = $conn->prepare("SELECT * FROM registration WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Now</title>
  <link rel="stylesheet" href="stylesheet/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/8735ffb818.js" crossorigin="anonymous"></script>
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
                <a class="two-nav-btn" href="profile.php">Profile</a>
                <a class="two-nav-btn" href="logout.php">Logout</a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        <?php else: ?>
            <div class="nav-icon">
                <a class="two-nav-btn" href="login.php">Login</a>
                <a class="two-nav-btn" href="signup.php">signup</a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        <?php endif; ?>
    </header>
    <div class="myProfile">
        <h1>Welcome, <?php echo htmlspecialchars($user['firstName']); ?></h1>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    </div>
</body>

</html>