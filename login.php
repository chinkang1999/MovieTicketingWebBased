<?php
include "db.php";
include "header.php";
$error = "";
$fullname = '';
$phone_number = '';
$email = '';
$date_of_birth = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT membership_id, password,points,customer_group FROM customer WHERE email = '$email' AND membership_status = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($result->num_rows == 1) {
        $stored_password = $row['password'];
        $password_is_valid = password_verify($password, $stored_password);
        if (!$password_is_valid && hash_equals($stored_password, $password)) {
            $password_is_valid = true;
        }

        if ($password_is_valid) {
            $_SESSION['id'] = $row['membership_id'];
            $_SESSION['number'] = 1;
            $SESSION['points']=$row['points'];
            $SESSION['role']=$row['customer_group'];
            header("Location: index.php");
            exit();
        }
    } else {
        $sql2 = "SELECT staff_id, staff_password FROM staff WHERE staff_email = '$email' AND account_status = 1";
        $result2 = $conn->query($sql2);
        $row2 = $result2->fetch_assoc();

        if ($result2->num_rows == 1) {
            $stored_password = $row2['staff_password'];
            $password_is_valid = password_verify($password, $stored_password);
            if (!$password_is_valid && hash_equals($stored_password, $password)) {
                $password_is_valid = true;
            }

            if ($password_is_valid) {
                $_SESSION['id'] = $row2['staff_id'];
                $_SESSION['number'] = 2;
                header("Location: cinewave_info_page.php");
                exit();
            }
        }
        $error = "Invalid username or password.";
    }
}

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        $birth_date_obj = new DateTime($date_of_birth);
        $current_date_obj = new DateTime();
        $diff = $current_date_obj->diff($birth_date_obj);
        $age_years = $diff->y;

        $customer_group = ($age_years > 17) ? "Adult" : "Child";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO customer(fullname, phone_number, email, date_of_birth, customer_group, password, points, membership_status, path) 
                VALUES('$fullname', '$phone_number', '$email', '$date_of_birth', '$customer_group', '$hashed_password', '0', '1', 'asset/customer/user.png');";

        if ($conn->query($sql) === TRUE) {
            $new_user_id = $conn->insert_id;
            if ($new_user_id > 0) {
                $_SESSION['id'] = $new_user_id;
                $_SESSION['number'] = 1;
                $_SESSION['points']=0;
                $_SESSION['role']=$customer_group;
                header("Location: index.php");
                exit();
            }
        }
    }
}

// Determine active tab state
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';
if (isset($_POST['register'])) {
    $active_tab = 'register';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login / Register - Cinewave</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="auth-layout">
        <section class="auth-copy">
            <span class="section-kicker">@CINEWAVE CINEMA</span>
            <h1 style="text-align: left;">THE<br><span>NEXT WAVE</span><br>STARTS HERE.</h1>
            <p>Register or sign in to book movies, select seats, and order food.</p>
        </section>

        <section class="auth-form-side">
            <div class="auth-box">
                <div class="auth-tabs">
                    <a href="login.php?tab=login" class="<?php echo ($active_tab === 'login') ? 'active' : ''; ?>">Login</a>
                    <a href="login.php?tab=register" class="<?php echo ($active_tab === 'register') ? 'active' : ''; ?>">Register</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($active_tab === 'register'): ?>
                    <h2>Create Account</h2>
                    <form action="login.php" method="POST">
                        <div class="field">
                            <label for="fullname">Full Name</label>
                            <input id="fullname" type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        </div>

                        <div class="field">
                            <label for="phone_number">Phone Number</label>
                            <input id="phone_number" type="tel" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" required>
                        </div>

                        <div class="field">
                            <label for="date_of_birth">Date of Birth</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>" required>
                        </div>

                        <div class="field">
                            <label for="email">Email Address</label>
                            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" required>
                        </div>

                        <div class="field">
                            <label for="password_confirm">Confirm Password</label>
                            <input id="password_confirm" type="password" name="password_confirm" required>
                        </div>

                        <br>

                        <input type="submit" name="register" value="Register" class="submit-button">
                    </form>

                <?php else: ?>
                    <h2>Sign In</h2>
                    <form action="login.php" method="POST">
                        <div class="field">
                            <label for="login_email">Email Address</label>
                            <input id="login_email" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="field">
                            <label for="login_password">Password</label>
                            <input id="login_password" type="password" name="password" required>
                        </div>

                        <br>

                        <input type="submit" name="login" value="Login" class="submit-button">
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>

</html>