<?php
// Sidebar Customer — shared partial
// Usage: include this file, set $active_nav = 'beranda' | 'informasi' | 'produk' | 'kontak'
$active_nav = $active_nav ?? 'beranda';
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" fill="#fff" fill-opacity="0.2"/>
                <circle cx="12" cy="12" r="6"  fill="#fff"/>
                <circle cx="12" cy="12" r="3"  fill="#152259"/>
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
        <a href="beranda.php" class="nav-item <?= $active_nav === 'beranda' ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 12L12 3l9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
            </svg>
            <span>Beranda</span>
        </a>
        <a href="informasi.php" class="nav-item">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Informasi</span>
        </a>
        <a href="index.php" class="nav-item <?= $active_nav === 'produk' ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>Produk</span>
        </a>
        <a href="kontak.php" class="nav-item">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Kontak</span>
        </a>
    </nav>
</aside>
