<?php

/**
 * Main index file
 * 
 * This script initiates a session, checks user login status,
 * and displays appropriate content based on the user's authentication status.
 * 
 * PHP version 8.3
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

/**
 * Display success message if available and unset it from session.
 */
if (isset($_SESSION['success_message'])) {
	// HTML structure for easy styling with CSS
	echo "<div class='success-message'><p>" . $_SESSION['success_message'] . "</p></div>";
	// Unset the success message after displaying it
	unset($_SESSION['success_message']);
}

/**
 * Check if the user is already logged in and include the appropriate content.
 */
if (isset($_SESSION['user_id'])) {
	// User is logged in
	include '../templates/logged_in_content.php';
} else {
	// User is not logged in
	include '../templates/welcome_content.php';
}
