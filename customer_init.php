<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    exit('Database connection is unavailable. Keep the original db.php in this folder.');
}

$conn->set_charset('utf8mb4');

function cw_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cw_customer_id()
{
    foreach (array('membership_id', 'membership', 'user_id') as $key) {
        if (isset($_SESSION[$key]) && ctype_digit((string) $_SESSION[$key])) {
            return (int) $_SESSION[$key];
        }
    }

    if (isset($_SESSION['id'], $_SESSION['number'])
        && (int) $_SESSION['number'] === 1
        && ctype_digit((string) $_SESSION['id'])) {
        return (int) $_SESSION['id'];
    }

    return null;
}

function cw_require_customer()
{
    $membership_id = cw_customer_id();

    if ($membership_id === null) {
        $return_to = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'movie.php';
        header('Location: auth.php?mode=login&return_to=' . rawurlencode($return_to));
        exit();
    }

    return $membership_id;
}

function cw_safe_return_to($value)
{
    $value = trim((string) $value);

    if ($value === '' || strpos($value, '://') !== false || strpos($value, '//') === 0) {
        return 'movie.php';
    }

    return ltrim($value, '/');
}

function cw_ticket_prices()
{
    return array(
        'Adult' => 22.00,
        'Child' => 14.00,
        'Student' => 18.00,
        'Senior' => 16.00,
        'OKU' => 15.00
    );
}

function cw_page_start($title, $active, $breadcrumbs)
{
    require_once __DIR__ . '/header.php';
    ?>
    <link rel="stylesheet" href="css/customer.css">
    <div class="cw-customer-module" data-page-title="<?php echo cw_h($title); ?>">
        <?php if (count($breadcrumbs) > 0) { ?>
            <div class="cw-breadcrumb">Home<?php foreach (array_slice($breadcrumbs, 1) as $crumb) { ?>&gt;&gt;<?php echo cw_h($crumb); ?><?php } ?></div>
        <?php } ?>
<?php
}

function cw_page_end()
{
    ?>
        <script src="js/customer.js"></script>
    </div>
<?php
}

function cw_fetch_movie($conn, $movie_id)
{
    $statement = $conn->prepare('SELECT * FROM movie WHERE movie_id = ?');
    $statement->bind_param('i', $movie_id);
    $statement->execute();
    $result = $statement->get_result();
    $movie = $result->fetch_assoc();
    $statement->close();

    return $movie;
}

function cw_fetch_showtime($conn, $showtime_id)
{
    $sql = 'SELECT s.*, m.movie_name, m.path AS movie_path, m.age_restriction,
                   h.hall_number, h.capacity
            FROM showtime s
            JOIN movie m ON m.movie_id = s.movie_id
            JOIN hall h ON h.hall_id = s.hall_id
            WHERE s.showtime_id = ?';
    $statement = $conn->prepare($sql);
    $statement->bind_param('i', $showtime_id);
    $statement->execute();
    $showtime = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $showtime;
}
