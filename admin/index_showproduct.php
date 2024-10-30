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
                    <?php include 'showproduct.php'; // ฟอร์มแก้ไขผู้ใช้ ?>
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
