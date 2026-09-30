<?php 
include "db.php";

if (isset($_GET['id'])) {
    $showtime_id = intval($_GET['id']);
} else {
    $showtime_id = 1; 
}

$_SESSION['showtime_id'] = $showtime_id;

$sql = "SELECT seat.seat_id, seat.seat_number, showtime.start_time, showtime.date 
        FROM seat 
        LEFT JOIN hall ON hall.hall_id = seat.hall_id
        LEFT JOIN showtime ON showtime.hall_id = hall.hall_id
        WHERE seat_status = 'available' 
        AND showtime_id = $showtime_id";

$result = $conn->query($sql);


$available_seats = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $available_seats[] = $row;
    }
}

$date = (!empty($available_seats)) ? $available_seats[0]['date'] : "N/A";
$start_time = (!empty($available_seats)) ? $available_seats[0]['start_time'] : "N/A";

include "header.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CineWave - Booking Seat</title>
        <link rel="stylesheet" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Showtime - Seat Booking</h1>
        
        <section class="container">
            <div class="container">
                <h4>Date:</h4>
                <p><?php echo htmlspecialchars($date); ?></p>
                <h4>Start Time:</h4>
                <p><?php echo htmlspecialchars($start_time); ?></p>
            </div>
            
            <hr >
            
            <h4>Seat Layout</h4>
            <div class="seat-grid" >
                <?php if (!empty($available_seats)) { ?>
                    <?php foreach ($available_seats as $seat) { ?>
                        <a href="ticket.php?id=<?php echo $seat['seat_id'];?>">
                            <?php echo htmlspecialchars($seat['seat_number']); ?>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <p>No available seats left for this showtime.</p>
                <?php } ?>
            </div>
        </section>
    </body>
    <?php include "footer.html"; ?>
</html>