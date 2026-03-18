<?php
if (!isset($conn)) {
    $type = "localhost";
    $username = "root";
    $password = "";
    $database = "caseblaze";

    $conn = mysqli_connect($type, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $conn->set_charset("utf8mb4");
}
?>