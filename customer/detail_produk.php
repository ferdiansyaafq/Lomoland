<?php
require_once __DIR__ . '/../config/config.php';

// =============================================
// CLASS TProdukDetail
// =============================================
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

// =============================================
// CLASS DetailProdukController
// =============================================
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
if ($kode_produk === '') {
    header("Location: index.php");
    exit;
}

$ctrl    = new DetailProdukController();
$produk  = $ctrl->ambilProduk($kode_produk);

if (!$produk) {
    header("Location: index.php");
    exit;
}

$jumlah_keranjang = $ctrl->hitungKeranjang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk->getJenis()) ?> - Lomoland</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f0; color: #333; }

        .navbar {
            background: #1a1a1a; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 32px; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .navbar .brand { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; color: #e8c97e; }
        .navbar nav a  { color: #ddd; text-decoration: none; margin-left: 20px; font-size: 0.9rem; }
        .navbar nav a:hover { color: #e8c97e; }
        .cart-btn {
            background: #e8c97e; color: #1a1a1a !important;
            padding: 8px 18px; border-radius: 20px; font-weight: 600;
        }

        .breadcrumb { padding: 16px 32px; font-size: 0.85rem; color: #888; }
        .breadcrumb a { color: #888; text-decoration: none; }
        .breadcrumb a:hover { color: #333; }

        .detail-wrap {
            max-width: 900px; margin: 0 auto; padding: 20px 32px 60px;
            display: flex; gap: 40px; flex-wrap: wrap;
        }
        .detail-img {
            flex: 1; min-width: 260px; max-width: 380px;
            background: #fff; border-radius: 16px;
            padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            display: flex; align-items: center; justify-content: center;
        }
        .detail-img img { width: 100%; object-fit: contain; max-height: 320px; }

        .detail-info { flex: 1; min-width: 260px; }
        .badge-kat {
            display: inline-block; background: #f0ede4; color: #888;
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;
        }
        .detail-info h1 { font-size: 1.8rem; font-weight: 700; line-height: 1.3; }
        .detail-kode    { color: #aaa; font-size: 0.85rem; margin: 6px 0 20px; }
        .detail-harga   { font-size: 2rem; font-weight: 800; color: #c0392b; }

        .divider { border: none; border-top: 1px solid #eee; margin: 24px 0; }

        .info-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 0.95rem; border-bottom: 1px solid #f0f0eb; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #888; }
        .info-val   { font-weight: 600; }

        .qty-wrap { margin-top: 24px; display: flex; align-items: center; gap: 14px; }
        .qty-wrap label { font-size: 0.9rem; color: #666; }
        .qty-input {
            width: 70px; padding: 8px 12px; border: 1.5px solid #ddd;
            border-radius: 8px; font-size: 1rem; text-align: center;
        }

        .btn-actions { display: flex; gap: 12px; margin-top: 20px; }
        .btn-keranjang, .btn-beli {
            flex: 1; padding: 14px; border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s;
            text-align: center; text-decoration: none;
        }
        .btn-keranjang { background: #f0f0eb; color: #333; }
        .btn-keranjang:hover { background: #ddd; }
        .btn-beli { background: #1a1a1a; color: #e8c97e; }
        .btn-beli:hover { background: #333; }

        footer { background: #1a1a1a; color: #888; text-align: center; padding: 20px; font-size: 0.82rem; }
        footer span { color: #e8c97e; }

        #toast {
            position: fixed; bottom: 30px; right: 30px;
            background: #1a1a1a; color: #e8c97e;
            padding: 14px 24px; border-radius: 10px;
            font-size: 0.9rem; font-weight: 600;
            display: none; z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="navbar">
    <span class="brand">LOMOLAND</span>
    <nav>
        <a href="index.php">Katalog</a>
        <a href="keranjang.php" class="cart-btn">🛒 Keranjang
            <?php if ($jumlah_keranjang > 0): ?>(<?= $jumlah_keranjang ?>)<?php endif; ?>
        </a>
    </nav>
</div>

<div class="breadcrumb">
    <a href="index.php">Katalog</a> &rsaquo;
    <a href="index.php?kategori=<?= $produk->getIdKategori() ?>"><?= htmlspecialchars($produk->getNamaKategori()) ?></a> &rsaquo;
    <?= htmlspecialchars($produk->getJenis()) ?>
</div>

<div class="detail-wrap">
    <!-- GAMBAR -->
    <div class="detail-img">
        <img src="../<?= htmlspecialchars($produk->getFile()) ?>"
             alt="<?= htmlspecialchars($produk->getJenis()) ?>">
    </div>

    <!-- INFO -->
    <div class="detail-info">
        <span class="badge-kat"><?= htmlspecialchars($produk->getNamaKategori()) ?></span>
        <h1><?= htmlspecialchars($produk->getJenis()) ?></h1>
        <p class="detail-kode">Kode: <?= htmlspecialchars($produk->getKodeProduk()) ?></p>
        <div class="detail-harga">Rp <?= number_format($produk->getHarga(), 0, ',', '.') ?></div>

        <hr class="divider">

        <div class="info-row">
            <span class="info-label">Kategori</span>
            <span class="info-val"><?= htmlspecialchars($produk->getNamaKategori()) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kode Produk</span>
            <span class="info-val"><?= htmlspecialchars($produk->getKodeProduk()) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Harga Satuan</span>
            <span class="info-val">Rp <?= number_format($produk->getHarga(), 0, ',', '.') ?></span>
        </div>

        <!-- FORM TAMBAH KE KERANJANG -->
        <form method="GET" action="proses_keranjang.php" id="formKeranjang">
            <input type="hidden" name="action" value="tambah">
            <input type="hidden" name="kode" value="<?= htmlspecialchars($produk->getKodeProduk()) ?>">
            <input type="hidden" name="redirect" value="detail">
            <input type="hidden" name="kode_balik" value="<?= urlencode($produk->getKodeProduk()) ?>">

            <div class="qty-wrap">
                <label for="jumlah">Jumlah:</label>
                <input type="number" id="jumlah" name="jumlah" class="qty-input"
                       value="1" min="1" max="99">
            </div>

            <div class="btn-actions">
                <button type="submit" class="btn-keranjang">🛒 Tambah ke Keranjang</button>
                <button type="submit" name="langsung_beli" value="1" class="btn-beli"
                        onclick="document.getElementById('formKeranjang').action='proses_keranjang.php'; this.form.elements['redirect'].value='checkout';">
                    ⚡ Beli Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<div id="toast"></div>

<footer>
    &copy; 2026 <span>Lomoland</span> — Sistem Penjualan Kamera Lomo Online
</footer>

<script>
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
