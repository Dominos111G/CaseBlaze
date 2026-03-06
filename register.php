<?php
include 'includes/config.php';
include 'includes/connect.php';
$error  = "";
$ret  = "";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
    header("Location: index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $confirmpassword = mysqli_real_escape_string($conn, $_POST["confirmpassword"]);

    if($password !== $confirmpassword){
        $error="Hasła się różnią";
    } else{
        $sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0){
            if (mysqli_fetch_assoc($result)['email'] == $email){
                $error = "Adres e-mail jest juz zajety";
            } else {
                $error = "Nazwa użytkownika jest juz zajeta";
            }
        } else{
            $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO `users`(`username`, `password`, `email`) 
                    VALUES ('$username','$hashedpassword','$email')";
        
            if(mysqli_query($conn, $sql)){
                $ret = "Dodano użytkownika";
            } else{
                echo"<p>Nie udało się dodać użytkownika</p>";
            }
    }  
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Register</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main class="div">
        <h1 class="cent-text">Rejestracja</h1><br>
        <?php
        if($error){
            echo"<p style='color: red'>$error</p><br>";
        } else if ($ret) {
            echo"<p style='color: green'>$ret</p><br>";
        }
        ?>
    
        <form class="cent-form" action="" method="POST">
            <label for="email">Email:</label><br>
            <input require type="email" name="email" id="email" required></input><br>
            <label for="username">Nazwa Użytkownika:</label><br>
            <input require type="text" name="username" id="username" required></input><br>
            <label for="password">Hasło:</label><br>
            <input require type="password" name="password" id="password" required></input><br>
            <label for="confirmpasswors">Powtórz Hasło:</label><br>
            <input require type="password" name="confirmpassword" id="confirmpassword" required></input><br>
            <input type="submit" value="Zarejestruj">
        </form>
    </main>
</body>
</html>