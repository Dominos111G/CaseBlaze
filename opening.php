<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Open crate</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <main id="crate-opening">
        <img src="/img/items/b.png" alt="Item image">
        <img src="/img/items/br.png" alt="Item image">
        <img src="/img/items/g.png" alt="Item image">
        <img src="/img/items/n.png" alt="Item image">
        <img src="/img/items/o.png" alt="Item image">
        <img src="/img/items/p.png" alt="Item image">
        <img src="/img/items/pi.png" alt="Item image">
        <img src="/img/items/r.png" alt="Item image" id="final-item">
        <img src="/img/items/y.png" alt="Item image">
    </main>
</body>
<script>
    const items = document.querySelectorAll('#crate-opening img');
    const finalItem = document.getElementById('final-item');

</script>
   
</html>