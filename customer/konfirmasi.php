<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['konfirmasi'])) {
    header("Location: index.php");
    exit;
}

$info = $_SESSION['konfirmasi'];
unset($_SESSION['konfirmasi']);
$jumlah_keranjang = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Lomoland</title>
    <style>
        :root {
            --navy: #152259;
            --bg: #f0f4ff;
            --white: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --accent: #2563eb;
            --success: #16a34a;
            --sidebar-w: 220px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }
        .app-layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh;
            background: var(--white); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-logo {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 20px 20px 10px; border-bottom: 1px solid var(--border);
        }
        .logo-icon {
            width: 36px; height: 36px; background: var(--navy);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-text { display: grid; grid-template-columns: max-content max-content; align-items: center; column-gap: 6px; }
        .logo-name { font-family: Georgia, 'Times New Roman', serif; font-size: 0.92rem; font-weight: 800; color: var(--navy); letter-spacing: 0; }
        .logo-badge {
            display: inline-block; background: #e8f0fe; color: var(--accent);
            font-size: 0.6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;
            align-self: center;
        }
        .logo-sub { grid-column: 1 / -1; font-size: 0.62rem; color: var(--muted); margin-top: 3px; white-space: nowrap; }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-label { font-size: 0.65rem; font-weight: 700; color: var(--muted); letter-spacing: 1.5px; padding: 0 8px; margin-bottom: 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px; text-decoration: none;
            color: var(--muted); font-size: 0.88rem; font-weight: 500;
            margin-bottom: 2px; transition: all 0.15s;
        }
        .nav-item:hover { background: #f1f5f9; color: var(--text); }
        .nav-item.active { background: var(--navy); color: #fff; }
        .nav-item.active svg { stroke: #fff; }

        /* Main */
        .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: var(--white); border-bottom: 1px solid var(--border);
            padding: 14px 28px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .breadcrumb { font-size: 0.82rem; color: var(--muted); display: flex; align-items: center; gap: 6px; }
        .breadcrumb a { color: var(--muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--text); }
        .breadcrumb span { color: var(--text); font-weight: 600; }
        .content { flex: 1; padding: 40px 28px; display: flex; align-items: flex-start; justify-content: center; }
        footer { background: var(--white); border-top: 1px solid var(--border); text-align: center; padding: 16px; font-size: 0.78rem; color: var(--muted); }

        /* Konfirmasi */
        .confirm-outer { width: 100%; max-width: 580px; }

        /* Progress steps */
        .steps {
            display: flex; gap: 0; margin-bottom: 32px;
            background: var(--white); border-radius: 12px; padding: 16px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .step {
            flex: 1; text-align: center; font-size: 0.78rem;
            padding: 8px 0; border-bottom: 3px solid #e2e8f0; color: var(--muted);
        }
        .step.done { border-bottom-color: var(--success); color: var(--success); font-weight: 700; }

        /* Card */
        .confirm-card {
            background: var(--white); border-radius: 16px; padding: 44px 40px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08); text-align: center;
        }
        .success-anim {
            width: 80px; height: 80px; background: #f0fdf4; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; animation: popIn 0.5s ease forwards;
        }
        @keyframes popIn {
            0%   { transform: scale(0.5); opacity: 0; }
            70%  { transform: scale(1.1); }
            100% { transform: scale(1);   opacity: 1; }
        }
        .confirm-card h2 { font-size: 1.6rem; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
        .confirm-card .sub { color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 28px; }

        .detail-box {
            background: #f8faff; border: 1px solid var(--border);
            border-radius: 12px; padding: 20px 24px; text-align: left; margin-bottom: 28px;
        }
        .detail-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; font-size: 0.9rem; border-bottom: 1px solid var(--border);
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--muted); }
        .detail-val { font-weight: 700; color: var(--text); }
        .trx-code { font-size: 1.15rem; font-weight: 800; color: var(--navy); letter-spacing: 1px; }

        .note { font-size: 0.82rem; color: var(--muted); margin-bottom: 28px; line-height: 1.6; }
        .note strong { color: var(--navy); }

        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; background: var(--navy); color: #fff;
            border-radius: 10px; text-decoration: none;
            font-weight: 700; font-size: 0.95rem; transition: background .2s;
        }
        .btn-home:hover { background: #1e3a8a; }
    </style>
</head>
<body>
<div class="app-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" fill="#fff" fill-opacity="0.2"/>
                    <circle cx="12" cy="12" r="6" fill="#fff"/>
                    <circle cx="12" cy="12" r="3" fill="#152259"/>
                </svg>
            </div>
            <div class="logo-text">
                <span class="logo-name">LOMOLAND</span>
                <span class="logo-badge">STORE</span>
                <div class="logo-sub">Analog Camera &amp; Film Shop</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-label">NAVIGASI</p>
            <a href="beranda.php" class="nav-item">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 12L12 3l9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                </svg>
                <span>Beranda</span>
            </a>
            <a href="index.php" class="nav-item active">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                <span>Produk</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main-wrap">
        <div class="topbar">
            <div class="breadcrumb">
                <a href="beranda.php">Beranda</a>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                <span>Konfirmasi Pesanan</span>
            </div>
        </div>

        <div class="content">
            <div class="confirm-outer">

                <!-- STEPS -->
                <div class="steps">
                    <div class="step done">✓ Keranjang</div>
                    <div class="step done">✓ Data Pengiriman</div>
                    <div class="step done">✓ Konfirmasi</div>
                </div>

                <!-- CARD -->
                <div class="confirm-card">
                    <div class="success-anim">
                        <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <h2>Pesanan Berhasil! 🎉</h2>
                    <p class="sub">Terima kasih, <strong><?= htmlspecialchars($info['nama']) ?></strong>!<br>
                        Pesananmu telah kami terima dan sedang diproses oleh tim Lomoland.</p>

                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">Kode Transaksi</span>
                            <span class="detail-val trx-code">#TRX-<?= str_pad($info['kode_transaksi'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Kode Customer</span>
                            <span class="detail-val">#CST-<?= str_pad($info['kode_customer'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama Penerima</span>
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

                    <p class="note">
                        Simpan kode transaksi ini sebagai bukti pembelian.<br>
                        Konfirmasi akan dikirim ke: <strong><?= htmlspecialchars($info['email']) ?></strong>
                    </p>

                    <a href="index.php" class="btn-home">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M3 12L12 3l9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                        </svg>
                        Kembali Berbelanja
                    </a>
                </div>

            </div>
        </div>

        <footer>© 2026 Lomoland Official. All rights reserved.</footer>
    </div>
</div>
</body>
</html>
