<?php
function displayContent($conn, $searchTerm = '', $allowTagging = false)
{
    $searchTerm = $conn->real_escape_string($searchTerm);
    $sql = "SELECT submissions.id, users.username, submissions.created_at, submissions.animal, submissions.food_name, submissions.description, submissions.extra_info, submissions.media_link, GROUP_CONCAT(tags.name SEPARATOR ', ') AS tags FROM submissions INNER JOIN users ON submissions.user_id = users.id LEFT JOIN submission_tags ON submissions.id = submission_tags.submission_id LEFT JOIN tags ON submission_tags.tag_id = tags.id";

    if (!empty($searchTerm)) {
        $sql .= " WHERE submissions.animal LIKE '%$searchTerm%' OR submissions.food_name LIKE '%$searchTerm%' OR submissions.description LIKE '%$searchTerm%' OR submissions.extra_info LIKE '%$searchTerm%' OR tags.name LIKE '%$searchTerm%'";
    }

    $sql .= " GROUP BY submissions.id ORDER BY submissions.created_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $foodName = $row['food_name'] ? htmlspecialchars($row['food_name']) : 'N/A';
            $animal = $row['animal'] ? htmlspecialchars($row['animal']) : 'N/A';
            $username = $row['username'] ? htmlspecialchars($row['username']) : 'Unknown';
            $description = $row['description'] ? htmlspecialchars($row['description']) : '';
            $extraInfo = $row['extra_info'] ? htmlspecialchars($row['extra_info']) : '';
            $mediaLink = $row['media_link'] ? htmlspecialchars($row['media_link']) : '';
            $tags = $row['tags'] ? htmlspecialchars($row['tags']) : 'No tags';

            echo "<div class='submission'>";
            echo "<h3>" . $foodName . " for " . $animal . "</h3>";
            echo "<p><strong>Submitted by:</strong> " . $username . "</p>";
            echo "<p><strong>Date & Time:</strong> " . $row['created_at'] . "</p>";
            echo "<p><strong>Description:</strong> " . nl2br($description) . "</p>";
            if (!empty($extraInfo)) {
                echo "<p><strong>Extra Info:</strong> " . nl2br($extraInfo) . "</p>";
            }
            if (!empty($mediaLink)) {
                echo "<img src='" . $mediaLink . "' alt='Submission Image' style='max-width: 100%; height: auto;'>";
            }

            echo "<p><strong>Tags:</strong> " . $tags . "</p>";
            if ($allowTagging) {
                echo "<form action='' method='post'>";
                echo "<input type='hidden' name='tag_submission_id' value='" . $row['id'] . "'>";
                echo "<input type='text' name='tags' placeholder='Enter tags...'>";
                echo "<button type='submit'>Add Tags</button>";
                echo "</form>";
            }

            echo "</div>";
        }
    } else {
        echo "No content submitted yet.";
    }
}
