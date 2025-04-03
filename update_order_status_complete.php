<?php
require 'db.php';

if (isset($_POST['table_id']) && isset($_POST['session_id'])) {
    $table_id = intval($_POST['table_id']);
    $session_id = intval($_POST['session_id']);

    $update_query = "UPDATE orders SET status = 'เสร็จสิ้น' 
                     WHERE table_id = $table_id AND order_session_id = $session_id AND is_checked_out = 0";
    
    if (mysqli_query($conn, $update_query)) {

        header("Location: kitchen.php");
        exit;
    } else {
        echo "<h3 class='text-danger text-center mt-5'>ไม่สามารถอัปเดตสถานะได้</h3>";
    }
} else {
    die("<h3 class='text-danger text-center mt-5'>ข้อมูลไม่ครบถ้วน</h3>");
}

mysqli_close($conn);
?>
