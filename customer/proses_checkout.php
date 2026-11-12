<?php
require_once __DIR__ . '/../config/config.php';

// Hanya boleh diakses via POST redirect dari checkout.php
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['checkout_data'])) {
    header("Location: checkout.php");
    exit;
}

$data = $_SESSION['checkout_data'];

// =============================================
// CLASS TCustomer (lengkap)
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
        return $this->nama !== '' && $this->email !== '' && $this->alamat !== ''
            && $this->provinsi !== '' && $this->kota !== '' && $this->telepon !== '';
    }
}

// =============================================
// CLASS ProsesTransaksi
// =============================================
class ProsesTransaksi {
    /**
     * Simpan customer baru & buat transaksi dari keranjang.
     * Mengembalikan kode_transaksi baru atau false jika gagal.
     */
    public function proses(TCustomer $customer, $id_temp) {
        $db = new KoneksiDatabase();

        // 1. Simpan data customer
        $nama     = $db->conn->real_escape_string($customer->getNama());
        $email    = $db->conn->real_escape_string($customer->getEmail());
        $alamat   = $db->conn->real_escape_string($customer->getAlamat());
        $provinsi = $db->conn->real_escape_string($customer->getProvinsi());
        $kota     = $db->conn->real_escape_string($customer->getKota());
        $telepon  = $db->conn->real_escape_string($customer->getTelepon());
        $via      = $db->conn->real_escape_string($customer->getVia());

        $sqlCustomer = "INSERT INTO customer (nama, email, alamat, provinsi, kota, telepon, via)
                        VALUES ('$nama', '$email', '$alamat', '$provinsi', '$kota', '$telepon', '$via')";
        $db->eksekusiQueryUpdate($sqlCustomer);
        $kode_customer = $db->conn->insert_id;

        if (!$kode_customer) {
            $db->tutupKoneksi();
            return false;
        }

        // 2. Ambil nomor transaksi berikutnya
        $res = $db->eksekusiQuerySelect("SELECT MAX(kode_transaksi) AS maks FROM transaksi");
        $kode_transaksi = (int)($res[0]['maks'] ?? 0) + 1;

        // 3. Ambil isi keranjang
        $esc_id = $db->conn->real_escape_string($id_temp);
        $items  = $db->eksekusiQuerySelect(
            "SELECT kode_produk, jumlah FROM temp WHERE id_temp = '$esc_id'"
        );

        if (empty($items)) {
            $db->tutupKoneksi();
            return false;
        }

        // 4. Insert setiap item ke transaksi
        foreach ($items as $item) {
            $kode_produk = $db->conn->real_escape_string($item['kode_produk']);
            $jumlah      = (int)$item['jumlah'];
            $db->eksekusiQueryUpdate(
                "INSERT INTO transaksi (kode_transaksi, kode_produk, jumlah, kode_customer)
                 VALUES ($kode_transaksi, '$kode_produk', $jumlah, $kode_customer)"
            );
        }

        // 5. Kosongkan keranjang
        $db->eksekusiQueryUpdate("DELETE FROM temp WHERE id_temp = '$esc_id'");

        // 6. Reset session id_temp agar keranjang benar-benar bersih
        $_SESSION['id_temp'] = session_id() . '_' . time();

        $db->tutupKoneksi();

        return [
            'kode_transaksi' => $kode_transaksi,
            'kode_customer'  => $kode_customer,
        ];
    }
}

// =============================================
// EKSEKUSI
// =============================================
$id_temp = $_SESSION['id_temp'];

$customer = new TCustomer();
$customer->setNama($data['nama']     ?? '');
$customer->setEmail($data['email']   ?? '');
$customer->setAlamat($data['alamat'] ?? '');
$customer->setProvinsi($data['provinsi'] ?? '');
$customer->setKota($data['kota']     ?? '');
$customer->setTelepon($data['telepon'] ?? '');
$customer->setVia($data['via']       ?? '');

if (!$customer->isValid()) {
    header("Location: checkout.php");
    exit;
}

$prosesCtrl = new ProsesTransaksi();
$hasil      = $prosesCtrl->proses($customer, $id_temp);

if ($hasil === false) {
    header("Location: checkout.php?error=gagal_transaksi");
    exit;
}

// Bersihkan data checkout dari session
unset($_SESSION['checkout_data']);

// Simpan info untuk halaman konfirmasi
$_SESSION['konfirmasi'] = [
    'kode_transaksi' => $hasil['kode_transaksi'],
    'kode_customer'  => $hasil['kode_customer'],
    'nama'           => $customer->getNama(),
    'email'          => $customer->getEmail(),
    'via'            => $customer->getVia(),
];

header("Location: konfirmasi.php");
exit;
?>
