<?php 
include "db.php";
include "header.php";
if (isset($_GET['id'])) {
    $hall_id = intval($_GET['id']);
} else {
    $hall_id = 1; 
}

?>            
<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Hall Edit</title>
    </head>
<body>
<h1>Hall Edit</h1> 
<?php
if(isset($_POST['update'])){
        $hall_number=$_POST['hall_number'];
        $capacity=$_POST['capacity'];
        $hall_status=$_POST['hall_status'];  
        $address=$_POST['address'];
        $sql_cinema="SELECT cinema_id FROM cinema WHERE address='$address'";
        $result_cinema = $conn->query($sql_cinema);
        $row_cinema=$result_cinema->fetch_assoc();
        $cinema_id=$row_cinema['cinema_id'];         
        $sql="UPDATE hall SET hall_number='$hall_number',capacity='$capacity',hall_status='$hall_status',cinema_id='$cinema_id' ,where hall_id=$hall_id";
        if($conn->query($sql) === TRUE){
            header("Location: hall_management.php");
            exit();
        }else {
            echo "Error: " . $conn->error;
    }}
$sql= "SELECT * FROM hall WHERE hall_id = $hall_id";
$result = $conn->query($sql);
$row=$result->fetch_assoc();

?>
    
 <form action="hall_edit.php?id=<?php echo $hall_id; ?>" method="post">
    Hall Number:<br>
    <input type="text" name="hall_number" value="<?php echo $row['hall_number']; ?>" required>
    <br><br>

    Capacity:<br>
    <input type="text" name="capacity" value="<?php echo $row['capacity']; ?>" required>
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
    <input type="submit" name="update" value="Update">
</form>

</body>
</html>
