<!DOCTYPE html>
<html>

<head>
    <title>Your Site Title</title>
    <!-- Link to your CSS files, if any -->
</head>

<body>
    <header>
        <!-- Home Button -->
        <a href="/animal-foods/" class="home-button">Home</a>
        <br>

        <?php
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // Fetch unread notifications for the user
            $notificationQuery = "SELECT * FROM notifications WHERE user_id = '$userId' AND is_read = 0"; // is_read is assumed to be a BOOLEAN or TINYINT
            $notificationResult = $conn->query($notificationQuery);

            echo '<div class="notifications">';
            while ($notification = $notificationResult->fetch_assoc()) {
                echo '<div class="notification">';
                echo '<a href="' . $notification['link'] . '">' . $notification['message'] . '</a>';
                echo '</div>';
            }
            echo '</div>';
            echo '<a href="/animal-foods/dashboard">Dashboard</a>';
            echo '<br>';
            echo '<a href="/animal-foods/src/logout.php">Logout</a>';
        } else {
            echo '<a href="/animal-foods/login_form">Login</a>';
            echo '<br>';
            echo '<a href="/animal-foods/signup_form">Signup</a>';
        }
        ?>
    </header>