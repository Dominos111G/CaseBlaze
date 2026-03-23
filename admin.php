<?php include 'includes/config.php'; ?>
<?php include 'includes/connect.php'; ?>

<?php
if ($_SESSION['is_admin'] == 0) {
    header("Location: /index.php");
}
?>

<?php
if ($_SESSION['is_admin'] == 0) {
    header("Location: /index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (isset($_POST['u_etype'])) {
        $uid = (int)$_POST['uid'];

        if ($_POST['u_etype'] == "remove_user") {
            $conn->query("DELETE FROM users WHERE id = $uid");
        } 
        elseif ($_POST['u_etype'] == "change_username") {
            $username = $_POST['username'];
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $uid);
            $stmt->execute();
        } 
        elseif ($_POST['u_etype'] == "change_email") {
            $email = $_POST['email'];
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $uid);
            $stmt->execute();
        } 
        elseif ($_POST['u_etype'] == "change_wallet") {
            $wallet = $_POST['wallet'];
            $stmt = $conn->prepare("UPDATE users SET wallet = ? WHERE id = ?");
            $stmt->bind_param("di", $wallet, $uid);
            $stmt->execute();
        } 
        elseif ($_POST['u_etype'] == "change_admin") {
            $conn->query("UPDATE users SET is_admin = 1 - is_admin WHERE id = $uid");
        }
    }


    if (isset($_POST['c_etype'])) {
        $cid = (int)$_POST['cid'];

        if ($_POST['c_etype'] == "rem_crate") {
            $conn->query("DELETE FROM crates WHERE id = $cid");
        } 
        elseif ($_POST['c_etype'] == "change_name") {
            $name = $_POST['name'];
            $stmt = $conn->prepare("UPDATE crates SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $cid);
            $stmt->execute();
        }
        elseif ($_POST['c_etype'] == "add_item") {
            $item_id = (int)$_POST['item_id'];
            $stmt = $conn->prepare("INSERT INTO crate_item (crate_id, item_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $cid, $item_id);
            $stmt->execute();
        }
    }

    if (isset($_POST['c_atype']) && $_POST['c_atype'] == "add_chest") {
        $name = $_POST['name'];
        $desc = $_POST['desc'];
        $price = $_POST['price'];
        
        $stmt = $conn->prepare("INSERT INTO crates (name, description, price, visible) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("ssd", $name, $desc, $price);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <h1>Admin Panel</h1>

    <h2>Find User</h2>
    <form method="post">
        <input type="hidden" name="u_atype" value="search_user">
        <label for="uid">UID</label>
        <input type="number" name="uid" id="uid">
        <label for="username">Username</label>
        <input type="text" name="username" id="username">
        <label for="email">Email</label>
        <input type="email" name="email" id="email">
        <input type="submit" name="submit" id="submit" value="Szukaj">
    </form>
    <br>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST['u_atype']) && $_POST['u_atype'] == "search_user") {
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
                    $username = $r['username'];
                    $email = $r['email'];
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
                                <input type="hidden" name="u_etype" value="remove_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="submit" value="Remove">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_username">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="text" name="username" value="' . $username . '">
                                <input type="submit" value="Change">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_email">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="email" name="email" value="' . $email . '">
                                <input type="submit" value="Change">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_wallet">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="number" name="wallet" step=0.01 min=0 max=999999999 value=' . $wallet . '>
                                <input type="submit" value="Change">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_admin">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="hidden" name="admin" value="' . $admin . '">
                                <input type="submit" value="Change">
                            </form>
                        </td>

                        <td>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_daily">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="datetime-local" name="dc" value="' . $dc . '">
                                <input type="submit" value="Change">
                            </form>
                        </td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="u_etype" value="change_weekly">
                                <input type="hidden" name="u_atype" value="search_user">
                                <input type="hidden" name="uid" value="' . $uid . '">
                                <input type="datetime-local" name="wc" value="' . $wc . '">
                                <input type="submit" value="Change">
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
    <br>

    <h2>Chests</h2>
    <?php
    echo '<details>
        <summary>All Chests</summary>';
            
            $query = 'SELECT * FROM crates;';
            $result = $conn->query($query);
            
            $i_query = 'SELECT DISTINCT i.id AS iid, i.name AS iname, i.description AS idesc, i.exterior_id AS iext, i.quality_id AS iqua, i.market_price AS imp, i.sell_price AS isp, ci.crate_id AS cid
                    FROM items AS i 
                    INNER JOIN crate_item AS ci ON ci.item_id = i.id;';
            $i_result = $conn->query($i_query);

            if ($result->num_rows > 0) {
                foreach ($result as $r) {
                    $id = $r['id'];
                    $name = $r['name'];
                    $price = $r['price'];
                    $desc = $r['description'];
                    $visible = $r['visible'];

                    echo '<table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Visible</th>
                            <th>Description</th>
                        </tr>
                        <tr>
                            <td>' . $id . '</td>
                            <td>' . $name . '</td>
                            <td>' . $price . '</td>
                            <td>' . $visible . '</td>
                            <td>' . $desc . '</td>
                        </tr>

                        <tr>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="c_etype" value="rem_crate">
                                    <input type="hidden" name="cid" value="' . $id . '">
                                    <input type="submit" value="Remove">
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="c_etype" value="change_name">
                                    <input type="hidden" name="cid" value="' . $id . '">
                                    <input type="text" max="50" name="name" value="' . $name . '">
                                    <input type="submit" value="Change">
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="c_etype" value="change_price">
                                    <input type="hidden" name="cid" value="' . $id . '">
                                    <input type="number" min="0" max="9999999" step="0.01" name="name" value="' . $price . '">
                                    <input type="submit" value="Change">
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="c_etype" value="change_visible">
                                    <input type="hidden" name="cid" value="' . $id . '">
                                    <input type="submit" value="Change">
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="c_etype" value="change_desc">
                                    <input type="hidden" name="cid" value="' . $id . '">
                                    <input type="text" name="name" value="' . $desc . '">
                                    <input type="submit" value="Change">
                                </form>
                            </td>
                        </tr>
                    </table>
                    <details style="margin-left: 10px;">
                        <summary>Items In ' . $name . '</summary>

                        <form method="post">
                            <input type="hidden" name="c_etype" value="add_item">
                            <input type="hidden" name="cid" value="' . $id . '">
                            <select name="item_id">';

                            foreach ($i_result as $i) {
                                if ($i['cid'] == $id) {
                                    continue;
                                }
                                echo '<option value="' . $i['iid'] . '">' . $i['iname'] . '</option>';
                            }

                        echo '</select>
                            <input type="submit" value="Add item">
                        </form>

                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Exterior</th>
                                <th>Quality</th>
                            </tr>';

                            foreach ($i_result as $i) {
                                if ($i['cid'] != $id) {
                                    continue;
                                }
                                echo '<tr><td>' . $i['iid'] . '</td>
                                    <td>' . $i['iname'] . '</td>
                                    <td>' . $i['iext'] . '</td>
                                    <td>' . $i['iqua'] . '</td></tr>';
                            }


                    echo '</table>
                    </details>
                    <br><br>';
                }
            }

    echo '</details>';
    
    ?>
    <br>

    <h2>Add Chest</h2>
    <form method="post">
        <input type="hidden" name="c_atype" value="add_chest">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required>
        <label for="desc">Description</label>
        <input type="text" name="desc" id="desc" required>
        <label for="price">Price</label>
        <input type="number" min="0.5" max="9999999" step="0.01" name="price" id="price" required>

        <input type="submit" name="submit" id="submit" value="Add Chest">
    </form>
    <br>

    <?php
    
    ?>
    <br>

    <h2>Items</h2>
    <details>
        <summary>All items</summary>
        <?php
            $i_query = "SELECT i.id as iid, i.name as iname, ex.name as exname, ex.id as exid, q.name as qname, q.id as qid
                        FROM items as i
                        INNER JOIN exteriors as ex ON ex.id = i.exterior_id
                        INNER JOIN quality as q ON q.id = i.quality_id";
            $i_result = $conn->query($i_query);
            
            $q_query = "SELECT id, name
                        FROM quality;";
            $q_result = $conn->query($q_query);
            
            $q_opt = "";
            foreach ($q_result as $r) {
                $q_opt .= '<option value="' . $r['id'] . '">' . $r['name'] . '</option>';
            }

            $ex_query = "SELECT id, name
                        FROM exteriors;";
            $ex_result = $conn->query($ex_query);
            
            $ex_opt = "";
            foreach ($ex_result as $r) {
                $ex_opt .= '<option value="' . $r['id'] . '">' . $r['name'] . '</option>';
            }
            
            if ($i_result->num_rows > 0){
                echo '<table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Exterior</th>
                        <th>Quality</th>
                    </tr>';

                foreach ($i_result as $i) {
                    $id = $i['iid'];
                    $name = $i['iname'];
                    $qid= $i['qid'];
                    $exid= $i['exid'];
                    echo '<tr><td>' . $i['iid'] . '</td>
                        <td>' . $i['iname'] . '</td>
                        <td>' . $i['exname'] . '</td>
                        <td>' . $i['qname'] . '</td></tr>';
                    echo '<tr>
                        <td>
                            <form method="post">
                                <input type="hidden" name="i_etype" value="rem_item">
                                <input type="hidden" name="iid" value="' . $id . '">
                                <input type="submit" value="Remove">
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="i_etype" value="change_name">
                                <input type="hidden" name="iid" value="' . $id . '">
                                <input type="text" max="50" name="name" value="' . $name . '">
                                <input type="submit" value="Change">
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="i_etype" value="change_exterior">
                                <input type="hidden" name="iid" value="' . $id . '">
                                <select name="exterior">'
                                . $ex_opt .
                                '</select>
                                <input type="submit" value="Change">
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="i_etype" value="change_quality">
                                <input type="hidden" name="iid" value="' . $id . '">
                                <select name="exterior">'
                                . $q_opt .
                                '</select>
                                <input type="submit" value="Change">
                            </form>
                        </td>
                    </tr>';
                }

                echo '</table>';
            } else {
                echo '<p style="color: red;>"No items found</p>';
            }
        ?>
    </details>
    <br>

    <h2>Add Item</h2>
    <form method="post">
        <input type="hidden" name="c_atype" value="add_chest">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required>
        <label for="desc">Description</label>
        <input type="text" name="desc" id="desc" required>
        <label for="price">Price</label>
        <input type="number" min="0.01" max="9999999" step="0.01" name="price" id="price" required>
        <label for="exterior">Exterior</label>
        <select name="exterior" id="exterior">
            <option value="1">Not Painted</option>
            <option value="2">Battle-Scarred</option>
            <option value="3">Well-Worn</option>
            <option value="4">Field-Tested</option>
            <option value="5">Minimal Wear</option>
            <option value="6">Factory New</option>
        </select>
        
        <label for="quality">Quality</label>
        <select name="quality" id="quality">
            <option value="1">Consumer Grade</option>
            <option value="2">Industrial Grade</option>
            <option value="3">Mil-Spec Grade</option>
            <option value="4">Restricted</option>
            <option value="5">Classified</option>
            <option value="6">Covert</option>
            <option value="7">Contraband</option>
        </select>

        <input type="submit" name="submit" id="submit" value="Add Item">
    </form>
    <br>

    <?php
    
    ?>
    <br>    
</body>
</html>