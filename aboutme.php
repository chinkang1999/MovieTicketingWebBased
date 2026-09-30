<?php
session_start();
include "db.php";
include "header.php";

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql="SELECT * FROM customer WHERE membership_id=$id;";
    $result =$conn->query($sql);
    $row=$result ->fetch_assoc();
    
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cinewave-Me</title>
        <link rel="stylesheet" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Profile</h1>
        <section class="container">
            <img src="<?php echo $row["path"];?>" alt="Profile Photo" width="250">
                <section class="profile">
                <h3>Email: <?php echo $row["email"];?></h3>
                <h3>Date Of Birth: <?php echo $row["date_of_birth"]; ?></h3>
                <h3>Phone number: <?php echo $row['phone_number'];?></h3>
                <h3>Membership points:<?php echo $row["points"]; ?> </h3>
                <section class="profile-button">
                <a href="profile_edit.php?id=<?php echo $row['membership_id'];?>"><btn class="btn">Edit Profile</btn></a> 
               <form method ="post" >
                <input  type="submit" name="logout" value="Log Out" class="btn">
                </form>
                </section>
                <?php
                    if(isset($_POST["logout"])){
                        session_unset();
                        session_destroy();
                        header("Location: index.php");
                    }
                ?>
                </section>
                <section>
                <h1>My Bookings</h1>
                <?php
                    $sql2="SELECT movie.movie_name,showtime.start_time, showtime.date,seat.seat_number,hall.hall_number,ticket.path from total_order
                    LEFT JOIN ticket on total_order.ticket_id=ticket.ticket_id
                    LEFT join showtime on ticket.showtime_id=showtime.showtime_id
                    LEFT join movie on showtime.movie_id=movie.movie_id
                    LEFT join seat on ticket.seat_id=seat.seat_id
                    LEFT join hall on seat.hall_id=hall.hall_id
                    where membership_id=$id;";
                    $result2 =$conn->query($sql2);
                  
                    if($result2->num_rows == 1){
                          $row2=$result2 ->fetch_assoc();
                ?>
                <div class="card">
                    <img src="<?php echo $row2['path'];?>" alt="qr code">
                    <h3><?php echo $row2['movie_name'];?></h4>
                    <h5>Date:<?php echo $row2['date'];?></h5>
                    <h5>Start Time:<?php echo $row2['start_time'];?></h5>
                    <h5>Seat Number: <?php echo $row2['seat_number'];?></h5>
                    <h5>Hall: <?php echo $row2['hall_number'];?></h5>
                </div>
                <?php
                }?>
             </section>    
            </section>

    </body>
<?php
}else{
    header("Location:login.php");
}
    include "footer.html";
?>
</html>