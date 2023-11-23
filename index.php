<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

if (isset($_SESSION['success_message'])) {
	echo "<p>" . $_SESSION['success_message'] . "</p>";
	// Unset the success message after displaying it
	unset($_SESSION['success_message']);
}

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
	// User is logged in
	include 'templates/logged_in_content.php';
} else {
	// User is not logged in
	include 'templates/welcome_content.php';
}
