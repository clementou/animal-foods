<!DOCTYPE html>
<html>

<head>
    <title>Your Site Title</title>
    <!-- Link to your CSS files, if any -->
</head>

<body>
    <header>
        <!-- Home Button -->
        <a href="index.php" class="home-button">Home</a>
        <br>

        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="dashboard.php">Dashboard</a>';
            echo '<br>';
            echo '<a href="../src/logout.php">Logout</a>';
        } else {
            echo '<a href="../templates/login_form.php">Login</a>';
            echo '<br>';
            echo '<a href="../templates/signup_form.php">Signup</a>';
        }
        ?>
    </header>