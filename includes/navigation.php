<nav>
    <ul>
        <li><a href="/">Crates</a></li>
        <li><a href="/free.php">Free crates</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/wallet.php">Wallet</a></li>
            <li><a href="/caseblaze/profile.php">Profile</a></li>
            <li><?php if (isset($_SESSION['username'])) {echo $_SESSION['username'];} else {echo "User";} ?></li>
            <li><?php if (isset($_SESSION['wallet'])) {echo $_SESSION['wallet'] . " vPLN";} else {echo "0 vPLN";} ?></li>
            <li><a href="/includes/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/caseblaze/login.php">Login</a></li>
            <li><a href="/caseblaze/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>