<?php
kirim-tresia
// Root landing page — redirect langsung ke customer atau pilih role
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lomoland — Analog Camera & Film Shop</title>
    <style>
        :root {
            --navy: #152259;
            --accent: #2563eb;
            --gold: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #152259 50%, #1e3a8a 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .wrapper {
            text-align: center;
            padding: 40px 20px;
            max-width: 480px;
            width: 100%;
        }
        .logo-wrap {
            display: inline-flex; align-items: center; gap: 14px;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-title {
            text-align: left;
        }
        .logo-title h1 { font-size: 1.8rem; font-weight: 900; letter-spacing: 2px; color: #fff; }
        .logo-title p  { font-size: 0.75rem; color: rgba(255,255,255,0.6); letter-spacing: 1px; margin-top: 2px; }

        .tagline {
            font-size: 1rem; color: rgba(255,255,255,0.7);
            line-height: 1.6; margin-bottom: 48px;
        }

        .btn-group { display: flex; flex-direction: column; gap: 14px; }

        .btn {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px; border-radius: 12px;
            text-decoration: none; font-weight: 700; font-size: 1rem;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .btn-label { display: flex; align-items: center; gap: 10px; }
        .btn-arrow { font-size: 1.2rem; }

        .btn-customer {
            background: #fff; color: var(--navy);
        }
        .btn-admin {
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.25);
            color: #fff;
        }

        .badge {
            display: inline-block; font-size: 0.65rem; font-weight: 700;
            padding: 2px 8px; border-radius: 20px; letter-spacing: 0.5px;
        }
        .badge-customer { background: #e8f0fe; color: var(--accent); }
        .badge-admin    { background: rgba(255,255,255,0.2); color: #fff; }

        footer { margin-top: 48px; font-size: 0.75rem; color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="logo-wrap">
        <div class="logo-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" fill="#fff" fill-opacity="0.15"/>
                <circle cx="12" cy="12" r="6" fill="#fff"/>
                <circle cx="12" cy="12" r="3" fill="#152259"/>
            </svg>
        </div>
        <div class="logo-title">
            <h1>LOMOLAND</h1>
            <p>ANALOG CAMERA &amp; FILM SHOP</p>
        </div>
    </div>

    <p class="tagline">Temukan kamera analog &amp; aksesori Lomography<br>terbaik untuk petualangan fotografimu.</p>

    <div class="btn-group">
        <a href="customer/beranda.php" class="btn btn-customer">
            <div class="btn-label">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#152259" stroke-width="2">
                    <path d="M3 12L12 3l9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                </svg>
                <span>Belanja Sekarang</span>
                <span class="badge badge-customer">Customer</span>
            </div>
            <span class="btn-arrow">→</span>
        </a>
        <a href="admin/login.php" class="btn btn-admin">
            <div class="btn-label">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <span>Masuk Dashboard</span>
                <span class="badge badge-admin">Admin</span>
            </div>
            <span class="btn-arrow">→</span>
        </a>
    </div>

    <footer>© 2026 Lomoland Official. All rights reserved.</footer>
</div>
</body>
</html>
=======
require_once __DIR__ . '/config/config.php';

// Fokus penuh ke Admin Lomoland
if (isset($_SESSION['user'])) {
    header("Location: admin/data_produk.php");
    exit;
}

header("Location: admin/login.php");
exit;
main
