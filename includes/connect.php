<?php
$type = "localhost";
$username = "root";
$password = "";
$database = "caseblaze";

$conn = mysqli_connect($type, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>