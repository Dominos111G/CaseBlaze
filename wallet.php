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
    <link rel="stylesheet" href="css/wallet.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <main style="margin-top:20px;">
        <div class="wallet-header"><h2>Doładowania</h2></div>
        <div class="wallet-grid">
            <div class="money">
                <img class="money-image" src="/img/wallet/20.png" alt="Money Image">
                <h3>20 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="20">Doładuj</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/50.png" alt="Money Image">
                <h3>50 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="50">Doładuj</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/100.png" alt="Money Image">
                <h3>100 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="100">Doładuj</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/200.png" alt="Money Image">
                <h3>200 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="200">Doładuj</button>
                </form>
            </div>
        </div>
    </main>
    <div>
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
                    echo "<script>alert('Doładowano portfel!');</script>";
                } else {
                    echo "<script>alert('Błąd!');</script>";
                }
            }
        ?>
    </div>
</body>
</html>