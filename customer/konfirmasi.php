<?php
require_once __DIR__ . '/../config/config.php';

// Hanya tampil jika transaksi baru saja selesai
if (!isset($_SESSION['konfirmasi'])) {
    header("Location: index.php");
    exit;
}

$info = $_SESSION['konfirmasi'];
unset($_SESSION['konfirmasi']); // Hapus setelah ditampilkan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Lomoland</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f0; color: #333; }

        .navbar {
            background: #1a1a1a; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .navbar .brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #e8c97e; }
        .navbar nav a  { color: #ddd; text-decoration: none; margin-left: 20px; font-size: 0.9rem; }
        .navbar nav a:hover { color: #e8c97e; }

        /* STEPS */
        .steps {
            display: flex; justify-content: center;
            padding: 24px 32px 0; max-width: 600px; margin: 0 auto;
        }
        .step {
            flex: 1; text-align: center; font-size: 0.8rem;
            padding: 10px 0; border-bottom: 3px solid #ddd; color: #aaa;
        }
        .step.done { border-bottom-color: #4caf50; color: #4caf50; font-weight: 700; }

        /* KONFIRMASI CARD */
        .confirm-wrap {
            max-width: 600px; margin: 40px auto; padding: 0 32px 60px;
        }
        .confirm-card {
            background: #fff; border-radius: 16px; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center;
        }
        .success-icon { font-size: 4rem; }
        .confirm-card h2 { font-size: 1.6rem; margin: 16px 0 8px; color: #1a1a1a; }
        .confirm-card p  { color: #666; font-size: 0.95rem; line-height: 1.6; }

        .detail-box {
            background: #f9f8f4; border-radius: 12px;
            padding: 20px 24px; margin: 28px 0; text-align: left;
        }
        .detail-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; font-size: 0.9rem;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; }
        .detail-val   { font-weight: 600; }
        .kode-trx     { font-size: 1.3rem; font-weight: 800; color: #1a1a1a; letter-spacing: 1px; }

        .btn-home {
            display: inline-block; padding: 14px 32px;
            background: #1a1a1a; color: #e8c97e;
            border-radius: 10px; text-decoration: none;
            font-weight: 700; font-size: 1rem;
            transition: background .2s; margin-top: 10px;
        }
        .btn-home:hover { background: #333; }
        .btn-secondary {
            display: inline-block; padding: 12px 24px;
            background: #f0f0eb; color: #333;
            border-radius: 10px; text-decoration: none;
            font-weight: 600; font-size: 0.9rem;
            transition: background .2s; margin-top: 10px; margin-left: 10px;
        }
        .btn-secondary:hover { background: #ddd; }

        footer { background: #1a1a1a; color: #888; text-align: center; padding: 20px; font-size: 0.82rem; }
        footer span { color: #e8c97e; }

        @keyframes popIn {
            0%   { transform: scale(0.5); opacity: 0; }
            70%  { transform: scale(1.1); }
            100% { transform: scale(1);   opacity: 1; }
        }
        .success-icon { animation: popIn 0.5s ease forwards; display: block; }
    </style>
</head>
<body>

<div class="navbar">
    <span class="brand">LOMOLAND</span>
    <nav>
        <a href="index.php">← Kembali Belanja</a>
    </nav>
</div>

<!-- STEPS -->
<div class="steps">
    <div class="step done">1. Keranjang</div>
    <div class="step done">2. Data Pengiriman</div>
    <div class="step done">3. Konfirmasi ✓</div>
</div>

<div class="confirm-wrap">
    <div class="confirm-card">
        <span class="success-icon">🎉</span>
        <h2>Pesanan Berhasil Ditempatkan!</h2>
        <p>Terima kasih, <strong><?= htmlspecialchars($info['nama']) ?></strong>!<br>
           Pesananmu telah kami terima dan sedang diproses.</p>

        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Kode Transaksi</span>
                <span class="detail-val kode-trx">#TRX-<?= str_pad($info['kode_transaksi'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kode Customer</span>
                <span class="detail-val">#CST-<?= str_pad($info['kode_customer'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Nama</span>
                <span class="detail-val"><?= htmlspecialchars($info['nama']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-val"><?= htmlspecialchars($info['email']) ?></span>
            </div>
            <?php if (!empty($info['via'])): ?>
            <div class="detail-row">
                <span class="detail-label">Metode Pengiriman</span>
                <span class="detail-val"><?= htmlspecialchars($info['via']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <p style="font-size:0.85rem; color:#aaa;">
            Simpan kode transaksi ini sebagai bukti pembelianmu.<br>
            Konfirmasi lebih lanjut akan dikirim ke email: <strong><?= htmlspecialchars($info['email']) ?></strong>
        </p>

        <div style="margin-top: 28px;">
            <a href="index.php" class="btn-home">🏠 Kembali Berbelanja</a>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 <span>Lomoland</span> — Sistem Penjualan Kamera Lomo Online
</footer>

</body>
</html>
