<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ครัวรับออเดอร์</title>
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

    @keyframes blink {
        0% {
            background-color: #ffcccc;
        }

        50% {
            background-color: rgba(255, 102, 102, 0.62);
        }

        100% {
            background-color: #ffcccc;
        }
    }

    .card {
        width: 13rem;
        margin: 10px;
        box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.2);
        animation: blink 1s infinite;
        cursor: pointer;
    }

    .row {
        justify-content: center;
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
            <?php if ($_SESSION['user_role'] == 'superadmin'): ?>
            <a href="dashboard.php">หน้ารายการอาหาร</a>
            <a href="order.php">หน้าสั่งอาหาร</a>
            <a href="kitchen.php">ครัวรับออเดอร์</a>
            <a href="user_management.php">จัดการผู้ใช้งาน</a>
            <a href="all_order.php">สรุปการสั่งอาหาร</a>
            <a href="order_checkbill.php">ชำระเงิน</a>
            <a href="gen_QR.php">QR Code</a>
            <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
            <a href="kitchen.php">ครัวรับออเดอร์</a>
            <?php endif; ?>
        </div>
    </header>
    <br> <br> <br>
    <h1 class="text-center">ครัวรับออเดอร์</h1>

    <div id="orders-list" class="row">

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

    function updateStatus(tableId, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_order_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = xhr.responseText.trim();
                if (response !== "สถานะออเดอร์ได้รับการอัปเดตแล้ว") {
                    alert(response);
                } else {
                    callback();
                }
            } else {
                alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
            }
        };
        xhr.send('table_id=' + tableId + '&status=กำลังเตรียมอาหาร');
    }

    function fetchOrders() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_orders.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('orders-list').innerHTML = xhr.responseText;

                const cards = document.querySelectorAll('.order-card');
                cards.forEach(card => {
                    card.addEventListener('click', function() {
                        const tableId = card.getAttribute('data-id');
                        updateStatus(tableId, function() {
                            window.location.href = `order_detail.php?id=${tableId}`;
                        });
                    });
                });
            }
        }
        xhr.send();
    }

    setInterval(fetchOrders, 5000);

    window.onload = fetchOrders;
    </script>
</body>

</html>