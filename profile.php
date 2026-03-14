<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Profile</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <?php
    $owner = false;
    $id = 0;
    if (isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
    }

    if (isset($_GET['uid'])) {
        $id = $_GET['uid'];
    }

    $zapytanie = "SELECT id, username, wallet FROM users WHERE id = " . $id . ";";
    $wynik = $conn->query($zapytanie);
    $w = $wynik->fetch_assoc();

    if ($w > 0) {
        echo "<h2>" . $w['username'] . "</h2>";
        echo "<p>Stan portfela: <b>" . $w['wallet'] . "</b> vPLN.</p>";
        echo '<div>
                <h2>Ekwipunek</h2>
                <div>';
                    include "inventory.php";
        echo '</div>
            </div>';
    } else {
        echo "<p style='color:red;'><b>Błąd 404</b>: nie znaleziono strony.</p>";
        die;
    }
    ?>
    
    
</body>
</html>