<?php
$con = mysqli_connect("localhost","root", "", "edutouri_edutourism_lk");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to handle special characters
mysqli_set_charset($con, "utf8mb4");


