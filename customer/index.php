<?php
require_once __DIR__ . '/../config/config.php';

// =============================================
// CLASS TProduk (Customer view)
// =============================================
class TProdukCustomer {
    private $kode_produk;
    private $jenis;
    private $file;
    private $id_kategori;
    private $harga;
    private $nama_kategori;

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

// =============================================
// CLASS KatalogProduk
// =============================================
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
    <title>Katalog Produk - Lomoland</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f0; color: #333; }

        /* NAVBAR */
        .navbar {
            background: #1a1a1a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 32px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .navbar .brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #e8c97e; }
        .navbar nav a {
            color: #ddd;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.9rem;
            transition: color .2s;
        }
        .navbar nav a:hover { color: #e8c97e; }
        .cart-btn {
            background: #e8c97e;
            color: #1a1a1a !important;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 600;
        }
        .cart-btn:hover { background: #d4a84b !important; color: #1a1a1a !important; }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #1a1a1a 60%, #3a2a0a);
            color: #fff;
            padding: 60px 32px;
            text-align: center;
        }
        .hero h1 { font-size: 2.4rem; letter-spacing: 3px; color: #e8c97e; }
        .hero p  { margin-top: 10px; font-size: 1rem; color: #ccc; }

        /* FILTER */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px 32px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
        }
        .filter-bar a {
            text-decoration: none;
            padding: 7px 18px;
            border-radius: 20px;
            border: 1.5px solid #ccc;
            font-size: 0.85rem;
            color: #555;
            transition: all .2s;
        }
        .filter-bar a:hover, .filter-bar a.active {
            background: #1a1a1a;
            color: #e8c97e;
            border-color: #1a1a1a;
        }

        /* PRODUK GRID */
        .section-title {
            padding: 28px 32px 10px;
            font-size: 1.1rem;
            color: #666;
        }
        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
            padding: 10px 32px 40px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
        }
        .card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.13); }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #fafaf8;
            padding: 16px;
        }
        .card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
        .card-kat  { font-size: 0.72rem; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .card-nama { font-size: 1rem; font-weight: 600; margin: 6px 0 4px; }
        .card-kode { font-size: 0.78rem; color: #aaa; }
        .card-harga { font-size: 1.1rem; font-weight: 700; color: #c0392b; margin-top: 10px; }
        .card-actions { display: flex; gap: 8px; margin-top: 14px; }
        .btn-detail, .btn-keranjang {
            flex: 1;
            padding: 9px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: background .2s;
        }
        .btn-detail    { background: #f0f0eb; color: #333; }
        .btn-detail:hover { background: #e0e0d8; }
        .btn-keranjang { background: #1a1a1a; color: #e8c97e; }
        .btn-keranjang:hover { background: #333; }

        /* KOSONG */
        .kosong { text-align: center; padding: 60px 32px; color: #aaa; font-size: 1.1rem; }

        /* FOOTER */
        footer { background: #1a1a1a; color: #888; text-align: center; padding: 20px; font-size: 0.82rem; }
        footer span { color: #e8c97e; }

        /* TOAST */
        #toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1a1a1a;
            color: #e8c97e;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            display: none;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <span class="brand">LOMOLAND</span>
    <nav>
        <a href="index.php">Katalog</a>
        <a href="keranjang.php" class="cart-btn">🛒 Keranjang
            <?php if ($jumlah_keranjang > 0): ?>
                (<?= $jumlah_keranjang ?>)
            <?php endif; ?>
        </a>
    </nav>
</div>

<!-- HERO -->
<div class="hero">
    <h1>LOMOLAND</h1>
    <p>Temukan kamera analog & aksesori Lomo terbaik untuk petualangan fotografimu</p>
</div>

<!-- FILTER KATEGORI -->
<div class="filter-bar">
    <a href="index.php" class="<?= $filter_kategori === 0 ? 'active' : '' ?>">Semua</a>
    <?php foreach ($daftar_kategori as $kat): ?>
        <a href="index.php?kategori=<?= $kat['id_kategori'] ?>"
           class="<?= $filter_kategori === (int)$kat['id_kategori'] ? 'active' : '' ?>">
            <?= htmlspecialchars($kat['nama_kategori']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- PRODUK -->
<p class="section-title">
    Menampilkan <strong><?= count($daftar_produk) ?></strong> produk
    <?php if ($filter_kategori > 0):
        foreach ($daftar_kategori as $kat) {
            if ((int)$kat['id_kategori'] === $filter_kategori) {
                echo ' — kategori <strong>' . htmlspecialchars($kat['nama_kategori']) . '</strong>';
                break;
            }
        }
    endif; ?>
</p>

<?php if (empty($daftar_produk)): ?>
    <div class="kosong">Tidak ada produk dalam kategori ini.</div>
<?php else: ?>
<div class="produk-grid">
    <?php foreach ($daftar_produk as $produk): ?>
    <div class="card">
        <img src="../<?= htmlspecialchars($produk->getFile()) ?>"
             alt="<?= htmlspecialchars($produk->getJenis()) ?>"
             onerror="this.src='../assets/images/placeholder.png'">
        <div class="card-body">
            <span class="card-kat"><?= htmlspecialchars($produk->getNamaKategori()) ?></span>
            <div class="card-nama"><?= htmlspecialchars($produk->getJenis()) ?></div>
            <div class="card-kode"><?= htmlspecialchars($produk->getKodeProduk()) ?></div>
            <div class="card-harga">Rp <?= number_format($produk->getHarga(), 0, ',', '.') ?></div>
            <div class="card-actions">
                <a href="detail_produk.php?kode=<?= urlencode($produk->getKodeProduk()) ?>" class="btn-detail">Detail</a>
                <a href="proses_keranjang.php?action=tambah&kode=<?= urlencode($produk->getKodeProduk()) ?>&redirect=index"
                   class="btn-keranjang">+ Keranjang</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- TOAST -->
<div id="toast"></div>

<footer>
    &copy; 2026 <span>Lomoland</span> — Sistem Penjualan Kamera Lomo Online
</footer>

<script>
    // Tampilkan toast jika ada pesan dari URL
    const params = new URLSearchParams(window.location.search);
    if (params.get('status') === 'tambah') {
        showToast('✓ Produk ditambahkan ke keranjang!');
    }
    function showToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.style.display = 'block';
        setTimeout(() => t.style.display = 'none', 2500);
    }
</script>
</body>
</html>
