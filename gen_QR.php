<?php
include 'db.php';

$result = $conn->query("SELECT * FROM tables");
$tables = [];
while ($row = $result->fetch_assoc()) {
    $tables[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Table Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    .table-form {
        width: 25%;
    }

    .card {
        width: 75%;
    }

    .qr-print-container {
        display: none;
    }

    @media print {

        header,
        .header,
        .logout,
        .menu-btn,
        .right-section,
        .card-body,
        .card-title,
        .btn,
        .form-control,
        h1 {
            display: none;
        }

        .no-print {
            display: none !important;
        }

        .qr-print-container {
            width: 100vw;
            display: flex !important;
            flex-wrap: wrap;
            justify-content: flex-start;
            justify-content: center;
        }

        .qr-print-card {
            width: 25%;

            margin: 10px;
            text-align: center;
            page-break-inside: avoid;
        }

        .qr-print-card img {
            width: 100%;
        }

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
    <h1 class="text-center">จัดการ QR Code โต๊ะอาหาร</h1>
    <br>
    <form action="add_table.php" method="post" class="d-flex justify-content-end mb-4">
        <input type="text" name="table_number" class="table-form form-control me-2" placeholder="หมายเลขโต๊ะ" required>
        <button class="btn btn-success">เพิ่มโต๊ะ</button>&nbsp;
        <button class="btn btn-primary" id="print-all-btn">ปริ้นทั้งหมด</button>
    </form>
    <br>
    <div class="row no-print">
        <?php foreach ($tables as $row): ?>
        <div class="col-md-3 mb-4">
            <div class="card">
                <img src="qrcodes/<?= $row['table_number'] ?>.png" class="card-img-top" alt="QR">
                <div class="card-body text-center">
                    <h5 class="card-title">โต๊ะ <?= $row['table_number'] ?></h5>
                    <a class="btn btn-secondary btn-sm" onclick="printSingleQR('<?= $row['table_number'] ?>')">ปริ้น</a>
                    <button class="btn btn-danger btn-sm"
                        onclick="confirmDelete(<?= $row['id'] ?>, '<?= $row['table_number'] ?>')">ลบ</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="qr-print-container">
        <?php foreach ($tables as $row): ?>
        <div class="qr-print-card">
            <img src="qrcodes/<?= $row['table_number'] ?>.png" alt="QR">
            <div>โต๊ะ <?= $row['table_number'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div id="print-single-container" style="display:none;"></div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบโต๊ะ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจหรือไม่ว่าต้องการลบ <strong id="modalTableNumber"></strong> ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">ลบเลย!</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แจ้งเตือนโต๊ะซ้ำ -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">เกิดข้อผิดพลาด</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <strong>โต๊ะหมายเลขนี้มีอยู่แล้วในระบบ!</strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
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

    document.getElementById("print-all-btn").addEventListener("click", function(e) {
        e.preventDefault();
        window.print();
    });

    function printSingleQR(tableNumber) {
        const printContainer = document.getElementById("print-single-container");

        printContainer.innerHTML = '';

        const qrCard = `
        <div style="width:100vw; display:flex; justify-content:center; align-items:center; flex-direction:column; margin-top:10%;">
            <div style="width:25%; text-align:center; page-break-inside: avoid;">
                <img src="qrcodes/${tableNumber}.png" style="width:100%;">
                <div style="margin-top:10px;">โต๊ะ ${tableNumber}</div>
            </div>
        </div>
    `;

        printContainer.innerHTML = qrCard;

        const win = window.open('', '', 'width=1300,height=800');
        win.document.write(`<html><head><title>Print QR</title></head><body>${printContainer.innerHTML}</body></html>`);
        win.document.close();
        win.focus();
        win.print();
        win.close();
    }

    function confirmDelete(id, tableNumber) {
        document.getElementById('modalTableNumber').textContent = `โต๊ะ ${tableNumber}`;
        document.getElementById('confirmDeleteBtn').href = `delete_table.php?id=${id}`;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    window.addEventListener('load', () => {
        errorModal.show();
    });
    <?php endif; ?>

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }
    </script>
</body>

</html>