<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['i_id'])) {
        $query = "UPDATE inventory SET locked = NOT locked WHERE id = " . $_POST['i_id'] . ";";
        $conn->query($query);
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Profile</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/inv.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <h3>Search User</h3>
    <form method="get">
        <input type="text" name="search" placeholder="UID / Username" <?php if (isset($_GET['search'])) echo " value=" . $_GET['search'] . " "; ?>>
        <input type="submit" value="Find User">
    </form>

    <?php
    $owner = false;
    $id = 0;
    if (isset($_GET['search'])) {
        $s_opt = $_GET['search'];
        $s_query = 'SELECT id FROM users WHERE id="' . $s_opt . '" OR username="' . $s_opt . '";';
        $s_result = $conn->query($s_query);
        $s_f = $s_result->fetch_assoc();
        if ($s_f > 0) {
            $id = $s_f['id'];
            header("Location: /profile.php?uid=$id");
            exit;
        } else {
            echo '<p style="color: red;">Couldn\'t find user.';
            return;
        }
    }
    if (isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
        $owner = true;
    }

    if (isset($_GET['uid'])) {
        $owner = false;
        if (isset($_SESSION['user_id']) && $_GET['uid'] == $id) {
            $owner = true;
        }
        $id = $_GET['uid'];
    }

    $zapytanie = "SELECT id, username, wallet FROM users WHERE id = " . $id . ";";
    $wynik = $conn->query($zapytanie);
    $w = $wynik->fetch_assoc();

    if ($w > 0) {
        echo "<h2>" . $w['username'] . "</h2>";
        echo "<p>Wallet: <b>" . $w['wallet'] . "</b> vPLN.</p>";
        echo '<div>
                <div class="header"><h2>Inventory</h2></div>';
                include "includes/inventory.php";
        echo '</div>';
        return;
    } else {
        echo "<p style='color:red;'><b>Error 404</b></p>";
        die;
    }
    ?>  
</body>
</html>