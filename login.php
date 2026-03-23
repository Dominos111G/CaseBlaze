<?php 
include 'includes/config.php';
include 'includes/connect.php';
$error = "";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['wallet'] = $user['wallet'];
            $_SESSION['fdc'] = $user['daily_crate'];
            $_SESSION['fwc'] = $user['weekly_crate'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error="User doesn't exist.";
    }  
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <main class="div">
        <form class="cent-form" action="" method="POST">
            <h1 class="cent-text">Login</h1><br>
            <label for="username">Username:</label><br>
            <input class="pole" type="text" name="username" id="username" required></input><br>
            <label for="password">Password:</label><br>
            <input class="pole" type="password" name="password" id="password" required></input><br>
            <input class="p-kolor" type="submit" value="Log in">
        </form>
        <?php
        if($error){
            echo"<p class='cent-text' style='color: red'><b>$error</b></p><br>";
        }
        ?>
    </main>
</body>
</html>