<?php 
include "header.php";
include "db.php";
$sql="SELECT *FROM reward";
$result=$conn->query($sql);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CineWave-Reward</title>
        <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Reward</h1>
        <section class="container">
        <?php
        while($row=$result -> fetch_assoc()){
        ?>
        
        <div class="card-reward">
            <h3>
                <?php echo $row["reward_name"];?>
            </h3>
            <p class="points">
                <?php echo $row["points_required"];?> pts
            </p>
            <p class="expiry">
                <?php echo $row["exp_date"]?>
            </p>
            <button class="btn">
                Use Now
            </button>
        </div>

        <?php
        }
        ?>
        </section>
    </body>
<?php
include "footer.html";
?>
</html>