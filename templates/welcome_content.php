<div class="login-prompt">
    <p>Want to submit your own content? <a href="templates/login_form.php">Log in</a> or <a href="templates/signup_form.php">sign up</a> to get started!</p>
</div>

<!-- Search Form -->
<form action="" method="get"> <!-- You can specify an action if needed -->
    <input type="text" name="search" placeholder="Search submissions...">
    <button type="submit">Search</button>
</form>


<?php
include 'config/database.php';
include 'src/display_content.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
displayContent($conn, $searchTerm);
?>