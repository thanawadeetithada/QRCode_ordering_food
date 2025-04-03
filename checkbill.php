<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : null;

if (!$table_id) {
    die("<h3 class='text-danger text-center mt-5'>กรุณาเลือกโต๊ะก่อน</h3>");
}

$table_query = mysqli_query($conn, "SELECT table_number FROM tables WHERE id = $table_id");
$table_data = mysqli_fetch_assoc($table_query);
$table_number = $table_data['table_number'] ?? '-';

$session_sql = "SELECT MAX(order_session_id) as session_id FROM orders WHERE table_id = $table_id AND is_checked_out = 1";
$session_result = mysqli_query($conn, $session_sql);
$session_row = mysqli_fetch_assoc($session_result);
$session_id = $session_row['session_id'] ?? 0;

// ปรับ SQL query ให้กรองเฉพาะออเดอร์ที่มี status = 'รอจ่าย' และ is_checked_out = 1
$sql = "SELECT o.status, m.name AS menu_name, SUM(o.quantity) AS total_qty, m.price
        FROM orders o
        JOIN menu_items m ON o.menu_item_id = m.id
        WHERE o.table_id = $table_id AND o.order_session_id = $session_id 
        AND o.is_checked_out = 1 AND o.status = 'รอจ่าย'
        GROUP BY o.status, m.name, m.price
        ORDER BY FIELD(o.status, 'รอรับออเดอร์', 'กำลังเตรียมอาหาร', 'รอจ่าย', 'เสร็จสิ้น'), m.name";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $grouped_orders = [];
    $can_checkout = true;
    $total_price = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $status = $row['status'];
        if ($status !== 'รอจ่าย') {
            $can_checkout = false;
        }

        if (!isset($grouped_orders[$status])) {
            $grouped_orders[$status] = [];
        }
        $grouped_orders[$status][] = $row;

        $total_price += $row['price'] * $row['total_qty'];
    }
} else {
    $grouped_orders = [];
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จ่ายเงิน</title>
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
            <button type="button" class="menu-btn" id="menu-toggle" onclick="window.location.href='order_checkbill.php'">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
        </div>
    </header>
    <br> <br> <br>

    <div class="container">
        <h1 class="text-center mb-4">ประวัติการสั่งอาหาร โต๊ะที่ <?php echo htmlspecialchars($table_number); ?></h1>

        <?php if (!empty($grouped_orders)): ?>
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
                                <th>ราคาต่อหน่วย</th>
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
        <?php else: ?>
            <p class="text-center text-muted">ยังไม่มีรายการอาหารในรอบนี้</p>
        <?php endif; ?>

        <?php if (!empty($grouped_orders)): ?>
        <div class="text-end mt-4">
            <h4>ราคารวมทั้งหมด: ฿<?php echo number_format($total_price, 2); ?></h4>
        </div>
        <?php endif; ?>

    </div>

    <?php if (!empty($grouped_orders)): ?>
    <div class="text-center mt-4">
        <form action="checkout_by_admin.php" method="POST">
            <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
            <button type="submit" class="btn btn-primary btn-lg" <?php echo $can_checkout ? '' : 'disabled'; ?>>
                ชำระเงิน
            </button>
        </form>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
