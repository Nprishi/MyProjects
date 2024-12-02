<?php
session_start(); // Start the session
include 'connect.php';

// Destroy the session to log the user out
session_destroy();
session_unset();  // Unset all session variables
// Redirect to the login page
header('Location: adminlog.php');
exit();
