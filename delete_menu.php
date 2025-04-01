<?php
require 'db.php';

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    if (!empty($delete_id)) {
        $delete_query = "DELETE FROM menu_items WHERE id = $delete_id";

        if (mysqli_query($conn, $delete_query)) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
