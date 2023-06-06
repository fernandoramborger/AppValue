<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";

// Function to get all ads from the database
function getAds($conn) {
    // Prepare the SQL statement
    $sql = "SELECT * FROM ads";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Fetch all rows as an associative array
    $ads = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Close the result set
    mysqli_free_result($result);

    return $ads;
}

function calculateAverageRating($conn, $adId) {
    $sql = "SELECT rating FROM comments WHERE ad_id = ?";

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

    $totalRating = 0;
    $numRatings = 0;

    foreach ($comments as $comment) {
        if ($comment['rating'] > 0) {
            $totalRating += $comment['rating'];
            $numRatings++;
        }
    }

    if ($numRatings > 0) {
        $averageRating = $totalRating / $numRatings;
    } else {
        $averageRating = 0;
    }

    return $averageRating;
}

// Retrieve all ads
$ads = getAds($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bulma.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <style>
        .comments-box {
            margin-top: 20px;
        }

        .comment {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .comment-text {
            margin-bottom: 5px;
        }

        .comment-rating {
            color: #ffdd57;
        }
        #add-service-btn {
        display: block;
        margin: 0 auto;
        text-align: center;
        width: fit-content;
        }
        header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: white;
        padding: 10px;
        }

        .logo {
        display: flex;
        align-items: center;
        }

        .logo img {
        width: 150px;
        height: auto;
        margin-right: 10px;
        }

        .logo-text {
        font-size: 20px;
        font-weight: bold;
        color : black !important;
        }

        .logout-button {
        background: none;
        border: none;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        }

    </style>
</head>
<body>
    <header>
        <nav class="navbar" role="navigation" aria-label="main navigation">
            <div class="navbar-brand">
                <div class="logo">
                    <img src="uploads/logo.png" alt="Logo">
                </div>
                <a class="navbar-item logout-button" href="logout.php">
                    Sair
                </a>
            </div>
        </nav>
    </header>


    <section class="section">
        <div class="container">
            <h1 style="font-size: 24px;font-weight: bold;margin: 20px 0;">Melhores serviços:</h1>
            <div class="columns is-multiline">
                <?php
                // Loop through ads and display them in cards
                foreach ($ads as $ad) {
                    $adId = $ad['id'];
                    $adName = $ad['name'];
                    $adDescription = $ad['description'];
                    $adImage = $ad['image'];
                    $adRating = $ad['rating'];
                    ?>
                    <div class="column is-4">
                        <div class="card ad-card" data-ad-id="<?php echo $adId; ?>">
                            <div class="card-image">
                                <figure class="image is-4by3">
                                    <img src="<?php echo $adImage; ?>" alt="Ad Image">
                                </figure>
                            </div>
                            <div class="card-content">
                                <p class="title is-4"><?php echo $adName; ?></p>
                                <p><?php echo $adDescription; ?></p>
                                <div class="rating">
                                    <?php
                                    $endL = calculateAverageRating($conn, $adId);
                                    for ($i = 1; $i <= $endL; $i++) {
                                        echo '<span class="icon"><i class="fas fa-star"></i></span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Comment modal -->
                        <div class="modal" id="modal-<?php echo $adId; ?>">
                            <div class="modal-background"></div>
                            <div class="modal-content">
                                <div class="box">
                                    <h3 class="title">Comentários de <?php echo $adName; ?></h3>
                                    <div class="comments-box">
                                        <!-- Comments will be dynamically loaded here using AJAX -->
                                    </div>
                                    <!-- Add comment form -->
                                    <form class="comment-form" data-ad-id="<?php echo $adId; ?>">
                                        <div class="field">
                                            <label class="label">Comentário:</label>
                                            <div class="control">
                                                <textarea class="textarea comment-input" rows="3" placeholder="Adicione um comentário..."></textarea>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="label">Avalie:</label>
                                            <div class="control">
                                                <label class="radio">
                                                    <input type="radio" class="rating-input" name="rating" value="1" checked>
                                                    <span class="icon"><i class="fas fa-star"></i></span>
                                                </label>
                                                <label class="radio">
                                                    <input type="radio" class="rating-input" name="rating" value="2">
                                                    <span class="icon"><i class="fas fa-star"></i></span>
                                                </label>
                                                <label class="radio">
                                                    <input type="radio" class="rating-input" name="rating" value="3">
                                                    <span class="icon"><i class="fas fa-star"></i></span>
                                                </label>
                                                <label class="radio">
                                                    <input type="radio" class="rating-input" name="rating" value="4">
                                                    <span class="icon"><i class="fas fa-star"></i></span>
                                                </label>
                                                <label class="radio">
                                                    <input type="radio" class="rating-input" name="rating" value="5">
                                                    <span class="icon"><i class="fas fa-star"></i></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="control">
                                                <button class="button is-primary" type="submit">Adicionar Comentário</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <button class="modal-close is-large" aria-label="close"></button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>

    <button class="button is-warning" id="add-service-btn">Deseja adicionar o seu serviço? Preencha o formulário!</button>

    <!-- Add service modal -->
    <div class="modal" id="add-service-modal">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="box">
                <h3 class="title">Adicionar novo serviço</h3>
                <form id="add-service-form">
                    <div class="field">
                        <label class="label">Nome do Serviço:</label>
                        <div class="control">
                            <input class="input" type="text" id="service-name" placeholder="Digite o nome do serviço" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">URL:</label>
                        <div class="control">
                            <input class="input" type="text" id="service-url" placeholder="Digite a URL do serviço" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Tipo de Serviço:</label>
                        <div class="control">
                            <input class="input" type="text" id="service-type" placeholder="Digite o tipo do serviço" required>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-primary" type="submit">Adicionar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <button class="modal-close is-large" aria-label="close"></button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script>
    $(document).ready(function() {
        // Open modal on ad click
        $(".ad-card").click(function() {
            var adId = $(this).data("ad-id");
            $("#modal-" + adId).addClass("is-active");

            // Load comments for the ad using AJAX
            $.ajax({
                url: "load_comments.php",
                method: "GET",
                data: { adId: adId },
                success: function(response) {
                    // Replace the content of the comments box with the loaded comments
                    $("#modal-" + adId + " .comments-box").html(response);
                },
                error: function(xhr, status, error) {
                    // Handle error response if needed
                }
            });
        });

        // Close modal
        $(".modal-close").click(function() {
            $(this).closest(".modal").removeClass("is-active");
        });

        // Submit comment form
        $("form[data-ad-id]").submit(function(e) {
            e.preventDefault();
            var adId = $(this).data("ad-id");
            var comment = $(this).find(".comment-input").val();
            var rating = $(this).find(".rating-input:checked").val();
            var form = $(this); // Store the form element for later use

            $.ajax({
                url: "add_comments.php",
                method: "POST",
                data: {
                    adId: adId,
                    comment: comment,
                    rating: rating
                },
                success: function(response) {
                    // Display success or failure message
                    if (response === "success") {
                        alert("Comment added successfully!");
                    } else {
                        alert("Failed to add comment. Because one user can give rating one time..");
                    }

                    // Reload comments after successful submission
                    $.ajax({
                        url: "load_comments.php",
                        method: "GET",
                        data: { adId: adId },
                        success: function(response) {
                            // Replace the content of the comments box with the updated comments
                            $("#modal-" + adId + " .comments-box").html(response);

                            // Disable the form inputs if the user has already commented or rated
                            form.find(".comment-input, .rating-input").prop("disabled", true);
                        },
                        error: function(xhr, status, error) {
                            // Handle error response if needed
                        }
                    });
                },
                error: function(xhr, status, error) {
                    // Handle error response if needed
                }
            });
        });

            // Validate and submit add service form
            $("#add-service-form").validate({
                rules: {
                    "service-name": {
                        required: true
                    },
                    "service-url": {
                        required: true,
                        url: true
                    },
                    "service-type": {
                        required: true
                    }
                },
                submitHandler: function(form) {
                    var serviceName = $("#service-name").val();
                    var serviceUrl = $("#service-url").val();
                    var serviceType = $("#service-type").val();

                    // Perform AJAX request to add the service
                    $.ajax({
                        url: "add_service.php",
                        method: "POST",
                        data: {
                            serviceName: serviceName,
                            serviceUrl: serviceUrl,
                            serviceType: serviceType
                        },
                        success: function(response) {
                            // Reset the form inputs
                            form.reset();
                            
                            // Close the add service modal
                            $("#add-service-modal").removeClass("is-active");

                            // Display a success message or perform any other action
                            alert("Service added successfully!");

                            // You can also update the ads list here if needed
                        },
                        error: function(xhr, status, error) {
                            // Handle error response if needed
                        }
                    });
                }
            });

            // Show add service modal on button click
            $("#add-service-btn").click(function() {
                $("#add-service-modal").addClass("is-active");
            });
        });
    </script>
</body>
</html>
