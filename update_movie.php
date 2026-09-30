<?php 
include "header.php";
include "db.php";
$sql="SELECT *FROM movie where current_showing_status='Now Showing' ";
$result=$conn->query($sql);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Movie</title>
        <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Movie</h1>
        <section class="container">
        <h3>Now Showing</h3>
        <?php
        while($row=$result -> fetch_assoc()){
        ?>
        <div class="container">
            <img src="<?php echo$row['path']?>" alt="<?php echo$row['movie_name']?>">
            <a href="movie_booking.php?id=<?php echo $row['movie_id'];?>" class="btn" >Book Now</a>
        </div>
        <?php
        }
        ?>
        </section>
        <section class="container">
        <h3>Coming Soon</h3>
        <?php
        $sql="SELECT *FROM movie where current_showing_status='Coming Soon' ";
        $result=$conn->query($sql);
        while($row=$result -> fetch_assoc()){
        ?>
        
        <div class="container">
            <img src="<?php echo$row['path']?>" alt="<?php echo$row['movie_name']?>">
            <a href="movie_booking.php?id=<?php echo $row['movie_id'];?>"  class="btn" >More Info</a>
        </div>
        <?php
        }
        ?>
    </body>
<?php
include "footer.html";
?>
</html>