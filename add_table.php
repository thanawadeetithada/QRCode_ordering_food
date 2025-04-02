<?php
require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

include 'db.php';

$table_number = $_POST['table_number'] ?? null;

if ($table_number) {
    $check = $conn->prepare("SELECT id FROM tables WHERE table_number = ?");
    $check->bind_param("s", $table_number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO tables (table_number) VALUES (?)");
        $stmt->bind_param("s", $table_number);
        $stmt->execute();

        if (!file_exists('qrcodes')) {
            mkdir('qrcodes', 0777, true);
        }

        $filename = 'qrcodes/' . $table_number . '.png';

        $qrContent = "http://localhost/QRCode_ordering_food/order.php?table=" . urlencode($table_number);

        $qrCode = new QrCode($qrContent);
        $writer = new PngWriter();
        $writer->write($qrCode)->saveToFile($filename);

        header("Location: gen_QR.php");
        exit;
    } else {
        header("Location: gen_QR.php?error=duplicate");
        exit;
    }
} else {
    echo "กรุณาใส่หมายเลขโต๊ะ";
}
?>
