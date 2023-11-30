<?php

/**
 * Logged In User Content
 * 
 * This template is displayed when a user is authenticated and logged in.
 * It includes features such as a welcome message, search functionality, 
 * content submission form, and display of submitted content.
 * 
 * PHP version 8.3
 */

include '../config/database.php';
include '../templates/header.php';
include '../src/display_content.php';

// Handle tag submission if POST request is made
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tag_submission_id'])) {
    include '../src/add_tags.php';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Welcome</title>
    <!-- Link to your CSS files, if any -->
    <link rel="stylesheet" type="text/css" href="path_to_your_stylesheet.css">
</head>

<body>
    <div class="welcome-message">
        <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    </div>

    <!-- Search Form -->
    <div class="search-form">
        <form action="" method="get">
            <input type="text" name="search" placeholder="Search submissions...">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Content Submission Form -->
    <div class="submission-form">
        <form action="/animal-foods/src/submit_content.php" method="post">
            <input type="text" name="animal" placeholder="Animal" required>
            <input type="text" name="food_name" placeholder="Food Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <textarea name="extra_info" placeholder="Extra Info"></textarea>
            <input type="text" name="media_link" placeholder="Media Link">
            <button type="submit">Submit</button>
        </form>
    </div>

    <!-- Display Submitted Content and Tagging Form -->
    <div class="content-display">
        <?php
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        displayContent($conn, $searchTerm, $_SESSION['user_id']);
        ?>
    </div>

</body>

</html>