<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<?php
$uid = -1;
if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Leaderboard</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/leaderboard.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="header"><h1 >Leaderboards</h1></div>

    <main>
        <div class="leaderboard">
            <h2 class="header">Most vPLN</h2>
            <table>
                <tr>
                    <th style='min-width: 20px'>Place</th>
                    <th style='min-width: 150px'>Username</th>
                    <th style='min-width: 50px'>Value</th>
                </tr>
                <?php
                $goal = 10;
                $w_query = "SELECT id, username, wallet FROM users ORDER BY wallet DESC LIMIT " . $goal . ";";
                $w_result = $conn->query($w_query);
                if ($w_result->num_rows > 0) {
                    $place = 1;
                    foreach ($w_result as $r) {
                        $styl = "";
                        if ($r['id'] == $uid) { 
                            $styl = 'style="background-color: #82b8f5;"';
                        }
                        echo '<tr ' . $styl . '>
                                <td style="text-align:center;">' . $place . '</td>
                                <td><a href="/profile.php?uid=' . $r['id'] . '">' . $r['username'] . '</td>
                                <td style="text-align: right;">' . $r['wallet'] . ' vPLN</td>
                            </tr>';
                        $place++;
                    }
                    for ($i=$place; $i <= $goal; $i++) {
                        echo '<tr>
                                <td style="text-align:center;">' . $i . '</td>
                                <td></td>
                                <td style="text-align: right;"></td>
                            </tr>';
                        
                    }
                } else {
                    echo "<tr><td colspan=3 style='text-align: center;'>No records found</td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="leaderboard">
            <h2 class="header">Most vPLN In Items</h2>
            <table>
                <tr>
                    <th style='min-width: 20px'>Place</th>
                    <th style='min-width: 150px'>Username</th>
                    <th style='min-width: 50px'>Value</th>
                    <th style='min-width: 30px'>Amount</th>
                </tr>
                <?php
                $goal = 10;
                $i_query = "SELECT 
                                u.id, 
                                u.username,
                                COALESCE(SUM(i.market_price), 0) as total_value,
                                COUNT(inv.id) as item_count
                            FROM users u
                            LEFT JOIN inventory inv ON u.id = inv.user_id
                            LEFT JOIN items i ON inv.item_id = i.id
                            GROUP BY u.id, u.username
                            HAVING total_value > 0
                            ORDER BY total_value DESC, item_count ASC, u.username ASC
                            LIMIT " . $goal . ";";
                
                $i_result = $conn->query($i_query);
                
                if ($i_result->num_rows > 0) {
                    $place = 1;
                    while ($r = $i_result->fetch_assoc()) {
                        $formatted_value = number_format($r['total_value'], 2, '.', ',');
                        
                        $styl = "";
                        if ($r['id'] == $uid) { 
                            $styl = 'style="background-color: #82b8f5;"';
                        }
                        echo '<tr ' . $styl . '>
                                <td style="text-align:center;">' . $place . '</td>
                                <td><a href="/profile.php?uid=' . (int)$r['id'] . '">' .
                                    htmlspecialchars($r['username']) . '</a></td>
                                <td style="text-align: right;">' . $formatted_value . ' vPLN</td>
                                <td style="text-align: center;">' . $r['item_count'] . '</td>
                            </tr>';
                        $place++;
                    }
                    for ($i=$place; $i <= $goal; $i++) {
                        echo '<tr>
                                <td style="text-align:center;">' . $i . '</td>
                                <td></td>
                                <td style="text-align: right;"></td>
                                <td style="text-align: center;"></td>
                            </tr>';
                        
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>No records found</td></tr>";
                }
                ?>
            </table>
        </div>
    </main>
</body>
</html>