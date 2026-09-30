<?php
include "db.php";
include "header.php";
if (isset($_GET['id'])) {
    $showtime_id = intval($_GET['id']);
} else {
    $showtime_id = 1;
}

?>
<?php

// this for edit form data display use
$sql = "SELECT * FROM showtime WHERE showtime_id = $showtime_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (isset($_POST['update'])) {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $showtime_status = $_POST['showtime_status'];
    $hall_number =   $_POST['hall_number'];
    $movie_name = $_POST['movie_name'];
    $sql_movie = "SELECT movie_id FROM movie WHERE movie_name='$movie_name'";
    $result_movie = $conn->query($sql_movie);
    $row_movie = $result_movie->fetch_assoc();
    $movie_id = $row_movie['movie_id'];
    $sql_hall = "SELECT hall_id FROM hall WHERE hall_number='$hall_number'";
    $result_hall = $conn->query($sql_hall);
    $row_hall = $result_hall->fetch_assoc();
    $hall_id = $row_hall['hall_id'];
    $sql = "UPDATE showtime SET date='$date',start_time='$start_time',showtime_status='$showtime_status', hall_id='$hall_id',movie_id='$movie_id' where showtime_id=$showtime_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: showtime_management.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}



?>
<!DOCTYPE html>
<html>

<head>
    <title>CineWave-Showtime Edit</title>
</head>

<body>
    <h1>Showtime Edit</h1>

    <form action="showtime_edit.php?id=<?php echo $showtime_id; ?>" method="post">
        Date:<br>
        <input type="date" name="date" value="<?php echo $row['date']; ?>" required>
        <br><br>

        Start Time:<br>
        <input type="time" name="start_time" value="<?php echo $row['start_time']; ?>" required>
        <br><br>

        Showtime Status:<br>
        <input type="radio" name="showtime_status" value="Open for Booking" required>Open for Booking
        <input type="radio" name="showtime_status" value="Scheduled" required>Scheduled
        <input type="radio" name="showtime_status" value="Sold Out" required>Sold Out
        <input type="radio" name="showtime_status" value="Cancelled" required>Cancelled
        <br><br>

        Movie:<br>
        <?php
        $sql_movie = "SELECT movie_name FROM movie WHERE current_showing_status='Now Showing'";
        $result_movie = $conn->query($sql_movie);
        while ($row_movie = $result_movie->fetch_assoc()) {
        ?>
            <input type="radio" name="movie_name" value="<?php echo $row_movie['movie_name'] ?>" required><?php echo $row_movie['movie_name'] ?>
        <?php
        } ?>
        <br><br>
        Hall:<br>
        <?php
        $sql_hall = "SELECT hall_number FROM hall WHERE hall_status='Available'";
        $result_hall = $conn->query($sql_hall);
        while ($row_hall = $result_hall->fetch_assoc()) {
        ?>
            <input type="radio" name="hall_number" value="<?php echo $row_hall['hall_number'] ?>" required><?php echo $row_hall['hall_number'] ?>
        <?php
        } ?>
        <br><br>
        <input type="submit" name="update" value="Update">
    </form>

</body>

</html>