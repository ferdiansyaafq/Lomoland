<?php
require_once __DIR__ . '/../config/config.php';

// =============================================
// CLASS TItemKeranjang
// =============================================
class TItemKeranjang {
    private $kode_produk;
    private $jenis;
    private $file;
    private $harga;
    private $jumlah;

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

// =============================================
// CLASS KeranjangController
// =============================================
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

// Pesan status
$pesan = '';
$tipe  = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'tambah':   $pesan = '✓ Produk berhasil ditambahkan ke keranjang!'; $tipe = 'sukses'; break;
        case 'update':   $pesan = '✓ Jumlah produk berhasil diperbarui!';        $tipe = 'sukses'; break;
        case 'hapus':    $pesan = '✓ Produk berhasil dihapus dari keranjang.';   $tipe = 'info';   break;
        case 'kosong':   $pesan = '⚠ Keranjang masih kosong, silakan pilih produk dulu.'; $tipe = 'warning'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Lomoland</title>
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
        .cart-btn { background: #e8c97e; color: #1a1a1a !important; padding: 8px 18px; border-radius: 20px; font-weight: 600; }

        .page-title { padding: 28px 32px 10px; font-size: 1.5rem; font-weight: 700; }

        .alert {
            margin: 0 32px 16px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .alert.sukses  { background: #d4edda; color: #155724; }
        .alert.info    { background: #d1ecf1; color: #0c5460; }
        .alert.warning { background: #fff3cd; color: #856404; }

        .keranjang-wrap {
            max-width: 900px; margin: 0 auto; padding: 0 32px 60px;
            display: flex; gap: 24px; flex-wrap: wrap;
        }
        .tabel-wrap { flex: 1; min-width: 300px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        thead { background: #1a1a1a; color: #e8c97e; }
        thead th { padding: 14px 16px; font-size: 0.85rem; text-align: left; }
        tbody td { padding: 14px 16px; border-bottom: 1px solid #f0f0eb; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }

        .item-img { width: 60px; height: 60px; object-fit: contain; background: #fafaf8; border-radius: 8px; padding: 4px; }
        .item-nama { font-weight: 600; font-size: 0.95rem; }
        .item-kode { font-size: 0.78rem; color: #aaa; }

        .qty-form { display: flex; align-items: center; gap: 6px; }
        .qty-input { width: 55px; padding: 6px 8px; border: 1.5px solid #ddd; border-radius: 6px; text-align: center; font-size: 0.95rem; }
        .btn-update { padding: 6px 10px; background: #f0f0eb; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
        .btn-update:hover { background: #ddd; }
        .btn-hapus { padding: 6px 10px; background: #fde8e8; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem; color: #c0392b; margin-left: 4px; }
        .btn-hapus:hover { background: #f5c6c6; }

        /* RINGKASAN */
        .ringkasan {
            width: 260px; min-width: 220px;
            background: #fff; border-radius: 12px;
            padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            height: fit-content;
        }
        .ringkasan h3 { font-size: 1rem; margin-bottom: 16px; }
        .r-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; }
        .r-row.total { font-weight: 800; font-size: 1.1rem; border-top: 1px solid #eee; padding-top: 12px; margin-top: 6px; color: #c0392b; }
        .btn-checkout {
            display: block; width: 100%; padding: 14px;
            background: #1a1a1a; color: #e8c97e;
            border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700;
            cursor: pointer; text-align: center;
            text-decoration: none; margin-top: 20px;
            transition: background .2s;
        }
        .btn-checkout:hover { background: #333; }
        .btn-lanjut {
            display: block; text-align: center;
            margin-top: 10px; color: #888; font-size: 0.85rem;
            text-decoration: none;
        }
        .btn-lanjut:hover { color: #333; }

        .kosong-wrap { text-align: center; padding: 60px 32px; color: #aaa; }
        .kosong-wrap a { color: #1a1a1a; font-weight: 600; }

        footer { background: #1a1a1a; color: #888; text-align: center; padding: 20px; font-size: 0.82rem; }
        footer span { color: #e8c97e; }
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

<h2 class="page-title">🛒 Keranjang Belanja</h2>

<?php if ($pesan): ?>
    <div class="alert <?= $tipe ?>"><?= htmlspecialchars($pesan) ?></div>
<?php endif; ?>

<?php if (empty($items)): ?>
    <div class="kosong-wrap">
        <p style="font-size:3rem;">🛒</p>
        <p style="font-size:1.1rem; margin:16px 0;">Keranjang belanjamu masih kosong.</p>
        <a href="index.php">← Lihat Katalog Produk</a>
    </div>
<?php else: ?>

<div class="keranjang-wrap">
    <!-- TABEL ITEM -->
    <div class="tabel-wrap">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img class="item-img" src="../<?= htmlspecialchars($item->getFile()) ?>" alt="">
                            <div>
                                <div class="item-nama"><?= htmlspecialchars($item->getJenis()) ?></div>
                                <div class="item-kode"><?= htmlspecialchars($item->getKodeProduk()) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>Rp <?= number_format($item->getHarga(), 0, ',', '.') ?></td>
                    <td>
                        <form method="GET" action="proses_keranjang.php" class="qty-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="kode" value="<?= htmlspecialchars($item->getKodeProduk()) ?>">
                            <input type="hidden" name="redirect" value="keranjang">
                            <input type="number" name="jumlah" class="qty-input"
                                   value="<?= $item->getJumlah() ?>" min="1" max="99">
                            <button type="submit" class="btn-update">↻</button>
                        </form>
                    </td>
                    <td><strong>Rp <?= number_format($item->getSubtotal(), 0, ',', '.') ?></strong></td>
                    <td>
                        <a href="proses_keranjang.php?action=hapus&kode=<?= urlencode($item->getKodeProduk()) ?>&redirect=keranjang"
                           class="btn-hapus"
                           onclick="return confirm('Hapus produk ini dari keranjang?')">🗑</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- RINGKASAN -->
    <div class="ringkasan">
        <h3>Ringkasan Pesanan</h3>
        <?php foreach ($items as $item): ?>
            <div class="r-row">
                <span><?= htmlspecialchars($item->getJenis()) ?> ×<?= $item->getJumlah() ?></span>
                <span>Rp <?= number_format($item->getSubtotal(), 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
        <div class="r-row total">
            <span>Total</span>
            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
        </div>
        <a href="checkout.php" class="btn-checkout">Lanjut ke Checkout →</a>
        <a href="index.php" class="btn-lanjut">← Lanjut Belanja</a>
    </div>
</div>

<?php endif; ?>

<footer>
    &copy; 2026 <span>Lomoland</span> — Sistem Penjualan Kamera Lomo Online
</footer>
</body>
</html>
