<?php
require 'db.php';

$query = "SELECT o.id AS order_id, o.table_id, t.table_number 
          FROM orders o
          JOIN tables t ON o.table_id = t.id 
          WHERE o.is_checked_out = 0 AND o.status = 'รอรับออเดอร์'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
while ($row = mysqli_fetch_assoc($result)) {
    $tableId = $row['table_id'];
    $tableNumber = $row['table_number'];
    echo "<div class='card order-card' data-id='{$tableId}'>
            <div class='card-body'>
                <h5 class='card-title'>โต๊ะ: " . htmlspecialchars($tableNumber) . "</h5>
            </div>
          </div>";
}

} else {
    echo "<h3 class='text-center mt-4'>ยังไม่มีออเดอร์</h3>";
}

mysqli_close($conn);
?>