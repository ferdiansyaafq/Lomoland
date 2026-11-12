<?php
require_once __DIR__ . '/../config/config.php';

class TItemKeranjang {
    private $kode_produk, $jenis, $file, $harga, $jumlah;
    public function setKodeProduk($v) { $this->kode_produk = $v; }
    public function getKodeProduk()   { return $this->kode_produk; }
    public function setJenis($v)      { $this->jenis = $v; }
    public function getJenis()        { return $this->jenis; }
    public function setFile($v)       { $this->file = $v; }
    public function getFile()         { return $this->file; }
    public function setHarga($v)      { $this->harga = (int)$v; }
    public function getHarga()        { return $this->harga; }
    public function setJumlah($v)     { $this->jumlah = (int)$v; }
    public function getJumlah()       { return $this->jumlah; }
    public function getSubtotal()     { return $this->harga * $this->jumlah; }
}

class KeranjangController {
    private $id_temp;
    public function __construct() {
        $this->id_temp = $_SESSION['id_temp'];
    }
    public function ambilIsiKeranjang() {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($this->id_temp);
        $sql = "SELECT t.kode_produk, t.jumlah, p.jenis, p.file, p.harga
                FROM temp t
                JOIN produk p ON t.kode_produk = p.kode_produk
                WHERE t.id_temp = '$esc'
                ORDER BY t.kode_produk ASC";
        $rows = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        $list = [];
        foreach ($rows as $row) {
            $item = new TItemKeranjang();
            $item->setKodeProduk($row['kode_produk']);
            $item->setJenis($row['jenis']);
            $item->setFile($row['file']);
            $item->setHarga($row['harga']);
            $item->setJumlah($row['jumlah']);
            $list[] = $item;
        }
        return $list;
    }
    public function hitungTotal(array $items) {
        $total = 0;
        foreach ($items as $item) $total += $item->getSubtotal();
        return $total;
    }
    public function hitungJumlahItem() {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($this->id_temp);
        $res = $db->eksekusiQuerySelect("SELECT SUM(jumlah) AS total FROM temp WHERE id_temp = '$esc'");
        $db->tutupKoneksi();
        return (int)($res[0]['total'] ?? 0);
    }
}

$ctrl   = new KeranjangController();
$items  = $ctrl->ambilIsiKeranjang();
$total  = $ctrl->hitungTotal($items);
$jumlah_keranjang = $ctrl->hitungJumlahItem();

$pesan = '';
$tipe  = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'tambah':   $pesan = '✓ Produk berhasil ditambahkan ke keranjang!'; $tipe = 'sukses'; break;
        case 'update':   $pesan = '✓ Jumlah produk berhasil diperbarui!';        $tipe = 'sukses'; break;
        case 'hapus':    $pesan = '✓ Produk berhasil dihapus dari keranjang.';   $tipe = 'info';   break;
        case 'kosong':   $pesan = '⚠ Keranjang masih kosong, silakan pilih produk dulu.'; $tipe = 'warning'; break;
        case 'gagal':    $pesan = 'Gagal memperbarui keranjang. Silakan coba lagi.'; $tipe = 'warning'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja — Lomoland Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #152259;
            --navy-hover: #1e3a8a;
            --bg: #f0f4ff;
            --white: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --accent: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --sidebar-w: 220px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }
        .app-layout { display: flex; min-height: 100vh; }
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
            font-size: 0.6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; align-self: center;
        }
        .logo-sub { grid-column: 1 / -1; font-size: 0.62rem; color: var(--muted); margin-top: 3px; white-space: nowrap; }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-label { font-size: 0.65rem; font-weight: 700; color: var(--muted); letter-spacing: 1.5px; padding: 0 8px; margin-bottom: 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px; text-decoration: none;
            color: var(--muted); font-size: 0.88rem; font-weight: 500;
            margin-bottom: 2px; transition: all 0.15s; position: relative;
        }
        .nav-item:hover { background: #f1f5f9; color: var(--text); }
        .nav-item.active { background: var(--navy); color: #fff; }
        .nav-item.active svg { stroke: #fff; }
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
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .btn-checkout {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--navy); color: #fff; border: none;
            padding: 8px 18px; border-radius: 8px; font-size: 0.82rem; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: background 0.15s;
        }
        .btn-checkout:hover { background: var(--navy-hover); }
        .content { flex: 1; padding: 28px; }
        footer { background: var(--white); border-top: 1px solid var(--border); text-align: center; padding: 16px; font-size: 0.78rem; color: var(--muted); }

        /* Page title */
        .page-title { font-size: 1.3rem; font-weight: 800; color: var(--navy); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .alert-sukses  { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }
        .alert-info    { background: #eff6ff; color: var(--accent);  border: 1px solid #bfdbfe; }
        .alert-warning { background: #fffbeb; color: #b45309;        border: 1px solid #fde68a; }

        /* Empty state */
        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-icon { font-size: 5rem; margin-bottom: 20px; }
        .empty-title { font-size: 1.3rem; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
        .empty-sub { color: var(--muted); font-size: 0.9rem; margin-bottom: 28px; }
        .btn-katalog {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--navy); color: #fff; padding: 12px 28px;
            border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;
            transition: background 0.15s;
        }
        .btn-katalog:hover { background: var(--navy-hover); }

        /* Cart layout */
        .cart-layout { display: flex; gap: 24px; align-items: flex-start; }
        .cart-table-wrap { flex: 1; }
        .cart-summary { width: 280px; flex-shrink: 0; }

        /* Table */
        .cart-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .cart-table thead th {
            background: var(--navy); color: #fff;
            padding: 12px 14px; text-align: left; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;
        }
        .cart-table thead th:last-child { text-align: center; }
        .cart-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
        .cart-table tbody tr:last-child { border-bottom: none; }
        .cart-table tbody tr:hover { background: #f8faff; }
        .cart-table td { padding: 12px 14px; vertical-align: middle; font-size: 0.88rem; }

        .prod-cell { display: flex; align-items: center; gap: 12px; }
        .prod-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); background: #f1f5f9; }
        .prod-img-placeholder { width: 60px; height: 60px; border-radius: 8px; border: 1px solid var(--border); background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--muted); flex-shrink: 0; }
        .prod-name { font-weight: 600; color: var(--text); font-size: 0.88rem; }
        .prod-code { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }

        .price-cell { font-weight: 600; color: var(--text); white-space: nowrap; }
        .subtotal-cell { font-weight: 700; color: var(--navy); white-space: nowrap; }

        /* Qty form */
        .qty-form { display: flex; align-items: center; gap: 6px; }
        .qty-input {
            width: 56px; padding: 6px 8px; border: 1px solid var(--border); border-radius: 6px;
            font-size: 0.88rem; text-align: center; font-family: inherit; color: var(--text);
            background: #f8faff;
        }
        .qty-input:focus { outline: none; border-color: var(--accent); }
        .btn-update {
            padding: 6px 10px; background: #f1f5f9; border: 1px solid var(--border);
            border-radius: 6px; cursor: pointer; font-size: 0.85rem; color: var(--muted);
            transition: all 0.15s;
        }
        .btn-update:hover { background: var(--navy); color: #fff; border-color: var(--navy); }

        /* Delete btn */
        .btn-hapus {
            padding: 7px 10px; background: #fff0f0; border: 1px solid #fecaca;
            border-radius: 6px; cursor: pointer; font-size: 0.95rem; color: var(--danger);
            transition: all 0.15s; display: block; margin: 0 auto;
        }
        .btn-hapus:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

        /* Summary card */
        .summary-card { background: var(--white); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; }
        .summary-header { background: var(--navy); color: #fff; padding: 14px 18px; font-size: 0.95rem; font-weight: 700; }
        .summary-body { padding: 18px; }
        .summary-items { list-style: none; margin-bottom: 16px; }
        .summary-items li { display: flex; justify-content: space-between; align-items: flex-start; padding: 7px 0; border-bottom: 1px solid var(--border); font-size: 0.83rem; gap: 8px; }
        .summary-items li:last-child { border-bottom: none; }
        .item-name { color: var(--text); font-weight: 500; flex: 1; }
        .item-price { color: var(--navy); font-weight: 600; white-space: nowrap; }
        .summary-divider { border: none; border-top: 2px solid var(--border); margin: 12px 0; }
        .summary-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .summary-total-label { font-size: 0.95rem; font-weight: 700; color: var(--text); }
        .summary-total-value { font-size: 1.2rem; font-weight: 800; color: var(--navy); }
        .btn-lanjut-checkout {
            display: block; width: 100%; padding: 13px; text-align: center;
            background: var(--navy); color: #fff; border: none; border-radius: 8px;
            font-size: 0.92rem; font-weight: 700; text-decoration: none; cursor: pointer;
            transition: background 0.15s; margin-bottom: 10px;
        }
        .btn-lanjut-checkout:hover { background: var(--navy-hover); }
        .link-lanjut-belanja { display: block; text-align: center; color: var(--muted); font-size: 0.82rem; text-decoration: none; margin-top: 6px; }
        .link-lanjut-belanja:hover { color: var(--text); }
    </style>
</head>
<body>
<div class="app-layout">

    <!-- Sidebar -->
    <?php $active_nav = 'produk'; include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <div class="main-wrap">
        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb">
                <a href="beranda.php">Beranda</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span>Keranjang</span>
            </div>
            <div class="topbar-right">
                <?php if (!empty($items)): ?>
                <a href="checkout.php" class="btn-checkout">
                    Lanjut ke Checkout
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="page-title">
                🛒 Keranjang Belanja
            </div>

            <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($items)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">🛒</div>
                <div class="empty-title">Keranjang Masih Kosong</div>
                <div class="empty-sub">Belum ada produk yang ditambahkan ke keranjang.<br>Yuk, mulai pilih produk favorit kamu!</div>
                <a href="index.php" class="btn-katalog">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kembali ke Katalog
                </a>
            </div>

            <?php else: ?>
            <!-- Cart Layout -->
            <div class="cart-layout">

                <!-- Table -->
                <div class="cart-table-wrap">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="prod-cell">
                                        <?php if ($item->getFile()): ?>
                                            <img src="../<?= htmlspecialchars(ltrim($item->getFile(), '/')) ?>" alt="<?= htmlspecialchars($item->getJenis()) ?>" class="prod-img">
                                        <?php else: ?>
                                            <div class="prod-img-placeholder">📦</div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="prod-name"><?= htmlspecialchars($item->getJenis()) ?></div>
                                            <div class="prod-code">#<?= htmlspecialchars($item->getKodeProduk()) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price-cell">Rp <?= number_format($item->getHarga(), 0, ',', '.') ?></td>
                                <td>
                                    <form method="POST" action="proses_keranjang.php" class="qty-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="kode" value="<?= htmlspecialchars($item->getKodeProduk()) ?>">
                                        <input type="number" name="jumlah" value="<?= $item->getJumlah() ?>" min="1" max="99" class="qty-input">
                                        <button type="submit" class="btn-update" title="Perbarui jumlah">↻</button>
                                    </form>
                                </td>
                                <td class="subtotal-cell">Rp <?= number_format($item->getSubtotal(), 0, ',', '.') ?></td>
                                <td>
                                    <a href="proses_keranjang.php?action=hapus&amp;kode=<?= urlencode($item->getKodeProduk()) ?>" class="btn-hapus" title="Hapus produk" onclick="return confirm('Hapus produk ini dari keranjang?')">🗑</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="cart-summary">
                    <div class="summary-card">
                        <div class="summary-header">Ringkasan Pesanan</div>
                        <div class="summary-body">
                            <ul class="summary-items">
                                <?php foreach ($items as $item): ?>
                                <li>
                                    <span class="item-name"><?= htmlspecialchars($item->getJenis()) ?> × <?= $item->getJumlah() ?></span>
                                    <span class="item-price">Rp <?= number_format($item->getSubtotal(), 0, ',', '.') ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <hr class="summary-divider">
                            <div class="summary-total">
                                <span class="summary-total-label">Total</span>
                                <span class="summary-total-value">Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            <a href="checkout.php" class="btn-lanjut-checkout">Lanjut ke Checkout →</a>
                            <a href="index.php" class="link-lanjut-belanja">← Lanjut Belanja</a>
                        </div>
                    </div>
                </div>

            </div>
            <?php endif; ?>
        </div>

        <footer>
            &copy; <?= date('Y') ?> Lomoland Store. All rights reserved.
        </footer>
    </div>
</div>
</body>
</html>
