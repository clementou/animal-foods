<?php
session_start();
include '../config/database.php';
include '../templates/header.php';
$user_id = $_SESSION['user_id'];

// Check if the user has the "First Post" badge
$badgeCheckSql = "SELECT b.name, b.image_url FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = '$user_id' AND b.name = 'First Post'";
$badgeCheckResult = $conn->query($badgeCheckSql);
if ($badgeCheckResult->num_rows > 0) {
    $badge = $badgeCheckResult->fetch_assoc();
    echo "<div id='badge-container'>";
    echo "<h3>Congratulations! You've earned a badge:</h3>";
    echo "<img src='" . $badge['image_url'] . "' alt='" . $badge['name'] . "' title='" . $badge['name'] . "'>";
    echo "<p>" . $badge['name'] . "</p>";
    echo "</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_username'])) {
    include '../src/edit_username.php';
}

$favoritesSql = "SELECT s.* FROM favorites f JOIN submissions s ON f.submission_id = s.id WHERE f.user_id = '$user_id'";
$favoritesResult = $conn->query($favoritesSql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>User Dashboard</title>
    <!-- Link to your CSS files, if any -->
</head>

<body>
    <h1>User Dashboard</h1>

    <!-- Edit Username Form -->
    <form action="" method="post">
        <label for="new_username">New Username:</label>
        <input type="text" id="new_username" name="new_username" required>
        <button type="submit">Update Username</button>
    </form>

    <!-- Display Favorited Posts -->
    <h2>Your Favorited Posts</h2>
    <?php
    if ($favoritesResult->num_rows > 0) {
        while ($row = $favoritesResult->fetch_assoc()) {
            echo "<div class='favorite-post'>";
            echo "<h3>" . htmlspecialchars($row['food_name']) . "</h3>";
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            // Add more details as you like, e.g., images, extra info, etc.
            echo "</div>";
        }
    } else {
        echo "<p>You have no favorited posts.</p>";
    }

    include '../templates/footer.php'; ?>
</body>

</html>