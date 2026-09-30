<?php
include "db.php";
include "header.php";

if(isset($_POST['add_new'])){
    $name = $_POST['new_name'];
    $price = $_POST['new_cost'];
    $staff = $_POST['staff_id'];
    $description = $conn->real_escape_string($_POST['new_description']);
    $desc = "";

    $conn->query("INSERT INTO fnb(fnb_name,fnb_price,staff_id,description,path)
    VALUES ('$name',$price,$staff,'$description','')");
    $new_id = $conn->insert_id;

    if(isset($_FILES['movie_img']) && $_FILES['movie_img']['error'] == 0){
        $folder_path = "asset/fnbpicture/";

        if(!is_dir($folder_path)){
            mkdir($folder_path,0777,true);
        }

        $db_path = $folder_path.$new_id.".png";

        if(move_uploaded_file($_FILES['movie_img']['tmp_name'],$db_path)){
            $conn->query("UPDATE fnb SET path = '$db_path' WHERE fnb_id=$new_id ");
        }
    }

    header("Location:fnbmanagement.php?msg=add new item successed!");
    exit();
}

if(isset($_POST['save_change'])){
    $id = $_POST['fnb_id'];
    $name = $_POST['fnb_name'];
    $price = $_POST['fnb_price'];
    $staff = 1001;
    $desc = isset($_POST['description']) ? $_POST['description'] : '';
    $conn->query("UPDATE fnb SET fnb_name = '$name',fnb_price =$price,description = '$desc',staff_id = $staff WHERE fnb_id=$id");
    header("Location: fnbmanagement.php?msg=item updated success!");
    exit();
}

if(isset($_GET['delete'])){
    $delete_id = intval($_GET['delete']);
    $resipotory = $conn->query("SELECT path FROM fnb WHERE  fnb_id=$delete_id");

    if($row = $resipotory->fetch_assoc()){
        if(!empty($row['path']) && file_exists($row['path'])){
            unlink($row['path']);
        }
    }

    $conn->query("DELETE FROM fnb WHERE fnb_id = $delete_id");
    header("Location:fnbmanagement.php?msg=item delete success!");
    exit();
}
?>


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinewave_info_page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        .fnbmanagement_container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .add_new_fnb_picture_box {
            width: 10rem;
            height: 10rem;
            position: relative;
            margin: 1.5rem;
            border: 3px dashed #022a3b;
            background-color: #00f6ff;
            overflow: hidden;
        }

        .add_new_fnb_picture_box .add_movie_form_input {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0;
        }

        .add_new_fnb_picture_box input[type="file"] {
            position: absolute;
            bottom: 0.75rem;
            right: -1.5rem;;
            max-width: 180px;
            color: #074659;
            font-size: 0.85rem;
            z-index: 10;
        }

        .add_new_fnb_picture_box label {
            color: #074659;
            font-weight: bold;
            font-size: 1.2rem;
            z-index: 1;
        }

        #img_preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 2;
        }
    </style>
</head>

<body class="body">
    <div class="container">
        <div class="fnbmanagement_container">
            <div>
                <h2>Cinewave FNB Management</h2>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="add_new_fnb_picture_box">
                        <div class="add_movie_form_input">
                            <label id="image_label">FNB Image</label>
                            <img id="img_preview" src="" alt="image preview" style="display: none;">
                            <input type="file" id="movie_img" name="movie_img" accept="image/*" required>
                        </div>
                    </div>
                    ItemName: <input type="text" name="new_name" placeholder="such as: lemonade" style="width:150px;" required>
                    Cost: <input type="number" step="0.01" name="new_cost" placeholder="such as (in RM) : 100" style="width:150px;" required>
                    StaffID: <input type="number" name="new_staff" placeholder="1001" style="width:80px;" required>
                    Description: <input type="text" name="new_description" placeholder="describe your product" style="width:180px;" required>
                    <button class="btn" type="submit" name="add_new">Add Stock</button>
                </form>
                <br><br>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>F/B Name</th>
                        <th>Cost</th>
                        <th>StaffID</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM fnb ORDER BY fnb_id DESC");
                    while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                            <form action="" method="post" id="movieForm">
                                <td>
                                    <input type="hidden" name="fnb_id" value="<?php echo $row['fnb_id']; ?>" required>
                                    <strong><?php echo $row['fnb_id']; ?></strong>
                                </td>

                                <td>
                                    <input type="text" name="fnb_name" value="<?php echo $row['fnb_name']; ?>" required>
                                </td>

                                <td>
                                    <input type="number" step="0.01" name="fnb_price" value="<?php echo $row['fnb_price']; ?>" required>
                                </td>

                                <td>
                                    <input type="number" name="staff_id" value="<?php echo $row['staff_id']; ?>" required>
                                </td>

                                <td>
                                    <input type="text" name="description" value="<?php echo $row['description']; ?>" required>
                                </td>
                                <td>
                                    <button type="submit" class="btn" name="save_change"class="btn" onclick="return confirm('Click Ok for Save data?')">Save</button>
                                    <a href="fnbmanagement.php?delete=<?php echo $row['fnb_id']; ?>" class="btn" onclick="return confirm('Are you sure delete it?')">Delete</a>
                                </td>
                            </form>
                        </tr>
                    <?php
                    }
                    ?>

                </tbody>
            </table>
        </div>

    </div>

    <script>
        const movieImgInput = document.getElementById('movie_img');
        const imgPreview = document.getElementById('img_preview');
        const imageLabel = document.getElementById('image_label');
        const movieForm = document.getElementById('movieForm');
        movieImgInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function(e) {
                    imgPreview.setAttribute('src', e.target.result);
                    imgPreview.style.display = 'block';
                    imageLabel.style.display = 'none';
                });
                reader.readAsDataURL(file);
            }
        });

        movieForm.addEventListener('reset', function() {
            imgPreview.setAttribute('src', '');
            imgPreview.style.display = 'none';
            imageLabel.style.display = 'block';
        });
    </script>
</body>

</html>

<?php
include "footer.html" ?>

</html>