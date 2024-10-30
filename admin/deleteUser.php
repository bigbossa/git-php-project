<?php
require '../dbConfig.php';
require '../dbConfigs.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ลบข้อมูลจากฐานข้อมูล
    $sql = "DELETE FROM user WHERE ID='$id'";
    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

header("Location: index_Showuser.php"); // เปลี่ยนไปยังหน้าแสดงข้อมูลผู้ใช้
exit();
?>
