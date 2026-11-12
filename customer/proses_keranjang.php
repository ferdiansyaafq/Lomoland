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
        $jumlah  = min(99, max(1, (int)$jumlah));

        $produk = $db->eksekusiQuerySelect(
            "SELECT kode_produk FROM produk WHERE kode_produk = '$esc_kode'"
        );
        if (empty($produk)) {
            $db->tutupKoneksi();
            return false;
        }

        // Cek apakah sudah ada di keranjang
        $cek = $db->eksekusiQuerySelect(
            "SELECT jumlah FROM temp WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );

        if (!empty($cek)) {
            $jumlah_baru = min(99, (int)$cek[0]['jumlah'] + $jumlah);
            $hasil = $db->eksekusiQueryUpdate(
                "UPDATE temp SET jumlah = $jumlah_baru WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
            );
        } else {
            $hasil = $db->eksekusiQueryUpdate(
                "INSERT INTO temp (id_temp, kode_produk, jumlah) VALUES ('$esc_id', '$esc_kode', $jumlah)"
            );
        }

        $db->tutupKoneksi();
        return $hasil !== false;
    }

    /** Update jumlah item di keranjang */
    public function update($kode_produk, $jumlah) {
        $db       = new KoneksiDatabase();
        $esc_id   = $db->conn->real_escape_string($this->id_temp);
        $esc_kode = $db->conn->real_escape_string($kode_produk);
        $jumlah   = min(99, max(1, (int)$jumlah));

        $hasil = $db->eksekusiQueryUpdate(
            "UPDATE temp SET jumlah = $jumlah WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );
        $db->tutupKoneksi();
        return $hasil !== false;
    }

    /** Hapus satu item dari keranjang */
    public function hapus($kode_produk) {
        $db       = new KoneksiDatabase();
        $esc_id   = $db->conn->real_escape_string($this->id_temp);
        $esc_kode = $db->conn->real_escape_string($kode_produk);
        $hasil = $db->eksekusiQueryUpdate(
            "DELETE FROM temp WHERE id_temp = '$esc_id' AND kode_produk = '$esc_kode'"
        );
        $db->tutupKoneksi();
        return $hasil !== false;
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
$action       = isset($_GET['action'])   ? $_GET['action']   : (isset($_POST['action']) ? $_POST['action'] : (isset($_POST['kode_produk']) ? 'tambah' : ''));
$kode_produk  = isset($_GET['kode'])     ? trim($_GET['kode']) : (isset($_POST['kode']) ? trim($_POST['kode']) : (isset($_POST['kode_produk']) ? trim($_POST['kode_produk']) : ''));
$jumlah       = isset($_GET['jumlah'])   ? (int)$_GET['jumlah'] : (isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1);
$redirect     = isset($_GET['redirect']) ? $_GET['redirect']  : (isset($_POST['redirect']) ? $_POST['redirect'] : 'keranjang');
$kode_balik   = isset($_GET['kode_balik']) ? $_GET['kode_balik'] : '';
$is_ajax      = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$proses = new ProsesKeranjang();

try {
    switch ($action) {
        case 'tambah':
            if ($kode_produk === '' || !$proses->tambah($kode_produk, $jumlah)) {
                if ($is_ajax) {
                    http_response_code(404);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
                    exit;
                }
                header("Location: index.php?status=produk_tidak_valid");
                exit;
            }
            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true]);
                exit;
            }
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
            if ($kode_produk === '' || !$proses->update($kode_produk, $jumlah)) {
                header("Location: keranjang.php?status=gagal");
                exit;
            }
            header("Location: keranjang.php?status=update");
            break;

        case 'hapus':
            if ($kode_produk === '' || !$proses->hapus($kode_produk)) {
                header("Location: keranjang.php?status=gagal");
                exit;
            }
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
} catch (mysqli_sql_exception $e) {
    error_log('Kesalahan proses keranjang: ' . $e->getMessage());
    if ($is_ajax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Keranjang gagal diperbarui. Silakan coba lagi.']);
        exit;
    }
    header("Location: keranjang.php?status=gagal");
    exit;
}
exit;
?>
