<?php
require_once __DIR__ . '/../config/config.php';

if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

header("Location: data_produk.php");
exit;
