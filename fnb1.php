<?php
require_once __DIR__ . '/customer_init.php';

$view = isset($_GET['view']) ? (string) $_GET['view'] : 'catalog';
if ($view === 'manage') {
    $staff_id = cw_require_staff();
    $manage_errors = array();
    $manage_success = '';
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!cw_verify_csrf()) {
            $manage_errors[] = 'Your form session expired. Please try again.';
        } elseif (isset($_POST['delete_fnb'])) {
            $target_id = filter_var($_POST['fnb_id'], FILTER_VALIDATE_INT);
            if (!$target_id) {
                $manage_errors[] = 'Invalid food or beverage item.';
            } else {
                $statement = $conn->prepare('DELETE FROM fnb WHERE fnb_id = ?');
                $statement->bind_param('i', $target_id);
                try {
                    $statement->execute();
                    $manage_success = 'Food or beverage item deleted.';
                    $delete_id = null;
                } catch (Throwable $exception) {
                    $manage_errors[] = 'This item is already used by an order and cannot be deleted.';
                }
                $statement->close();
            }
        } else {
            $target_id = isset($_POST['fnb_id']) && $_POST['fnb_id'] !== '' ? filter_var($_POST['fnb_id'], FILTER_VALIDATE_INT) : null;
            $item_name = trim(isset($_POST['fnb_name']) ? $_POST['fnb_name'] : '');
            $item_path = trim(isset($_POST['path']) ? $_POST['path'] : '');
            $item_description = trim(isset($_POST['description']) ? $_POST['description'] : '');
            $item_price = filter_var(isset($_POST['fnb_price']) ? $_POST['fnb_price'] : null, FILTER_VALIDATE_FLOAT);
            if (strlen($item_name) < 2 || strlen($item_name) > 50) $manage_errors[] = 'Item name must contain 2 to 50 characters.';
            if ($item_path === '' || strlen($item_path) > 255) $manage_errors[] = 'Enter a valid SQL image path.';
            if (strlen($item_description) < 3) $manage_errors[] = 'Enter an item description.';
            if ($item_price === false || $item_price <= 0 || $item_price > 9999999) $manage_errors[] = 'Enter a valid price.';
            if (count($manage_errors) === 0) {
                if ($target_id) {
                    $statement = $conn->prepare('UPDATE fnb SET fnb_name = ?, path = ?, description = ?, fnb_price = ?, staff_id = ? WHERE fnb_id = ?');
                    $statement->bind_param('sssdii', $item_name, $item_path, $item_description, $item_price, $staff_id, $target_id);
                    $manage_success = 'Food or beverage item updated.';
                } else {
                    $statement = $conn->prepare('INSERT INTO fnb (fnb_name, path, description, fnb_price, staff_id) VALUES (?, ?, ?, ?, ?)');
                    $statement->bind_param('sssdi', $item_name, $item_path, $item_description, $item_price, $staff_id);
                    $manage_success = 'Food or beverage item added.';
                }
                $statement->execute();
                $statement->close();
                $edit_id = null;
            }
        }
    }

    $editing = null;
    if ($edit_id) {
        $statement = $conn->prepare('SELECT fnb_id, fnb_name, path, description, fnb_price FROM fnb WHERE fnb_id = ?');
        $statement->bind_param('i', $edit_id);
        $statement->execute();
        $editing = $statement->get_result()->fetch_assoc();
        $statement->close();
    }
    $manage_items = array();
    $result = $conn->query('SELECT fnb_id, fnb_name, path, description, fnb_price FROM fnb ORDER BY fnb_id');
    while ($row = $result->fetch_assoc()) $manage_items[] = $row;

    cw_page_start('FNB Management', 'fnb', array('Home', 'Avatar', 'CineWave Info Edit', 'FNB Management'));
    ?>
    <main class="page-shell fnb-manage-page">
        <div class="manage-title-line"><h1>Edit FNB :</h1><a class="next-circle" href="fnb.php?view=manage&amp;edit_id=0" aria-label="Add FNB item">+</a></div>
        <?php if ($manage_success !== '') { ?><div class="success-message"><?php echo cw_h($manage_success); ?></div><?php } ?>
        <?php if (count($manage_errors) > 0) { ?><div class="errors"><?php foreach ($manage_errors as $error) { ?><p><?php echo cw_h($error); ?></p><?php } ?></div><?php } ?>
        <?php if ($editing || isset($_GET['edit_id'])) { ?>
            <section class="fnb-editor-panel">
                <div class="fnb-editor-preview"><?php if ($editing) { ?><img src="<?php echo $editing['path']; ?>" alt="<?php echo cw_h($editing['fnb_name']); ?>" data-fallback><?php } ?><span>Insert Picture</span></div>
                <form action="fnb.php?view=manage" method="post"><input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>"><input type="hidden" name="fnb_id" value="<?php echo $editing ? cw_h($editing['fnb_id']) : ''; ?>"><div class="field"><label for="fnb_name">Item Name:</label><input id="fnb_name" name="fnb_name" maxlength="50" value="<?php echo $editing ? cw_h($editing['fnb_name']) : ''; ?>" placeholder="Enter FNB Name" required></div><div class="field"><label for="path">Picture SQL Path:</label><input id="path" name="path" maxlength="255" value="<?php echo $editing ? cw_h($editing['path']) : ''; ?>" placeholder="asset/fnb/example.png" required></div><div class="field"><label for="description">Description:</label><textarea id="description" name="description" required><?php echo $editing ? cw_h($editing['description']) : ''; ?></textarea></div><div class="field"><label for="fnb_price">Price:</label><input id="fnb_price" type="number" name="fnb_price" min="0.01" step="0.01" value="<?php echo $editing ? cw_h($editing['fnb_price']) : ''; ?>" placeholder="Enter Price" required></div><div class="form-actions"><button class="ghost-button" type="reset">Clear</button><button class="cyan-button" type="submit">Submit</button></div></form>
            </section>
        <?php } ?>
        <?php if ($delete_id) { ?><div class="delete-confirm"><p>Are You Sure Delete This Food/Beverage?</p><form action="fnb.php?view=manage" method="post"><input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>"><input type="hidden" name="fnb_id" value="<?php echo cw_h($delete_id); ?>"><button class="danger-button" name="delete_fnb" value="1" type="submit">Yes</button><a class="ghost-button" href="fnb.php?view=manage">No</a></form></div><?php } ?>
        <section class="fnb-manage-grid"><?php foreach ($manage_items as $item) { ?><article class="fnb-card"><div class="fnb-visual"><img src="<?php echo $item['path']; ?>" alt="<?php echo cw_h($item['fnb_name']); ?>" data-fallback></div><div class="fnb-card-body"><h3><?php echo cw_h($item['fnb_name']); ?></h3><p><?php echo cw_h($item['description']); ?></p><strong>RM<?php echo number_format($item['fnb_price'], 2); ?></strong><div class="manage-card-actions"><a class="cyan-button" href="fnb.php?view=manage&amp;edit_id=<?php echo cw_h($item['fnb_id']); ?>">Edit</a><a class="danger-button" href="fnb.php?view=manage&amp;delete_id=<?php echo cw_h($item['fnb_id']); ?>">Delete</a></div></div></article><?php } ?></section>
    </main>
    <?php cw_page_end(); exit();
}

$membership_id = cw_require_customer();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !cw_verify_csrf()) {
    $errors[] = 'Your form session expired. Please return to the seat page and try again.';
}

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

$booking_ready = count($errors) === 0 && $showtime_id && count($selected_seats) > 0 && $ticket_count_total === count($selected_seats);
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

$order_complete = false;
$booking_id = 0;
$ticket_total = 0.00;
$fnb_total = 0.00;
$selected_fnb_summary = array();
$selected_seat_numbers = array();

if ($booking_ready && isset($_POST['complete_order'])) {
    foreach ($ticket_counts as $type => $count) {
        $ticket_total += $ticket_prices[$type] * $count;
    }

    foreach ($food_items as $item) {
        $quantity = isset($_POST['fnb'][$item['fnb_id']]) ? filter_var($_POST['fnb'][$item['fnb_id']], FILTER_VALIDATE_INT) : 0;
        $quantity = $quantity === false ? 0 : max(0, min(10, (int) $quantity));
        if ($quantity > 0) {
            $subtotal = (float) $item['fnb_price'] * $quantity;
            $fnb_total += $subtotal;
            $selected_fnb_summary[] = array('type' => 'fnb', 'id' => (int) $item['fnb_id'], 'name' => $item['fnb_name'], 'quantity' => $quantity, 'unit_price' => (float) $item['fnb_price'], 'subtotal' => $subtotal);
        }
    }

    foreach ($drink_items as $item) {
        $quantity = isset($_POST['fnb'][$item['fnb_id']]) ? filter_var($_POST['fnb'][$item['fnb_id']], FILTER_VALIDATE_INT) : 0;
        $quantity = $quantity === false ? 0 : max(0, min(10, (int) $quantity));
        if ($quantity > 0) {
            $subtotal = (float) $item['fnb_price'] * $quantity;
            $fnb_total += $subtotal;
            $selected_fnb_summary[] = array('type' => 'fnb', 'id' => (int) $item['fnb_id'], 'name' => $item['fnb_name'], 'quantity' => $quantity, 'unit_price' => (float) $item['fnb_price'], 'subtotal' => $subtotal);
        }
    }

    foreach ($combos as $combo) {
        $quantity = isset($_POST['combo'][$combo['combo_id']]) ? filter_var($_POST['combo'][$combo['combo_id']], FILTER_VALIDATE_INT) : 0;
        $quantity = $quantity === false ? 0 : max(0, min(10, (int) $quantity));
        if ($quantity > 0) {
            $subtotal = (float) $combo['combo_price'] * $quantity;
            $fnb_total += $subtotal;
            $selected_fnb_summary[] = array('type' => 'combo', 'id' => (int) $combo['combo_id'], 'name' => $combo['combo_name'], 'quantity' => $quantity, 'unit_price' => (float) $combo['combo_price'], 'subtotal' => $subtotal);
        }
    }

    $grand_total = $ticket_total + $fnb_total;
    try {
        $conn->begin_transaction();

        $lock = $conn->prepare('SELECT se.seat_id, se.seat_number
            FROM seat se
            LEFT JOIN customer_booking_seat bs
              ON bs.seat_id = se.seat_id AND bs.showtime_id = ?
            WHERE se.hall_id = ? AND se.seat_id = ?
              AND se.seat_status = 0 AND bs.booking_seat_id IS NULL
            FOR UPDATE');
        $available_rows = array();
        foreach ($selected_seats as $seat_id) {
            $lock->bind_param('iii', $showtime_id, $showtime['hall_id'], $seat_id);
            $lock->execute();
            $seat_row = $lock->get_result()->fetch_assoc();
            if ($seat_row) {
                $available_rows[] = $seat_row;
            }
        }
        $lock->close();

        if (count($available_rows) !== count($selected_seats)) {
            throw new RuntimeException('One or more selected seats have just been booked. Please choose seats again.');
        }
        foreach ($available_rows as $available_seat) {
            $selected_seat_numbers[] = $available_seat['seat_number'];
        }

        $ticket_label_parts = array();
        foreach ($ticket_counts as $type => $count) {
            $ticket_label_parts[] = $type . ' x ' . $count;
        }
        $ticket_label = implode(', ', $ticket_label_parts);
        $status = 'Confirmed';
        $tax_amount = 0.00;
        $insert_booking = $conn->prepare('INSERT INTO customer_booking
            (membership_id, showtime_id, ticket_type, ticket_count, ticket_total, fnb_total, tax_amount, total_amount, booking_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert_booking->bind_param('iisidddds', $membership_id, $showtime_id, $ticket_label, $ticket_count_total, $ticket_total, $fnb_total, $tax_amount, $grand_total, $status);
        $insert_booking->execute();
        $booking_id = $insert_booking->insert_id;
        $insert_booking->close();

        $insert_seat = $conn->prepare('INSERT INTO customer_booking_seat (booking_id, showtime_id, seat_id) VALUES (?, ?, ?)');
        foreach ($selected_seats as $seat_id) {
            $insert_seat->bind_param('iii', $booking_id, $showtime_id, $seat_id);
            $insert_seat->execute();
        }
        $insert_seat->close();

        $insert_item = $conn->prepare('INSERT INTO customer_booking_fnb
            (booking_id, item_type, fnb_id, combo_id, item_name, quantity, unit_price, item_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($selected_fnb_summary as $selected_item) {
            $item_type = $selected_item['type'];
            $fnb_id = $item_type === 'fnb' ? $selected_item['id'] : null;
            $combo_id = $item_type === 'combo' ? $selected_item['id'] : null;
            $unit_price = $selected_item['unit_price'];
            $insert_item->bind_param('isiisidd', $booking_id, $item_type, $fnb_id, $combo_id, $selected_item['name'], $selected_item['quantity'], $unit_price, $selected_item['subtotal']);
            $insert_item->execute();
        }
        $insert_item->close();
        $conn->commit();
        $order_complete = true;
    } catch (Throwable $exception) {
        $conn->rollback();
        $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'The booking could not be completed. Please try again.';
    }

    $_SESSION['cinewave_order'] = array(
        'booking_id' => $booking_id,
        'showtime_id' => $showtime_id,
        'seats' => $selected_seats,
        'ticket_counts' => $ticket_counts,
        'ticket_total' => $ticket_total,
        'fnb_items' => $selected_fnb_summary,
        'fnb_total' => $fnb_total,
        'grand_total' => $grand_total
    );
}

cw_page_start('Food & Beverages', 'fnb', $booking_ready ? array('Home', 'Movie', $showtime['movie_name'], 'Showtime', 'Seat', 'FNB') : array('Home', 'FNB'));
?>
<main class="page-shell fnb-page">

    <?php if (count($errors) > 0) { ?>
        <div class="errors" role="alert"><?php foreach ($errors as $error) { ?><p><?php echo cw_h($error); ?></p><?php } ?></div>
    <?php } ?>

    <?php if ($order_complete) { ?>
        <section class="receipt">
            <div class="receipt-head">
                <h1>Order Confirmed</h1>
                <p>Your movie booking and food selections have been saved.</p>
            </div>
            <div class="summary-row"><span>Booking ID</span><strong>#<?php echo cw_h($booking_id); ?></strong></div>
            <div class="summary-row"><span>Movie</span><strong><?php echo cw_h($showtime['movie_name']); ?></strong></div>
            <div class="summary-row"><span>Showtime</span><strong><?php echo cw_h($showtime['date'] . ' ' . $showtime['start_time']); ?></strong></div>
            <div class="summary-row"><span>Seats</span><strong><?php echo cw_h(implode(', ', $selected_seat_numbers)); ?></strong></div>
            <div class="summary-row"><span>Tickets</span><strong>RM<?php echo number_format($ticket_total, 2); ?></strong></div>
            <?php foreach ($selected_fnb_summary as $selected_item) { ?>
                <div class="summary-row"><span><?php echo cw_h($selected_item['name']); ?> × <?php echo cw_h($selected_item['quantity']); ?></span><strong>RM<?php echo number_format($selected_item['subtotal'], 2); ?></strong></div>
            <?php } ?>
            <div class="summary-row"><span>Food &amp; Beverages</span><strong>RM<?php echo number_format($fnb_total, 2); ?></strong></div>
            <div class="summary-row total"><span>Total</span><strong>RM<?php echo number_format($ticket_total + $fnb_total, 2); ?></strong></div>
            <p><a class="cyan-button" href="movie.php">Back to Movies</a></p>
        </section>
    <?php } else { ?>

    <form action="fnb.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>">
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
                <button class="cyan-button" type="submit" name="complete_order" value="1">Complete Order</button>
            <?php } else { ?>
                <a class="cyan-button" href="movie.php">Choose a movie to order</a>
            <?php } ?>
        </div>
    </form>
    <?php } ?>
</main>
<?php cw_page_end(); ?>
