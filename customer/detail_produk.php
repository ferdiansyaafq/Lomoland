<?php
require_once __DIR__ . '/../config/config.php';

class TProdukDetail {
    private $kode_produk, $jenis, $file, $id_kategori, $harga, $nama_kategori;
    public function setKodeProduk($v)    { $this->kode_produk    = $v; }
    public function getKodeProduk()      { return $this->kode_produk; }
    public function setJenis($v)         { $this->jenis           = $v; }
    public function getJenis()           { return $this->jenis; }
    public function setFile($v)          { $this->file            = $v; }
    public function getFile()            { return $this->file; }
    public function setIdKategori($v)    { $this->id_kategori     = (int)$v; }
    public function getIdKategori()      { return $this->id_kategori; }
    public function setHarga($v)         { $this->harga           = (int)$v; }
    public function getHarga()           { return $this->harga; }
    public function setNamaKategori($v)  { $this->nama_kategori   = $v; }
    public function getNamaKategori()    { return $this->nama_kategori; }
}

class DetailProdukController {
    public function ambilProduk($kode_produk) {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($kode_produk);
        $sql = "SELECT p.*, k.nama_kategori
                FROM produk p
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                WHERE p.kode_produk = '$esc'";
        $rows = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        if (empty($rows)) return null;
        $row = $rows[0];
        $p   = new TProdukDetail();
        $p->setKodeProduk($row['kode_produk']);
        $p->setJenis($row['jenis']);
        $p->setFile($row['file']);
        $p->setIdKategori($row['id_kategori']);
        $p->setHarga($row['harga']);
        $p->setNamaKategori($row['nama_kategori']);
        return $p;
    }
    public function hitungKeranjang() {
        $id_temp = $_SESSION['id_temp'];
        $db      = new KoneksiDatabase();
        $esc     = $db->conn->real_escape_string($id_temp);
        $res     = $db->eksekusiQuerySelect("SELECT SUM(jumlah) AS total FROM temp WHERE id_temp = '$esc'");
        $db->tutupKoneksi();
        return (int)($res[0]['total'] ?? 0);
    }
}

$kode_produk = isset($_GET['kode']) ? trim($_GET['kode']) : '';
if ($kode_produk === '') { header("Location: index.php"); exit; }
$ctrl    = new DetailProdukController();
$produk  = $ctrl->ambilProduk($kode_produk);
if (!$produk) { header("Location: index.php"); exit; }
$jumlah_keranjang = $ctrl->hitungKeranjang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk->getJenis()) ?> – Lomoland</title>
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

        /* Main layout */
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
        .topbar-date { font-size: 0.8rem; color: var(--muted); display: flex; align-items: center; gap: 5px; }
        .btn-secure {
            display: flex; align-items: center; gap: 6px;
            background: #f0fdf4; color: var(--success); border: 1.5px solid #bbf7d0;
            padding: 6px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; text-decoration: none;
        }
        .content { flex: 1; padding: 28px; }
        footer { background: var(--white); border-top: 1px solid var(--border); text-align: center; padding: 16px; font-size: 0.78rem; color: var(--muted); }

        /* Cart button in topbar */
        .btn-cart {
            display: flex; align-items: center; gap: 7px;
            background: var(--navy); color: #fff;
            padding: 8px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 600;
            text-decoration: none; transition: background 0.15s;
        }
        .btn-cart:hover { background: var(--navy-hover); }
        .cart-badge {
            background: #ef4444; color: #fff;
            font-size: 0.65rem; font-weight: 700;
            padding: 1px 7px; border-radius: 10px;
        }

        /* Detail layout */
        .detail-layout {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        /* Image card */
        .detail-img-card {
            flex: 0 0 340px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            padding: 32px;
            display: flex; align-items: center; justify-content: center;
            min-height: 320px;
        }
        .detail-img-card img {
            max-width: 100%; max-height: 320px;
            object-fit: contain;
        }
        .detail-img-card .no-img {
            font-size: 5rem; color: #c7d2fe;
        }

        /* Info card */
        .detail-info-card {
            flex: 1;
            min-width: 280px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            padding: 32px;
        }
        .detail-badge {
            display: inline-block; background: var(--navy); color: #fff;
            font-size: 0.72rem; font-weight: 700; padding: 4px 12px; border-radius: 20px;
            margin-bottom: 14px; letter-spacing: 0.5px;
        }
        .detail-name {
            font-size: 1.8rem; font-weight: 800; color: var(--text); margin-bottom: 6px; line-height: 1.25;
        }
        .detail-code {
            font-size: 0.82rem; color: var(--muted); margin-bottom: 14px;
        }
        .detail-price {
            font-size: 2rem; font-weight: 800; color: var(--navy); margin-bottom: 20px;
        }
        .detail-divider { border: none; border-top: 1px solid var(--border); margin-bottom: 20px; }

        /* Info rows */
        .info-rows { margin-bottom: 24px; }
        .info-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row-label { width: 140px; color: var(--muted); font-weight: 500; flex-shrink: 0; }
        .info-row-value { color: var(--text); font-weight: 600; }

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 0.85rem; font-weight: 600;
            color: var(--text); margin-bottom: 7px;
        }
        .form-group input[type="number"] {
            width: 120px; padding: 9px 14px; border-radius: 8px;
            border: 1.5px solid var(--border); font-size: 0.95rem;
            color: var(--text); outline: none; transition: border 0.15s;
        }
        .form-group input[type="number"]:focus { border-color: var(--navy); }

        /* Action buttons */
        .action-btns { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn-add {
            display: flex; align-items: center; gap: 7px;
            padding: 11px 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 700;
            border: 1.5px solid #cbd5e1; background: #f8fafc; color: var(--text);
            cursor: pointer; text-decoration: none; transition: all 0.15s;
        }
        .btn-add:hover { border-color: var(--navy); color: var(--navy); background: #f0f4ff; }
        .btn-buy {
            display: flex; align-items: center; gap: 7px;
            padding: 11px 24px; border-radius: 8px; font-size: 0.9rem; font-weight: 700;
            border: none; background: var(--navy); color: #fff;
            cursor: pointer; text-decoration: none; transition: background 0.15s;
        }
        .btn-buy:hover { background: var(--navy-hover); }

        /* Back link */
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: var(--muted); font-size: 0.82rem; text-decoration: none;
            margin-bottom: 20px; transition: color 0.15s;
        }
        .back-link:hover { color: var(--text); }

        /* Toast */
        #toast {
            position: fixed; bottom: 28px; right: 28px;
            background: var(--navy); color: #fff;
            padding: 12px 24px; border-radius: 10px;
            font-size: 0.88rem; font-weight: 600;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s;
            z-index: 9999;
        }
        #toast.show { opacity: 1; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php $active_nav = 'produk'; include 'partials/sidebar.php'; ?>

    <div class="main-wrap">
        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb">
                <a href="beranda.php">Beranda</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="index.php">Produk</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span><?= htmlspecialchars($produk->getJenis()) ?></span>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo date('d M Y'); ?>
                </div>
                <a href="keranjang.php" class="btn-cart">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    Keranjang
                    <?php if ($jumlah_keranjang > 0): ?>
                        <span class="cart-badge"><?= $jumlah_keranjang ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <a href="index.php" class="back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Kembali ke Katalog
            </a>

            <div class="detail-layout">
                <!-- Gambar -->
                <div class="detail-img-card">
                    <?php if (!empty($produk->getFile())): ?>
                        <img src="../<?= htmlspecialchars(ltrim($produk->getFile(), '/')) ?>"
                             alt="<?= htmlspecialchars($produk->getJenis()) ?>">
                    <?php else: ?>
                        <span class="no-img">📷</span>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="detail-info-card">
                    <span class="detail-badge"><?= htmlspecialchars($produk->getNamaKategori() ?? 'Produk') ?></span>
                    <h1 class="detail-name"><?= htmlspecialchars($produk->getJenis()) ?></h1>
                    <div class="detail-code">Kode: #<?= htmlspecialchars($produk->getKodeProduk()) ?></div>
                    <div class="detail-price">Rp <?= number_format($produk->getHarga(), 0, ',', '.') ?></div>
                    <hr class="detail-divider">

                    <!-- Info rows -->
                    <div class="info-rows">
                        <div class="info-row">
                            <span class="info-row-label">Kategori</span>
                            <span class="info-row-value"><?= htmlspecialchars($produk->getNamaKategori() ?? '-') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Kode Produk</span>
                            <span class="info-row-value"><?= htmlspecialchars($produk->getKodeProduk()) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Harga Satuan</span>
                            <span class="info-row-value">Rp <?= number_format($produk->getHarga(), 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <!-- Form tambah keranjang -->
                    <form id="form-keranjang" onsubmit="return false;">
                        <div class="form-group">
                            <label for="jumlah">Jumlah:</label>
                            <input type="number" id="jumlah" name="jumlah" value="1" min="1" max="99">
                        </div>
                        <div class="action-btns">
                            <button type="button" class="btn-add" onclick="tambahKeranjang()">
                                🛒 Tambah ke Keranjang
                            </button>
                            <button type="button" class="btn-buy" onclick="beliSekarang()">
                                ⚡ Beli Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <footer>© 2026 Lomoland Official. All rights reserved.</footer>
    </div>
</div>

<!-- Toast notification -->
<div id="toast"></div>

<script>
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

function tambahKeranjang() {
    const jumlah = parseInt(document.getElementById('jumlah').value) || 1;
    fetch('proses_keranjang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=tambah&kode=<?= urlencode($produk->getKodeProduk()) ?>&jumlah=' + jumlah
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menambahkan produk ke keranjang.');
        }
        showToast('✓ Produk berhasil ditambahkan ke keranjang!');
    })
    .catch(error => showToast(error.message || 'Gagal menambahkan ke keranjang.'));
}

function beliSekarang() {
    const jumlah = parseInt(document.getElementById('jumlah').value) || 1;
    fetch('proses_keranjang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=tambah&kode=<?= urlencode($produk->getKodeProduk()) ?>&jumlah=' + jumlah
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal memproses pembelian.');
        }
        window.location.href = 'checkout.php';
    })
    .catch(error => showToast(error.message || 'Gagal memproses pembelian.'));
}
</script>
</body>
</html>
