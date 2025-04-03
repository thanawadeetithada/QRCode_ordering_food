<?php
session_start();
require 'db.php';

$table_number = isset($_GET['table_number']) ? $_GET['table_number'] : null;


if ($table_number === null) {

    die("<h3 class='text-danger text-center mt-5'>กรุณาเลือกโต๊ะก่อน</h3>");

}
$table_query = mysqli_query($conn, "SELECT id, table_number FROM tables WHERE table_number = '$table_number'");
$table_data = mysqli_fetch_assoc($table_query);

if (!$table_data) {
    die("<h3 class='text-danger text-center mt-5'>โต๊ะไม่ถูกต้อง</h3>");
}

$table_id = $table_data['id'];
$table_number = $table_data['table_number'] ?? '-';

// ดึงข้อมูล session_id
$session_sql = "SELECT MAX(order_session_id) as session_id FROM orders WHERE table_id = $table_id AND is_checked_out = 0";
$session_result = mysqli_query($conn, $session_sql);
$session_row = mysqli_fetch_assoc($session_result);
$session_id = $session_row['session_id'] ?? 0;

// คำสั่ง SQL สำหรับดึงข้อมูลคำสั่งอาหาร
$sql = "SELECT o.status, m.name AS menu_name, SUM(o.quantity) AS total_qty, m.price
        FROM orders o
        JOIN menu_items m ON o.menu_item_id = m.id
        WHERE o.table_id = $table_id AND o.order_session_id = $session_id AND o.is_checked_out = 0
        GROUP BY o.status, m.name, m.price
        ORDER BY FIELD(o.status, 'รอรับออเดอร์', 'กำลังเตรียมอาหาร', 'เสร็จสิ้น'), m.name";

$result = mysqli_query($conn, $sql);

$grouped_orders = [];
$can_checkout = true;

while ($row = mysqli_fetch_assoc($result)) {
    $status = $row['status'];
    if ($status !== 'เสร็จสิ้น') {
        $can_checkout = false;
    }

    if (!isset($grouped_orders[$status])) {
        $grouped_orders[$status] = [];
    }
    $grouped_orders[$status][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งอาหาร</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        padding: 2rem;
        background-color: #f8f9fa;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
        padding: 20px;
        height: 8vh;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
    }

    header button {
        background-color: transparent;
        padding: 0px;
        border: 0px;
        color: white;
        outline: none !important;
    }

    header i {
        font-size: 1.5rem;
        color: black;
    }

    .section-title {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .status-badge {
        font-size: 0.9rem;
        padding: 0.4rem 0.6rem;
    }

    .table {
        border-radius: 10px;
        overflow: hidden;
    }

    .table thead {
        background-color: rgb(95 158 252 / 68%);
    }

    .table td {
        background-color: #dee2e67a;
    }
    </style>
</head>

<body>
    <header class="header">
        <div class="left-section">
            <button type="button" class="menu-btn" id="menu-toggle"
                onclick="window.location.href='order.php?table_number=<?php echo $table_number; ?>'">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
        </div>
    </header>

    <br> <br> <br>

    <div class="container">
        <h1 class="text-center mb-4">ประวัติการสั่งอาหาร โต๊ะที่ <?php echo htmlspecialchars($table_number); ?></h1>

        <?php foreach ($grouped_orders as $status => $orders): ?>
        <h4 class="section-title">
            <span class="badge bg-<?php
                    echo $status === 'รอรับออเดอร์' ? 'secondary' :
                         ($status === 'กำลังเตรียมอาหาร' ? 'warning text-dark' :
                         'success'); ?> status-badge">
                <?php echo htmlspecialchars($status); ?>
            </span>
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hovershadow-sm">
                <thead>
                    <tr>
                        <th>ชื่ออาหาร</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                        <th>ราคารวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                        <td><?php echo $order['total_qty']; ?></td>
                        <td>฿<?php echo number_format($order['price'], 2); ?></td>
                        <td>฿<?php echo number_format($order['price'] * $order['total_qty'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>

        <?php if (empty($grouped_orders)): ?>
        <p class="text-center text-muted">ยังไม่มีรายการอาหารในรอบนี้</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($grouped_orders)): ?>
    <div class="text-center mt-4">
        <form action="checkout.php" method="POST">
            <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
            <button type="submit" class="btn btn-primary btn-lg" <?php echo $can_checkout ? '' : 'disabled'; ?>>
                ชำระเงิน
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">ชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจที่จะยืนยันการชำระเงินใช่ไหม?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                        <button type="submit" class="btn btn-primary">ยืนยัน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
    </script>
</body>

</html>