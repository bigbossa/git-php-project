<?php
session_start(); // Start the session
// Check if user is logged in
if (!isset($_SESSION['id'])) {
    // Redirect to login page if not logged in
    header('Location: ../login.php');
    exit();
}
// Include necessary files
include 'head.php';
include 'sidebar.php';
include 'Nav.php';
include 'content.php';
include 'footer.php';
include 'fixed-plugin.php';
include 'javaScrip.php';



// You can access session variables like this:
$userId = $_SESSION['id'];
$userEmail = $_SESSION['email'];

// Display the user's email (or use it as needed)
echo "Welcome, " . htmlspecialchars($userEmail);
?>
