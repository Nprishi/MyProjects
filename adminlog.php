<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'connect.php';

    $user_name = trim($_POST['admin_name']);
    $user_password = trim($_POST['admin_password']);

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT * FROM `admin-login` WHERE admin_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Compare the plain text password directly
    if ($user && $user_password === $user['admin_password']) {
        // Successful login
        $_SESSION['id'] = $user['id'];
        $_SESSION['admin_name'] = $user['admin_name'];
        header("Location: admin.php");
        exit();
    } else {
        // Failed login
        $_SESSION['login_error'] = "Incorrect Username and Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* Basic styling for the login form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-container {
            display: block;
            background-color: white;
            padding: 2vw;
            border-radius: 0.5vw;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            margin: 2vw;
            width: 30vw;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 10vw;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <!-- Login Form -->
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="error" id="errorMessage">
                <?php echo $_SESSION['login_error'];
                unset($_SESSION['login_error']); ?>
            </div>
            <style>
                #errorMessage{
                    color: red;
                    margin-bottom: 1vw;
                }
            </style>
        <?php endif; ?>
        <form action="?" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="admin_name" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="admin_password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>
