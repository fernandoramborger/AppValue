<?php
session_start();
require_once "config.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $adId = $_GET["adId"];

    // Prepare the SQL statement
    $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.ad_id = ?";

    // Create a prepared statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, "i", $adId);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    // Fetch all rows as an associative array
    $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Close the statement
    mysqli_stmt_close($stmt);

    // Output the comments as HTML
    foreach ($comments as $comment) {
        $commentId = $comment["id"];
        $commentText = $comment["comment"];
        $commentRating = $comment["rating"];
        $commentUsername = $comment["username"];

        echo '<div class="comment">';
        echo '<p class="comment-text"><strong>' . $commentUsername . ': </strong>' . $commentText . '</p>';
        echo '<div class="comment-rating">';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $commentRating) {
                echo '<span class="icon"><i class="fas fa-star"></i></span>';
            } else {
                echo '<span class="icon"><i class="far fa-star"></i></span>';
            }
        }
        echo '</div>';
        echo '</div>';
    }
}
?>
