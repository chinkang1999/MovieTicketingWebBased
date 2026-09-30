<?php 
include "db.php";
include "header.php";


?>            
<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Add Hall </title>
    </head>
<body>
<h1>Add Hall</h1> 
<?php
if(isset($_POST['submit'])){
        $hall_number=$_POST['hall_number'];
        $capacity=$_POST['capacity'];
        $hall_status=$_POST['hall_status']; 
        $address=$_POST['address'];
        $sql_cinema="SELECT cinema_id FROM cinema WHERE address='$address'";
        $result_cinema = $conn->query($sql_cinema);
        $row_cinema=$result_cinema->fetch_assoc();
        $cinema_id=$row_cinema['cinema_id'];           
        $sql="INSERT INTO hall(hall_number,capacity,hall_status,cinema_id) VALUES('$hall_number','$capacity','$hall_status','$cinema_id')";
        if($conn->query($sql) === TRUE){
            header("Location: hall_management.php");
            exit();
        }else {
            echo "Error: " . $conn->error;
    }}

?>
    
 <form action="hall_edit.php" method="post">
    Hall Number:<br>
    <input type="text" name="hall_number"  required>
    <br><br>

    Capacity:<br>
    <input type="text" name="capacity"  required>
     <br><br>

    Hall Status<br>
    <input type="radio" name="hall_status" value="Available" required>Available
    <input type="radio" name="hall_status" value="Under Maintainenece" required>Under Maintainence
    <br><br>
    
     Branch:<br>
    <?php
        $sql_cinema="SELECT address FROM cinema ";
        $result_cinema = $conn->query($sql_cinema);
        while($row_cinema=$result_cinema->fetch_assoc()){
    ?>
    <input type="radio" name="address" value="<?php echo$row_cinema['address']?>" required><?php echo$row_cinema['address']?>
    <?php
    }?>
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
             