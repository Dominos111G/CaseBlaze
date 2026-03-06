<nav>
    <ul>
        <li><a href="/">Crates</a></li>
        <li><a href="/free.php">Free crates</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/wallet.php">Wallet</a></li>
            <li><a href="/profile.php">Profile</a></li>
        <?php else: ?>
            <li><a href="/login.php">Login</a></li>
            <li><a href="/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>