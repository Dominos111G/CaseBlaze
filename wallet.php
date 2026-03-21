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
    <style>
        .money button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #666;
        }
        .timer {
            font-size: 12px;
            margin-top: 5px;
            color: #ff9800;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <main style="margin-top:20px;">
        <div class="wallet-header"><h2>Wallet</h2></div>
        <div class="wallet-grid">
            <div class="money">
                <img class="money-image" src="/img/wallet/20.png" alt="Money Image">
                <h3>20 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="20" id="btn_20" class="recharge-btn">Collect</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/50.png" alt="Money Image">
                <h3>50 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="50" id="btn_50" class="recharge-btn">Collect</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/100.png" alt="Money Image">
                <h3>100 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="100" id="btn_100" class="recharge-btn">Collect</button>
                </form>
            </div>
            <div class="money">
                <img class="money-image" src="/img/wallet/200.png" alt="Money Image">
                <h3>200 vPLN</h3>
                <form method="POST">
                    <button type="submit" name="add" value="200" id="btn_200" class="recharge-btn">Collect</button>
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
                    echo "<script>alert('You need to wait before doing this again!');</script>";
                } else {
                    $stmt = $conn->prepare("UPDATE users SET wallet = wallet + ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $user_id);
                    
                    if ($stmt->execute()) {
                        $cookie_value = time() + (60 * 5); // Zapisz timestamp zakończenia cooldownu
                        setcookie($cookie_name, $cookie_value, time() + (60 * 5), "/"); 
                        echo "<script>alert('Money added to your wallet!');</script>";
                        echo "<script>window.location.href = window.location.href;</script>"; // Odśwież stronę
                    } else {
                        echo "<script>alert('Error!');</script>";
                    }
                }
            }
        ?>
    </div>
    
    <script>
    // Funkcja do odliczania czasu
    function updateTimers() {
        const amounts = [20, 50, 100, 200];
        
        amounts.forEach(amount => {
            const cookieName = `cooldown${amount}`;
            const endTime = getCookie(cookieName);
            const button = document.getElementById(`btn_${amount}`);
            const timerSpan = document.getElementById(`btn_${amount}`);
            
            if (endTime) {
                const currentTime = Math.floor(Date.now() / 1000);
                const timeLeft = endTime - currentTime;
                
                if (timeLeft > 0) {
                    // Wyłącz przycisk
                    if (button) {
                        button.disabled = true;
                    }
                    
                    // Formatuj pozostały czas
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (timerSpan) {
                        timerSpan.textContent = `Wait: ${timeString}`;
                    }
                } else {
                    // Odblokuj przycisk i wyczyść timer
                    if (button) {
                        button.disabled = false;
                    }
                    if (timerSpan) {
                        timerSpan.textContent = '';
                    }
                    // Usuń przeterminowane ciasteczko
                    document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
                }
            } else {
                // Brak cooldownu - odblokuj przycisk
                if (button) {
                    button.disabled = false;
                }
                if (timerSpan) {
                    timerSpan.textContent = 'Collect';
                }
            }
        });
    }
    
    // Funkcja do pobierania ciasteczek
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parseInt(parts.pop().split(';').shift());
        }
        return null;
    }
    
    // Aktualizuj timery co sekundę
    setInterval(updateTimers, 1000);
    
    // Uruchom timery przy ładowaniu strony
    document.addEventListener('DOMContentLoaded', updateTimers);
    </script>
</body>
</html>