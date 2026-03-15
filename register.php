<?php
include 'includes/config.php';
include 'includes/connect.php';
$error  = "";
$ret  = "";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    header("Location: index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $confirmpassword = mysqli_real_escape_string($conn, $_POST["confirmpassword"]);

    if($password !== $confirmpassword){
        $error="Passwords aren't identical.";
    } else{
        $query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $result = $conn->query($query);
        if($result->num_rows > 0){
            if ($result->fetch_assoc['email'] == $email) {
                $error = "Email is already used.";
            } else {
                $error = "Username is already used.";
            }
        } else{
            $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
            $add_query = "INSERT INTO `users`(`username`, `password`, `email`, `wallet`, `daily_crate`, `weekly_crate`)
                    VALUES ('$username','$hashedpassword','$email', 10, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY)";
        
            if($conn->query($add_query)) {
                $ret = "User added successfully.";
            } else{
                echo"<p>Couldn't add user.</p>";
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
        <h1 class="cent-text">Register</h1><br>
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
            <label for="username">Username:</label><br>
            <input require type="text" name="username" id="username" required></input><br>
            <label for="password">Password:</label><br>
            <input require type="password" name="password" id="password" required></input><br>
            <label for="confirmpasswors">Repeat password:</label><br>
            <input require type="password" name="confirmpassword" id="confirmpassword" required></input><br>
            <input type="submit" value="Create Account">
        </form>
    </main>
</body>
</html>