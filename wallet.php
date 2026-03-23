<?php 
include 'includes/connect.php'; 
include 'includes/config.php'; 

if (!isset($_SESSION['user_id'])) {
    die("Nie jesteś zalogowany! <a href='/login.php'><button>Zaloguj się</button></a>");
}

$user_id = $_SESSION['user_id'];
$times = [20 => 10, 50 => 30, 100 => 45, 200 => 60];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $amount = filter_var($_POST['add'], FILTER_VALIDATE_INT);
    
    if (!array_key_exists($amount, $times)) {
        die("Invalid amount");
    }
    
    $col_name = $amount . "vpln";
    $current_time = time();
    $conn->begin_transaction();
    
    try {
        $stmt_check = $conn->prepare("SELECT `$col_name` FROM users WHERE id = ?");
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $row = $stmt_check->get_result()->fetch_assoc();
        $current_db_time = $row[$col_name] ?? 0;
        $stmt_check->close();
        
        if ($current_db_time > $current_time) {
            throw new Exception("Cooldown!");
        }

        $new_time = $current_time + ($times[$amount] * 60);
        
        $stmt_w = $conn->prepare("UPDATE users SET wallet = wallet + ? WHERE id = ?");
        $stmt_w->bind_param("di", $amount, $user_id);
        
        $stmt_t = $conn->prepare("UPDATE users SET `$col_name` = ? WHERE id = ?");
        $stmt_t->bind_param("ii", $new_time, $user_id);
        
        if ($stmt_t->execute() && $stmt_w->execute()) {
            $conn->commit();
            $_SESSION[$col_name] = $new_time; // Synchronizacja sesji dla JS
            
            // Przekierowanie zapobiega ponownemu wysłaniu formularza przy odświeżeniu (F5)
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            throw new Exception("Save error.");
        }
    } catch (Exception $e) {
        $conn->rollback();
    }
}
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
    
    <script>
    function updateTimers() {
        const amounts = [20, 50, 100, 200];
        
        // Pobieramy czasy wygaśnięcia z sesji PHP do obiektu JS
        const endTimes = {
            20: <?= $_SESSION['20vpln'] ?? 0 ?>,
            50: <?= $_SESSION['50vpln'] ?? 0 ?>,
            100: <?= $_SESSION['100vpln'] ?? 0 ?>,
            200: <?= $_SESSION['200vpln'] ?? 0 ?>
        };
        
        amounts.forEach(amount => {
            const endTime = endTimes[amount];
            const button = document.getElementById(`btn_${amount}`);

            if (endTime > 0) {
                const currentTime = Math.floor(Date.now() / 1000);
                const timeLeft = endTime - currentTime;
                
                if (timeLeft > 0) {
                    button.disabled = true;
                    
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    button.textContent = `Wait: ${timeString}`;
                } else {
                    button.disabled = false;
                    button.textContent = 'Collect';
                }
            }
        });
    }

    setInterval(updateTimers, 1000);
    document.addEventListener('DOMContentLoaded', updateTimers);
    </script>
</body>
</html>