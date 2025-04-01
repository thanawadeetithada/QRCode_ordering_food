<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? null;

$category_query = "SELECT DISTINCT category FROM menu_items";
$category_result = mysqli_query($conn, $category_query);

$selected_category = $_GET['category'] ?? null;

if ($selected_category) {
    $query = "SELECT * FROM menu_items WHERE category = '" . mysqli_real_escape_string($conn, $selected_category) . "'";
} else {
    $query = "SELECT * FROM menu_items";
}
$result = mysqli_query($conn, $query);


// เพิ่มรายการอาหาร
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $additional_info = $_POST['additional_info'];
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $recommended = isset($_POST['recommended']) ? 1 : 0;
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    $insert_query = "INSERT INTO menu_items (name, category, image, price, additional_info, is_visible, recommended) 
                 VALUES ('$name', '$category', '$image', '$price', '$additional_info', '$is_visible', '$recommended')";
    if (mysqli_query($conn, $insert_query)) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// แก้ไขรายการอาหาร
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $additional_info = $_POST['additional_info'];
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $recommended = isset($_POST['recommended']) ? 1 : 0;
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    if (empty($image)) {
        $image_query = "SELECT image FROM menu_items WHERE id = '$id'";
        $image_result = mysqli_query($conn, $image_query);
        if ($row = mysqli_fetch_assoc($image_result)) {
            $image = $row['image'];
        }
    }

    $update_query = "UPDATE menu_items 
                 SET name = '$name', 
                     category = '$category', 
                     price = '$price',
                     additional_info = '$additional_info', 
                     image = '$image',
                     is_visible = '$is_visible',
                     recommended = '$recommended'
                 WHERE id = '$id'";

if (mysqli_query($conn, $update_query)) {
    $redirect_url = $_SERVER['PHP_SELF'];
    if ($selected_category) {
        $redirect_url .= '?category=' . urlencode($selected_category);
    }
    header("Location: " . $redirect_url);
    exit();
} else {
    echo "Error updating record: " . mysqli_error($conn);
}

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Menu</title>
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

    .content {
        margin: 5vh 15vw;
        text-align: -webkit-center;
    }

    .text-center {
        text-align: center;
    }

    .form-control {
        margin-bottom: 10px;
    }

    .card img {
        padding-top: 16px;
        width: 150px;
        height: 150px;
    }

    .card-center {
        /* display: flex;
        width: 18rem;
        margin-top: 20px;
        align-items: center; */
        margin-top: 20px;
    }

    /* .row {
        max-width: fit-content;
    }

    .row>* {
        padding-left: 0;
    } */

    .top-btn {
        display: flex;
        justify-content: flex-end;
    }

    .dropdown {
        margin-right: 1rem;
    }

    .upload-btn {
        margin-right: 10vw;
    }

    .modal-upload-btn {
        float: right;
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
            <a href="user_management.php">จัดการผู้ใช้งาน</a>
            <a href="record_products.php">บันทึกข้อมูลสินค้า</a>
            <a href="gen_QR.php">QR Code</a>
        </div>
    </header>
    <br> <br> <br>
    <h1 class="text-center">รายการอาหาร</h1>
    <?php if ($selected_category): ?>
    <h5 class="text-center text-muted">กำลังดูหมวดหมู่: <?php echo htmlspecialchars($selected_category); ?></h5>
    <?php endif; ?>

    <br>
    <div class="top-btn">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                หมวดหมู่
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="dashboard.php">ทั้งหมด</a></li>
                <?php while ($cat = mysqli_fetch_assoc($category_result)): ?>
                <li>
                    <a class="dropdown-item" href="?category=<?php echo urlencode($cat['category']); ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <button type="button" class="upload-btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            เพิ่มรายการอาหาร
        </button>
    </div>
    <div class="content">
        <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row justify-content-center">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center mb-4">
                <div class="card" style="width: 18rem;">
                    <img src="<?php echo $row['image']; ?>" class="card-img-top mx-auto d-block" alt="...">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $row['name']; ?></h5>
                        <p class="card-text">
                            <?php echo $row['additional_info'] ? $row['additional_info'] : 'ไม่มีข้อมูลเพิ่มเติม'; ?>
                        </p>
                        <p class="card-text">ราคา: ฿<?php echo number_format($row['price'], 2); ?></p>

                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" id="is_visible_<?php echo $row['id']; ?>"
                                <?php echo $row['is_visible'] == 1 ? 'checked' : ''; ?>
                                data-id="<?php echo $row['id']; ?>">
                            <label class="form-check-label mb-0" for="is_visible_<?php echo $row['id']; ?>">
                                <?php echo $row['is_visible'] == 1 ? 'แสดงสินค้า' : 'แสดงสินค้า'; ?>
                            </label>
                        </div>
                        <br>

                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                            <input class="form-check-input recommended-toggle" type="checkbox"
                                id="is_recommended_<?php echo $row['id']; ?>"
                                <?php echo $row['recommended'] == 1 ? 'checked' : ''; ?>
                                data-id="<?php echo $row['id']; ?>">
                            <label class="form-check-label mb-0" for="is_recommended_<?php echo $row['id']; ?>">
                                สินค้าแนะนำ
                            </label>
                        </div>
                        <br>

                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?php echo $row['id']; ?>" 
                            data-name="<?php echo $row['name']; ?>"
                            data-category="<?php echo $row['category']; ?>" 
                            data-price="<?php echo $row['price']; ?>"
                            data-additional_info="<?php echo $row['additional_info']; ?>"
                            data-image="<?php echo $row['image']; ?>"
                            data-is_visible="<?php echo $row['is_visible']; ?>"
                            data-recommended="<?php echo $row['recommended']; ?>">
                            แก้ไข
                        </a>
                        <a href="#" class="btn btn-danger btn-delete" data-id="<?php echo $row['id']; ?>"
                            data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">ลบ</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php else: ?>
        <h3 class="text-center">ไม่มีรายการอาหาร</h3>
        <?php endif; ?>
    </div>

    <!-- Modal เพิ่ม-->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">อัปโหลดรายการอาหาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name">ชื่ออาหาร</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">หมวดหมู่</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="price">ราคา</label>
                            <input type="number" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="additional_info">ข้อมูลเพิ่มเติม</label>
                            <textarea class="form-control" id="additional_info" name="additional_info"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">เลือกรูปภาพ</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="upload_is_visible" name="is_visible"
                                checked>
                            <label class="form-check-label" for="upload_is_visible">แสดงสินค้า</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="upload_recommended" name="recommended">
                            <label class="form-check-label" for="upload_recommended">สินค้าแนะนำ</label>
                        </div>

                        <button type="submit" name="upload"
                            class="modal-upload-btn btn btn-primary mt-3">อัปโหลด</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ลบ -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a href="#" id="deleteConfirmBtn" class="btn btn-danger">ยืนยันการลบ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไข -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">แก้ไขรายการอาหาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_name">ชื่ออาหาร</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_category">หมวดหมู่</label>
                            <input type="text" class="form-control" id="edit_category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_price">ราคา</label>
                            <input type="number" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_additional_info">ข้อมูลเพิ่มเติม</label>
                            <textarea class="form-control" id="edit_additional_info" name="additional_info"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_image">เลือกรูปภาพ</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="edit_is_visible" name="is_visible">
                            <label class="form-check-label" for="edit_is_visible">แสดงสินค้า</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="edit_recommended" name="recommended">
                            <label class="form-check-label" for="edit_recommended">สินค้าแนะนำ</label>
                        </div>

                        <button type="submit" name="update"
                            class="modal-upload-btn btn btn-primary mt-3">บันทึกการแก้ไข</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
    // bar ข้างซ้าย
    document.getElementById("menu-toggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");
        if (sidebar.style.left === "0px") {
            sidebar.style.left = "-250px";
        } else {
            sidebar.style.left = "0";
        }
    });

    // update status show
    // สำหรับ is_visible เท่านั้น
    document.querySelectorAll('.form-check-input[id^="is_visible_"]').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var itemId = this.getAttribute('data-id');
            var isVisible = this.checked ? 1 : 0;

            fetch('update_visibility_menu.php?id=' + itemId + '&is_visible=' + isVisible)
                .then(response => response.text())
                .then(data => {
                    console.log("Visibility: ", data);
                });
        });
    });


    // อัปเดต recommended
    document.querySelectorAll('.recommended-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var itemId = this.getAttribute('data-id');
            var recommended = this.checked ? 1 : 0;

            fetch('update_recommended_menu.php?id=' + itemId + '&recommended=' + recommended)
                .then(response => response.text())
                .then(data => {
                    console.log("Recommended: ", data); // debug
                });
        });
    });

    // แก้ไข
    document.querySelectorAll('.btn-primary[data-bs-toggle="modal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const category = this.getAttribute('data-category');
            const price = this.getAttribute('data-price');
            const additional_info = this.getAttribute('data-additional_info');
            const is_visible = this.getAttribute('data-is_visible') == "1";
            const recommended = this.getAttribute('data-recommended') == "1";
            const image = this.getAttribute('data-image');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_additional_info').value = additional_info;
            document.getElementById('edit_is_visible').checked = is_visible;
            document.getElementById('edit_recommended').checked = recommended;
        });
    });

    // ลบ
    document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function() {
            deleteId = this.getAttribute('data-id');
            document.getElementById('deleteConfirmBtn').href = 'delete_menu.php?delete_id=' + deleteId;
        });
    });
    </script>
</body>

</html>