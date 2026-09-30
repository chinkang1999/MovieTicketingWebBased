<?php 
include "db.php";
include "header.php";
$id = $_SESSION['id'];
?>            
<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Add Reward </title>
    </head>
<body>
<h1>Add Reward</h1> 
<?php
if(isset($_POST['submit'])){
        $reward_name=$_POST['reward_name'];
        $points_required=$_POST['points_required'];
        $exp_date=$_POST['exp_date'];           
        $sql="INSERT INTO reward(reward_name,points_required,exp_date,staff_id) VALUES('$reward_name','$points_required','$exp_date','$id')";
        if($conn->query($sql) === TRUE){
            header("Location: reward_management.php");
            exit();
        }else {
            echo "Error: " . $conn->error;
    }}

?>
    
 <form action="add_reward.php" method="post">
    Name:<br>
    <input type="text" name="reward_name"  required>
    <br><br>

    Points_required:<br>
    <input type="text" name="points_required"  required>
     <br><br>

    Expiry date<br>
    <input type=date name="exp_date" required>
    <br><br>
   
    <input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
             