<?php
include "db.php";
include "header.php";

// --- Now Showing movies ---
$sql = "SELECT * FROM movie WHERE current_showing_status = 'Now Showing'";
$result = $conn->query($sql);

// --- Quick status metrics for the dashboard strip ---
$movieCount = $conn->query("SELECT COUNT(*) AS total FROM movie WHERE current_showing_status = 'Now Showing'")->fetch_assoc()['total'];
$showtimeCount = $conn->query("SELECT COUNT(*) AS total FROM showtime WHERE showtime_status = 'Open for Booking'")->fetch_assoc()['total'];
$rewardCount = $conn->query("SELECT COUNT(*) AS total FROM reward")->fetch_assoc()['total'];
$hallCount = $conn->query("SELECT COUNT(*) AS total FROM hall WHERE hall_status = 'Available'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinewave</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
<main>

    <section class="hero">
        <h1>Big Screens. Bigger Moments.</h1>
        <p>Book your seats, grab your snacks, and catch the latest movies at Cinewave &mdash; all in one place.</p>
        <div class="hero-actions">
            <a href="movie.php"><btn class="nav-btn">Browse Movies</btn></a>
            <a href="promotion.php"><btn class="btn btn-secondary">View Promotions</btn></a>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Cinewave Right Now</h2>
        <div class="flex flex-wrap gap-md" style="justify-content:center;">
            <div class="stat-card">
                <span class="stat-number"><?php echo htmlspecialchars($movieCount); ?></span>
                <span class="stat-label">Movies Now Showing</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo htmlspecialchars($showtimeCount); ?></span>
                <span class="stat-label">Showtimes Open for Booking</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo htmlspecialchars($rewardCount); ?></span>
                <span class="stat-label">Claimable rewards once logged in</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo htmlspecialchars($hallCount); ?></span>
                <span class="stat-label">Halls Available</span>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Now Showing</h2>
        <div class="movie-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="movie-card">
                        <img src="<?php echo htmlspecialchars($row['path']); ?>" alt="<?php echo htmlspecialchars($row['movie_name']); ?> Poster">
                        <h3><?php echo htmlspecialchars($row['movie_name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['genre']); ?></p>
                    </div>
                <?php } ?>
            <?php else: ?>
                <p class="text-muted">No movies are currently showing. Check back soon.</p>
            <?php endif; ?>
        </div>
        <div class="flex-center" style="margin-top: 20px;">
            <a href="movie.php"><btn class="nav-btn">See Full Lineup</btn></a>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Quick Links</h2>
        <div class="container">
            <div class="card">
                <h3>Food &amp; Beverage</h3>
                <p>Pre-order popcorn, snacks, and combos before you arrive.</p>
                <a href="fnb.php"><btn class="btn btn-sm">Order Now</btn></a>
            </div>
            <div class="card">
                <h3>Promotions</h3>
                <p>Check out our latest deals and special offers.</p>
                <a href="promotion.php"><btn class="btn btn-sm" style="margin-top: var(--space-4);">View Promotions</btn></a>
            </div>
            <div class="card">
                <h3>Group Booking</h3>
                <p>Planning an outing with friends, family, or colleagues?</p>
                <a href="groupbooking.php"><btn class="btn btn-sm" style="margin-top: var(--space-4);">Book a Group</btn></a>
            </div>
        </div>
    </section>

</main>
</body>
<?php
include "footer.html";
$conn->close();
?>

</html>