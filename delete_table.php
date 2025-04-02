<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT table_number FROM tables WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $tableNumber = $row['table_number'];
        $qrPath = "qrcodes/{$tableNumber}.png";

        $deleteStmt = $conn->prepare("DELETE FROM tables WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();

        if (file_exists($qrPath)) {
            unlink($qrPath);
        }

        header("Location: gen_QR.php?msg=delete_success");
        exit();
    } else {
        header("Location: gen_QR.php?error=not_found");
        exit();
    }
} else {
    header("Location: gen_QR.php?error=invalid_request");
    exit();
}
?>
