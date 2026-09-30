<?php 
include "db.php";
include "header.php";

$sql= "SELECT * FROM movie";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cinewave-Promotion Management</title>
        <link rel="stylesheet" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>Movie Management</h1>
        <table border="1" class="table" >
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Poster</th>
                <th>Sypnosis</th>
                <th>Genre</th>
                <th>Current Showing Status</th>
                <th>Age Restriction</th>
            </tr>
            <?php  while($row=$result->fetch_assoc()){?>
            <tr>
                <td><?php echo $row['movie_id'];?></td>
                <td><?php echo $row['movie_name'];?></td>
                <td><img src="<?php echo $row['path'];?>" alt="<?php echo $row['movie_name'];?>" width="250px"></td>
                <td><?php echo $row['synopsis'];?></td>
                <td><?php echo $row['genre'];?></td>
                <td><?php echo $row['current_showing_status'];?></td>
                <td><?php echo $row['age_restriction'];?></td>
                <td>
                    <a href="movie_edit.php?id=<?php echo $row['movie_id'];?>"class="btn">Edit</a>
                    <a href="movie_delete.php?id=<?php echo $row['movie_id'];?>"class="btn">Delete</a>
                </td>
            </tr>
            <?php
            }?>
        </table>
        <a href="add_movie.php" class="btn">Add Movie</a>
    </body>
</html>

