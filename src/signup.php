<?php
include '../config/database.php';

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $conn->real_escape_string($_POST['email']);

    if (!isValidEmail($email)) {
        echo "Invalid email format";
        return;
    }

    // Check if username or email already exists
    $checkUser = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $result = $conn->query($checkUser);

    if ($result->num_rows > 0) {
        echo "Username or Email already exists.";
        return;
    }

    // Insert new user
    $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Account created successfully. You can now login.";
        header("Location: /animal-foods/");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
