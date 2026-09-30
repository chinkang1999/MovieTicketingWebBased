<?php 
include "header.php";
include "db.php";
if (isset($_GET['id'])) {
    $movie_id = intval($_GET['id']);
} else {
    $movie_id = 1; 
}
$sql="SELECT showtime.showtime_id,showtime.date, showtime.start_time, movie.path, movie.movie_name 
        FROM showtime 
        LEFT JOIN movie ON showtime.movie_id = movie.movie_id
        WHERE showtime.movie_id = $movie_id 
        AND (showtime.showtime_status = 'Open for booking' OR showtime.showtime_status = 'Scheduled')";
$result=$conn->query($sql);
$row=$result -> fetch_assoc();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Booking Showtime</title>
        <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Showtime</h1>
        <section class="container">
        <div class="container">
            <img src="<?php echo$row['path']?>" alt="<?php echo$row['movie_name']?>">
            <div>
            <h3><?php echo$row['movie_name']?></h3>
            <h4>Date:</h4>
            <?php 
            $sql="SELECT showtime.showtime_id,showtime.date, showtime.start_time, movie.path, movie.movie_name 
        FROM showtime 
        LEFT JOIN movie ON showtime.movie_id = movie.movie_id
        WHERE showtime.movie_id = $movie_id 
        AND (showtime.showtime_status = 'Open for booking' OR showtime.showtime_status = 'Scheduled')";
$result=$conn->query($sql);
while($row=$result -> fetch_assoc()){?>
            <p><?php echo$row['date']?></p>
            <?php } ?>
            <h4>Start_time:</h4>
            <?php
            $sql="SELECT showtime.showtime_id,showtime.date, showtime.start_time, movie.path, movie.movie_name 
        FROM showtime 
        LEFT JOIN movie ON showtime.movie_id = movie.movie_id
        WHERE showtime.movie_id = $movie_id 
        AND (showtime.showtime_status = 'Open for booking' OR showtime.showtime_status = 'Scheduled')";
$result=$conn->query($sql);
 while($row=$result -> fetch_assoc()){?>
            <a href="seat.php?id=<?php echo $row['showtime_id'];?>"class="btn"><?php echo$row['start_time']?> </a>
            <?php
            }?>
         </div>   
        </div>
</section>
    </body>
<?php
include "footer.html";
?>
</html>