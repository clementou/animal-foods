<?php
include '../config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $submissionId = $_POST['submission_id'];

    // Check if already favorited
    $checkSql = "SELECT * FROM favorites WHERE user_id = $userId AND submission_id = $submissionId";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Insert new favorite
        $insertSql = "INSERT INTO favorites (user_id, submission_id) VALUES ($userId, $submissionId)";
        $conn->query($insertSql);
    }

    header('Location: /animal-foods/'); // Redirect back to content page
    exit();
}
