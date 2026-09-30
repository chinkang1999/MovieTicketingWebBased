<?php
require_once __DIR__ . '/customer_init.php';

$view = isset($_GET['view']) ? (string) $_GET['view'] : 'movies';
$page_errors = array();
$page_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($view, array('contact', 'group-booking'), true)) {
    if (!cw_verify_csrf()) {
        $page_errors[] = 'Your form session expired. Please submit the form again.';
    }

    if ($view === 'contact') {
        $contact_name = trim(isset($_POST['contact_name']) ? $_POST['contact_name'] : '');
        $contact_email = strtolower(trim(isset($_POST['contact_email']) ? $_POST['contact_email'] : ''));
        $contact_category = trim(isset($_POST['contact_category']) ? $_POST['contact_category'] : 'Members');
        $contact_message = trim(isset($_POST['contact_message']) ? $_POST['contact_message'] : '');
        if (strlen($contact_name) < 2 || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $page_errors[] = 'Please enter your name and a valid email address.';
        }
        if (!in_array($contact_category, array('Members', 'Cinema', 'Transaction'), true)) {
            $page_errors[] = 'Please select a valid FAQ category.';
        }
        if (strlen($contact_message) < 10 || strlen($contact_message) > 1000) {
            $page_errors[] = 'Your message must contain between 10 and 1000 characters.';
        }
        if (count($page_errors) === 0) {
            $statement = $conn->prepare('INSERT INTO customer_contact (contact_name, contact_email, category, message, contact_status) VALUES (?, ?, ?, ?, ?)');
            $status = 'New';
            $statement->bind_param('sssss', $contact_name, $contact_email, $contact_category, $contact_message, $status);
            $statement->execute();
            $statement->close();
            $page_success = 'Your message has been submitted. Our CineWave team will contact you soon.';
        }
    } else {
        $membership_id = cw_require_customer();
        $group_name = trim(isset($_POST['group_name']) ? $_POST['group_name'] : '');
        $contact_person = trim(isset($_POST['contact_person']) ? $_POST['contact_person'] : '');
        $contact_phone = trim(isset($_POST['contact_phone']) ? $_POST['contact_phone'] : '');
        $preferred_date = trim(isset($_POST['preferred_date']) ? $_POST['preferred_date'] : '');
        $guest_count = filter_var(isset($_POST['guest_count']) ? $_POST['guest_count'] : 0, FILTER_VALIDATE_INT);
        $notes = trim(isset($_POST['notes']) ? $_POST['notes'] : '');
        if (strlen($group_name) < 2 || strlen($contact_person) < 2) {
            $page_errors[] = 'Please enter the group name and contact person.';
        }
        if (!preg_match('/^[0-9+() -]{8,20}$/', $contact_phone)) {
            $page_errors[] = 'Please enter a valid contact number.';
        }
        $date = DateTime::createFromFormat('Y-m-d', $preferred_date);
        if (!$date || $date->format('Y-m-d') !== $preferred_date || $date < new DateTime('today')) {
            $page_errors[] = 'Please select a valid future booking date.';
        }
        if ($guest_count === false || $guest_count < 20 || $guest_count > 500) {
            $page_errors[] = 'Group booking requires between 20 and 500 guests.';
        }
        if (count($page_errors) === 0) {
            $conn->begin_transaction();
            try {
                $status = 'Pending';
                $statement = $conn->prepare('INSERT INTO group_booking (group_booking_name, group_booking_status) VALUES (?, ?)');
                $statement->bind_param('ss', $group_name, $status);
                $statement->execute();
                $group_booking_id = $statement->insert_id;
                $statement->close();
                $statement = $conn->prepare('INSERT INTO customer_group_booking_request (group_booking_id, membership_id, contact_person, contact_phone, preferred_date, guest_count, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $statement->bind_param('iisssis', $group_booking_id, $membership_id, $contact_person, $contact_phone, $preferred_date, $guest_count, $notes);
                $statement->execute();
                $statement->close();
                $conn->commit();
                $page_success = 'Your whole-hall booking request has been received.';
            } catch (Throwable $exception) {
                $conn->rollback();
                $page_errors[] = 'The group booking request could not be saved. Please try again.';
            }
        }
    }
}

if ($view === 'contact') {
    cw_page_start('Contact & FAQ', 'movie', array('Home', 'Contact & FAQ'));
    ?>
    <main class="page-shell contact-page">
        <section class="contact-message-panel">
            <h1>Contact &amp; FAQ</h1>
            <?php if ($page_success !== '') { ?><div class="success-message" role="status"><?php echo cw_h($page_success); ?></div><?php } ?>
            <?php if (count($page_errors) > 0) { ?><div class="errors" role="alert"><?php foreach ($page_errors as $error) { ?><p><?php echo cw_h($error); ?></p><?php } ?></div><?php } ?>
            <form action="movie.php?view=contact" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>">
                <div class="form-grid"><div class="field"><label for="contact_name">Name</label><input id="contact_name" name="contact_name" maxlength="50" required></div><div class="field"><label for="contact_email">Email</label><input id="contact_email" type="email" name="contact_email" maxlength="80" required></div></div>
                <div class="field"><label for="contact_category">FAQ Category</label><select id="contact_category" name="contact_category"><option>Members</option><option>Cinema</option><option>Transaction</option></select></div>
                <div class="field"><label for="contact_message">Message</label><textarea id="contact_message" name="contact_message" maxlength="1000" placeholder="Enter your message here..." required></textarea></div>
                <div class="form-actions"><button class="ghost-button" type="reset">Clear</button><button class="cyan-button" type="submit">Submit</button></div>
            </form>
        </section>
        <aside class="faq-panel"><h2>FAQs</h2><details open><summary>Members</summary><p>Membership points and account details are available after signing in.</p></details><details><summary>Cinema</summary><p>Choose a movie, date and available showtime before selecting seats.</p></details><details><summary>Transaction</summary><p>Confirmed bookings are protected against duplicate seat reservations.</p></details><a class="cyan-button" href="movie.php?view=group-booking">Group Booking Form</a></aside>
    </main>
    <?php cw_page_end(); exit();
}

if ($view === 'group-booking') {
    cw_page_start('Group Booking Form', 'movie', array('Home', 'Group Booking Form'));
    ?>
    <main class="page-shell group-booking-page">
        <section class="group-booking-intro"><h1>Group Booking Form</h1><p>Planning to book a whole hall? We got you covered.</p><p>Fill out the form and our team will get back to you.</p><h2>Why Book a Whole Hall with Us?</h2><ul><li>Private screening experience</li><li>Flexible seating for large groups</li><li>Food and beverage packages</li><li>Dedicated CineWave assistance</li></ul></section>
        <section class="group-booking-form"><h2>Information Required</h2><?php if ($page_success !== '') { ?><div class="success-message" role="status"><?php echo cw_h($page_success); ?></div><?php } ?><?php if (count($page_errors) > 0) { ?><div class="errors" role="alert"><?php foreach ($page_errors as $error) { ?><p><?php echo cw_h($error); ?></p><?php } ?></div><?php } ?><form action="movie.php?view=group-booking" method="post"><input type="hidden" name="csrf_token" value="<?php echo cw_h(cw_csrf_token()); ?>"><div class="field"><label for="group_name">Group / Event Name</label><input id="group_name" name="group_name" maxlength="50" required></div><div class="form-grid"><div class="field"><label for="contact_person">Contact Person</label><input id="contact_person" name="contact_person" maxlength="50" required></div><div class="field"><label for="contact_phone">Phone Number</label><input id="contact_phone" name="contact_phone" maxlength="20" required></div><div class="field"><label for="preferred_date">Preferred Date</label><input id="preferred_date" type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>" required></div><div class="field"><label for="guest_count">Number of Guests</label><input id="guest_count" type="number" name="guest_count" min="20" max="500" required></div></div><div class="field"><label for="notes">Additional Information</label><textarea id="notes" name="notes" maxlength="1000"></textarea></div><button class="cyan-button" type="submit">Submit Request</button></form></section>
    </main>
    <?php cw_page_end(); exit();
}

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
