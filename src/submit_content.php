<?php
session_start();
include '../config/database.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $animal = $conn->real_escape_string($_POST['animal']);
    $food_name = $conn->real_escape_string($_POST['food_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $extra_info = $conn->real_escape_string($_POST['extra_info']);
    $media_link = $conn->real_escape_string($_POST['media_link']);

    $sql = "INSERT INTO submissions (user_id, animal, food_name, description, extra_info, media_link) VALUES ('$user_id', '$animal', '$food_name', '$description', '$extra_info', '$media_link')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Content submitted successfully.";
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
