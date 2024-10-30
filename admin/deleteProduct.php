<?php
require '../dbConfig.php'; // Your database configuration file
require '../dbConfigs.php'; // Your database configuration file

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product ID from URL
if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    // Prepare the DELETE statement
    $sql = "DELETE FROM product WHERE ID = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $productId);
        
        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Redirect back to product list after successful deletion
            header("Location: index_showproduct.php?msg=Product deleted successfully");
            exit(); // Ensure no further code is executed
        } else {
            echo "Error deleting record: " . $conn->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
