<?php
require_once __DIR__ . '/data_produk.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: data_produk.php");
    exit;
}

$kode_produk = isset($_POST['kode_produk']) ? trim($_POST['kode_produk']) : '';
$jenis       = isset($_POST['jenis']) ? trim($_POST['jenis']) : '';
$id_kategori = isset($_POST['id_kategori']) ? (int) $_POST['id_kategori'] : 0;

// Bersihkan format harga (contoh: "7.000.000" atau "7000000")
$harga_raw   = isset($_POST['harga']) ? str_replace(['.', ',', ' ', 'Rp', 'rp'], '', trim($_POST['harga'])) : '0';
$harga       = (int)$harga_raw;

if ($kode_produk === '' || $jenis === '' || $id_kategori <= 0 || $harga <= 0) {
    header("Location: data_produk.php?edit=" . urlencode($kode_produk) . "&status=data_tidak_valid");
    exit;
}

$produkCtrl = new EditDataProduk();
$produkLama = $produkCtrl->cariProdukById($kode_produk);

$file_path = $produkLama ? $produkLama->getFile() : 'assets/images/Lomo LC-A.png';

$input_file = isset($_FILES['file_foto']) ? $_FILES['file_foto'] : (isset($_FILES['file']) ? $_FILES['file'] : (isset($_FILES['fupload']) ? $_FILES['fupload'] : null));

if ($input_file && isset($input_file['error']) && $input_file['error'] === UPLOAD_ERR_OK) {
    $target_dir = __DIR__ . '/../assets/images/';
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    $filename    = basename($input_file['name']);
    $target_file = $target_dir . $filename;

    if (@move_uploaded_file($input_file['tmp_name'], $target_file)) {
        $file_path = "assets/images/" . $filename;
    }
}

$produkUpdate = new TProduk();
$produkUpdate->setKode_produk($kode_produk);
$produkUpdate->setJenis($jenis);
$produkUpdate->setFile($file_path);
$produkUpdate->setId_Kategori($id_kategori);
$produkUpdate->setHarga($harga);

if ($produkCtrl->mengubahProduk($produkUpdate)) {
    header("Location: data_produk.php?status=sukses_update&edit=" . urlencode($kode_produk));
    exit;
} else {
    header("Location: data_produk.php?edit=" . urlencode($kode_produk) . "&status=gagal_update");
    exit;
}
