<?php
session_start();
require_once "config.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the form data
    $adId = $_POST["adId"];
    $comment = $_POST["comment"];
    $rating = $_POST["rating"];
    $userId = $_SESSION["user_id"];

    // Prepare the SQL statement
    $sql = "INSERT INTO comments (user_id, ad_id, comment, rating) VALUES (?, ?, ?, ?)";

    // Create a prepared statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind the parameters to the statement
        mysqli_stmt_bind_param($stmt, "iisi", $userId, $adId, $comment, $rating);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "success";
        } else {
            echo "Error executing the statement: " . mysqli_stmt_error($stmt);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing the statement: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request";
}
?>
