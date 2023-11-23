<?php
include 'config/database.php';
include 'src/display_content.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Welcome</title>
    <!-- Link to your CSS files, if any -->
</head>

<body>
    <h1>Welcome Back, <?php echo $_SESSION['username']; ?>!</h1>

    <!-- Logout Button -->
    <a href="src/logout.php" class="logout-button">Logout</a>

    <!-- Search Form -->
    <form action="" method="get"> <!-- You can specify an action if needed -->
        <input type="text" name="search" placeholder="Search submissions...">
        <button type="submit">Search</button>
    </form>


    <!-- Content Submission Form -->
    <form action="src/submit_content.php" method="post">
        <input type="text" name="animal" placeholder="Animal" required>
        <input type="text" name="food_name" placeholder="Food Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <textarea name="extra_info" placeholder="Extra Info"></textarea>
        <input type="text" name="media_link" placeholder="Media Link">
        <button type="submit">Submit</button>
    </form>

    <!-- Display Submitted Content -->
    <?php
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    displayContent($conn, $searchTerm);
    ?>

</body>

</html>