<?php
include"db.php";
include"customer_init.php";

$movie_id = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
$selected_movie = $movie_id ? cw_fetch_movie($conn, $movie_id) : null;

if ($selected_movie) {
    cw_page_start($selected_movie['movie_name'], 'movie', array('Home', 'Movie', $selected_movie['movie_name']));
    ?>
    <main class="page-shell detail-layout">
        <section class="detail-poster-column">
            <div class="detail-poster movie-poster">
                <span class="poster-fallback"><?php echo cw_h(strtoupper(substr($selected_movie['movie_name'], 0, 1))); ?></span>
                <img src="<?php echo $selected_movie['path']; ?>" alt="<?php echo cw_h($selected_movie['movie_name']); ?> poster" data-fallback>
            </div>
            <?php if ($selected_movie['current_showing_status'] === 'Now Showing') { ?>
                <a class="cyan-button detail-buy" href="booking.php?movie_id=<?php echo cw_h($selected_movie['movie_id']); ?>">Buy now</a>
            <?php } else { ?>
                <a class="ghost-button detail-buy" href="movie.php">Back to movies</a>
            <?php } ?>
        </section>

        <section class="detail-copy">
            <h1><?php echo cw_h($selected_movie['movie_name']); ?></h1>
            <dl class="canva-facts">
                <div><dt>Description</dt><dd><?php echo cw_h($selected_movie['synopsis']); ?></dd></div>
                <div><dt>Duration</dt><dd><?php echo cw_h((int) round((float) $selected_movie['duration'] * 60)); ?> minutes</dd></div>
                <div><dt>Genre</dt><dd><?php echo cw_h($selected_movie['genre']); ?></dd></div>
                <div><dt>Audio Language</dt><dd><?php echo cw_h($selected_movie['language']); ?></dd></div>
                <div><dt>Subtitle</dt><dd><?php echo cw_h($selected_movie['subtitle']); ?></dd></div>
                <div><dt>Age restriction</dt><dd><?php echo cw_h($selected_movie['age_restriction']); ?></dd></div>
            </dl>
        </section>
    </main>
    <?php
    cw_page_end();
    exit();
}

$now_showing = array();
$coming_soon = array();
$result = $conn->query("SELECT * FROM movie ORDER BY FIELD(current_showing_status, 'Now Showing', 'Coming Soon'), movie_name");

while ($row = $result->fetch_assoc()) {
    if ($row['current_showing_status'] === 'Now Showing') {
        $now_showing[] = $row;
    } else {
        $coming_soon[] = $row;
    }
}

cw_page_start('Movies', 'movie', array('Home', 'Movie'));
?>
<main class="page-shell movie-page">
    <h1 class="canva-heading">Now Showing</h1>

    <?php if (count($now_showing) === 0) { ?>
        <div class="panel empty-state">No movies are available.</div>
    <?php } else { ?>
        <section class="movie-grid" aria-label="Now showing movies">
            <?php foreach ($now_showing as $movie) { ?>
                <article class="movie-card" aria-label="<?php echo cw_h($movie['movie_name']); ?>">
                    <a class="movie-poster" href="movie.php?movie_id=<?php echo cw_h($movie['movie_id']); ?>">
                        <span class="poster-fallback"><?php echo cw_h(strtoupper(substr($movie['movie_name'], 0, 1))); ?></span>
                        <img src="<?php echo $movie['path']; ?>" alt="<?php echo cw_h($movie['movie_name']); ?> poster" data-fallback>
                    </a>
                    <a class="poster-book-button" href="movie.php?movie_id=<?php echo cw_h($movie['movie_id']); ?>">Book now</a>
                </article>
            <?php } ?>
        </section>
    <?php } ?>

    <?php if (count($coming_soon) > 0) { ?>
        <h2 class="canva-heading coming-heading">Coming Soon</h2>
        <section class="coming-grid" aria-label="Coming soon movies">
            <?php foreach ($coming_soon as $movie) { ?>
                <a class="coming-poster movie-poster" href="movie.php?movie_id=<?php echo cw_h($movie['movie_id']); ?>" aria-label="View <?php echo cw_h($movie['movie_name']); ?>">
                    <span class="poster-fallback"><?php echo cw_h(strtoupper(substr($movie['movie_name'], 0, 1))); ?></span>
                    <img src="<?php echo $movie['path']; ?>" alt="<?php echo cw_h($movie['movie_name']); ?> poster" data-fallback>
                </a>
            <?php } ?>
        </section>
    <?php } ?>
</main>
<?php cw_page_end(); ?>
