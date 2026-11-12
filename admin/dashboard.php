<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: login.php");
    exit;
}

// Helper untuk format tanggal Indonesia dinamis sesuai hari dan tanggal sistem nyata
function getTanggalHariIniIndo() {
    $nama_hari = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    $nama_bulan = [
        1  => 'Jan',
        2  => 'Feb',
        3  => 'Mar',
        4  => 'Apr',
        5  => 'Mei',
        6  => 'Jun',
        7  => 'Jul',
        8  => 'Agu',
        9  => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des'
    ];

    $hari_inggris = date('l');
    $hari_indo    = isset($nama_hari[$hari_inggris]) ? $nama_hari[$hari_inggris] : $hari_inggris;
    $tgl          = date('d');
    $bln          = (int)date('m');
    $bulan_indo   = isset($nama_bulan[$bln]) ? $nama_bulan[$bln] : date('M');
    $tahun        = date('Y');

    return "{$hari_indo}, {$tgl} {$bulan_indo} {$tahun}";
}

$admin_active = isset($_SESSION['user']) ? $_SESSION['user'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Lomoland System Access</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-shell">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand-box">
                <div class="brand-lens-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="4"></circle>
                        <line x1="12" y1="2" x2="12" y2="4"></line>
                        <line x1="12" y1="20" x2="12" y2="22"></line>
                        <line x1="2" y1="12" x2="4" y2="12"></line>
                        <line x1="20" y1="12" x2="22" y2="12"></line>
                    </svg>
                </div>
                <div class="brand-text-col">
                    <span class="brand-subtitle">System Access</span>
                    <span class="brand-title">LOMOLAND</span>
                </div>
            </div>

            <div class="sidebar-nav-section">
                <div class="nav-section-title">NAVIGASI</div>
                <ul class="sidebar-nav-menu">
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                            </span>
                            <span>Data Artikel</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="data_produk.php">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                </svg>
                            </span>
                            <span>Data Produk</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <span>Data Customer</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <span>Kontak</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="6" y1="3" x2="6" y2="15"></line>
                                    <circle cx="18" cy="6" r="3"></circle>
                                    <circle cx="6" cy="18" r="3"></circle>
                                </svg>
                            </span>
                            <span>Kategori</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                </svg>
                            </span>
                            <span>Data Pengguna</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                            </span>
                            <span>Data Transaksi</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-user-card">
                <div class="user-profile-pill">
                    <div class="user-profile-left">
                        <div class="avatar-badge">A</div>
                        <span class="user-name-text"><?= htmlspecialchars($admin_active) ?></span>
                    </div>
                    <a href="index.php?logout=true" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="logout-icon-btn" title="Keluar">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="topbar-header">
                <nav class="breadcrumbs-trail">
                    <span>Beranda</span>
                    <span class="breadcrumb-sep">&rsaquo;</span>
                    <span class="crumb-current">Dashboard</span>
                </nav>

                <div class="topbar-right-tools">
                    <div class="date-indicator" title="Tanggal Hari Ini: <?= date('d/m/Y') ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span><?= getTanggalHariIniIndo() ?></span>
                    </div>

                    <div class="topbar-user-avatar">
                        <div class="avatar-badge avatar-badge-light">A</div>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </header>

            <div class="content-body-padding">
                <h1 class="page-headline-serif">Selamat Datang, <?= htmlspecialchars($admin_active) ?>!</h1>
                <div class="page-headline-divider"></div>
                <p style="color: #64748b; margin-bottom: 30px;">Silakan pilih menu di bawah ini untuk mengelola sistem operasional Lomoland.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <a href="data_produk.php" style="background: #ffffff; border: 1px solid #eaedf3; border-radius: 16px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.2s ease; display: block;" onmouseover="this.style.borderColor='#1665d8'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#eaedf3'; this.style.transform='none'">
                        <div style="width: 44px; height: 44px; background: #eff6ff; color: #1665d8; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            </svg>
                        </div>
                        <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Manajemen Produk</h3>
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 18px;">Edit dan kelola katalog data produk kamera Lomo.</p>
                        <span style="color: #1665d8; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            Buka Manajemen Produk &rarr;
                        </span>
                    </a>
                </div>

                <div class="copyright-bottom-note">
                    &copy; 2026 Lomoland Official. All rights reserved.
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
