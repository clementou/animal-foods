<?php
function displayContent($conn, $searchTerm = '', $userID = null)
{
    $searchTerm = $conn->real_escape_string($searchTerm);

    // Base SQL query for all posts
    $baseSql = "SELECT submissions.id, users.username, submissions.created_at, submissions.animal, submissions.food_name, submissions.description, submissions.extra_info, submissions.media_link, 
                GROUP_CONCAT(DISTINCT tags.name SEPARATOR ', ') AS tags,
                (SELECT COUNT(*) FROM votes WHERE votes.submission_id = submissions.id AND votes.vote_type = 'upvote') - 
                (SELECT COUNT(*) FROM votes WHERE votes.submission_id = submissions.id AND votes.vote_type = 'downvote') AS vote_score, 
                COUNT(DISTINCT favorites.id) as favorite_count 
                FROM submissions 
                INNER JOIN users ON submissions.user_id = users.id 
                LEFT JOIN submission_tags ON submissions.id = submission_tags.submission_id 
                LEFT JOIN tags ON submission_tags.tag_id = tags.id 
                LEFT JOIN favorites ON submissions.id = favorites.submission_id";

    // Search condition
    if (!empty($searchTerm)) {
        $baseSql .= " WHERE submissions.animal LIKE '%$searchTerm%' OR submissions.food_name LIKE '%$searchTerm%' OR submissions.description LIKE '%$searchTerm%' OR submissions.extra_info LIKE '%$searchTerm%' OR tags.name LIKE '%$searchTerm%'";
    }

    $baseSql .= " GROUP BY submissions.id";

    if ($userID != null) {
        // For logged-in users: Sort by recommendation score first, then by creation date
        $sql = "SELECT DISTINCT submissions.id, users.username, submissions.created_at, submissions.animal, submissions.food_name, submissions.description, submissions.extra_info, submissions.media_link, 
                GROUP_CONCAT(DISTINCT tags.name SEPARATOR ', ') AS tags,
                (SELECT COUNT(*) FROM votes WHERE votes.submission_id = submissions.id AND votes.vote_type = 'upvote') - 
                (SELECT COUNT(*) FROM votes WHERE votes.submission_id = submissions.id AND votes.vote_type = 'downvote') AS vote_score, 
                COUNT(DISTINCT favorites.id) as favorite_count,
                IFNULL(user_recommendations.score, 0) as rec_score
                FROM submissions
                LEFT JOIN user_recommendations ON submissions.id = user_recommendations.recommended_submission_id AND user_recommendations.user_id = '$userID'
                INNER JOIN users ON submissions.user_id = users.id
                LEFT JOIN submission_tags ON submissions.id = submission_tags.submission_id
                LEFT JOIN tags ON submission_tags.tag_id = tags.id
                LEFT JOIN favorites ON submissions.id = favorites.submission_id
                GROUP BY submissions.id
                ORDER BY rec_score DESC, submissions.created_at DESC";
    } else {
        // For logged-out users: Sort by creation date
        $sql = $baseSql . " ORDER BY submissions.created_at DESC";
    }

    // Execute the query and display the submissions
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

            // Get vote score and favorite count
            $voteScore = $row['vote_score'];
            $favoriteCount = $row['favorite_count'];

            // Display vote score and favorite count
            echo "<div class='submission-stats'>";
            echo "<p>Vote Score: $voteScore</p>";
            echo "<p>Favorites: $favoriteCount</p>";
            echo "</div>";

            echo "<p><strong>Tags:</strong> " . $tags . "</p>";
            if ($userID != null) {
                $submissionId = $row['id'];

                // Check for vote status
                $voteSql = "SELECT vote_type FROM votes WHERE user_id = $userID AND submission_id = $submissionId";
                $voteResult = $conn->query($voteSql);
                $voteRow = $voteResult->fetch_assoc();
                $userVote = $voteRow['vote_type'] ?? null;

                // Check for favorite status
                $favSql = "SELECT * FROM favorites WHERE user_id = $userID AND submission_id = $submissionId";
                $favResult = $conn->query($favSql);
                $isFavorite = $favResult->num_rows > 0;

                echo "<form action='' method='post'>";
                echo "<input type='hidden' name='tag_submission_id' value='" . $row['id'] . "'>";
                echo "<input type='text' name='tags' placeholder='Enter tags...'>";
                echo "<button type='submit'>Add Tags</button>";
                echo "</form>";

                // Voting and favoriting forms...
                echo "<div class='submission-actions'>";

                // Voting buttons
                echo "<form action='src/vote.php' method='post'>";
                echo "<input type='hidden' name='submission_id' value='" . $submissionId . "'>";
                echo "<button type='submit' name='vote' value='upvote' " . ($userVote === 'upvote' ? 'disabled' : '') . ">Upvote</button>";
                echo "<button type='submit' name='vote' value='downvote' " . ($userVote === 'downvote' ? 'disabled' : '') . ">Downvote</button>";
                echo "</form>";

                // Favorite button
                echo "<form action='src/favorite.php' method='post'>";
                echo "<input type='hidden' name='submission_id' value='" . $submissionId . "'>";
                echo "<button type='submit' name='favorite' " . ($isFavorite ? 'disabled' : '') . ">Favorite</button>";
                echo "</form>";
                echo "</div>";
            }

            echo "</div>";
        }
    } else {
        echo "No content submitted yet.";
    }
}
