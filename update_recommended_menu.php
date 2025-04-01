<?php
require 'db.php';

if (isset($_GET['id']) && isset($_GET['recommended'])) {
    $id = intval($_GET['id']);
    $recommended = intval($_GET['recommended']);

    $query = "UPDATE menu_items SET recommended = $recommended WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "Recommended status updated.";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid parameters.";
}
?>
