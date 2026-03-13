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
    $nazwa = $_SESSION['username'];
    echo $nazwa;

    $zapytanie = "SELECT wallet FROM users WHERE username = '".$nazwa."';";
    $wynik = $conn->query($zapytanie);
    $w=$wynik->fetch_assoc();
    echo "Stan portfela: ".$w['wallet']."!";
    ?>
    <button><a href="inventory.php">Twój ekwipunek</a></button>
</body>
</html>