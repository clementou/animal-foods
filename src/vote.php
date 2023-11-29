<?php
include '../config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id']; // assuming you store user_id in session
    $submissionId = $_POST['submission_id'];
    $voteType = $_POST['vote'];

    // Check if user has already voted on this submission
    $checkSql = "SELECT * FROM votes WHERE user_id = $userId AND submission_id = $submissionId";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        // Update existing vote
        $updateSql = "UPDATE votes SET vote_type = '$voteType' WHERE user_id = $userId AND submission_id = $submissionId";
        $conn->query($updateSql);
    } else {
        // Insert new vote
        $insertSql = "INSERT INTO votes (user_id, submission_id, vote_type) VALUES ($userId, $submissionId, '$voteType')";
        $conn->query($insertSql);
    }

    header('Location: /animal-foods/'); // Redirect back to content page
    exit();
}
