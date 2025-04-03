<?php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$table_id = isset($data['table_id']) ? intval($data['table_id']) : null;

if ($table_id) {
    $query = "SELECT table_number FROM tables WHERE id = $table_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $table_number = $row['table_number'];

        echo json_encode(['success' => true, 'table_number' => $table_number]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบหมายเลขโต๊ะ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid table_id']);
}
?>
