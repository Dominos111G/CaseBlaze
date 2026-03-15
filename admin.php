<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<?php
if ($_SESSION['is_admin'] == 0) {
    header("Location: /index.php");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Admin</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <h1>Panel Admina</h1>

    <h2>Znajdź użytkownika</h2>
    <form method="post">
        <input type="hidden" name="atype" value="search_user">
        <label for="uid">UID</label>
        <input type="number" name="uid" id="uid">
        <label for="username">Nazwa użytkownika</label>
        <input type="text" name="username" id="username">
        <label for="email">Email</label>
        <input type="email" name="email" id="email">
        <input type="submit" name="submit" id="submit" value="Szukaj">
    </form>
    <br>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST['atype']) && $_POST['atype'] == "search_user") {
            $uid = isset($_POST['uid']) ? strlen($_POST['uid']) > 0 ? (int) $_POST['uid'] : false : false;
            $username = isset($_POST['username']) ? strlen($_POST['username']) > 0 ? $_POST['username'] : false : false;
            $email = isset($_POST['email']) ? strlen($_POST['email']) > 0 ? $_POST['email'] : false : false;
            $wallet = 0;
            $admin = 0;
            $options = null;

            if ($uid != false) {
                $options = "id=" . $uid;
            } elseif ($email != false) {
                $options = "email='" . $email . "'";                
            } elseif ($username != false) {
                $options = "username='" . $username . "'";                
            }

            if ($options == null) {
                echo "<p style='color: red;'>Podaj dane szukania!</p>";
                return;
            }

            $query = 'SELECT id, username, email, is_admin, registration_date, wallet, daily_crate, weekly_crate
                    FROM users
                    WHERE ' . $options . 
                    ' ORDER BY id ASC, username ASC
                    LIMIT 1;';

            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                echo "<table><tr>
                    <th>UID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Wallet</th>
                    <th>Is Admin</th>
                    <th>Reg Date</th>
                    <th>Daily Crate</th>
                    <th>Weekly Crate</th></tr>";
                foreach ($result as $r) {
                    $uid = $r['id'];
                    $wallet = $r['wallet'];
                    $admin = $r['is_admin'];
                    $rd = $r['registration_date'];
                    $dc = $r['daily_crate'];
                    $wc = $r['weekly_crate'];
                    echo $rd;
                    echo "<tr>
                    <td>{$r['id']}</td>
                    <td>{$r['username']}</td>
                    <td>{$r['email']}</td>
                    <td>{$r['wallet']}</td>
                    <td>{$r['is_admin']}</td>
                    <td>{$r['registration_date']}</td>
                    <td>{$r['daily_crate']}</td>
                    <td>{$r['weekly_crate']}</td>
                    </tr>";
                }
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $uid) {
                    echo '
                    <tr>
                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="remove_user">
                                <input type="hidden" name="uid" value="{$uid}">
                                <input type="submit" value="Usuń">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="change_username">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="{$uid}">
                                <input type="text" name="username">
                                <input type="submit" value="Zmień">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="change_email">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="email" name="email">
                                <input type="submit" value="Zmień">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="change_wallet">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="number" name="wallet" step=0.01 min=0 max=999999999 value=' . $wallet . '>
                                <input type="submit" value="Ustaw">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="change_admin">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="hidden" name="admin" value="' . $admin . '">
                                <input type="submit" value="Zmień">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="rem_user">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="datetime-local" name="dc" value="' . $dc . '">
                                <input type="submit" value="Ustwa">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="etype" value="rem_user">
                                <input type="hidden" name="atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="datetime-local" name="wc" value="' . $wc . '">
                                <input type="submit" value="Ustaw">
                            </form>
                        </td>
                    </tr>';
                }

                echo "</table>";
            } else {
                echo "<p style='color: red;'>Nie znaleziono użytkownika!</p>";
                return;
            }
        }
    }
    ?>
    
</body>
</html>