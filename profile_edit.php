<?php
include "db.php";
include "header.php";
$id = $_SESSION['id'];
 $sql="SELECT * FROM customer WHERE membership_id='$id';";
    $result =$conn->query($sql);
    $row=$result ->fetch_assoc();
?>
<!DOCTYPE html>
<html>
    <head>
        <title> CineWave-Profile Edit</title>
    </head>
    <body class="body">
        <?php
        if(isset($_POST["submit"])){
            $fullname=$_POST["fullname"];
            $phone_number=$_POST["phone_number"];
            $email=$_POST["email"];
            $date_of_birth=$_POST["date_of_birth"];

            $sql="UPDATE customer
                SET fullname='$fullname', phone_number='$phone_number',email='$email',date_of_birth='$date_of_birth'
                WHERE membership_id='$id'";

            if($conn->query($sql)===TRUE){
                echo"Profile Change Successfully";
                header("Location: aboutme.php");
            }else{
                echo "Error".$conn->error;
            }
        }else{
        ?>
        <form method="post">
            Full Name:<br>
            <input type="text" name="fullname" value="<?php echo $row['fullname']; ?>" required>
            <br><br>

            Phone Number:<br>
            <input type="text" name="phone_number" value="<?php echo $row['phone_number']; ?>" required>
            <br><br>

            Email:
            <input type="email" name="email" value="<?php echo $row['email'];?>"required>
            <br><br>

            Date Of Birth:
            <input type="date" name="date_of_birth" value="<?php echo $row['date_of_birth'];?> required">
            <br><br>

            <input type="submit" name="submit" value="Update Customer">
        </form>
        <?php
        }?>
    </body>
</html>