<?php
require 'db.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_GET['id']) && isset($_GET['is_visible'])) {
    $id = $_GET['id'];
    $is_visible = $_GET['is_visible'];

    // เปลี่ยนสถานะ is_visible ในฐานข้อมูล
    $update_query = "UPDATE menu_items SET is_visible = '$is_visible' WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo "สถานะการแสดงสินค้าถูกอัปเดตเรียบร้อยแล้ว!";
    } else {
        echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}
?>
