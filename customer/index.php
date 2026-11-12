<?php
require_once __DIR__ . '/../config/config.php';

class TProdukCustomer {
    private $kode_produk, $jenis, $file, $id_kategori, $harga, $nama_kategori;
    public function setKodeProduk($v) { $this->kode_produk = $v; }
    public function getKodeProduk()   { return $this->kode_produk; }
    public function setJenis($v) { $this->jenis = $v; }
    public function getJenis()   { return $this->jenis; }
    public function setFile($v) { $this->file = $v; }
    public function getFile()   { return $this->file; }
    public function setIdKategori($v) { $this->id_kategori = (int)$v; }
    public function getIdKategori()   { return $this->id_kategori; }
    public function setHarga($v) { $this->harga = (int)$v; }
    public function getHarga()   { return $this->harga; }
    public function setNamaKategori($v) { $this->nama_kategori = $v; }
    public function getNamaKategori()   { return $this->nama_kategori; }
}

class KatalogProduk {
    public function ambilSemuaProduk($filter_kategori = null) {
        $db  = new KoneksiDatabase();
        $sql = "SELECT p.kode_produk, p.jenis, p.file, p.id_kategori, p.harga, k.nama_kategori
                FROM produk p
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori";
        if ($filter_kategori !== null && (int)$filter_kategori > 0) {
            $fk   = (int)$filter_kategori;
            $sql .= " WHERE p.id_kategori = $fk";
        }
        $sql .= " ORDER BY p.kode_produk ASC";
        $rows = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        $list = [];
        foreach ($rows as $row) {
            $p = new TProdukCustomer();
            $p->setKodeProduk($row['kode_produk']);
            $p->setJenis($row['jenis']);
            $p->setFile($row['file']);
            $p->setIdKategori($row['id_kategori']);
            $p->setHarga($row['harga']);
            $p->setNamaKategori($row['nama_kategori']);
            $list[] = $p;
        }
        return $list;
    }
    public function ambilSemuaKategori() {
        $db   = new KoneksiDatabase();
        $data = $db->eksekusiQuerySelect("SELECT * FROM kategori ORDER BY id_kategori ASC");
        $db->tutupKoneksi();
        return $data;
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

$katalog         = new KatalogProduk();
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$daftar_produk   = $katalog->ambilSemuaProduk($filter_kategori ?: null);
$daftar_kategori = $katalog->ambilSemuaKategori();
$jumlah_keranjang = $katalog->hitungKeranjang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk – Lomoland</title>
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

        /* Cart button */
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

        /* Section header */
        .section-header { margin-bottom: 24px; }
        .section-header h2 { font-size: 1.4rem; font-weight: 800; color: var(--navy); margin-bottom: 4px; }
        .section-header p { font-size: 0.88rem; color: var(--muted); }

        /* Filter bar */
        .filter-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
        .filter-pill {
            padding: 6px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 600;
            border: 1.5px solid var(--border); background: var(--white);
            color: var(--muted); text-decoration: none; transition: all 0.15s; cursor: pointer;
        }
        .filter-pill:hover { border-color: var(--navy); color: var(--navy); }
        .filter-pill.active { background: var(--navy); color: #fff; border-color: var(--navy); }

        /* Info bar */
        .info-bar { font-size: 0.82rem; color: var(--muted); margin-bottom: 20px; }
        .info-bar strong { color: var(--text); }

        /* Product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            overflow: hidden;
            display: flex; flex-direction: column;
            transition: box-shadow 0.15s, transform 0.15s;
        }
        .product-card:hover { box-shadow: 0 4px 16px rgba(21,34,89,0.12); transform: translateY(-2px); }
        .product-img-wrap {
            background: #f8f9ff;
            height: 180px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .product-img-wrap img {
            max-width: 100%; max-height: 100%;
            object-fit: contain; padding: 12px;
        }
        .product-img-wrap .no-img {
            font-size: 2.5rem; color: #c7d2fe;
        }
        .product-body { padding: 14px; flex: 1; display: flex; flex-direction: column; }
        .product-badge {
            display: inline-block; background: var(--navy); color: #fff;
            font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;
            margin-bottom: 8px; letter-spacing: 0.5px;
        }
        .product-name { font-size: 0.95rem; font-weight: 700; color: var(--text); margin-bottom: 3px; line-height: 1.3; }
        .product-code { font-size: 0.72rem; color: var(--muted); margin-bottom: 10px; }
        .product-price { font-size: 1.1rem; font-weight: 800; color: var(--navy); margin-top: auto; margin-bottom: 12px; }
        .product-actions { display: flex; gap: 8px; }
        .btn-detail {
            flex: 1; padding: 7px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600;
            border: 1.5px solid var(--navy); color: var(--navy); background: transparent;
            text-align: center; text-decoration: none; transition: all 0.15s; cursor: pointer;
        }
        .btn-detail:hover { background: var(--navy); color: #fff; }
        .btn-add-cart {
            flex: 1; padding: 7px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600;
            border: none; background: var(--navy); color: #fff;
            text-align: center; cursor: pointer; transition: background 0.15s;
        }
        .btn-add-cart:hover { background: var(--navy-hover); }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
        .empty-state .empty-icon { font-size: 3rem; margin-bottom: 12px; }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }

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
                <span>Produk</span>
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
            <!-- Section header -->
            <div class="section-header">
                <h2>Katalog Produk</h2>
                <p>Temukan kamera analog &amp; aksesori Lomo terbaikmu</p>
            </div>

            <!-- Filter bar -->
            <div class="filter-bar">
                <a href="index.php" class="filter-pill <?= $filter_kategori === 0 ? 'active' : '' ?>">Semua</a>
                <?php foreach ($daftar_kategori as $kat): ?>
                    <a href="index.php?kategori=<?= $kat['id_kategori'] ?>"
                       class="filter-pill <?= $filter_kategori === (int)$kat['id_kategori'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Info bar -->
            <div class="info-bar">
                Menampilkan <strong><?= count($daftar_produk) ?> produk</strong>
                <?php if ($filter_kategori > 0):
                    foreach ($daftar_kategori as $kat) {
                        if ((int)$kat['id_kategori'] === $filter_kategori) {
                            echo ' dalam kategori <strong>' . htmlspecialchars($kat['nama_kategori']) . '</strong>';
                            break;
                        }
                    }
                endif; ?>
            </div>

            <!-- Product grid -->
            <?php if (empty($daftar_produk)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📷</div>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Belum ada produk dalam kategori ini.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($daftar_produk as $p): ?>
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <?php if (!empty($p->getFile())): ?>
                                    <img src="../<?= htmlspecialchars(ltrim($p->getFile(), '/')) ?>"
                                         alt="<?= htmlspecialchars($p->getJenis()) ?>">
                                <?php else: ?>
                                    <span class="no-img">📷</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <span class="product-badge"><?= htmlspecialchars($p->getNamaKategori() ?? 'Produk') ?></span>
                                <div class="product-name"><?= htmlspecialchars($p->getJenis()) ?></div>
                                <div class="product-code">#<?= htmlspecialchars($p->getKodeProduk()) ?></div>
                                <div class="product-price">Rp <?= number_format($p->getHarga(), 0, ',', '.') ?></div>
                                <div class="product-actions">
                                    <a href="detail_produk.php?kode=<?= urlencode($p->getKodeProduk()) ?>" class="btn-detail">Detail</a>
                                    <button class="btn-add-cart"
                                            onclick="tambahKeranjang('<?= htmlspecialchars($p->getKodeProduk(), ENT_QUOTES) ?>', '<?= htmlspecialchars($p->getJenis(), ENT_QUOTES) ?>')">
                                        + Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

function tambahKeranjang(kode, nama) {
    fetch('proses_keranjang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=tambah&kode=' + encodeURIComponent(kode) + '&jumlah=1'
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menambahkan produk ke keranjang.');
        }
        showToast('✓ ' + nama + ' ditambahkan ke keranjang!');
    })
    .catch(error => showToast(error.message || 'Gagal menambahkan ke keranjang.'));
}
</script>
</body>
</html>
