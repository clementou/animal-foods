<?php
function displayContent($conn, $searchTerm = '')
{
    $searchTerm = $conn->real_escape_string($searchTerm);
    $sql = "SELECT users.username, submissions.created_at, submissions.animal, submissions.food_name, submissions.description, submissions.extra_info, submissions.media_link FROM submissions INNER JOIN users ON submissions.user_id = users.id";

    if (!empty($searchTerm)) {
        $sql .= " WHERE submissions.animal LIKE '%$searchTerm%' OR submissions.food_name LIKE '%$searchTerm%' OR submissions.description LIKE '%$searchTerm%' OR submissions.extra_info LIKE '%$searchTerm%'";
    }

    $sql .= " ORDER BY submissions.created_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='submission'>";
            echo "<h3>" . htmlspecialchars($row['food_name']) . " for " . htmlspecialchars($row['animal']) . "</h3>";
            echo "<p><strong>Submitted by:</strong> " . htmlspecialchars($row['username']) . "</p>";
            echo "<p><strong>Date & Time:</strong> " . $row['created_at'] . "</p>";
            echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['description'])) . "</p>";
            if (!empty($row['extra_info'])) {
                echo "<p><strong>Extra Info:</strong> " . nl2br(htmlspecialchars($row['extra_info'])) . "</p>";
            }
            if (!empty($row['media_link'])) {
                echo "<img src='" . htmlspecialchars($row['media_link']) . "' alt='Submission Image' style='max-width: 100%; height: auto;'>";
            }
            echo "</div>";
        }
    } else {
        echo "No content submitted yet.";
    }
}
