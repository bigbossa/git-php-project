<?php
require '../dbConfig.php';
require '../dbConfigs.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the directory where the uploaded files will be saved
$target_dir = "../uploads";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    // Handle the file upload
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Check file size (limit to 2MB)
    if ($_FILES["image"]["size"] > 2000000) {
        die("Sorry, your file is too large.");
    }

    // Allow only certain file formats
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowed_types)) {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        die("Sorry, there was an error uploading your file.");
    }

    // File upload was successful, now proceed with database insertion

    // First, check if the type already exists in the type table
    $type_sql = "SELECT ID FROM type WHERE type = ?";
    $stmt = $conn->prepare($type_sql);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Type exists, get the type ID
        $row = $result->fetch_assoc();
        $type_id = $row['ID'];
    } else {
        // Type doesn't exist, insert a new type
        $insert_type_sql = "INSERT INTO type (type) VALUES (?)";
        $stmt = $conn->prepare($insert_type_sql);
        $stmt->bind_param("s", $type);
        if ($stmt->execute()) {
            $type_id = $conn->insert_id;  // Get the inserted type ID
        } else {
            die("Error inserting type: " . $conn->error);
        }
    }

    // Insert into detail_product table
    $insert_detail_sql = "INSERT INTO detail_product (detail, amount, type_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_detail_sql);
    $stmt->bind_param("sii", $detail, $amount, $type_id);
    if ($stmt->execute()) {
        $detail_id = $conn->insert_id;  // Get the inserted detail ID
    } else {
        die("Error inserting detail: " . $conn->error);
    }

    // Insert into product table, saving the file path of the uploaded image
    $insert_product_sql = "INSERT INTO product (Name, image, price, detail_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_product_sql);
    $stmt->bind_param("ssdi", $name, $target_file, $price, $detail_id);
    if ($stmt->execute()) {
        echo "Product added successfully!";
    } else {
        die("Error inserting product: " . $conn->error);
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!-- HTML Form to add product with Bootstrap 5 -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Product</title>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4" style="color: black;">Add Product</h1>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label" style="color: black;">Product Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label" style="color: black;">Upload Image:</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label" style="color: black;">Price:</label>
                <input type="text" class="form-control" id="price" name="price" required>
            </div>

            <div class="mb-3">
                <label for="detail" class="form-label" style="color: black;">Product Detail:</label>
                <textarea class="form-control" id="detail" name="detail" required></textarea>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label" style="color: black;">Amount:</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label" style="color: black;">Product Type:</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="Notebook">Notebook</option>
                    <option value="Computer">Computer</option>
                    <option value="Outer">Outer</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
