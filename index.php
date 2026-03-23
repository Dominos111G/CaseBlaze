<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main style="margin-top:20px;">
        <?php
            include 'includes/connect.php';
            $query = 'SELECT * FROM crates ORDER BY price ASC;';
            $result = $conn->query($query);
            $freeCrates = [];
            $premiumList = ['Water Case', 'Knife Case'];
            $premiumCrates = [];
            $mainCrates = [];
            if ($result->num_rows > 0) {
                foreach ($result as $r) {
                    if ($r['visible'] == 0) {
                        continue;
                    }
                    if ($r['price'] == 0) {
                        $freeCrates[] = $r;
                    } elseif (in_array($r['name'], $premiumList)) {
                        $premiumCrates[] = $r;
                    } else {
                        $mainCrates[] = $r;
                    }
                }
            }
            echo '<div class="crate-header"><h2>Free Crates</h2></div>
            <div class="chest-grid">';
            foreach ($freeCrates as $r) {
                $fprice = $r['price'];
                if ($fprice == 0) {
                    $fprice = "Free";
                } else {
                    $fprice = $fprice . " vPLN";
                }
                echo '<div class="chest">
                    <img class="chest-image" src="/img/chests/' . $r['img'] . '" alt="Chest Image">
                    <h3>' . $r['name'] . '</h3>
                    <p>' . $r['description'] . '</p>
                    <form action="view.php" method="get">
                        <input type="hidden" name="id" value="' . $r['id'] . '">
                        <input type="submit" value="' . $fprice . '">
                    </form>
                </div>';
            }
            echo '</div>';
            
            echo '<div class="crate-header"><h2>Main Crates</h2></div>
            <div class="chest-grid">';
            foreach ($mainCrates as $r) {
                $fprice = $r['price'] . " vPLN";
                echo '<div class="chest">
                    <img class="chest-image" src="/img/chests/' . $r['img'] . '" alt="Chest Image">
                    <h3>' . $r['name'] . '</h3>
                    <p>' . $r['description'] . '</p>
                    <form action="view.php" method="get">
                        <input type="hidden" name="id" value="' . $r['id'] . '">
                        <input type="submit" value="' . $fprice . '">
                    </form>
                </div>';
            }
            echo '</div>';
            
            echo '<div class="crate-header"><h2>Special Crates</h2></div>
            <div class="chest-grid">';
            foreach ($premiumCrates as $r) {
                $fprice = $r['price'] . " vPLN";
                echo '<div class="chest">
                    <img class="chest-image" src="/img/chests/' . $r['img'] . '" alt="Chest Image">
                    <h3>' . $r['name'] . '</h3>
                    <p>' . $r['description'] . '</p>
                    <form action="view.php" method="get">
                        <input type="hidden" name="id" value="' . $r['id'] . '">
                        <input type="submit" value="' . $fprice . '">
                    </form>
                </div>';
            }
            echo '</div>';
        ?>
    </main>
</body>
</html>