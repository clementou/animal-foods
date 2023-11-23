<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "animal_foods";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!mysqli_select_db($conn, $database)) {
    $sql = "CREATE DATABASE $database";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully\n";
        mysqli_select_db($conn, $database);
    } else {
        echo "Error creating database: " . $conn->error;
    }
} else {
    // echo "Connected to database $database\n";
}
