<?php
// Include database configuration file
include '../config/database.php';

// Check if the form data is set
if (isset($_POST['tag_submission_id']) && isset($_POST['tags'])) {
    $submissionId = $_POST['tag_submission_id'];
    $tagString = $_POST['tags'];

    // Sanitize the inputs
    $submissionId = $conn->real_escape_string($submissionId);
    $tagString = $conn->real_escape_string($tagString);

    // Split the tag string into an array of individual tags
    $tags = explode(',', $tagString);

    foreach ($tags as $tag) {
        $tag = trim($tag); // Remove whitespace

        if (!empty($tag)) {
            // Check if the tag exists in the database
            $tagQuery = "SELECT id FROM tags WHERE name = '$tag'";
            $tagResult = $conn->query($tagQuery);

            if ($tagResult->num_rows > 0) {
                // Tag exists, fetch its ID
                $tagRow = $tagResult->fetch_assoc();
                $tagId = $tagRow['id'];
            } else {
                // Tag does not exist, insert it
                $insertTagQuery = "INSERT INTO tags (name) VALUES ('$tag')";
                $conn->query($insertTagQuery);
                $tagId = $conn->insert_id;
            }

            // Link the tag with the submission
            $insertSubmissionTagQuery = "INSERT INTO submission_tags (submission_id, tag_id) VALUES ('$submissionId', '$tagId')";
            $conn->query($insertSubmissionTagQuery);
        }
    }
}

header("Location: index.php");
exit();
