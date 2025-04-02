<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$table_id = $data['table_id'];
$items = $data['items'];

// หา session ล่าสุดของโต๊ะนี้
$session_query = "SELECT MAX(order_session_id) as max_session FROM orders WHERE table_id = $table_id";
$session_result = mysqli_query($conn, $session_query);
$row = mysqli_fetch_assoc($session_result);
$session_id = ($row['max_session'] ?? 0);

// ถ้ายังไม่ได้ checkout → ใช้ session เดิม
// ถ้า checkout ไปแล้ว → สร้าง session ใหม่
$check = "SELECT COUNT(*) AS count FROM orders WHERE table_id = $table_id AND order_session_id = $session_id AND is_checked_out = 0";
$check_result = mysqli_query($conn, $check);
$check_data = mysqli_fetch_assoc($check_result);

if ($check_data['count'] == 0) {
    $session_id += 1; // สร้างรอบใหม่
}

foreach ($items as $item) {
    $name = mysqli_real_escape_string($conn, $item['name']);
    $qty = intval($item['qty']);

    // หาเมนู id จากชื่อ
    $menu_query = "SELECT id FROM menu_items WHERE name = '$name' LIMIT 1";
    $menu_result = mysqli_query($conn, $menu_query);
    if ($menu_row = mysqli_fetch_assoc($menu_result)) {
        $menu_id = $menu_row['id'];
        mysqli_query($conn, "INSERT INTO orders (table_id, menu_item_id, quantity, order_session_id) VALUES ($table_id, $menu_id, $qty, $session_id)");
    }
}

echo json_encode(['success' => true]);
