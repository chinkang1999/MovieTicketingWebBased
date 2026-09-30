<?php
require_once __DIR__ . '/customer_init.php';

$showtime_id = isset($_POST['showtime_id']) ? filter_var($_POST['showtime_id'], FILTER_VALIDATE_INT) : null;
$selected_seats = isset($_POST['seats']) && is_array($_POST['seats']) ? array_values(array_unique(array_filter(array_map('intval', $_POST['seats'])))) : array();
$ticket_prices = cw_ticket_prices();
$ticket_counts = array();
$ticket_count_total = 0;

foreach ($ticket_prices as $type => $price) {
    $count = isset($_POST['ticket_counts'][$type]) ? filter_var($_POST['ticket_counts'][$type], FILTER_VALIDATE_INT) : 0;
    $count = $count === false ? 0 : max(0, min(10, (int) $count));
    if ($count > 0) {
        $ticket_counts[$type] = $count;
        $ticket_count_total += $count;
    }
}

if ($ticket_count_total === 0 && isset($_POST['ticket_type']) && isset($ticket_prices[$_POST['ticket_type']])) {
    $ticket_counts[$_POST['ticket_type']] = count($selected_seats);
    $ticket_count_total = count($selected_seats);
}

$booking_ready = $showtime_id && count($selected_seats) > 0 && $ticket_count_total === count($selected_seats);
$showtime = $booking_ready ? cw_fetch_showtime($conn, $showtime_id) : null;
$booking_ready = $booking_ready && $showtime !== null;

$food_items = array();
$drink_items = array();
$fnb_result = $conn->query('SELECT fnb_id, fnb_name, path, description, fnb_price FROM fnb ORDER BY fnb_name');
while ($row = $fnb_result->fetch_assoc()) {
    if (preg_match('/water|cola|drink|juice|coffee|tea/i', $row['fnb_name'])) {
        $drink_items[] = $row;
    } else {
        $food_items[] = $row;
    }
}

$combos = array();
$combo_result = $conn->query('SELECT combo_id, combo_name, path, description, combo_price FROM combo ORDER BY combo_name');
while ($row = $combo_result->fetch_assoc()) {
    $combos[] = $row;
}

cw_page_start('Food & Beverages', 'fnb', $booking_ready ? array('Home', 'Movie', $showtime['movie_name'], 'Showtime', 'Seat', 'FNB') : array('Home', 'FNB'));
?>
<main class="page-shell fnb-page">

    <form action="payment.php" method="post">
        <?php if ($booking_ready) { ?>
            <input type="hidden" name="showtime_id" value="<?php echo cw_h($showtime_id); ?>">
            <?php foreach ($ticket_counts as $type => $count) { ?>
                <input type="hidden" name="ticket_counts[<?php echo cw_h($type); ?>]" value="<?php echo cw_h($count); ?>">
            <?php } ?>
            <?php foreach ($selected_seats as $seat_id) { ?>
                <input type="hidden" name="seats[]" value="<?php echo cw_h($seat_id); ?>">
            <?php } ?>
        <?php } ?>

        <div class="catalog-tabs">
            <button class="catalog-tab active" type="button" data-catalog-tab="food">Foods</button>
            <button class="catalog-tab" type="button" data-catalog-tab="drink">Drinks</button>
            <button class="catalog-tab" type="button" data-catalog-tab="combo">Combo</button>
        </div>

        <div class="catalog-shelf">
        <section class="fnb-grid" data-catalog-panel="food">
            <?php foreach ($food_items as $item) { ?>
                <article class="fnb-card">
                    <div class="fnb-visual">
                        <img src="<?php echo $item['path']; ?>" alt="<?php echo cw_h($item['fnb_name']); ?>" data-fallback>
                    </div>
                    <div class="fnb-card-body">
                        <h3><?php echo cw_h($item['fnb_name']); ?></h3>
                        <p><?php echo cw_h($item['description']); ?></p>
                        <div class="price-line">
                            <span>RM<?php echo number_format($item['fnb_price'], 2); ?></span>
                            <?php if ($booking_ready) { ?>
                                <input class="quantity" id="fnb-<?php echo cw_h($item['fnb_id']); ?>" type="number" name="fnb[<?php echo cw_h($item['fnb_id']); ?>]" value="0" min="0" max="10" aria-label="<?php echo cw_h($item['fnb_name']); ?> quantity">
                                <button class="fnb-add-button" type="button" data-add-quantity="fnb-<?php echo cw_h($item['fnb_id']); ?>" aria-label="Add <?php echo cw_h($item['fnb_name']); ?>">+</button>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </section>

        <section class="fnb-grid" data-catalog-panel="drink" hidden>
            <?php foreach ($drink_items as $item) { ?>
                <article class="fnb-card">
                    <div class="fnb-visual">
                        <img src="<?php echo $item['path']; ?>" alt="<?php echo cw_h($item['fnb_name']); ?>" data-fallback>
                    </div>
                    <div class="fnb-card-body">
                        <h3><?php echo cw_h($item['fnb_name']); ?></h3>
                        <p><?php echo cw_h($item['description']); ?></p>
                        <div class="price-line">
                            <span>RM<?php echo number_format($item['fnb_price'], 2); ?></span>
                            <?php if ($booking_ready) { ?>
                                <input class="quantity" id="fnb-<?php echo cw_h($item['fnb_id']); ?>" type="number" name="fnb[<?php echo cw_h($item['fnb_id']); ?>]" value="0" min="0" max="10" aria-label="<?php echo cw_h($item['fnb_name']); ?> quantity">
                                <button class="fnb-add-button" type="button" data-add-quantity="fnb-<?php echo cw_h($item['fnb_id']); ?>" aria-label="Add <?php echo cw_h($item['fnb_name']); ?>">+</button>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </section>

        <section class="fnb-grid" data-catalog-panel="combo" hidden>
            <?php foreach ($combos as $combo) { ?>
                <article class="fnb-card">
                    <div class="fnb-visual">
                        <img src="<?php echo $combo['path']; ?>" alt="<?php echo cw_h($combo['combo_name']); ?>" data-fallback>
                    </div>
                    <div class="fnb-card-body">
                        <h3><?php echo cw_h($combo['combo_name']); ?></h3>
                        <p><?php echo cw_h($combo['description']); ?></p>
                        <div class="price-line">
                            <span>RM<?php echo number_format($combo['combo_price'], 2); ?></span>
                            <?php if ($booking_ready) { ?>
                                <input class="quantity" id="combo-<?php echo cw_h($combo['combo_id']); ?>" type="number" name="combo[<?php echo cw_h($combo['combo_id']); ?>]" value="0" min="0" max="10" aria-label="<?php echo cw_h($combo['combo_name']); ?> quantity">
                                <button class="fnb-add-button" type="button" data-add-quantity="combo-<?php echo cw_h($combo['combo_id']); ?>" aria-label="Add <?php echo cw_h($combo['combo_name']); ?>">+</button>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </section>
        </div>

        <div class="fnb-action-line">
            <?php if ($booking_ready) { ?>
                <button class="next-circle" type="submit" aria-label="Continue to payment">&gt;</button>
            <?php } else { ?>
                <a class="cyan-button" href="movie.php">Choose a movie to order</a>
            <?php } ?>
        </div>
    </form>
</main>
<?php cw_page_end(); ?>
