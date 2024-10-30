<?php
ob_start(); // Start output buffering
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../dbConfigs.php'; // Check path

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user data from session
$user_id = $_SESSION['id'];

try {
    // Retrieve user information from User and Profile tables
    $sql = "SELECT u.ID AS user_id, u.User, u.Email, u.Password, p.ID AS profile_id, p.fname, p.lname, p.address, p.telephone, p.img
            FROM User u
            JOIN Profile p ON u.profile_id = p.ID
            WHERE u.ID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<div class='alert alert-danger'>User not found!</div>";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // If it's for the profile update
        if (!isset($_POST['current_password'])) {
            // Get data from the form
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $fname = trim($_POST['fname']);
            $lname = trim($_POST['lname']);
            $address = trim($_POST['address']);
            $telephone = trim($_POST['telephone']);

            // Check if a file was uploaded
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $targetDir = "../uploads/"; // Directory where images are saved
                $fileName = basename($_FILES["profile_image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Allow certain file formats
                $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowedTypes)) {
                    // Move the uploaded file to the server
                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
                        // Save the file path to the database
                        $img = $targetFilePath;
                    } else {
                        echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
                }
            } else {
                // If no new image is uploaded, retain the existing one
                $img = $user['img'];
            }

            // Basic validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='alert alert-danger'>Invalid email format!</div>";
            } elseif (!preg_match('/^[0-9]{10}$/', $telephone)) { // Example: Adjust based on your format
                echo "<div class='alert alert-danger'>Invalid telephone number!</div>";
            } else {
                // Update data in Profile table, including the image path
                $sql = "UPDATE Profile SET fname = :fname, lname = :lname, address = :address, telephone = :telephone, img = :img WHERE ID = :profile_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':fname', $fname);
                $stmt->bindParam(':lname', $lname);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':telephone', $telephone);
                $stmt->bindParam(':img', $img);
                $stmt->bindParam(':profile_id', $user['profile_id']);
                $stmt->execute();

                // Update data in User table
                $sql = "UPDATE User SET User = :username, Email = :email WHERE ID = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();

                // Set flash message
                $_SESSION['message'] = "Profile updated successfully!";
                // No redirection here
            }
        } 
        // If it's for the password change
        elseif (isset($_POST['current_password'])) {
            $current_password = trim($_POST['current_password']);
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            // Verify current password
            if (password_verify($current_password, $user['Password'])) {
                // Check if new passwords match
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database
                    $sql = "UPDATE User SET Password = :new_password WHERE ID = :user_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':new_password', $hashed_password);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();

                    $_SESSION['message'] = "Password updated successfully!";
                } else {
                    echo "<div class='alert alert-danger'>New passwords do not match!</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Current password is incorrect!</div>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 style="color: black;">แก้ไขข้อมูลผู้ใช้</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class='alert alert-success'><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data"> <!-- Add enctype for file upload -->
            <div class="mb-3">
            <?php if (!empty($user['img'])): ?>
                    <div class="mt-3">
                        <img src="<?php echo htmlspecialchars($user['img']); ?>" alt="Profile Image" width="150" height="150">
                    </div>
                    <br>
                <?php endif; ?>
                <label for="username" class="form-label" style="color: black;">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['User']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label" style="color: black;">อีเมล</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="fname" class="form-label" style="color: black;">ชื่อจริง</label>
                <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="lname" class="form-label" style="color: black;">นามสกุล</label>
                <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label" style="color: black;">ที่อยู่</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="telephone" class="form-label" style="color: black;">หมายเลขโทรศัพท์</label>
                <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="profile_image" class="form-label" style="color: black;">โปรไฟล์รูปภาพ</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary" style="color: black;">บันทึกการเปลี่ยนแปลง</button>
        </form>

        <!-- Change Password Button -->
        <button type="button" class="btn btn-warning mt-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            เปลี่ยนรหัสผ่าน
        </button>
        <br>
        <br>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordLabel" style="color: black;">เปลี่ยนรหัสผ่าน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" method="POST">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label" style="color: black;">รหัสผ่านปัจจุบัน</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required style="color: black;">
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label" style="color: black;">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required style="color: black;">
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label" style="color: black;">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required style="color: black;">
                    </div>
                    <button type="submit" class="btn btn-primary">ยืนยัน</button>
                </form>
            </div>
        </div>
    </div>
</div>
<br><br>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>