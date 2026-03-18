<?php
    $required_vars = ['status', 'status_text', 'id', 'isAvaliable', 'price', 'u_id', 'wallet'];
    $missing = [];

    foreach ($required_vars as $var) {
        if (!isset($var)) {
            $missing[] = $var;
        }
    }
    if (!empty($missing)) {
        echo "<p style='color: red;'>Missing data: " . implode(', ', $missing) . "</p>";
        return;
    }

    $query = 'SELECT DISTINCT i.id, i.img FROM items AS i
            INNER JOIN crate_item AS ci ON ci.item_id = i.id
            WHERE ci.crate_id = ' . $id . ';';
    $result = $conn->query($query);

    echo '<div id="crate-opening">';
    foreach ($result as $r) {
        echo '<img class="open-img" src="/img/items/' . $r['img']  . '" alt="Item image">';
    }
    echo '</div>';
?>

 <!-- id="final-item"> -->

<script>
    const items = document.querySelectorAll('#crate-opening img');
    const finalItem = document.getElementById('final-item');

</script>