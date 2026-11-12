<?php $active_nav = 'beranda'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda — Lomoland</title>
    <style>
        :root {
            --navy: #152259;
            --bg: #f0f4ff;
            --white: #fff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --sidebar-w: 220px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--white);
            border-right: 1px solid var(--border); display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-logo { display: flex; align-items: flex-start; gap: 10px; padding: 20px 20px 10px; border-bottom: 1px solid var(--border); }
        .logo-icon { width: 36px; height: 36px; background: var(--navy); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .logo-text { display: grid; grid-template-columns: max-content max-content; align-items: center; column-gap: 6px; }
        .logo-name { font-family: Georgia, 'Times New Roman', serif; font-size: 0.92rem; font-weight: 800; color: var(--navy); }
        .logo-badge { background: #e8f0fe; color: #2563eb; font-size: 0.6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; }
        .logo-sub { grid-column: 1 / -1; margin-top: 3px; color: var(--muted); font-size: 0.62rem; white-space: nowrap; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-label { margin: 0 8px 8px; color: var(--muted); font-size: 0.65rem; font-weight: 700; letter-spacing: 1.5px; }
        .nav-item { display: flex; align-items: center; gap: 10px; margin-bottom: 2px; padding: 10px 12px; border-radius: 8px; color: var(--muted); font-size: 0.88rem; font-weight: 500; text-decoration: none; position: relative; }
        .nav-item:hover { background: #f1f5f9; color: var(--text); }
        .nav-item.active { background: var(--navy); color: #fff; }
        .nav-item.active svg { stroke: #fff; }
        .main-wrap { display: flex; flex: 1; flex-direction: column; min-height: 100vh; margin-left: var(--sidebar-w); }
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 14px 28px; background: var(--white); border-bottom: 1px solid var(--border); }
        .breadcrumb { color: var(--text); font-size: 0.84rem; font-weight: 600; }
        .topbar-date { color: var(--muted); font-size: 0.8rem; }
        .content { width: 100%; max-width: 1280px; flex: 1; margin: 0 auto; padding: 32px; }
        .page-heading { margin-bottom: 24px; }
        .page-heading h1 { margin-bottom: 7px; color: var(--navy); font-size: 1.6rem; font-weight: 800; }
        .page-heading p { color: var(--muted); font-size: 0.92rem; line-height: 1.6; }
        .hero-card { padding: 36px; border: 1px solid var(--border); border-radius: 14px; background: linear-gradient(130deg, #fff, #edf2ff); box-shadow: 0 1px 5px rgba(21,34,89,0.06); }
        .hero-icon { display: grid; width: 46px; height: 46px; place-items: center; margin-bottom: 16px; border-radius: 12px; background: #e8f0fe; font-size: 1.4rem; }
        .hero-card h2 { max-width: 650px; color: var(--navy); font-size: clamp(1.7rem, 3vw, 2.5rem); line-height: 1.2; }
        .hero-card p { max-width: 650px; margin: 12px 0 22px; color: var(--muted); font-size: 1rem; line-height: 1.6; }
        .button-link { display: inline-flex; align-items: center; gap: 9px; padding: 11px 18px; border-radius: 8px; background: var(--navy); color: #fff; font-size: 0.9rem; font-weight: 700; text-decoration: none; }
        .button-link:hover { background: #1e3a8a; }
        footer { padding: 16px; border-top: 1px solid var(--border); background: var(--white); color: var(--muted); text-align: center; font-size: 0.78rem; }
        @media (max-width: 680px) {
            .sidebar { width: 68px; }
            .sidebar-logo { justify-content: center; padding: 16px 8px; }
            .sidebar-logo .logo-text, .nav-label, .nav-item span { display: none; }
            .sidebar-nav { padding: 14px 8px; }
            .nav-item { justify-content: center; padding: 12px 8px; }
            .main-wrap { margin-left: 68px; }
            .topbar { padding: 12px 16px; }
            .content { padding: 22px 16px; }
            .hero-card { padding: 22px; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="main-wrap">
        <header class="topbar">
            <div class="breadcrumb">
                <a href="beranda.php" style="color:inherit;text-decoration:none;">Beranda</a>
            </div>
            <div class="topbar-date"><?= htmlspecialchars(date('d M Y')) ?></div>
        </header>
        <div class="content">
            <div class="page-heading">
                <h1>Beranda</h1>
                <p>Selamat datang di Lomoland, toko kamera analog dan film.</p>
            </div>
            <section class="hero-card">
                <span class="hero-icon" aria-hidden="true">📷</span>
                <h2>Abadikan momen dengan karakter analog.</h2>
                <p>Temukan kamera analog, film, dan aksesori Lomography untuk menemani cerita fotografimu.</p>
                <a class="button-link" href="index.php">Jelajahi Produk <span aria-hidden="true">→</span></a>
            </section>
        </div>
        <footer>&copy; <?= date('Y') ?> Lomoland Official. All rights reserved.</footer>
    </main>
</div>
</body>
</html>
