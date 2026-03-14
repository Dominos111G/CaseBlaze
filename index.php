<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main>
        <div class="showcase">
            <h2>Main Crates</h2>
            <?php
                include 'includes/connect.php';
                
                $sgenerated = false;

                $query = 'SELECT * FROM crates ORDER BY price ASC;';
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $snames = ["Water Case", "Knife Case"];
                    foreach ($result as $r) {
                        $fprice = $r['price'];
                        if ($fprice == 0) {
                            $fprice = "Free";
                            continue;
                        } else {
                            $fprice = $fprice . " vPLN";
                        }
                        if (in_array($r['name'], $snames) && $sgenerated == false) {
                            echo "</div><div class='showcase'>
                                    <h2>Special Crates</h2>";
                            $sgenerated = true;
                        }
                        echo '<div class="crate">
                                <h3>' . $r['name'] . '</h3>
                                <p>' . $r['description'] . '</p>
                                <form action="view.php" method="get">
                                    <input type="hidden" name="id" value="' . $r['id'] . '">
                                    <input type="submit" value="' . $fprice . '">
                                </form>
                            </div>';
                    }
                } else {
                    echo "<p style='color: red;'>none</p>";
                }
            ?>
        </div>
    </main>
</body>
</html>