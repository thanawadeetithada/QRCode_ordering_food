<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $table_id = intval($_POST['table_id']);
    $session_id = intval($_POST['session_id']);
    
    if ($table_id && $session_id) {
        $sql = "UPDATE orders SET is_checked_out = 1, status = 'เสร็จสิ้น' WHERE table_id = $table_id AND order_session_id = $session_id";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: order_checkbill.php"); 
            exit();
        } else {
            echo "<h3 class='text-center text-danger'>เกิดข้อผิดพลาดในการเช็คเอาท์</h3>";
        }
    } else {
        echo "<h3 class='text-center text-danger'>ข้อมูลไม่ครบถ้วน</h3>";
    }
}
?>
