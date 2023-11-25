<?php
include '../templates/header.php';
?>

<!-- Search Form -->
<form action="" method="get">
    <input type="text" name="search" placeholder="Search submissions...">
    <button type="submit">Search</button>
</form>

<?php
include '../config/database.php';
include '../src/display_content.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
displayContent($conn, $searchTerm);
?>