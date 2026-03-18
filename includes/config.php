<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // $_SESSION['logged_in'] = true;
    // $_SESSION['username'] = $user['username'];
    // $_SESSION['email'] = $user['email'];
    // $_SESSION['is_admin'] = $user['is_admin'];
    // $_SESSION['user_id'] = $user['id'];
    // $_SESSION['wallet'] = $user['wallet'];
    // $_SESSION['fdc'] = $user['daily_crate'];
    // $_SESSION['fwc'] = $user['weekly_crate'];
?>