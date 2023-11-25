<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /animal-foods/");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $animal = $conn->real_escape_string($_POST['animal']);
    $food_name = $conn->real_escape_string($_POST['food_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $extra_info = $conn->real_escape_string($_POST['extra_info']);
    $media_link = $conn->real_escape_string($_POST['media_link']);

    // Insert the new post
    $insertSql = "INSERT INTO submissions (user_id, animal, food_name, description, extra_info, media_link) VALUES ('$user_id', '$animal', '$food_name', '$description', '$extra_info', '$media_link')";
    if ($conn->query($insertSql) === TRUE) {
        $_SESSION['success_message'] = "Content submitted successfully.";

        // Check if this is the user's first post
        $countSql = "SELECT COUNT(*) AS post_count FROM submissions WHERE user_id = '$user_id'";
        $countResult = $conn->query($countSql);
        $countRow = $countResult->fetch_assoc();
        if ($countRow['post_count'] == 1) { // It means this is the first post
            // Get the badge ID for "First Post" badge from the `badges` table
            $badgeSql = "SELECT id FROM badges WHERE name = 'First Post'";
            $badgeResult = $conn->query($badgeSql);
            if ($badgeResult->num_rows > 0) {
                $badgeRow = $badgeResult->fetch_assoc();
                $badgeId = $badgeRow['id'];

                // Insert the badge into the `user_badges` table
                $userBadgeSql = "INSERT INTO user_badges (user_id, badge_id) VALUES ('$user_id', '$badgeId')";
                $conn->query($userBadgeSql);
            }
        }

        // Redirect to home page
        header("Location: /animal-foods/");
        exit;
    } else {
        echo "Error: " . $insertSql . "<br>" . $conn->error;
    }

    $conn->close();
}
