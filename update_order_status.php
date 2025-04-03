<?php
require 'db.php';

if (isset($_POST['table_id']) && isset($_POST['status'])) {
    $table_id = intval($_POST['table_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $check_status_query = "SELECT status FROM orders WHERE table_id = $table_id AND is_checked_out = 0 AND status = 'รอรับออเดอร์'";
    $result = mysqli_query($conn, $check_status_query);
    
    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE orders SET status = 'กำลังเตรียมอาหาร' WHERE table_id = $table_id AND status = 'รอรับออเดอร์' AND is_checked_out = 0";
        if (mysqli_query($conn, $update_query)) {
            echo "สถานะออเดอร์ได้รับการอัปเดตแล้ว";
        } else {
            echo "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
        }
    } else {
        echo "ไม่มีออเดอร์ที่สถานะเป็น 'รอรับออเดอร์' เพื่อทำการอัปเดต";
    }
} else {
    echo "ข้อมูลไม่ถูกต้อง";
}

mysqli_close($conn);
?>
