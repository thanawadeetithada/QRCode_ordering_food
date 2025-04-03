<?php
require 'db.php';

$query = "
    SELECT
        o.table_id,
        o.order_session_id,
        m.name AS menu_name,
        SUM(o.quantity) AS total_qty,
        m.price,
        SUM(o.quantity * m.price) AS total_amount,
        DATE(o.order_time) AS order_date
    FROM orders o
    JOIN menu_items m ON o.menu_item_id = m.id
    WHERE o.is_checked_out = 1 AND o.status = 'เสร็จสิ้น'
    GROUP BY o.table_id, o.order_session_id, m.name, m.price, DATE(o.order_time)
    ORDER BY DATE(o.order_time) DESC, o.table_id, o.order_session_id, m.name
";

$result = mysqli_query($conn, $query);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายการอาหาร</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        margin: 1rem;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #222222;
        padding: 20px;
        height: 8vh;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
    }

    header span {
        font-size: larger;
    }

    header .right-section {
        display: flex;
    }

    .right-section a {
        text-decoration: none;
    }

    header .sidebar {
        position: fixed;
        top: 8vh;
        left: -250px;
        width: 250px;
        height: 92vh;
        background-color: #333;
        color: white;
        padding-top: 20px;
        transition: 0.3s;
        z-index: 9;
    }

    header .sidebar a {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: white;
        font-size: 16px;
        border-bottom: 1px solid #444;
    }

    header .sidebar a:hover {
        background-color: #BFBBBA;
        color: #333;
    }

    header .menu-btn {
        font-size: 1.5rem;
    }

    .logout {
        color: white;
    }

    .logout:hover {
        color: white;
        text-decoration: none;
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
            <button type="button" class="menu-btn" id="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="right-section">
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div id="sidebar" class="sidebar">
            <a href="dashboard.php">หน้ารายการอาหาร</a>
            <a href="order.php">หน้าสั่งอาหาร</a>
            <a href="kitchen.php">ครัวรับออเดอร์</a>
            <a href="user_management.php">จัดการผู้ใช้งาน</a>
            <a href="all_order.php">สรุปการสั่งอาหาร</a>
            <a href="order_checkbill.php">ชำระเงิน</a>
            <a href="gen_QR.php">QR Code</a>
        </div>
    </header>
    <br> <br> <br>

    <?php
    $previous_date = '';
    $daily_total_amount = 0;
    ?>

    <div class="container">
        <h1 class="text-center">สรุปการสั่งอาหาร</h1>
        <br>

        <?php
    foreach ($orders as $order):
        if ($order['order_date'] !== $previous_date):
            if ($previous_date != '') { 
                echo '</tbody>';
                echo '<tfoot><tr><td colspan="6" class="text-end"><strong>ราคารวมทั้งหมด: ฿' . number_format($daily_total_amount, 2) . '</strong></td></tr></tfoot>';
                echo '</table><br>';
            }

            echo "<h5>วันที่: " . htmlspecialchars($order['order_date']) . "</h5>";
            echo '<table class="table table-bordered table-hovershadow-sm">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>โต๊ะ</th>';
            echo '<th>รอบเช็คบิล</th>';
            echo '<th>ชื่ออาหาร</th>';
            echo '<th>จำนวน</th>';
            echo '<th>ราคา/หน่วย</th>';
            echo '<th>ราคารวม</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $daily_total_amount = 0;
            $previous_date = $order['order_date'];
        endif;

        $daily_total_amount += $order['total_amount'];
        ?>

        <tr>
            <td><?php echo $order['table_id']; ?></td>
            <td><?php echo $order['order_session_id']; ?></td>
            <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
            <td><?php echo $order['total_qty']; ?></td>
            <td>฿<?php echo number_format($order['price'], 2); ?></td>
            <td>฿<?php echo number_format($order['total_amount'], 2); ?></td>
        </tr>

        <?php endforeach; ?>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end"><strong>ราคารวมทั้งหมด:
                        ฿<?php echo number_format($daily_total_amount, 2); ?></strong></td>
            </tr>
        </tfoot>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
    document.getElementById("menu-toggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");
        if (sidebar.style.left === "0px") {
            sidebar.style.left = "-250px";
        } else {
            sidebar.style.left = "0";
        }
    });
    </script>
</body>

</html>