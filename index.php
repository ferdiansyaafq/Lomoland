<?php
require_once __DIR__ . '/config/config.php';

// Fokus penuh ke Admin Lomoland
if (isset($_SESSION['user'])) {
    header("Location: admin/data_produk.php");
    exit;
}

header("Location: admin/login.php");
exit;
