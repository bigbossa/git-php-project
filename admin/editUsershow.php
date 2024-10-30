<?php
ob_start(); // Start output buffering

require '../dbConfig.php';
require '../dbConfigs.php';

$showAlert = false; // Variable to control SweetAlert display

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $address = $_POST['address'];
    $telephone = $_POST['telephone'];

    // Update user information in the database using prepared statements
    $sql = "UPDATE user u 
            JOIN profile p ON u.profile_id = p.ID 
            SET u.User=?, u.Email=?, p.fname=?, p.lname=?, p.address=?, p.telephone=? 
            WHERE u.ID=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $username, $email, $fname, $lname, $address, $telephone, $id);

    if ($stmt->execute()) {
        // Set variable to show SweetAlert after successful update
        $showAlert = true; 
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// Fetch user data for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT u.ID, u.User, u.Email, p.fname, p.lname, p.address, p.telephone 
            FROM user u 
            JOIN profile p ON u.profile_id = p.ID 
            WHERE u.ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 style="color: black;">Edit User</h2>
    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ID']); ?>">
        
        <div class="mb-3">
            <label for="username" class="form-label" style="color: black;">Username:</label>
            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($row['User']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label" style="color: black;">Email:</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($row['Email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="fname" class="form-label" style="color: black;">First Name:</label>
            <input type="text" class="form-control" name="fname" value="<?php echo htmlspecialchars($row['fname']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="lname" class="form-label" style="color: black;">Last Name:</label>
            <input type="text" class="form-control" name="lname" value="<?php echo htmlspecialchars($row['lname']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label" style="color: black;">Address:</label>
            <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($row['address']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label" style="color: black;">Telephone:</label>
            <input type="text" class="form-control" name="telephone" value="<?php echo htmlspecialchars($row['telephone']); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="index_Showuser.php" class="btn btn-secondary">Cancel</a> <!-- Button to cancel -->
    </form>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap JS (if needed for Bootstrap JavaScript) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Check if showAlert is set to true
    <?php if ($showAlert): ?>
        Swal.fire({
            title: 'Success!',
            text: 'User updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the index page after the alert is closed
                window.location.href = 'index_Showuser.php';
            }
        });
    <?php endif; ?>
</script>
</body>
</html>
