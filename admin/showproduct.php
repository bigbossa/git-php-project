<?php
require '../dbConfig.php'; // Your database configuration file
require '../dbConfigs.php'; // Your additional database configuration if any

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "
    SELECT 
        p.ID,
        p.Name, 
        p.image, 
        p.price, 
        d.detail, 
        d.amount, 
        t.type 
    FROM 
        product p
    JOIN 
        detail_product d ON p.detail_id = d.ID
    JOIN 
        type t ON d.type_id = t.ID
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        img {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Product List</h1>
    <table>
        <tr>
            <th>Product Name</th>
            <th>Image</th>
            <th>Price</th>
            <th>Detail</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['image']) . "' alt='Product Image'></td>";
                echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                echo "<td>" . htmlspecialchars($row['detail']) . "</td>";
                echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                echo "<td>
                        <a href='editProduct.php?id=" . htmlspecialchars($row['ID']) . "'>Edit</a> | 
                        <a href='#' onclick='deleteProduct(" . htmlspecialchars($row['ID']) . ")'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No products found.</td></tr>";
        }
        $conn->close();
        ?>
    </table>

    <script>
    function deleteProduct(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to deleteProduct.php with the product ID
                window.location.href = 'deleteProduct.php?id=' + productId; // Fixed URL
            }
        });
    }
    </script>
</body>
</html>
