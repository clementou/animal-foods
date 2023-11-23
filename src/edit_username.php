<?php
session_start();

include '../config/database.php';

if (isset($_SESSION['user_id']) && isset($_POST['new_username'])) {
    $userId = $_SESSION['user_id']; // Assuming you store user's ID in session upon login
    $newUsername = trim($_POST['new_username']);

    // Validate new username
    if (empty($newUsername)) {
        echo "Username cannot be empty!";
        exit;
    }

    // Check if the new username is already taken
    $checkQuery = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $newUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "This username is already taken!";
        exit;
    }

    // Update the username in the database
    $updateQuery = "UPDATE users SET username = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newUsername, $userId);

    if ($stmt->execute()) {
        echo "Username updated successfully!";
        // Update session variable if needed
        $_SESSION['username'] = $newUsername;
    } else {
        echo "Error updating username!";
    }
} else {
    echo "Invalid request!";
}
