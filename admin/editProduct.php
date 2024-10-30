<?php
require_once '../dbConfig.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้ถูกต้อง
require_once '../dbConfigs.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้ถูกต้อง
include 'head.php';            // ส่วนหัวของ HTML
include 'sidebar.php';         // เมนูด้านข้าง
include 'Nav.php';            // แถบการนำทาง

?>

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body all-icons">
                <?php
require '../dbConfig.php';
require '../dbConfigs.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$productID = $_GET['id'] ?? null; // Get product ID from URL
$product = null;
$detailID = null; // Initialize variable for detail_id

// Fetch product data if ID is set
if ($productID) {
    $sql = "SELECT p.ID, p.Name, p.image, p.price, d.detail, d.amount, d.ID as detail_id, t.ID as type_id, t.type 
            FROM product p 
            JOIN detail_product d ON p.detail_id = d.ID 
            JOIN type t ON d.type_id = t.ID 
            WHERE p.ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $productID); // 'i' indicates the type is integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $detailID = $product['detail_id']; // Capture the detail ID for later use
    } else {
        echo "Product not found.";
        exit;
    }
}

// Update product if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];
    $amount = $_POST['amount'];
    $type_id = $_POST['type_id'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = 'uploads/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Update product details with new image
            $updateSql = "UPDATE product SET Name=?, image=?, price=? WHERE ID=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('ssdi', $name, $imagePath, $price, $productID);
            $updateStmt->execute();
        } else {
            echo "Failed to upload image.";
        }
    } else {
        // If no new image is uploaded, update other fields without changing the image
        $updateSql = "UPDATE product SET Name=?, price=? WHERE ID=?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sdi', $name, $price, $productID);
        $updateStmt->execute();
    }

    // Update detail_product details
    if ($detailID) {
        $updateDetailSql = "UPDATE detail_product SET detail=?, amount=?, type_id=? WHERE ID=?";
        $updateDetailStmt = $conn->prepare($updateDetailSql);
        $updateDetailStmt->bind_param('siii', $detail, $amount, $type_id, $detailID);
        $updateDetailStmt->execute();
    }

    // Redirect after update
    header("Location: index_showproduct.php");
    exit;
}

// Fetch all types for dropdown
$typeSql = "SELECT ID, type FROM type";
$typeResult = $conn->query($typeSql);
$types = [];
if ($typeResult->num_rows > 0) {
    while ($row = $typeResult->fetch_assoc()) {
        $types[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        img {
            max-width: 200px;
            height: auto;
            display: block;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <h1>Edit Product</h1>

    <?php if ($product): ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['Name']); ?>" required>

            <label for="image">Current Image:</label>
            <?php if ($product['image']): ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
            <?php endif; ?>
            <label for="image">Upload New Image:</label>
            <input type="file" name="image" id="image">

            <label for="price">Price:</label>
            <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>

            <label for="detail">Detail:</label>
            <input type="text" name="detail" id="detail" value="<?php echo htmlspecialchars($product['detail']); ?>" required>

            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" value="<?php echo htmlspecialchars($product['amount']); ?>" required>

            <label for="type_id">Type:</label>
            <select name="type_id" id="type_id" required>
                <?php foreach ($types as $type): ?>
                    <option value="<?php echo $type['ID']; ?>" <?php echo ($type['ID'] == $product['type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['type']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" value="Update Product">
        </form>
    <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>

</body>

</html>


                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';          // ส่วนท้ายของ HTML
include 'fixed-plugin.php';    // ส่วนเพิ่มเติม (ถ้ามี)
include 'javaScrip.php';       // สคริปต์ JavaScript
?>