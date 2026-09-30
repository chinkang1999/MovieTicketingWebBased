<?php 
include "db.php";
include "header.php";
?>            
<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Add Showtime</title>
    </head>
<body>
<h1>Add Showtime </h1> 
<?php
if(isset($_POST['update'])){
        $date=$_POST['date'];
        $start_time=$_POST['start_time'];
        $showtime_status=$_POST['showtime_status']; 
        $hall_number=   $_POST['hall_number'];    
        $movie_name=$_POST['movie_name'];
        $sql_movie="SELECT movie_id FROM movie WHERE movie_name='$movie_name'";
        $result_movie = $conn->query($sql_movie);
        $row_movie=$result_movie->fetch_assoc();
        $movie_id=$row_movie['movie_id'];
        $sql_hall="SELECT hall_id FROM hall WHERE hall_number='$hall_number'";
        $result_hall= $conn->query($sql_hall);
        $row_hall=$result_hall->fetch_assoc();
        $hall_id=$row_hall['hall_id'];          
        $sql="INSERT INTO showtime (date,start_time,showtime_status,movie_id,hall_id)VALUES('$date','$start_time','$showtime_status','$movie_id','$hall_id')";
        if($conn->query($sql) === TRUE){
            header("Location: showtime_management.php");
            exit();
        }else {
            echo "Error: " . $conn->error;
    }}
?>
    
 <form action="add_showtime.php ?>" method="post">
    Date:<br>
    <input type="date" name="date"  required>
    <br><br>

    Start Time:<br>
    <input type="time" name="start_time"  required>
     <br><br>

    Showtime Status<br>
    <input type="radio" name="hall_status" value="Open for Booking" required>Available
    <input type="radio" name="hall_status" value="Scheduled" required>Under Maintainence
    <input type="radio" name="hall_status" value="Sold Out" required>Sold Out
    <input type="radio" name="hall_status" value="Cancelled" required>Cancelled
    <br><br>
    Showtime Status:<br>
    <input type="radio" name="showtime_status" value="Open for Booking"required>Open for Booking
    <input type="radio" name="showtime_status"  value="Scheduled"required>Scheduled
    <input type="radio" name="showtime_status"  value="Sold Out" required>Sold Out
    <input type="radio" name="showtime_status" value="Cancelled" required>Cancelled
    <br><br>

    Movie:<br>
    <?php
        $sql_movie="SELECT movie_name FROM movie WHERE current_showing_status='Now Showing'";
        $result_movie = $conn->query($sql_movie);
        while($row_movie=$result_movie->fetch_assoc()){
    ?>
    <input type="radio" name="movie_name" value="<?php echo$row_movie['movie_name']?>" required><?php echo$row_movie['movie_name']?>
    <?php
    }?>
<br><br>
    Hall:<br>
    <?php
        $sql_hall="SELECT hall_number FROM hall WHERE hall_status='Available'";
        $result_hall = $conn->query($sql_hall);
        while($row_hall=$result_hall->fetch_assoc()){
    ?>
    <input type="radio" name="hall_number" value="<?php echo$row_hall['hall_number']?>"required><?php echo$row_hall['hall_number']?>
    <?php
    }?>
    <br><br>
    <input type="submit" name="update" value="Update">
</form>

</body>
</html>
             