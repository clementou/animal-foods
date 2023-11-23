<?php
include '../config/database.php';
include '../templates/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_username'])) {
    include '../src/edit_username.php';
}
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

    <?php include '../templates/footer.php'; ?>
</body>

</html>