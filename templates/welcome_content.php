<?php

/**
 * Welcome Content for Guests
 * 
 * This template is displayed for users who are not logged in.
 * It includes a search functionality and displays general site information,
 * with prompts for login or registration.
 * 
 * PHP version 8.3
 */

include '../templates/header.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Welcome to Animal Foods</title>
    <!-- Link to your CSS files, if any -->
    <link rel="stylesheet" type="text/css" href="path_to_your_stylesheet.css">
</head>

<body>
    <!-- Search Form -->
    <div class="search-form">
        <form action="" method="get">
            <input type="text" name="search" placeholder="Search submissions...">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php
    include '../config/database.php';
    include '../src/display_content.php';

    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    displayContent($conn, $searchTerm);
    ?>
</body>

</html>