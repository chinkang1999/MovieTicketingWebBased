<?php
include "db.php";
include "header.php";

$id = -1;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

// 获取当前电影数据
$stmt = $conn->prepare("SELECT * FROM movie WHERE movie_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    // 如果找不到电影，可以做适当处理，这里简单赋值为空数组避免报错
    $row = [
        'movie_name' => '', 'path' => '', 'synopsis' => '', 'genre' => '',
        'duration' => '', 'language' => '', 'subtitle' => '', 'current_showing_status' => '', 'age_restriction' => ''
    ];
}

// 处理更新请求
if (isset($_POST['update'])) {
    $movie_name = $_POST['movie_name'];
    $movie_synopsis = $_POST['movie_synopsis'];
    $movie_genre = $_POST['movie_genre'];
    $movie_duration = intval($_POST['movie_duration']);
    $movie_language = $_POST['movie_language'];
    $movie_subtitle = $_POST['movie_subtitle'];
    $movie_current_showing_status = $_POST['movie_showing_status'];
    $movie_age_restriction = $_POST['age_restriction_change'];
    
    $movie_path = $_POST['original_movie_path']; // 默认保持原路径

    // 检查是否有上传新文件
    if (isset($_FILES['movie_path']) && $_FILES['movie_path']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['movie_path']['tmp_name'];
        $fileName = basename($_FILES['movie_path']['name']);
        $uploadFileDir = 'asset/movie/';
        
        // 确保目录存在
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        
        $dest_path = $uploadFileDir . $fileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            // 删除旧文件（如果存在且不为空）
            if (!empty($_POST['original_movie_path']) && file_exists($_POST['original_movie_path'])) {
                unlink($_POST['original_movie_path']);
            }
            $movie_path = $dest_path; // 更新为新路径
        }
    }

    // 使用 UPDATE 更新数据库，避免数据丢失
    $update_sql = "UPDATE movie SET movie_name = ?, synopsis = ?, genre = ?, duration = ?, language = ?, subtitle = ?, path = ?, current_showing_status = ?, age_restriction = ? WHERE movie_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssisssssi", $movie_name, $movie_synopsis, $movie_genre, $movie_duration, $movie_language, $movie_subtitle, $movie_path, $movie_current_showing_status, $movie_age_restriction, $id);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('更新成功！'); window.location.href='edit_movie.php?id=$id';</script>";
    } else {
        echo "更新失败: " . $conn->error;
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>CineWave-Movie Edit</title>
</head>

<body>
    <h1>Movie Edit</h1>

    <!-- 注意：上传文件必须加上 enctype="multipart/form-data"，action 指向当前文件 -->
    <form action="edit_movie.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
        Movie Name:<br>
        <input type="text" name="movie_name" value="<?php echo htmlspecialchars($row['movie_name']); ?>" required>
        <br><br>

        Movie Poster:<br>
        <?php if (!empty($row['path'])): ?>
            <img src="<?php echo htmlspecialchars($row['path']); ?>" alt="<?php echo htmlspecialchars($row['movie_name']); ?>" width="250px"><br>
        <?php endif; ?>
        <input type="hidden" name="original_movie_path" value="<?php echo htmlspecialchars($row['path']); ?>">
        <input type="file" name="movie_path">
        <br><br>

        Synopsis:<br>
        <input type="text" name="movie_synopsis" value="<?php echo htmlspecialchars($row['synopsis']); ?>" required>
        <br><br>

        Genre:<br>
        <input type="text" name="movie_genre" value="<?php echo htmlspecialchars($row['genre']); ?>" required>
        <br><br>

        Duration:<br>
        <input type="number" name="movie_duration" value="<?php echo htmlspecialchars($row['duration']); ?>">
        <br><br>

        Language: <br>
        <input type="text" name="movie_language" value="<?php echo htmlspecialchars($row['language']); ?>" required>
        <br><br>

        Subtitle: <br>
        <input type="text" name="movie_subtitle" value="<?php echo htmlspecialchars($row['subtitle']); ?>" required>
        <br><br>

        Current Showing Status: <br>
        <input type="text" name="movie_showing_status" value="<?php echo htmlspecialchars($row['current_showing_status']); ?>" required>
        <br><br>

        Age Restriction: <br>
        <div>
            <input type="radio" name="age_restriction_change" value="U" <?php echo ($row['age_restriction'] == 'U') ? 'checked' : ''; ?>>U
            <input type="radio" name="age_restriction_change" value="PG13" <?php echo ($row['age_restriction'] == 'PG13') ? 'checked' : ''; ?>>PG13
        </div>

        <br><br>
        <button type="submit" name="update" class="btn">Update</button>
    </form>
<?php
include 'footer.html';
?>
</body>

</html>