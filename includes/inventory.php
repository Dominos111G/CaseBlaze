<?php

if (!isset($id)) {
    echo "<p style='color: red;'>Can not load user inventory!</p>";
    return;
}

$i_query = "SELECT eq.id as 'i_id', eq.locked, i.name, i.img, e.name as 'zuzycie', q.name as 'jakosc', i.sell_price, i.description FROM (((inventory as eq INNER JOIN users as u ON eq.user_id=u.id) INNER JOIN items as i ON eq.item_id=i.id) INNER JOIN quality as q ON i.quality_id=q.id) INNER JOIN exteriors as e ON i.exterior_id=e.id WHERE u.id = '$id' ORDER BY i.sell_price DESC;";
$i_result = $conn->query($i_query);

if ($id == $_SESSION['user_id']) {
    $all_val = 0;
    if ($i_result->num_rows > 0) {
        foreach ($i_result as $w) {
            if ($w['locked'] == 1) { continue; }
            $all_val += $w['sell_price'];
        }
    }
}
?>

<?php 
if (isset($all_val) && $all_val > 0) {
    echo '<form action="includes/sell.php" method="post">
        <input type="hidden" name="i_id" value="all">
        <input type="hidden" name="back" value="profile.php">
        <input class ="sell-btn" type="submit" value="Sell All for ' . $all_val . ' vPLN">
    </form>';
}

if ($i_result->num_rows > 0) {
    echo '<div class="items-grid">';
    foreach ($i_result as $w) {
        echo '<div class="item">
                <img class="item-image" src="/img/items/' . $w['img'] . '" alt="Item Image">
                <h4>' . $w['name'] . '</h4>
                <p>' . $w['zuzycie'] . '</p>
                <p>' . $w['jakosc'] . '</p>';

                if ($owner) {
                    $locked = $w['locked'];
                    $lstyle = $locked ? " disabled " : "";
                    $emoji = $locked == 1 ? "🔒" : "🔓";
                    echo '<div cless="inp-box"><form action="includes/sell.php" method="post">
                            <input type="hidden" name="i_id" value="' . $w['i_id'] . '">
                            <input type="hidden" name="back" value="profile.php">
                            <input ' . $lstyle . ' class="inp" type="submit" value="Sell for ' . $w['sell_price'] . ' vPLN">
                        </form>';
                    echo '<form method="post">
                            <input type="hidden" name="i_id" value="' . $w['i_id'] . '">
                            <input class="inp-lock" type="submit" value="' . $emoji . '">
                        </form></div>';
                } else {
                    echo '<p>Price ' . $w['sell_price'] . ' vPLN</p>';
                }
            echo '</div>';
    }
    echo '</div>';
} else {
    echo "<p style='text-align: center;'>Ekwipunek jest pusty!</p>";
}
?>
