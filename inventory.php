<?php

if (!isset($id)) {
    echo "<p style='color: red;'>Can not load user inventory!</p>";
    return;
}

$i_query = "SELECT eq.id as 'i_id', i.name, e.name as 'zuzycie', q.name as 'jakosc', i.sell_price, i.description FROM (((inventory as eq INNER JOIN users as u ON eq.user_id=u.id) INNER JOIN items as i ON eq.item_id=i.id) INNER JOIN quality as q ON i.quality_id=q.id) INNER JOIN exteriors as e ON i.exterior_id=e.id WHERE u.id = '$id';";
$i_result = $conn->query($i_query);
if ($i_result->fetch_assoc() > 0) {
    foreach ($i_result as $w) {
        echo '<div class="item">
                <h4>' . $w['name'] . '</h4>
                <p>' . $w['zuzycie'] . '</p>
                <p>' . $w['jakosc'] . '</p>
                <form action="includes/sell.php" method="post">
                    <input type="hidden" name="i_id" value="' . $w['i_id'] . '">
                    <input type="submit" value="Sell for ' . $w['sell_price'] . '">
                </form>
            </div>';
    }
} else {
    echo "<p>Ekwipunek jest pusty!</p>";
}
?>
