<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Lomoland</title>
</head>
<body>
    <div class="navbar">
        <h2>Lomoland Admin Panel</h2>
        <a href="index.php?logout=true" onclick="return confirm('Yakin ingin logout?')">Logout</a>
    </div>

    <div class="container">
        <h3>Selamat Datang, <?= htmlspecialchars($_SESSION['user']) ?>!</h3>
        <p>Silakan pilih menu di bawah ini untuk mengelola sistem.</p>
        
        <div class="card-container">
            <div class="card">
                <h3>Manajemen Produk</h3>
                <p>Edit data produk kamera Lomo</p>
                <a href="data_produk.php">Kelola Produk</a>
            </div>
            
            <div class="card" style="opacity: 0.6;">
                <h3>Data Transaksi</h3>
                <p>Lihat riwayat transaksi customer.</p>
                <a href="#" style="background:#999;">Lihat Transaksi</a>
            </div>
        </div>
    </div>
</body>
</html>