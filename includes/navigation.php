<?php
include "connect.php";
include "config.php";

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $q = 'SELECT wallet FROM users WHERE id = ' . $uid . ';';
    $r = $conn->query($q);
    if ($r->num_rows > 0) {
        $_SESSION['wallet'] = $r->fetch_assoc()['wallet'];
    }
}
?>

<nav>
    <ul>
        <li><a href="/">Crates</a></li>
        <li><a href="/free.php">Free crates</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/wallet.php">Wallet</a></li>
            <li><a href="/profile.php">Profile</a></li>
            <li><?php if (isset($_SESSION['username'])) {echo $_SESSION['username'];} else {echo "User";} ?></li>
            <li id='wallet'><?php if (isset($_SESSION['wallet'])) {echo $_SESSION['wallet'] . " vPLN";} else {echo "0 vPLN";} ?></li>
            <li><a href="/includes/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/login.php">Login</a></li>
            <li><a href="/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>