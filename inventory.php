<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Inventory</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <?php
    echo "<table>";
    $zapytanie = "SELECT i.name, e.name as 'zuzycie', q.name as 'jakosc', i.sell_price, i.description FROM (((inventory as eq INNER JOIN users as u ON eq.user_id=u.id) INNER JOIN items as i ON eq.item_id=i.id) INNER JOIN quality as q ON i.quality_id=q.id) INNER JOIN exteriors as e ON i.exterior_id=e.id;";
    $wynik = $conn->query($zapytanie);
    $w=$wynik->fetch_assoc();
    foreach ($wynik as $w){
        echo "<tr><td>".$w['name']."</td> <td>".$w['zuzycie']."</td> <td>".$w['jakosc']."</td> <td>".$w['sell_price']."</td> <td>".$w['description']."</td> </tr>";
    }
    echo "</table>";
    ?>
</body>
</html>