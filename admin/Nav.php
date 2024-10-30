<?php
// Start the session only if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../dbConfig.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Store session variables for use in the navbar
$userEmail = $_SESSION['email'];
$userName = $_SESSION['username']; 

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
    $sql = "SELECT u.ID AS user_id, u.User, u.Email, p.ID AS profile_id, p.fname, p.lname, p.address, p.telephone, p.img
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
        // Get data from the form

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
catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>


<div class="main-panel">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
        <div class="container-fluid">
            <div class="navbar-wrapper">
                <div class="navbar-toggle d-inline">
                    <button type="button" class="navbar-toggler">
                        <span class="navbar-toggler-bar bar1"></span>
                        <span class="navbar-toggler-bar bar2"></span>
                        <span class="navbar-toggler-bar bar3"></span>
                    </button>
                </div>
                
                <a class="navbar-brand" href="javascript:void(0)">JTP | Store | <?php echo htmlspecialchars($userName) . ' | ' . htmlspecialchars($userEmail); ?></a>


            </div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-bar navbar-kebab"></span>
                <span class="navbar-toggler-bar navbar-kebab"></span>
                <span class="navbar-toggler-bar navbar-kebab"></span>
            </button>
            <div class="collapse navbar-collapse" id="navigation">
                <ul class="navbar-nav ml-auto">
                    <li class="search-bar input-group">
                        <button class="btn btn-link" id="search-button" data-toggle="modal" data-target="#searchModal"><i class="tim-icons icon-zoom-split"></i>
                            <span class="d-lg-none d-md-block">Search</span>
                        </button>
                    </li>
                    <li class="dropdown nav-item">
                        <a href="javascript:void(0)" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <div class="notification d-none d-lg-block d-xl-block"></div>
                            <i class="tim-icons icon-sound-wave"></i>
                            <p class="d-lg-none">
                                Notifications
                            </p>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right dropdown-navbar">
                            <li class="nav-link"><a href="#" class="nav-item dropdown-item">Mike John responded to your email</a></li>
                            <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">You have 5 more tasks</a></li>
                            <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Your friend Michael is in town</a></li>
                            <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Another notification</a></li>
                            <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Another one</a></li>
                        </ul>
                    </li>
                    <li class="dropdown nav-item">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <div class="photo">
                                 <img src="<?php echo htmlspecialchars($user['img']); ?>" alt="Profile Image" width="30px" height="30px">
                            </div>
                            <b class="caret d-none d-lg-block d-xl-block"></b>
                            <p class="d-lg-none">
                                Log out
                            </p>
                        </a>
                        <ul class="dropdown-menu dropdown-navbar">
                            <li class="nav-link"><a href="index_edituser.php" class="nav-item dropdown-item">Profile</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="nav-link"><a href="../Logout.php" class="nav-item dropdown-item">Log out</a></li>
                        </ul>
                    </li>
                    <li class="separator d-lg-none"></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="modal modal-search fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="SEARCH">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="tim-icons icon-simple-remove"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>