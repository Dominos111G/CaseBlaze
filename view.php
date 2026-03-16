<?php include 'includes/config.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    header('Location: /');
    exit;
} elseif (!isset($_GET['id'])) {
    header('Location: /');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - View Crate</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main>
        <div class="showcase">
            <h2>Crate View</h2>
            <?php
                include 'includes/connect.php';

                $id = $_GET['id'];
                $query = "SELECT * FROM crates WHERE id='$id';";
                $result = $conn->query($query);

                if ($result->fetch_assoc() > 0) {
                    foreach ($result as $r) {
                        if ($r['visible'] == 0) {
                            header('Location: /');
                            exit;
                        }

                        $status = isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] ? true : false : false;
                        $statusText = $status ? '$'.$r['price'] : "Login to open";
                        $isAvaliable = false;
                        $price = $r['price'];
                        $wallet = null;
                        $u_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                        if ($u_id) {
                            $u_query = "SELECT * FROM users WHERE id='$u_id';";
                            $u_result = $conn->query($u_query);

                            $u_assoc = $u_result->fetch_assoc();
                            if ($u_assoc) {
                                $wallet = $u_assoc['wallet'];
                                echo "<p class='wallet'>Your wallet: <b>" . $wallet . "</b> vPLN</p>";
                                if ($wallet >= $r['price']) {
                                    $isAvaliable = true;
                                    $statusText = "Open for ".$r['price'] . " vPLN";
                                }
                            }
                        }
                        

                        echo '<div class="crate-view">
                                <h3>' . $r['name'] . '</h3>
                                <p>' . $r['description'] . '</p>
                                <p>Price: ' . ($r['price'] == 0 ? "Free" : $r['price'] . " vPLN") . '</p>                                
                            </div>';
                        
                        include 'opening.php';
                        
                        $i_query = "SELECT i.id, i.name, i.description, i.market_price FROM crate_item INNER JOIN items AS i ON crate_item.item_id = i.id WHERE crate_id='$id' ORDER BY i.market_price;";
                        $i_result = $conn->query($i_query);

                        if ($i_result->fetch_assoc() > 0) {
                            foreach ($i_result as $ir) {
                                echo '<div class="item">
                                        <h4>' . $ir['name'] . '</h4>
                                        <p>' . $ir['description'] . '</p>
                                        <p>Market Price: ' . $ir['market_price'] . ' vPLN</p>
                                    </div>';
                            }
                        } else {
                            echo "<p style='color: red;'>No items found in this crate.</p>";
                        }

                        break;
                    }
                } else {
                    echo "<p style='color: red;'>Crate not found.</p>";
                }
            ?>
        </div>
    </main>
</body>
</html>