<?php
require_once __DIR__ . '/customer_init.php';

$showtime_id = filter_input(INPUT_GET, 'showtime_id', FILTER_VALIDATE_INT);
$showtime = $showtime_id ? cw_fetch_showtime($conn, $showtime_id) : null;
$movie_id = $showtime ? (int) $showtime['movie_id'] : filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
$movie = $movie_id ? cw_fetch_movie($conn, $movie_id) : null;

if (!$movie) {
    header('Location: movie.php');
    exit();
}

$sql = "SELECT s.*, h.hall_number
        FROM showtime s
        JOIN hall h ON h.hall_id = s.hall_id
        WHERE s.movie_id = ?
          AND s.date >= CURDATE()
          AND LOWER(TRIM(s.showtime_status)) NOT IN ('cancelled', 'canceled', 'completed', 'closed')
        ORDER BY s.date, s.start_time";
$statement = $conn->prepare($sql);
$statement->bind_param('i', $movie_id);
$statement->execute();
$result = $statement->get_result();
$showtimes = array();

while ($row = $result->fetch_assoc()) {
    if (!isset($showtimes[$row['date']])) {
        $showtimes[$row['date']] = array();
    }
    $showtimes[$row['date']][] = $row;
}
$statement->close();
$dates = array_keys($showtimes);

if (!$showtime) {
    cw_page_start('Choose showtime', 'movie', array('Home', 'Movie', $movie['movie_name'], 'Showtime'));
    ?>
    <main class="page-shell showtime-page">
        <div class="showtime-poster movie-poster">
            <span class="poster-fallback"><?php echo cw_h(strtoupper(substr($movie['movie_name'], 0, 1))); ?></span>
            <img src="<?php echo $movie['path']; ?>" alt="<?php echo cw_h($movie['movie_name']); ?> poster" data-fallback>
        </div>

        <section class="showtime-picker">
            <?php if (count($showtimes) === 0) { ?>
                <div class="empty-state">
                    <p>No bookable showtimes are available for this movie.</p>
                    <a class="cyan-button" href="movie.php">Choose another movie</a>
                </div>
            <?php } else { ?>
                <h1 class="picker-title">Date</h1>
                <div class="date-pills">
                    <?php foreach ($dates as $index => $date_value) { ?>
                        <button class="date-pill <?php echo $index === 0 ? 'active' : ''; ?>" type="button" data-show-date="<?php echo cw_h($date_value); ?>">
                            <strong><?php echo cw_h(date('d/m/Y', strtotime($date_value))); ?></strong>
                            <span><?php echo cw_h(date('l', strtotime($date_value))); ?></span>
                        </button>
                    <?php } ?>
                </div>

                <h2 class="picker-title time-title">Time</h2>
                <?php foreach ($showtimes as $date_value => $daily_showtimes) { ?>
                    <div class="time-group time-pills" data-time-date="<?php echo cw_h($date_value); ?>" <?php echo $date_value !== $dates[0] ? 'hidden' : ''; ?>>
                        <?php foreach ($daily_showtimes as $time) { ?>
                            <a class="time-pill" href="booking.php?showtime_id=<?php echo cw_h($time['showtime_id']); ?>">
                                <strong><?php echo cw_h(date('g:i A', strtotime($time['start_time']))); ?></strong>
                                <span class="sr-only">Hall <?php echo cw_h($time['hall_number']); ?></span>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </section>
    </main>
    <?php
    cw_page_end();
    exit();
}

cw_require_customer();

if (in_array(strtolower(trim($showtime['showtime_status'])), array('cancelled', 'canceled', 'completed', 'closed'), true)) {
    header('Location: booking.php?movie_id=' . $movie_id);
    exit();
}

$sql = 'SELECT se.*,
               CASE WHEN bs.booking_seat_id IS NULL THEN 0 ELSE 1 END AS is_booked
        FROM seat se
        LEFT JOIN customer_booking_seat bs
          ON bs.seat_id = se.seat_id AND bs.showtime_id = ?
        WHERE se.hall_id = ?
        ORDER BY LEFT(se.seat_number, 1), CAST(SUBSTRING(se.seat_number, 2) AS UNSIGNED)';
$statement = $conn->prepare($sql);
$statement->bind_param('ii', $showtime_id, $showtime['hall_id']);
$statement->execute();
$result = $statement->get_result();
$seat_rows = array();
$max_columns = 0;

while ($seat = $result->fetch_assoc()) {
    if (!preg_match('/^([A-Za-z]+)([0-9]+)$/', $seat['seat_number'], $matches)) {
        continue;
    }
    $row_name = strtoupper($matches[1]);
    if (!isset($seat_rows[$row_name])) {
        $seat_rows[$row_name] = array();
    }
    $seat_rows[$row_name][] = $seat;
    $max_columns = max($max_columns, count($seat_rows[$row_name]));
}
$statement->close();
$ticket_prices = cw_ticket_prices();

$ticket_stage = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_stage']) && $_POST['booking_stage'] === 'tickets';
if ($ticket_stage) {
    if (!cw_verify_csrf()) {
        exit('Your form session expired. Please return to the seat page and try again.');
    }
    $posted_showtime_id = isset($_POST['showtime_id']) ? filter_var($_POST['showtime_id'], FILTER_VALIDATE_INT) : false;
    $posted_seats = isset($_POST['seats']) && is_array($_POST['seats'])
        ? array_values(array_unique(array_filter(array_map('intval', $_POST['seats'])))) : array();
    if ((int) $posted_showtime_id !== (int) $showtime_id || count($posted_seats) === 0) {
        header('Location: booking.php?showtime_id=' . $showtime_id);
        exit();
    }
    $selected_numbers = array();
    foreach ($seat_rows as $row_seats) {
        foreach ($row_seats as $seat) {
            if (in_array((int) $seat['seat_id'], $posted_seats, true) && (int) $seat['seat_status'] === 0 && (int) $seat['is_booked'] === 0) {
                $selected_numbers[] = $seat['seat_number'];
            }
        }
    }
    if (count($selected_numbers) !== count($posted_seats)) {
        header('Location: booking.php?showtime_id=' . $showtime_id . '&seat_error=1');
        exit();
    }
    cw_page_start('Choose ticket types', 'movie', array('Home', 'Movie', $showtime['movie_name'], 'Showtime', 'Seat'));
    ?>
    <main class="page-shell ticket-page">
        <section class="ticket-summary-panel">
            <h1>Showtime</h1>
            <p><?php echo cw_h(date('d/m/Y (l)', strtotime($showtime['date']))); ?> <strong><?php echo cw_h(date('g.i A', strtotime($showtime['start_time']))); ?></strong></p>
            <h2>Seats</h2>
            <p class="selected-ticket-seats"><?php echo cw_h(implode(', ', $selected_numbers)); ?></p>
        </section>
        <form class="ticket-allocation" action="fnb.php" method="post" data-ticket-form data-seat-total="<?php echo count($posted_seats); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>">
            <input type="hidden" name="showtime_id" value="<?php echo cw_h($showtime_id); ?>">
            <?php foreach ($posted_seats as $seat_id) { ?><input type="hidden" name="seats[]" value="<?php echo cw_h($seat_id); ?>"><?php } ?>
            <h2>Ticket Type</h2>
            <?php foreach ($ticket_prices as $type => $price) { ?>
                <div class="ticket-counter-row">
                    <div><strong><?php echo cw_h($type === 'Child' ? 'Children' : $type); ?></strong><span>RM<?php echo number_format($price, 2); ?></span></div>
                    <div class="ticket-stepper">
                        <button type="button" data-ticket-minus aria-label="Remove <?php echo cw_h($type); ?> ticket">&minus;</button>
                        <input type="number" name="ticket_counts[<?php echo cw_h($type); ?>]" value="0" min="0" max="10" readonly data-ticket-quantity>
                        <button type="button" data-ticket-plus aria-label="Add <?php echo cw_h($type); ?> ticket">+</button>
                    </div>
                </div>
            <?php } ?>
            <p class="ticket-validation" data-ticket-validation>Select <?php echo count($posted_seats); ?> ticket(s).</p>
            <button class="next-circle ticket-next" type="submit" data-ticket-continue disabled aria-label="Continue to food and beverages">&gt;</button>
        </form>
    </main>
    <?php cw_page_end(); exit();
}

cw_page_start('Choose seats', 'movie', array('Home', 'Movie', $showtime['movie_name'], 'Showtime', 'Seat'));
?>
<main class="page-shell seat-page">
    <div class="seat-topline">
        <div class="showtime-copy">
            <h1>Showtime</h1>
            <p><?php echo cw_h(date('d/m/Y (l)', strtotime($showtime['date']))); ?> <span><?php echo cw_h(date('g.i A', strtotime($showtime['start_time']))); ?></span></p>
            <h2>Seats</h2>
            <p class="selected-seat-inline" data-selected-seats-text>—</p>
        </div>
        <div class="legend canva-legend">
            <span class="legend-item"><i class="legend-swatch sold"></i>Sold</span>
            <span class="legend-item"><i class="legend-swatch selected"></i>Selected Seats</span>
            <span class="legend-item"><i class="legend-swatch"></i>Available</span>
            <span class="legend-item"><i class="legend-swatch oku"></i>OKU</span>
        </div>
    </div>

    <?php if (isset($_GET['seat_error'])) { ?><div class="errors" role="alert"><p>One or more selected seats are no longer available. Please choose again.</p></div><?php } ?>
    <form action="booking.php?showtime_id=<?php echo cw_h($showtime_id); ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>">
        <input type="hidden" name="booking_stage" value="tickets">
        <section class="seat-stage">
            <div class="screen">Screen</div>
            <div class="seat-map">
                <?php foreach ($seat_rows as $row_name => $seats) { ?>
                    <div class="seat-row" style="--seat-columns: <?php echo cw_h($max_columns); ?>;">
                        <span class="row-label"><?php echo cw_h($row_name); ?></span>
                        <?php foreach ($seats as $seat) { ?>
                            <?php $sold = (int) $seat['seat_status'] === 1 || (int) $seat['is_booked'] === 1; ?>
                            <?php if ($sold) { ?>
                                <span class="seat sold" title="Sold"><?php echo cw_h($seat['seat_number']); ?></span>
                            <?php } else { ?>
                                <label class="seat" title="Seat <?php echo cw_h($seat['seat_number']); ?>">
                                    <input type="checkbox" name="seats[]" value="<?php echo cw_h($seat['seat_id']); ?>" data-seat-number="<?php echo cw_h($seat['seat_number']); ?>">
                                    <span><?php echo cw_h($seat['seat_number']); ?></span>
                                </label>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>

        <div class="seat-action-line">
            <span><strong data-seat-count>0</strong> seat(s)</span>
            <input type="hidden" name="showtime_id" value="<?php echo cw_h($showtime_id); ?>">
            <button class="next-circle" type="submit" data-seat-continue disabled aria-label="Continue to food and beverages">&gt;</button>
        </div>
    </form>
</main>
<?php cw_page_end(); ?>
