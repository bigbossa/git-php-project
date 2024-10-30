<?php
require '../dbConfig.php';
require '../dbConfigs.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ขั้นตอนที่ 2: ดึงข้อมูลผู้ใช้
$sql = "SELECT u.ID, u.User, u.Email, p.fname, p.lname, p.address, p.telephone 
        FROM user u 
        JOIN profile p ON u.profile_id = p.ID";
$result = $conn->query($sql);

// ขั้นตอนที่ 3: แสดงข้อมูล
echo "<table id='userTable' class='display'>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Address</th>
                <th>Telephone</th>
                <th>  </th>
                <th>  </th>
                <th>  </th>
                <th>Action</th> <!-- คอลัมน์สำหรับปุ่ม -->
            </tr>
        </thead>
        <tbody>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["ID"] . "</td>
                <td>" . $row["User"] . "</td>
                <td>" . $row["Email"] . "</td>
                <td>" . $row["fname"] . "</td>
                <td>" . $row["lname"] . "</td>
                <td>" . $row["address"] . "</td>
                <td>" . $row["telephone"] . "</td>
                <th>  </th>
                <th>  </th>
                <th>  </th>
                <td>
                    <a href='showedituser.php?id=" . $row["ID"] . "'>Edit</a> | 
                    <a href='deleteUser.php?id=" . $row["ID"] . "' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>0 results</td></tr>";
}

echo "</tbody></table>";
$conn->close();
?>
