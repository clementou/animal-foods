<?php
include '../config/database.php';
include '../templates/header.php';
include '../src/display_content.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tag_submission_id'])) {
    include '../src/add_tags.php';
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Welcome</title>
    <!-- Link to your CSS files, if any -->
</head>

<body>
    <h1>Welcome Back, <?php echo $_SESSION['username']; ?>!</h1>

    <!-- Search Form -->
    <form action="" method="get">
        <input type="text" name="search" placeholder="Search submissions...">
        <button type="submit">Search</button>
    </form>


    <!-- Content Submission Form -->
    <form action="/animal-foods/src/submit_content.php" method="post">
        <input type="text" name="animal" placeholder="Animal" required>
        <input type="text" name="food_name" placeholder="Food Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <textarea name="extra_info" placeholder="Extra Info"></textarea>
        <input type="text" name="media_link" placeholder="Media Link">
        <button type="submit">Submit</button>
    </form>

    <!-- Display Submitted Content and Tagging Form -->
    <?php
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    displayContent($conn, $searchTerm, $_SESSION['user_id']);
    ?>

</body>

</html>