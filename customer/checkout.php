<?php
require_once __DIR__ . '/../config/config.php';

$id_temp = $_SESSION['id_temp'];
$db_cek  = new KoneksiDatabase();
$esc_id  = $db_cek->conn->real_escape_string($id_temp);
$cek_res = $db_cek->eksekusiQuerySelect("SELECT COUNT(*) AS jml FROM temp WHERE id_temp = '$esc_id'");
$db_cek->tutupKoneksi();

if ((int)($cek_res[0]['jml'] ?? 0) === 0) {
    header("Location: keranjang.php?status=kosong");
    exit;
}

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

class CheckoutController {
    public function ambilRingkasan($id_temp) {
        $db  = new KoneksiDatabase();
        $esc = $db->conn->real_escape_string($id_temp);
        $sql = "SELECT t.kode_produk, t.jumlah, p.jenis, p.harga, p.file, k.nama_kategori
                FROM temp t
                JOIN produk p ON t.kode_produk = p.kode_produk
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
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

// Biaya ongkir per metode
$ongkir_map = [
    'JNE Express' => 20000,
    'Reguler'     => 18000,
    'JNT Kargo'   => 22000,
    'Pos Indonesia' => 15000,
    'Ambil di Toko' => 0,
];

$error = isset($_GET['error']) && $_GET['error'] === 'gagal_transaksi'
    ? 'Transaksi gagal disimpan. Keranjang Anda tetap tersedia; silakan coba lagi.'
    : '';
$post  = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $post  = $_POST;
    } else {
        $_SESSION['checkout_data'] = $_POST;
        header("Location: proses_checkout.php");
        exit;
    }
}

// Generate draft TRX number
$trx_draft = 'TRX-DRAFT-' . rand(10000, 99999);
$hari_ini   = date('l, d M Y');
$hari_map   = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis',
                'Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
$bulan_map  = ['Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','May'=>'Mei','Jun'=>'Jun',
               'Jul'=>'Jul','Aug'=>'Agu','Sep'=>'Sep','Oct'=>'Okt','Nov'=>'Nov','Dec'=>'Des'];
$hari_en = date('l'); $tgl = date('d'); $bln = date('M'); $thn = date('Y');
$hari_id = $hari_map[$hari_en] ?? $hari_en;
$bln_id  = $bulan_map[$bln] ?? $bln;
$tanggal_id = "$hari_id, $tgl $bln_id $thn";

$via_selected = $post['via'] ?? 'JNE Express';
$ongkir_selected = $ongkir_map[$via_selected] ?? 20000;
$total_dengan_ongkir = $total + $ongkir_selected;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Lomoland Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #152259;
            --navy-hover: #1e3a8a;
            --bg: #faf9ff;
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
        .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: var(--white); border-bottom: 1px solid var(--border);
            padding: 12px 32px; display: flex; align-items: center; justify-content: space-between;
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
        .content { flex: 1; padding: 28px 32px; }
        footer { background: var(--white); border-top: 1px solid var(--border); text-align: center; padding: 16px; font-size: 0.78rem; color: var(--muted); }

        /* Page header */
        .page-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 5px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .page-header-left {}
        .page-subtitle { font-size: 0.88rem; color: var(--muted); margin-top: 2px; }
        .progress-wrap { text-align: right; }
        .progress-label { font-size: 0.75rem; color: var(--muted); margin-bottom: 5px; font-weight: 600; }
        .progress-bar-bg { width: 180px; height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-left: auto; }
        .progress-bar-fill { height: 100%; width: 66%; background: var(--accent); border-radius: 10px; }

        /* Alert */
        .alert-error { background: #fff0f0; color: var(--danger); border: 1px solid #fecaca; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 500; display: flex; align-items: center; gap: 10px; }

        /* Two-column layout */
        .checkout-layout { display: grid; grid-template-columns: minmax(340px, 0.72fr) minmax(580px, 1.28fr); gap: 24px; align-items: start; }
        .panel-left  { min-width: 0; display: flex; flex-direction: column; gap: 20px; }
        .panel-right { min-width: 0; }

        /* Cards */
        .card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 1px 4px rgba(21,34,89,0.04); overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .card-header-title { font-family: Georgia, 'Times New Roman', serif; font-size: 1.15rem; font-weight: 700; color: #17233c; flex: 1; }
        .card-body { padding: 24px; }
        .order-card .card-header, .order-summary { background: #f1f2ff; }
        .order-card .card-header { padding: 18px 22px; }
        .order-card > .card-body { padding: 0 22px; }
        .checkout-card-icon { width: 36px; height: 36px; display: grid; place-items: center; flex: 0 0 auto; color: #fff; background: #082c7d; border-radius: 11px; }
        .order-heading { min-width: 0; }
        .order-heading .card-header-title { display: block; font-size: 1rem; }
        .order-count { display: block; margin-top: 3px; color: #4b5563; font-size: 0.82rem; }

        /* Gold badge */
        .badge-gold { display: inline-block; background: #fff; color: var(--accent); border: 1px solid var(--border); font-size: 0.72rem; font-weight: 700; padding: 6px 12px; border-radius: 10px; white-space: nowrap; }
        .badge-cat  { display: inline-block; background: transparent; color: #737782; font-size: 0.7rem; font-weight: 700; padding: 0; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 2px; }
        .badge-green { display: inline-block; background: #f0fdf4; color: var(--success); font-size: 0.65rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
        .badge-trx  { display: inline-block; background: #f1f5f9; color: var(--muted); font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 8px; font-family: monospace; }
        .badge-ship { display: inline-flex; align-items: center; gap: 4px; background: #eff6ff; color: var(--accent); font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: 8px; }

        /* Subheader items count */
        .items-count { font-size: 0.8rem; color: var(--muted); margin-bottom: 14px; font-weight: 500; }

        /* Product list in left panel */
        .prod-list { display: flex; flex-direction: column; margin-bottom: 0; }
        .prod-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .prod-thumb { width: 48px; height: 48px; border-radius: 11px; object-fit: contain; padding: 3px; border: 1px solid var(--border); background: #fff; flex-shrink: 0; }
        .prod-thumb-ph { width: 48px; height: 48px; border-radius: 11px; border: 1px solid var(--border); background: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .prod-info { flex: 1; min-width: 0; }
        .prod-info-name { font-size: 0.88rem; font-weight: 700; color: var(--text); line-height: 1.3; }
        .prod-info-qty { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }
        .prod-info-price { font-size: 0.9rem; font-weight: 700; color: var(--navy); white-space: nowrap; }
        .order-summary { margin: 0 -22px; padding: 12px 22px 18px; }

        /* Summary rows */
        .sum-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 7px 0; font-size: 0.85rem; }
        .sum-row + .sum-row { border-top: 0; }
        .sum-label { color: var(--muted); }
        .sum-value { font-weight: 600; color: var(--text); }
        .sum-row.total-row { padding-top: 14px; margin-top: 6px; border-top: 1px solid #d5d8e8; }
        .sum-row.total-row .sum-label { font-size: 1rem; font-weight: 700; color: var(--text); }
        .sum-row.total-row .sum-value { font-size: 1.35rem; font-weight: 800; color: var(--accent); }

        /* Guarantee card */
        .guarantee-row { display: flex; align-items: flex-start; gap: 12px; }
        .guarantee-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 14px; background: #e0e7ff; font-size: 1.6rem; flex-shrink: 0; }
        .guarantee-title { font-size: 0.88rem; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
        .guarantee-desc { font-size: 0.78rem; color: var(--muted); line-height: 1.5; }

        /* Form */
        .form-subtitle { font-size: 0.8rem; color: var(--muted); margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; }
        .form-label { display: block; font-size: 0.9rem; font-weight: 700; color: #17233c; margin-bottom: 8px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); display: flex; pointer-events: none; }
        .textarea-icon { position: absolute; left: 12px; top: 14px; color: var(--muted); display: flex; pointer-events: none; }
        .form-control {
            width: 100%; min-height: 44px; padding: 10px 14px 10px 42px;
            border: 1.5px solid #c9cde0; border-radius: 11px;
            font-family: inherit; font-size: 0.92rem; color: var(--text);
            background: #f2f3ff; transition: border-color 0.15s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); background: var(--white); }
        .form-control.no-icon { padding-left: 42px; }
        textarea.form-control { resize: vertical; min-height: 76px; }

        /* Shipping cards */
        .shipping-label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text); margin-bottom: 10px; }
        .shipping-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .ship-card {
            display: flex; flex-wrap: wrap; align-content: flex-start; align-items: flex-start; gap: 8px;
            position: relative; min-width: 0; min-height: 138px;
            border: 1.5px solid #d5d8e8; border-radius: 14px;
            padding: 16px 18px; cursor: pointer; transition: all 0.18s;
            background: #f4f4ff;
        }
        .ship-card:hover { border-color: #93c5fd; background: #f0f9ff; }
        .ship-card.selected { border: 2px solid #075be8; background: #f8f9ff; }
        .ship-radio { width: 24px; height: 24px; border-radius: 50%; border: 1.8px solid #858a99; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; position: absolute; top: 14px; right: 14px; }
        .ship-card.selected .ship-radio { border-color: #075be8; background: #fff; }
        .ship-check { display: none; }
        .ship-card.selected .ship-check { display: block; }
        .ship-icon { display: none; }
        .ship-info { flex: 0 0 100%; min-width: 0; padding-right: 26px; }
        .ship-name { font-family: Georgia, 'Times New Roman', serif; font-size: 0.84rem; font-weight: 700; color: var(--navy); }
        .ship-desc { font-size: 0.75rem; color: var(--muted); margin-top: 1px; }
        .ship-right { text-align: left; margin-top: auto; }
        .ship-price { font-size: 0.9rem; font-weight: 800; color: var(--navy); }
        .ship-eta { font-size: 0.72rem; color: var(--muted); margin-top: 2px; }

        /* Submit button */
        .btn-submit {
            display: block; width: min(300px, 100%); padding: 16px 22px; margin-left: auto;
            background: var(--navy); color: #fff; border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700; font-family: inherit;
            cursor: pointer; transition: background 0.15s; margin-top: 20px;
            letter-spacing: 0.3px;
        }
        .btn-submit:hover { background: var(--navy-hover); }
        .transaction-header { display: block; padding: 30px 34px 28px; }
        .transaction-heading { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .transaction-heading .card-header-title { flex: 0 1 auto; font-size: 1.3rem; }
        .transaction-heading .badge-ship { margin-left: auto; }
        .transaction-header .form-subtitle { margin: 12px 0 0; max-width: 620px; line-height: 1.5; font-size: 0.9rem; }
        .transaction-body { padding: 34px; }
        @media (max-width: 1100px) {
            .checkout-layout { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); gap: 20px; }
            .content { padding-right: 24px; padding-left: 24px; }
            .transaction-header, .transaction-body { padding-right: 24px; padding-left: 24px; }
        }
        @media (max-width: 860px) {
            .checkout-layout { grid-template-columns: minmax(0, 1fr); }
            .panel-left { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 0.8fr); align-items: start; }
            .panel-left > .card:first-child { grid-row: span 2; }
        }
        @media (max-width: 680px) {
            .content { padding: 22px 16px; }
            .page-header { align-items: flex-start; flex-direction: column; }
            .progress-wrap { text-align: left; width: 100%; }
            .progress-bar-bg { margin-left: 0; }
            .panel-left { display: flex; }
            .shipping-cards { grid-template-columns: 1fr; }
            .ship-card { min-height: 110px; }
            .form-row { flex-direction: column; gap: 0; }
            .topbar { padding: 12px 16px; flex-wrap: wrap; gap: 10px; }
            .topbar-right { margin-left: auto; }
            .btn-secure { display: none; }
        }
    </style>
</head>
<body>
<div class="app-layout">

    <!-- Sidebar -->
    <?php $active_nav = 'produk'; include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <div class="main-wrap">

        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb">
                <a href="beranda.php">Beranda</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="index.php">Produk</a>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span>Transaksi</span>
            </div>
            <div class="topbar-right">
                <span class="btn-secure">
                    🔒 Checkout Aman &amp; Terenkripsi
                </span>
                <div class="topbar-date">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?= htmlspecialchars($tanggal_id) ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">

            <!-- Page header -->
            <div class="page-header">
                <div class="page-header-left">
                    <div class="page-subtitle">Lengkapi data pemesanan untuk konfirmasi dan proses order.</div>
                </div>
                <div class="progress-wrap">
                    <div class="progress-label">Tahap 2 dari 3: Formulir Pengiriman</div>
                    <div class="progress-bar-bg"><div class="progress-bar-fill"></div></div>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Two-column layout -->
            <div class="checkout-layout">

                <!-- ===== PANEL KIRI ===== -->
                <div class="panel-left">

                    <!-- Card 1: Keranjang -->
                    <div class="card order-card">
                        <?php $total_items = array_sum(array_column($items, 'jumlah')); ?>
                        <div class="card-header order-card-header">
                            <span class="checkout-card-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </span>
                            <span class="order-heading">
                                <span class="card-header-title">Keranjang &amp; Produk Terpilih</span>
                                <span class="order-count"><?= count($items) ?> Barang (Total <?= $total_items ?> Items)</span>
                            </span>
                            <span class="badge-gold">Lomoland ID</span>
                        </div>
                        <div class="card-body">
                            <div class="prod-list">
                                <?php foreach ($items as $r): ?>
                                <div class="prod-row">
                                    <?php if (!empty($r['file'])): ?>
                                        <img src="../<?= htmlspecialchars(ltrim($r['file'], '/')) ?>" alt="<?= htmlspecialchars($r['jenis']) ?>" class="prod-thumb">
                                    <?php else: ?>
                                        <div class="prod-thumb-ph">📦</div>
                                    <?php endif; ?>
                                    <div class="prod-info">
                                        <?php if (!empty($r['nama_kategori'])): ?>
                                            <div><span class="badge-cat"><?= htmlspecialchars($r['nama_kategori']) ?></span></div>
                                        <?php endif; ?>
                                        <div class="prod-info-name"><?= htmlspecialchars($r['jenis']) ?></div>
                                        <div class="prod-info-qty"><?= (int)$r['jumlah'] ?> × Rp <?= number_format((int)$r['harga'], 0, ',', '.') ?></div>
                                    </div>
                                    <div>
                                        <div class="prod-info-price">Rp <?= number_format((int)$r['harga'] * (int)$r['jumlah'], 0, ',', '.') ?></div>
                                        <div style="text-align:right; margin-top:4px;"><span class="badge-green">Tersedia</span></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-summary">
                                <div class="sum-row">
                                    <span class="sum-label">Subtotal Produk</span>
                                    <span class="sum-value" id="js-subtotal">Rp <?= number_format($total, 0, ',', '.') ?></span>
                                </div>
                                <div class="sum-row">
                                    <span class="sum-label">Biaya Ongkir (<span id="js-via-label"><?= htmlspecialchars($via_selected) ?></span>)</span>
                                    <span class="sum-value" id="js-ongkir">Rp <?= number_format($ongkir_selected, 0, ',', '.') ?></span>
                                </div>
                                <div class="sum-row total-row">
                                    <span class="sum-label">Total Pesanan</span>
                                    <span class="sum-value" id="js-total">Rp <?= number_format($total_dengan_ongkir, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Garansi -->
                    <div class="card">
                        <div class="card-body">
                            <div class="guarantee-row">
                                <div class="guarantee-icon">🛡️</div>
                                <div>
                                    <div class="guarantee-title">Garansi Lomoland Original 100%</div>
                                    <div class="guarantee-desc">Data customer disimpan aman dan hanya digunakan untuk keperluan pencetakan resi resmi &amp; invoice.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ===== PANEL KANAN ===== -->
                <div class="panel-right">
                    <div class="card">
                        <div class="card-header transaction-header">
                            <div class="transaction-heading">
                                <span class="card-header-title">Formulir Transaksi</span>
                                <span class="badge-trx">#<?= htmlspecialchars($trx_draft) ?></span>
                                <span class="badge-ship">📦 Pengiriman Domestik</span>
                            </div>
                            <p class="form-subtitle">Pastikan informasi alamat penerima dan kontak sudah sesuai untuk pengiriman ekspedisi.</p>
                        </div>
                        <div class="card-body transaction-body">

                            <form method="POST" action="">
                                <!-- Nama Lengkap -->
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap Penerima *</label>
                                    <div class="input-wrap">
                                        <span class="input-icon">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </span>
                                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap penerima" value="<?= htmlspecialchars($post['nama'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- Email + Telepon -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Alamat E-mail *</label>
                                        <div class="input-wrap">
                                            <span class="input-icon">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                            </span>
                                            <input type="email" name="email" class="form-control" placeholder="email@contoh.com" value="<?= htmlspecialchars($post['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">No. Handphone / WhatsApp *</label>
                                        <div class="input-wrap">
                                            <span class="input-icon">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.65 3.5 2 2 0 0 1 3.62 1.5h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.21a16 16 0 0 0 6.29 6.29l1.88-1.88a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                            </span>
                                            <input type="tel" name="telepon" class="form-control" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($post['telepon'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alamat -->
                                <div class="form-group">
                                    <label class="form-label">Alamat Lengkap Pengiriman *</label>
                                    <div class="input-wrap">
                                        <span class="textarea-icon">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        </span>
                                        <textarea name="alamat" class="form-control" placeholder="Jl. Contoh No. 1, RT/RW, Kelurahan, Kecamatan..." required><?= htmlspecialchars($post['alamat'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Provinsi + Kota -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Provinsi *</label>
                                        <div class="input-wrap">
                                            <span class="input-icon">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/></svg>
                                            </span>
                                            <input type="text" name="provinsi" class="form-control" placeholder="Provinsi" value="<?= htmlspecialchars($post['provinsi'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Kota / Kabupaten *</label>
                                        <div class="input-wrap">
                                            <span class="input-icon">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="10" width="18" height="11" rx="1"/><path d="M7 10V6h10v4M9 14v2M15 14v2M12 14v2M2 21h20"/></svg>
                                            </span>
                                            <input type="text" name="kota" class="form-control" placeholder="Kota / Kabupaten" value="<?= htmlspecialchars($post['kota'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Metode Pengiriman -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="shipping-label">Metode Pengiriman / Ekspedisi *</label>
                                    <input type="hidden" name="via" id="via-input" value="<?= htmlspecialchars($via_selected) ?>">
                                    <div class="shipping-cards">

                                        <!-- JNE Express -->
                                        <div class="ship-card <?= $via_selected === 'JNE Express' ? 'selected' : '' ?>" data-via="JNE Express" data-ongkir="20000" onclick="selectShipping(this)">
                                            <div class="ship-radio">
                                                <svg class="ship-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#075be8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                            <div class="ship-icon">🚚</div>
                                            <div class="ship-info">
                                                <div class="ship-name">JNE Express</div>
                                                <div class="ship-desc">Normal</div>
                                            </div>
                                            <div class="ship-right">
                                                <div class="ship-price">Rp 20.000</div>
                                                <div class="ship-eta">Estimasi 4 - 6 Hari</div>
                                            </div>
                                        </div>

                                        <!-- Reguler REG -->
                                        <div class="ship-card <?= $via_selected === 'Reguler' ? 'selected' : '' ?>" data-via="Reguler" data-ongkir="18000" onclick="selectShipping(this)">
                                            <div class="ship-radio">
                                                <svg class="ship-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#075be8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                            <div class="ship-icon">📦</div>
                                            <div class="ship-info">
                                                <div class="ship-name">Reguler</div>
                                                <div class="ship-desc">Reguler Service (REG)</div>
                                            </div>
                                            <div class="ship-right">
                                                <div class="ship-price">Rp 18.000</div>
                                                <div class="ship-eta">Estimasi 7 - 10 Hari</div>
                                            </div>
                                        </div>

                                        <!-- JNT Kargo -->
                                        <div class="ship-card <?= $via_selected === 'JNT Kargo' ? 'selected' : '' ?>" data-via="JNT Kargo" data-ongkir="22000" onclick="selectShipping(this)">
                                            <div class="ship-radio">
                                                <svg class="ship-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#075be8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                            <div class="ship-icon">⚡</div>
                                            <div class="ship-info">
                                                <div class="ship-name">JNT Kargo</div>
                                                <div class="ship-desc">Best Cargo Next Day</div>
                                            </div>
                                            <div class="ship-right">
                                                <div class="ship-price">Rp 22.000</div>
                                                <div class="ship-eta">Estimasi 2 - 3 Hari</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <button type="submit" class="btn-submit">Konfirmasi Pesanan →</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <footer>
            &copy; <?= date('Y') ?> Lomoland Store. All rights reserved.
        </footer>
    </div>
</div>

<script>
    const subtotalBase = <?= $total ?>;
    const ongkirMap = {
        'JNE Express': 20000,
        'Reguler': 18000,
        'JNT Kargo': 22000,
        'Pos Indonesia': 15000,
        'Ambil di Toko': 0
    };

    function formatRupiah(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function selectShipping(el) {
        // Remove selected from all
        document.querySelectorAll('.ship-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');

        const via = el.dataset.via;
        const ongkir = parseInt(el.dataset.ongkir, 10);

        // Update hidden input
        document.getElementById('via-input').value = via;

        // Update panel kiri
        document.getElementById('js-via-label').textContent = via;
        document.getElementById('js-ongkir').textContent = formatRupiah(ongkir);
        document.getElementById('js-total').textContent = formatRupiah(subtotalBase + ongkir);
    }
</script>
</body>
</html>
