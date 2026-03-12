<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Free Crates</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main>
        <div class="showcase">
            <h2>Free Crates</h2>
            <?php
                include 'includes/connect.php';

                $query = 'SELECT * FROM crates WHERE price = 0 ORDER BY name ASC;';
                $result = $conn->query($query);

                if ($result->fetch_assoc() > 0) {
                    foreach ($result as $r) {
                        $status = isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] ? true : false : false;
                        $status_text = $status ? "Open" : "Login to open";
                        echo '<div class="crate">
                                <h3>' . $r['name'] . '</h3>
                                <p>' . $r['description'] . '</p>
                                <form action="view.php" method="get">
                                    <input type="hidden" name="id" value="' . $r['id'] . '">
                                    <input type="submit"'; if (!$status) echo ' disabled'; echo ' value="' . $status_text . '">
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