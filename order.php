<?php
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งอาหาร</title>
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

    .card-img-top {
        height: 180px;
        object-fit: cover;
    }

    .quantity-control button {
        width: 32px;
        height: 32px;
    }

    .quantity-control input {
        width: 45px;
        text-align: center;
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .container {
        margin-top: 10vh;
    }

    .container h1 {
        text-align: center;
    }

    .select-table {
        display: flex;
        align-content: center;
        justify-content: flex-end;
        align-items: center;
    }

    .form-select {
        width: auto;
    }

    .form-label {
        margin-bottom: 0px;
    }

    .card-body {
        align-items: center;
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

    <div class="container">
        <h1 class="mb-4">สั่งอาหาร</h1>

        <div class="select-table mb-4">
            <label for="tableSelect" class="form-label">เลือกโต๊ะ</label> &nbsp;&nbsp;
            <select class="form-select" id="tableSelect">
                <?php
        $result = mysqli_query($conn, "SELECT id, table_number FROM tables");
        while($row = mysqli_fetch_assoc($result)) {
          echo '<option value="' . $row['id'] . '">โต๊ะ ' . $row['table_number'] . '</option>';
        }
        ?>
            </select>
        </div>
        <br>
        <div class="row" id="menuContainer">
            <?php

    $category_query = "SELECT DISTINCT category FROM menu_items WHERE is_visible = 1";
    $category_result = mysqli_query($conn, $category_query);

    while ($cat = mysqli_fetch_assoc($category_result)) {
        $current_category = $cat['category'];
        echo '<div class="col-12"><h3 class="mt-4 mb-3">' . htmlspecialchars($current_category) . '</h3></div>';

        $item_query = "SELECT * FROM menu_items WHERE category = '" . mysqli_real_escape_string($conn, $current_category) . "' AND is_visible = 1";
        $item_result = mysqli_query($conn, $item_query);

        while ($item = mysqli_fetch_assoc($item_result)) {
            echo '<div class="col-sm-6 col-md-4 col-lg-3 mb-4">';
            echo '  <div class="card h-100 shadow">';
            echo '    <img src="' . htmlspecialchars($item['image']) . '" class="card-img-top" alt="...">';
            echo '    <div class="card-body d-flex flex-column">';
            echo '      <h5 class="card-title">' . htmlspecialchars($item['name']) . '</h5>';
            echo '      <p class="card-text">' . htmlspecialchars($item['additional_info']) . '</p>';
            echo '      <p class="card-text fw-bold">฿' . number_format($item['price'], 2) . '</p>';
            echo '      <div class="mt-auto d-flex justify-content-between align-items-center">';
            echo '        <div class="quantity-control d-flex align-items-center">';
            echo '          <button class="btn btn-outline-secondary btn-sm" onclick="decreaseQty(this)">-</button>';
            echo '          <input type="text" class="form-control mx-1" value="0" readonly />';
            echo '          <button class="btn btn-outline-secondary btn-sm" onclick="increaseQty(this)">+</button>';
            echo '        </div>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    }
    ?>
        </div>
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="submitOrder()">สั่งอาหาร</button>
            <button class="btn btn-outline-primary" onclick="goToHistory()">ดูประวัติการสั่งอาหาร</button>
        </div>
    </div>

    <!-- Modal สั่งอาหาร-->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">สั่งอาหารสำเร็จ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body">
                    ขอบคุณที่สั่งอาหาร ออเดอร์ของคุณกำลังถูกส่งไปยังครัว 🍽️
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: ไม่ได้เลือกอาหาร -->
    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="warningModalLabel">ยังไม่ได้เลือกอาหาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body">
                    กรุณาเลือกอาหารอย่างน้อย 1 รายการก่อนกดสั่ง 🙏
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">โอเค</button>
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

    function increaseQty(button) {
        const input = button.parentElement.querySelector('input');
        input.value = parseInt(input.value) + 1;
    }

    function decreaseQty(button) {
        const input = button.parentElement.querySelector('input');
        if (parseInt(input.value) > 0) {
            input.value = parseInt(input.value) - 1;
        }
    }

    function submitOrder() {
        const items = [];
        const tableId = document.getElementById('tableSelect').value;

        document.querySelectorAll('#menuContainer .card').forEach(card => {
            const quantityInput = card.querySelector('input[type="text"]');
            const qty = parseInt(quantityInput.value);
            if (qty > 0) {
                const name = card.querySelector('.card-title').textContent;
                items.push({
                    name,
                    qty
                });
            }
        });

        if (items.length === 0) {
            const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
            warningModal.show();
            return;
        }

        fetch('submit_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    table_id: tableId,
                    items
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = new bootstrap.Modal(document.getElementById('successModal'));
                    modal.show();

                    document.querySelectorAll('#menuContainer input[type="text"]').forEach(input => input.value =
                        '0');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.error);
                }
            });

    }

    function goToHistory() {
        const tableId = document.getElementById('tableSelect').value;
        window.location.href = 'history_order.php?table_id=' + tableId;
    }

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tableId = urlParams.get('table');

        if (tableId) {
            document.getElementById('tableSelect').value = tableId;
        }
    }
    </script>
</body>

</html>