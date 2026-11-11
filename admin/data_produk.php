<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

class TKategori {
    private $id_kategori;
    private $nama_kategori;

    public function setId_kategori($id_kategori) { $this->id_kategori = (int)$id_kategori; }
    public function getIdKategori() { return $this->id_kategori; }

    public function setNama_kategori($nama_kategori) { $this->nama_kategori = $nama_kategori; }
    public function getNamaKategori() { return $this->nama_kategori; }
}

class TProduk {
    private $kode_produk;
    private $jenis;
    private $file;
    private $id_kategori;
    private $harga;

    public function setKode_produk($kode_produk) { $this->kode_produk = $kode_produk; }
    public function getKodeProduk() { return $this->kode_produk; }
    public function getKode_produk() { return $this->kode_produk; }

    public function setJenis($jenis) { $this->jenis = $jenis; }
    public function getJenis() { return $this->jenis; }

    public function setFile($file) { $this->file = $file; }
    public function getFile() { return $this->file; }

    public function setId_Kategori($id_kategori) { $this->id_kategori = (int)$id_kategori; }
    public function setIdKategori($id_kategori) { $this->id_kategori = (int)$id_kategori; }
    public function getId_kategori() { return $this->id_kategori; }

    public function setHarga($harga) { $this->harga = (int)$harga; }
    public function getHarga() { return $this->harga; }
}

class EditDataProduk {
    public function cariProdukById($kode_produk) {
        $db = new KoneksiDatabase();
        $kode_escaped = $db->conn->real_escape_string($kode_produk);
        $sql = "SELECT p.*, k.nama_kategori 
                FROM produk p 
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                WHERE p.kode_produk = '$kode_escaped'";
        $hasil = $db->eksekusiQuerySelect($sql);

        $produk = null;
        if (!empty($hasil)) {
            $row = $hasil[0];
            $produk = new TProduk();
            $produk->setKode_produk($row['kode_produk']);
            $produk->setJenis($row['jenis']);
            $produk->setFile($row['file']);
            $produk->setId_Kategori($row['id_kategori']);
            $produk->setHarga($row['harga']);
        }

        $db->tutupKoneksi();
        return $produk;
    }

    public function mengubahProduk(TProduk $produk) {
        $db = new KoneksiDatabase();
        $kode        = $db->conn->real_escape_string($produk->getKodeProduk());
        $jenis       = $db->conn->real_escape_string($produk->getJenis());
        $file        = $db->conn->real_escape_string($produk->getFile());
        $id_kategori = (int)$produk->getId_kategori();
        $harga       = (int)$produk->getHarga();

        $sql = "UPDATE produk 
                SET jenis = '$jenis', file = '$file', id_kategori = $id_kategori, harga = $harga 
                WHERE kode_produk = '$kode'";

        $status = $db->eksekusiQueryUpdate($sql);
        $db->tutupKoneksi();
        return $status;
    }

    public function menghapusProduk($kode_produk) {
        $db = new KoneksiDatabase();
        $kode_escaped = $db->conn->real_escape_string($kode_produk);
        
        $sqlCek = "SELECT file FROM produk WHERE kode_produk = '$kode_escaped'";
        $res = $db->eksekusiQuerySelect($sqlCek);
        if (!empty($res) && !empty($res[0]['file'])) {
            $filePath = __DIR__ . '/../' . $res[0]['file'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $sql = "DELETE FROM produk WHERE kode_produk = '$kode_escaped'";
        $status = $db->eksekusiQueryUpdate($sql);
        $db->tutupKoneksi();
        return $status;
    }

    public function ambilSemuaProduk() {
        $db = new KoneksiDatabase();
        $sql = "SELECT p.kode_produk, p.jenis, p.file, p.id_kategori, p.harga, k.nama_kategori 
                FROM produk p 
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                ORDER BY p.kode_produk ASC";
        $data = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        return $data;
    }

    public function ambilSemuaKategori() {
        $db = new KoneksiDatabase();
        $sql = "SELECT * FROM kategori ORDER BY id_kategori ASC";
        $data = $db->eksekusiQuerySelect($sql);
        $db->tutupKoneksi();
        return $data;
    }
}

$produkCtrl = new EditDataProduk();
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['kode'])) {
    if ($produkCtrl->menghapusProduk($_GET['kode'])) {
        header("Location: data_produk.php?status=sukses_hapus");
        exit;
    } else {
        header("Location: data_produk.php?status=gagal_hapus");
        exit;
    }
}

$pesan = '';
$tipe_pesan = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'sukses_update':
            $pesan = "Data produk telah berhasil diubah!";
            $tipe_pesan = "sukses";
            break;
        case 'sukses_hapus':
            $pesan = "Data produk telah berhasil dihapus!";
            $tipe_pesan = "sukses";
            break;
        case 'data_tidak_valid':
            $pesan = "Data produk yang dimasukkan tidak valid / tidak lengkap!";
            $tipe_pesan = "error";
            break;
        case 'gagal_update':
        case 'gagal_hapus':
            $pesan = "Terjadi kegagalan proses di database!";
            $tipe_pesan = "error";
            break;
    }
}

$edit_data = null;
if (isset($_GET['edit']) && $_GET['edit'] !== '') {
    $produkObj = $produkCtrl->cariProdukById($_GET['edit']);
    if ($produkObj) {
        $daftarKat = $produkCtrl->ambilSemuaKategori();
        $namaKat = '';
        foreach ($daftarKat as $k) {
            if ($k['id_kategori'] == $produkObj->getId_kategori()) {
                $namaKat = $k['nama_kategori'];
                break;
            }
        }

        $edit_data = [
            'kode_produk'   => $produkObj->getKodeProduk(),
            'jenis'         => $produkObj->getJenis(),
            'file'          => $produkObj->getFile(),
            'id_kategori'   => $produkObj->getId_kategori(),
            'nama_kategori' => $namaKat,
            'harga'         => $produkObj->getHarga()
        ];
    }
}

$daftar_kategori = $produkCtrl->ambilSemuaKategori();
$daftar_produk   = $produkCtrl->ambilSemuaProduk();
$total_data      = count($daftar_produk);
$admin_active    = $_SESSION['user'];
?>
