<?php include 'includes/config.php';
include 'includes/connect.php';

$error = "";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Hasło jest niepoprawne!";
        }
    } else {
        $error="Nie ma takiego użytkownika!";
    }  
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Login</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <main class="div">
        <h1 class="cent-text">Logowanie</h1><br>
        <form class="cent-form" action="" method="POST">
            <label for="username">Nazwa Użytkownika:</label><br>
            <input class="pole" require type="text" name="username" id="username" required></input><br>
            <label for="password">Hasło:</label><br>
            <input class="pole" type="password" name="password" id="password" required></input><br>
            <input class="p-kolor" type="submit" value="Zaloguj">
        </form>
        <?php
        if($error){
            echo"<p class='cent-text' style='color: red'><b>$error</b></p><br>";
        }
        ?>
    </main>
</body>
</html>