<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'connect.php';

    $email = $_SESSION['email'];
    $verificationCode = $_POST['verification_code'];

    // Prepare the SQL query to get the stored verification code
    $stmt = $conn->prepare("SELECT verification_code FROM `registration` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedCode);
    $stmt->fetch();

    if ($storedCode && $storedCode == $verificationCode) {
        // Update the is_verified column to 1
        $stmt->close(); // Close the previous statement before opening a new one
        $stmt = $conn->prepare("UPDATE `registration` SET is_verified = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful!";
            header("Location: login.php");
            exit();
        } else {
            die("Error: " . $stmt->error);
        }
    } else {
        echo "Invalid verification code.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            display: flex;
            flex-direction: column;
            text-align: center;
            width: 300px;
            padding: 20px;
            background-color: aliceblue;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .code {
            margin: 10px 0;
        }

        input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        .confirm-btn {
            color: #fff;
            background-color: blue;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .confirm-btn:hover {
            background-color: darkblue;
        }
    </style>
</head>

<body>
    <form action="" method="post">
        <label for="verification_code">
            <h4>Enter The Code</h4>
            <p>Please check your email for the verification code.</p>
        </label>
        <input type="number" class="code" id="verification_code" name="verification_code" placeholder="Enter your code" required>
        <button class="confirm-btn" type="submit">Confirm</button>
    </form>
</body>

</html>