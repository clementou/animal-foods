<?php
include '../config/database.php';

function redirectToHome()
{
    header("Location: /animal-foods/");
    exit;
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Redirect to home page
            redirectToHome();
        } else {
            // Password is not correct
            echo "Incorrect password.";
        }
    } else {
        // No user found with that username
        echo "Username does not exist.";
    }

    $conn->close();
}
