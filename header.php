<?php
include "db.php";
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>

<body>
    <header>

        <?php
        if (!isset($_SESSION['id'])) {
        ?>
            <a href="index.php">
                <h1>Cinewave</h1>
            </a>
            <nav>
                <a href="update_movie.php">Movie</a>
                <a href="fnb.php">Food and Beverage</a>
                <a href="promotion.php">Promotion</a>
                <a href="login.php" class="btn btn-register">Login/Register</a>
            </nav>
            <?php
        } else {
            if ($_SESSION['number'] == 1) {
            ?>
                <a href="index.php">
                    <h1>Cinewave</h1>
                </a>
                <nav>
                    <a href="update_movie.php">Movie</a>
                    <a href="fnb.php">Food and Beverage</a>
                    <a href="promotion.php">Promotion</a>
                    <a href="reward.php">Reward</a>
                    <a href="aboutme.php">Me</a>
                </nav>

            <?php
            } else {
            ?>
                <a href="cinewave_info_page.php">
                    <h1>Cinewave</h1>
                </a>
                <nav>
                    <a href="booking_management.php">Booking</a>
                    <a href="moviemanagement.php">Movie</a>
                    <a href="fnbmanagement.php">FNB</a>
                    <a href="showtime_management.php">Showtime</a>
                    <a href="hall_management.php">Hall</a>
                    <a href="promotion_management.php">Promotion</a>
                    <a href="reward_management.php">Reward</a>
                    <form method="post">
                        <input type="submit" name="logout" value="Log Out" class="btn">
                    </form>
                    <?php
                    if (isset($_POST["logout"])) {
                        session_unset();
                        session_destroy();
                        header("Location: index.php");
                    }
                    ?>
                </nav>
        <?php
            }
        }
        ?>
    </header>
</body>

</html>