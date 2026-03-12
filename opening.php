<?php
    // Walidacja czy kod gotowy do działania
    if (!isset($status) || !isset($status_text) 
        || !isset($isAvaliable) || !isset($price) 
        || !isset($u_id) || !isset($wallet)) {
        echo "<p style='color: red;'>Some data are missing!</p>";
        return;
    }
?>
<div id="crate-opening">
    <img src="/img/items/b.png" alt="Item image">
    <img src="/img/items/br.png" alt="Item image">
    <img src="/img/items/g.png" alt="Item image">
    <img src="/img/items/n.png" alt="Item image">
    <img src="/img/items/o.png" alt="Item image">
    <img src="/img/items/p.png" alt="Item image">
    <img src="/img/items/pi.png" alt="Item image">
    <img src="/img/items/r.png" alt="Item image" id="final-item">
    <img src="/img/items/y.png" alt="Item image">
</div>

<script>
    const items = document.querySelectorAll('#crate-opening img');
    const finalItem = document.getElementById('final-item');

</script>