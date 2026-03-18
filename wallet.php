<?php include 'includes/connect.php'; ?>
<?php include 'includes/config.php'; ?>
<?php 
if (!isset($_SESSION['user_id'])) {
    die("Nie jesteś zalogowany!");
    echo"<button><a href='\login.php'>Zaloguj się<button>";
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Wallet</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <div>
        <form method="POST">
            <button type="submit" name="add" value="20">Dodaj 20 vPLN</button>
        </form>
        <form method="POST">
            <button type="submit" name="add" value="50">Dodaj 50 vPLN</button>
        </form>
        <form method="POST">
            <button type="submit" name="add" value="100">Dodaj 100 vPLN</button>
        </form>
        <form method="POST">
            <button type="submit" name="add" value="200">Dodaj 200 vPLN</button>
        </form>
        <?php
            if (isset($_POST['add'])) {
                $amount = $_POST['add'];  
                $cookie_name = "cooldown" . $amount;
                if (isset($_COOKIE[$cookie_name])) {
                    return;
                }

                $stmt = $conn->prepare("UPDATE users SET wallet = wallet + ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $user_id);

                if ($stmt->execute()) {
                    $cookie_value = true;
                    setcookie($cookie_name, $cookie_value, time() + (60 * 5), "/"); 
                    echo "Doładowano portfel!";
                } else {
                    echo "Błąd!";
                }
            }
        ?>
    </div>
</body>
</html>