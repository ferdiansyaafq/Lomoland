<?php
require_once __DIR__ . '/../config/config.php';

// Cek keranjang tidak kosong
$id_temp = $_SESSION['id_temp'];
$db_cek  = new KoneksiDatabase();
$esc_id  = $db_cek->conn->real_escape_string($id_temp);
$cek_res = $db_cek->eksekusiQuerySelect("SELECT COUNT(*) AS jml FROM temp WHERE id_temp = '$esc_id'");
$db_cek->tutupKoneksi();

if ((int)($cek_res[0]['jml'] ?? 0) === 0) {
    header("Location: keranjang.php?status=kosong");
    exit;
}

// =============================================
// CLASS TCustomer
// =============================================
class TCustomer {
    private $nama, $email, $alamat, $provinsi, $kota, $telepon, $via;

    public function setNama($v)     { $this->nama     = trim($v); }
    public function getNama()       { return $this->nama; }
    public function setEmail($v)    { $this->email    = trim($v); }
    public function getEmail()      { return $this->email; }
    public function setAlamat($v)   { $this->alamat   = trim($v); }
    public function getAlamat()     { return $this->alamat; }
    public function setProvinsi($v) { $this->provinsi = trim($v); }
    public function getProvinsi()   { return $this->provinsi; }
    public function setKota($v)     { $this->kota     = trim($v); }
    public function getKota()       { return $this->kota; }
    public function setTelepon($v)  { $this->telepon  = trim($v); }
    public function getTelepon()    { return $this->telepon; }
    public function setVia($v)      { $this->via      = trim($v); }
    public function getVia()        { return $this->via; }

    public function isValid() {
        return $this->nama     !== '' &&
               $this->email    !== '' &&
               $this->alamat   !== '' &&
               $this->provinsi !== '' &&
               $this->kota     !== '' &&
               $this->telepon  !== '';
    }
}

// =============================================
// CLASS CheckoutController — ambil ringkasan
// =============================================
class CheckoutController {
    public function ambilRingkasan($id_temp) {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($id_temp);
        $sql = "SELECT t.kode_produk, t.jumlah, p.jenis, p.harga
                FROM temp t
                JOIN produk p ON t.kode_produk = p.kode_produk
                WHERE t.id_temp = '$esc'
                ORDER BY t.kode_produk ASC";
        $rows = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        return $rows;
    }

    public function hitungTotal(array $rows) {
        $total = 0;
        foreach ($rows as $r) $total += (int)$r['harga'] * (int)$r['jumlah'];
        return $total;
    }

    public function hitungKeranjang($id_temp) {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($id_temp);
        $res = $db->eksekusiQuerySelect("SELECT SUM(jumlah) AS total FROM temp WHERE id_temp = '$esc'");
        $db->tutupKoneksi();
        return (int)($res[0]['total'] ?? 0);
    }
}

$ctrl   = new CheckoutController();
$items  = $ctrl->ambilRingkasan($id_temp);
$total  = $ctrl->hitungTotal($items);
$jumlah_keranjang = $ctrl->hitungKeranjang($id_temp);

$error = '';
// Jika form dikirim tapi gagal validasi — tampilkan kembali
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi ringan — jika lolos, forward ke proses_checkout.php
    $c = new TCustomer();
    $c->setNama($_POST['nama'] ?? '');
    $c->setEmail($_POST['email'] ?? '');
    $c->setAlamat($_POST['alamat'] ?? '');
    $c->setProvinsi($_POST['provinsi'] ?? '');
    $c->setKota($_POST['kota'] ?? '');
    $c->setTelepon($_POST['telepon'] ?? '');
    $c->setVia($_POST['via'] ?? '');

    if (!$c->isValid()) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Simpan ke session sementara lalu forward
        $_SESSION['checkout_data'] = $_POST;
        header("Location: proses_checkout.php");
        exit;
    }
}

$post = $_POST;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lomoland</title>
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

        /* STEPS */
        .steps {
            display: flex; justify-content: center; gap: 0;
            padding: 24px 32px 0; max-width: 600px; margin: 0 auto;
        }
        .step {
            flex: 1; text-align: center; font-size: 0.8rem;
            padding: 10px 0; border-bottom: 3px solid #ddd; color: #aaa;
        }
        .step.active { border-bottom-color: #e8c97e; color: #1a1a1a; font-weight: 700; }
        .step.done   { border-bottom-color: #4caf50; color: #4caf50; }

        /* LAYOUT */
        .checkout-wrap {
            max-width: 900px; margin: 24px auto; padding: 0 32px 60px;
            display: flex; gap: 24px; flex-wrap: wrap;
        }

        /* FORM */
        .form-card {
            flex: 1; min-width: 300px;
            background: #fff; border-radius: 12px; padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }
        .form-card h3 { margin-bottom: 20px; font-size: 1.1rem; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #ddd; border-radius: 8px;
            font-size: 0.95rem; font-family: inherit;
            transition: border-color .2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #1a1a1a; outline: none;
        }
        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; }

        .alert-error {
            background: #fde8e8; color: #c0392b;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 16px; font-size: 0.9rem; font-weight: 600;
        }

        /* RINGKASAN */
        .ringkasan {
            width: 260px; min-width: 220px;
            background: #fff; border-radius: 12px; padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07); height: fit-content;
        }
        .ringkasan h3 { font-size: 1rem; margin-bottom: 16px; }
        .r-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.88rem; }
        .r-row .nama { max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .r-row.total { font-weight: 800; font-size: 1.1rem; border-top: 1px solid #eee; padding-top: 12px; margin-top: 6px; color: #c0392b; }

        .btn-submit {
            width: 100%; padding: 14px;
            background: #1a1a1a; color: #e8c97e;
            border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: background .2s; margin-top: 10px;
        }
        .btn-submit:hover { background: #333; }
        .btn-back { display: block; text-align: center; margin-top: 10px; color: #888; font-size: 0.85rem; text-decoration: none; }
        .btn-back:hover { color: #333; }

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

<!-- STEPS -->
<div class="steps">
    <div class="step done">1. Keranjang</div>
    <div class="step active">2. Data Pengiriman</div>
    <div class="step">3. Konfirmasi</div>
</div>

<div class="checkout-wrap">
    <!-- FORM DATA CUSTOMER -->
    <div class="form-card">
        <h3>📋 Data Penerima & Pengiriman</h3>

        <?php if ($error): ?>
            <div class="alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" required
                       placeholder="Masukkan nama lengkap"
                       value="<?= htmlspecialchars($post['nama'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       placeholder="email@contoh.com"
                       value="<?= htmlspecialchars($post['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="telepon">Nomor Telepon *</label>
                <input type="tel" id="telepon" name="telepon" required
                       placeholder="08xxxxxxxxxx"
                       value="<?= htmlspecialchars($post['telepon'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Lengkap *</label>
                <textarea id="alamat" name="alamat" rows="3" required
                          placeholder="Jl. ..., No. ..., RT/RW ..."><?= htmlspecialchars($post['alamat'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="provinsi">Provinsi *</label>
                    <input type="text" id="provinsi" name="provinsi" required
                           placeholder="Contoh: Jawa Timur"
                           value="<?= htmlspecialchars($post['provinsi'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="kota">Kota / Kabupaten *</label>
                    <input type="text" id="kota" name="kota" required
                           placeholder="Contoh: Surabaya"
                           value="<?= htmlspecialchars($post['kota'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="via">Metode Pengiriman</label>
                <select id="via" name="via">
                    <option value="" <?= empty($post['via']) ? 'selected' : '' ?>>— Pilih Pengiriman —</option>
                    <option value="JNE"           <?= (($post['via'] ?? '') === 'JNE')             ? 'selected' : '' ?>>JNE</option>
                    <option value="JNT"           <?= (($post['via'] ?? '') === 'JNT')             ? 'selected' : '' ?>>J&T Express</option>
                    <option value="SiCepat"       <?= (($post['via'] ?? '') === 'SiCepat')         ? 'selected' : '' ?>>SiCepat</option>
                    <option value="Pos Indonesia" <?= (($post['via'] ?? '') === 'Pos Indonesia')   ? 'selected' : '' ?>>Pos Indonesia</option>
                    <option value="Ambil di Toko" <?= (($post['via'] ?? '') === 'Ambil di Toko')   ? 'selected' : '' ?>>Ambil di Toko</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Lanjutkan ke Konfirmasi →</button>
        </form>
        <a href="keranjang.php" class="btn-back">← Kembali ke Keranjang</a>
    </div>

    <!-- RINGKASAN PESANAN -->
    <div class="ringkasan">
        <h3>Ringkasan Pesanan</h3>
        <?php foreach ($items as $r): ?>
            <div class="r-row">
                <span class="nama"><?= htmlspecialchars($r['jenis']) ?> ×<?= (int)$r['jumlah'] ?></span>
                <span>Rp <?= number_format((int)$r['harga'] * (int)$r['jumlah'], 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
        <div class="r-row total">
            <span>Total</span>
            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 <span>Lomoland</span> — Sistem Penjualan Kamera Lomo Online
</footer>
</body>
</html>
