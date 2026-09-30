<?php 
// 1. FORCE PHP TO DISPLAY ERRORS (Turns off the silent blank white screen)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db.php";

// 1. Initial extraction of the seat ID
if (isset($_GET['id'])) {
    $seat_id = intval($_GET['id']);
} elseif (isset($_SESSION['seat_id'])) {
    $seat_id = intval($_SESSION['seat_id']);
} elseif (isset($_SESSION['selected_seat'])) {
    $seat_id = intval($_SESSION['selected_seat']);
} else {
    $seat_id = 0; // Fallback to verify against database next
}

// 2. DATABASE SANITY CHECK: Ensure this seat ID actually exists in the seat table!
$check_seat = $conn->query("SELECT seat_id FROM seat WHERE seat_id = $seat_id");

if (!$check_seat || $check_seat->num_rows === 0) {
    // CRITICAL FIX: The ID doesn't exist! Let's pull the very first valid seat ID available in your system.
    $fallback_query = $conn->query("SELECT seat_id FROM seat LIMIT 1");
    if ($fallback_query && $fallback_query->num_rows > 0) {
        $fallback_row = $fallback_query->fetch_assoc();
        $seat_id = intval($fallback_row['seat_id']);
    } else {
        // If this drops, it means your seat table is completely empty!
        die("<div style='color:#fff; background:#d9534f; padding:20px; text-align:center; font-family:sans-serif; border-radius:4px; max-width:600px; margin:50px auto;'>
                <strong>Database Configuration Error</strong><br>
                Your <code>seat</code> table appears to be completely empty. Please seed or insert rows into your <code>seat</code> table in phpMyAdmin first!
             </div>");
    }
}

// Lock the verified, valid ID back into the session
$_SESSION['seat_id'] = $seat_id;

if (!isset($_SESSION['showtime_id'])) {
    $_SESSION['showtime_id'] = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : 1;
}
$showtime_id = intval($_SESSION['showtime_id']);

if (!isset($_SESSION['ticket_price'])) {
    $_SESSION['ticket_price'] = 12.50; 
}
$ticket_price = floatval($_SESSION['ticket_price']);

if (!isset($_SESSION['ticket_type'])) {
    $_SESSION['ticket_type'] = 'Standard';
}
$ticket_type = $_SESSION['ticket_type'];

if (!isset($_SESSION['id'])) { $_SESSION['id'] = 1; } 
if (!isset($_SESSION['points'])) { $_SESSION['points'] = 0; }

$points = intval($_SESSION['points']);
$membership_id = intval($_SESSION['id']);

$fnb_item_price = isset($_SESSION['fnb_item_price']) ? floatval($_SESSION['fnb_item_price']) : 0.00;
$combo_item_price = isset($_SESSION['combo_item_price']) ? floatval($_SESSION['combo_item_price']) : 0.00;


$fnb_item_id = (isset($_SESSION['fnb_item_id']) && !empty($_SESSION['fnb_item_id'])) ? intval($_SESSION['fnb_item_id']) : "NULL";
$combo_item_id = (isset($_SESSION['combo_item_id']) && !empty($_SESSION['combo_item_id'])) ? intval($_SESSION['combo_item_id']) : "NULL";

$amount_paid = $ticket_price + $fnb_item_price + $combo_item_price;


if (isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $promotion_name = $_POST['promotion_name'];
    $reward_name = $_POST['reward_name'];
    
    $promotion_id = "NULL";
    if (!empty($promotion_name)) {
        $p_res = $conn->query("SELECT promotion_id FROM promotion WHERE promotion_name = '" . $conn->real_escape_string($promotion_name) . "'");
        if ($p_res && $p_row = $p_res->fetch_assoc()) { $promotion_id = intval($p_row['promotion_id']); }
    }
    
    $reward_id = "NULL";
    if (!empty($reward_name)) {
        $r_res = $conn->query("SELECT reward_id FROM reward WHERE reward_name = '" . $conn->real_escape_string($reward_name) . "'");
        if ($r_res && $r_row = $r_res->fetch_assoc()) { $reward_id = intval($r_row['reward_id']); }
    }

    $sql_ticket = "INSERT INTO ticket (ticket_type, ticket_price, path, showtime_id, seat_id) 
                   VALUES ('$ticket_type', '$ticket_price', 'asset/ticket/qr.png', $showtime_id, $seat_id)";
    
    if ($conn->query($sql_ticket) === TRUE) {
        $ticket_id = $conn->insert_id;
        
       $current_datetime = date("Y-m-d H:i:s");

        // Include the datetime column and pass the variable into the VALUES list
       $current_datetime = date("Y-m-d H:i:s");

        // Include group_booking_order_id in the fields and pass NULL in the VALUES
      $sql_order = "INSERT INTO total_order (membership_id, promotion_id, reward_id, ticket_id, fnb_item_id, combo_item_id, total_order_datetime, group_booking_order_id) 
                      VALUES ($membership_id, $promotion_id, $reward_id, $ticket_id, $fnb_item_id, $combo_item_id, '$current_datetime', NULL)";
        if ($conn->query($sql_order) === TRUE) {
            $total_order_id = $conn->insert_id;
            
            $txn_ref = "TXN" . date("Ymd") . rand(1000, 9999);
                $sql_payment = "INSERT INTO payment (payment_method, amount_paid, transaction_reference, payment_status, total_order_id) 
                                VALUES ('$payment_method', $amount_paid, '$txn_ref', 'Paid', $total_order_id)";
                
            if ($conn->query($sql_payment) === TRUE) {
                echo "<script>alert('Payment Successful!'); window.location.href='index.php';</script>";
                exit();
            }
        }
    }
    die("Database Error occurred: " . $conn->error);
}


$movie_sql = "SELECT s.date, s.start_time, m.path, m.movie_name 
              FROM showtime s 
              LEFT JOIN movie m ON s.movie_id = m.movie_id 
              WHERE s.showtime_id = $showtime_id";
$movie_res = $conn->query($movie_sql);
$movie_info = ($movie_res && $movie_res->num_rows > 0) ? $movie_res->fetch_assoc() : ['date'=>'N/A', 'start_time'=>'N/A', 'path'=>'', 'movie_name'=>'Test Movie'];

$seat_res = $conn->query("SELECT seat_number FROM seat WHERE seat_id = $seat_id");
$seat_info = ($seat_res && $seat_res->num_rows > 0) ? $seat_res->fetch_assoc() : ['seat_number' => 'A' . $seat_id];

// Dynamic structural include check
if(file_exists("header.php")) { include "header.php"; }
?>

<!DOCTYPE html>
<html>
<head>
    <title>CineWave - Payment</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Quantico:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="body" >
    <h1 >Payment Summary</h1>
    
    <form method="POST" action="">
        <section class>
            
            <?php if(!empty($movie_info['path'])): ?>
                <img src="<?php echo htmlspecialchars($movie_info['path']); ?>" alt="Movie Poster" style="max-width:200px; border-radius:4px; margin-bottom:15px; display:block;">
            <?php endif; ?>
            
            <h3>Movie:</h3>
            <p><?php echo htmlspecialchars($movie_info['movie_name']); ?></p>
            
            <h3>Showtime:</h3>
            <p><?php echo htmlspecialchars($movie_info['date']) . " @ " . htmlspecialchars($movie_info['start_time']); ?></p>
            
            <h3>Seat Allocated:</h3>
            <p><?php echo htmlspecialchars($seat_info['seat_number']); ?></p>
            
            <h3>Ticket Selection:</h3>
            <p><?php echo htmlspecialchars($ticket_type); ?> ($<?php echo number_format($ticket_price, 2); ?>)</p>
            


            <label for="promotion_name" >Select Promotion</label>
            <select name="promotion_name" id="promotion_name" >
                <option value="">None</option>
                <?php
                $p_list = $conn->query("SELECT promotion_name FROM promotion");
                if ($p_list && $p_list->num_rows > 0) {
                    while($p_row = $p_list->fetch_assoc()){
                        echo "<option value='".htmlspecialchars($p_row['promotion_name'])."'>".htmlspecialchars($p_row['promotion_name'])."</option>";
                    }
                }
                ?>
            </select>   

            <label for="reward_name" s>Select Reward Redemption</label>
            <select name="reward_name" id="reward_name" >
                <option value="">None</option>
                <?php 
                $r_list = $conn->query("SELECT reward_name, points_required FROM reward");
                if ($r_list && $r_list->num_rows > 0) {
                    while($r_row = $r_list->fetch_assoc()){
                        if($points >= $r_row['points_required']){
                            echo "<option value='".htmlspecialchars($r_row['reward_name'])."'>".htmlspecialchars($r_row['reward_name'])."</option>";
                        }
                    }
                }
                ?>
            </select> 

            <h3>Total Due Amount:</h3>
            <p >
                $<?php echo number_format($amount_paid, 2); ?>
            </p>

            <label for="payment_method" >Select Payment Method</label>
            <select name="payment_method" id="payment_method" required >
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
                <option value="Online Banking">Online Banking</option>
            </select>   
            
            <button type="submit" name="submit_payment" class="btn" >
                Confirm & Pay
            </button>
        </section>
    </form>
</body>
<?php 
if(file_exists("footer.html")) { include "footer.html"; } 
?>
</html>