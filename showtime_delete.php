
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include"db.php";
if (isset($_GET['id'])) {
    $showtime_id = intval($_GET['id']);
} else {
    $showtime_id = 1; 
}

if ($showtime_id > 0) {
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");


    $sql = "DELETE FROM showtime WHERE showtime_id = $showtime_id";
    if ($conn->query($sql) === TRUE) {
        header("Location:showtime_management.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Cinewave-Showtime delete</title>
</head>

<body>
    <h2>Delete Showtime</h2>
    <?php
        echo "Error: " . $conn->error;

    ?>

</body>

</html>