<?php
// ตรวจสอบว่าได้รับหมายเลขโต๊ะผ่าน URL หรือไม่
$table_number = isset($_GET['table']) ? $_GET['table'] : null;

if ($table_number) {
    echo "<h1>สั่งอาหารสำหรับโต๊ะ " . htmlspecialchars($table_number) . "</h1>";

    // สมมติว่ามีเมนูอาหารในฐานข้อมูล
    // คุณสามารถดึงข้อมูลเมนูจากฐานข้อมูลของคุณได้ที่นี่
    echo "<ul>
            <li>เมนู 1</li>
            <li>เมนู 2</li>
            <li>เมนู 3</li>
          </ul>";

    // ตัวอย่างฟอร์มสำหรับการสั่งอาหาร (อาจจะปรับให้เหมาะสม)
    echo "<form action='submit_order.php' method='post'>
            <input type='hidden' name='table_number' value='" . htmlspecialchars($table_number) . "'>
            <button type='submit'>ยืนยันการสั่ง</button>
          </form>";

} else {
    echo "<h1>กรุณาเลือกโต๊ะ</h1>";
}
?>
