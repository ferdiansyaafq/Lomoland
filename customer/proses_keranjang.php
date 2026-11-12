<?php
require_once __DIR__ . '/../config/config.php';

// =============================================
// CLASS ProsesKeranjang
// =============================================
class ProsesKeranjang {
    private $id_temp;

    public function __construct() {
        $this->id_temp = $_SESSION['id_temp'];
    }

    /** Tambah produk ke keranjang (atau tambah jumlah jika sudah ada) */
    public function tambah($kode_produk, $jumlah = 1) {
        $db      = new KoneksiDatabase();
        $esc_id  = $db->conn->real_escape_string($this->id_temp);
        $esc_kode = $db->conn->real_escape_string($kode_produk);
        $jumlah  = max(1, (int)$jumlah);

        // Cek apakah sudah ada di keranjang
        $cek = $db->eksekusiQuerySelect(
            "SELECT jumlah FROM temp WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );

        if (!empty($cek)) {
            $jumlah_baru = (int)$cek[0]['jumlah'] + $jumlah;
            $db->eksekusiQueryUpdate(
                "UPDATE temp SET jumlah = $jumlah_baru WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
            );
        } else {
            $db->eksekusiQueryUpdate(
                "INSERT INTO temp (id_temp, kode_produk, jumlah) VALUES ('$esc_id', '$esc_kode', $jumlah)"
            );
        }

        $db->tutupKoneksi();
    }

    /** Update jumlah item di keranjang */
    public function update($kode_produk, $jumlah) {
        $db       = new KoneksiDatabase();
        $esc_id   = $db->conn->real_escape_string($this->id_temp);
        $esc_kode = $db->conn->real_escape_string($kode_produk);
        $jumlah   = max(1, (int)$jumlah);

        $db->eksekusiQueryUpdate(
            "UPDATE temp SET jumlah = $jumlah WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );
        $db->tutupKoneksi();
    }

    /** Hapus satu item dari keranjang */
    public function hapus($kode_produk) {
        $db       = new KoneksiDatabase();
        $esc_id   = $db->conn->real_escape_string($this->id_temp);
        $esc_kode = $db->conn->real_escape_string($kode_produk);
        $db->eksekusiQueryUpdate(
            "DELETE FROM temp WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );
        $db->tutupKoneksi();
    }

    /** Kosongkan seluruh keranjang */
    public function kosongkan() {
        $db     = new KoneksiDatabase();
        $esc_id = $db->conn->real_escape_string($this->id_temp);
        $db->eksekusiQueryUpdate("DELETE FROM temp WHERE id_temp = '$esc_id'");
        $db->tutupKoneksi();
    }
}

// =============================================
// ROUTING AKSI
// =============================================
$action       = isset($_GET['action'])   ? $_GET['action']   : (isset($_POST['action']) ? $_POST['action'] : '');
$kode_produk  = isset($_GET['kode'])     ? trim($_GET['kode']) : (isset($_POST['kode']) ? trim($_POST['kode']) : '');
$jumlah       = isset($_GET['jumlah'])   ? (int)$_GET['jumlah'] : (isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1);
$redirect     = isset($_GET['redirect']) ? $_GET['redirect']  : (isset($_POST['redirect']) ? $_POST['redirect'] : 'keranjang');
$kode_balik   = isset($_GET['kode_balik']) ? $_GET['kode_balik'] : '';

$proses = new ProsesKeranjang();

switch ($action) {
    case 'tambah':
        $proses->tambah($kode_produk, max(1, $jumlah));
        if ($redirect === 'checkout') {
            header("Location: checkout.php");
        } elseif ($redirect === 'detail') {
            header("Location: detail_produk.php?kode=" . urlencode($kode_balik) . "&status=tambah");
        } elseif ($redirect === 'keranjang') {
            header("Location: keranjang.php?status=tambah");
        } else {
            header("Location: index.php?status=tambah");
        }
        break;

    case 'update':
        $proses->update($kode_produk, $jumlah);
        header("Location: keranjang.php?status=update");
        break;

    case 'hapus':
        $proses->hapus($kode_produk);
        header("Location: keranjang.php?status=hapus");
        break;

    case 'kosongkan':
        $proses->kosongkan();
        header("Location: keranjang.php?status=hapus");
        break;

    default:
        header("Location: keranjang.php");
        break;
}
exit;
?>
