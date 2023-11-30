<?php
session_start();
include '../config/database.php';

// Redirect to homepage if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /animal-foods/");
    exit;
}

// Handling POST request for content submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $animal = $conn->real_escape_string($_POST['animal']);
    $food_name = $conn->real_escape_string($_POST['food_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $extra_info = $conn->real_escape_string($_POST['extra_info']);
    $media_link = $conn->real_escape_string($_POST['media_link']);

    // Insert the new post
    $insertSql = "INSERT INTO submissions (user_id, animal, food_name, description, extra_info, media_link) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param('isssss', $user_id, $animal, $food_name, $description, $extra_info, $media_link);

    if ($stmt->execute()) {
        $submissionId = $conn->insert_id;

        // Automatically upvote the post by the user who submitted it
        $autoUpvoteSql = "INSERT INTO votes (user_id, submission_id, vote_type) VALUES (?, ?, 'upvote')";
        $upvoteStmt = $conn->prepare($autoUpvoteSql);
        $upvoteStmt->bind_param('ii', $user_id, $submissionId);
        $upvoteStmt->execute();

        $_SESSION['success_message'] = "Content submitted successfully.";

        // Check for user's first post and award badge if applicable
        $countSql = "SELECT COUNT(*) AS post_count FROM submissions WHERE user_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param('i', $user_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();

        if ($countRow['post_count'] == 1) {
            // Award 'First Post' badge
            $badgeSql = "SELECT id FROM badges WHERE name = 'First Post'";
            $badgeResult = $conn->query($badgeSql);
            if ($badgeResult->num_rows > 0) {
                $badgeRow = $badgeResult->fetch_assoc();
                $badgeId = $badgeRow['id'];

                $userBadgeSql = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
                $userBadgeStmt = $conn->prepare($userBadgeSql);
                $userBadgeStmt->bind_param('ii', $user_id, $badgeId);
                $userBadgeStmt->execute();
            }
        }

        // Redirect to home page
        header("Location: /animal-foods/");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
