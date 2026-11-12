<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Helper untuk format tanggal Indonesia dinamis sesuai hari dan tanggal sistem nyata
function getTanggalHariIniIndo() {
    $nama_hari = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    $nama_bulan = [
        1  => 'Jan',
        2  => 'Feb',
        3  => 'Mar',
        4  => 'Apr',
        5  => 'Mei',
        6  => 'Jun',
        7  => 'Jul',
        8  => 'Agu',
        9  => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des'
    ];

    $hari_inggris = date('l');
    $hari_indo    = isset($nama_hari[$hari_inggris]) ? $nama_hari[$hari_inggris] : $hari_inggris;
    $tgl          = date('d');
    $bln          = (int)date('m');
    $bulan_indo   = isset($nama_bulan[$bln]) ? $nama_bulan[$bln] : date('M');
    $tahun        = date('Y');

    return "{$hari_indo}, {$tgl} {$bulan_indo} {$tahun}";
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
    private function getInitialMockData() {
        return [
            'LOM-001' => [
                'kode_produk'   => 'LOM-001',
                'jenis'         => 'Analog Compact',
                'file'          => 'assets/images/Lomo LC-A+ 35mm Analog Camera.png',
                'id_kategori'   => 1,
                'nama_kategori' => 'CAT-01',
                'harga'         => 4250000
            ],
            'LOM-002' => [
                'kode_produk'   => 'LOM-002',
                'jenis'         => 'Medium Format',
                'file'          => 'assets/images/Lomo Medium Format Package.png',
                'id_kategori'   => 2,
                'nama_kategori' => 'CAT-02',
                'harga'         => 1650000
            ],
            'LOM-003' => [
                'kode_produk'   => 'LOM-003',
                'jenis'         => 'Compact',
                'file'          => 'assets/images/Lomo LC-A.png',
                'id_kategori'   => 3,
                'nama_kategori' => 'CAT-03',
                'harga'         => 1200000
            ],
            'LOM-004' => [
                'kode_produk'   => 'LOM-004',
                'jenis'         => 'Roll Film 35mm',
                'file'          => "assets/images/Lomography Color '92.png",
                'id_kategori'   => 4,
                'nama_kategori' => 'CAT-04',
                'harga'         => 230000
            ],
            'LOM-005' => [
                'kode_produk'   => 'LOM-005',
                'jenis'         => 'Lensa 55mm',
                'file'          => 'assets/images/Lensa manual 55mm.png',
                'id_kategori'   => 5,
                'nama_kategori' => 'CAT-05',
                'harga'         => 1450000
            ]
        ];
    }

    private function initSessionProducts() {
        if (!isset($_SESSION['admin_mock_products']) || !is_array($_SESSION['admin_mock_products'])) {
            $_SESSION['admin_mock_products'] = $this->getInitialMockData();
        }
    }

    private function getDb() {
        try {
            $db = new KoneksiDatabase();
            if ($db && isset($db->conn) && $db->conn && !$db->conn->connect_error) {
                return $db;
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function cariProdukById($kode_produk) {
        $db = $this->getDb();
        if ($db) {
            try {
                $kode_escaped = $db->conn->real_escape_string($kode_produk);
                $sql = "SELECT p.*, k.nama_kategori 
                        FROM produk p 
                        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                        WHERE p.kode_produk = '$kode_escaped'";
                $hasil = $db->eksekusiQuerySelect($sql);
                $db->tutupKoneksi();

                if (!empty($hasil)) {
                    $row = $hasil[0];
                    $produk = new TProduk();
                    $produk->setKode_produk($row['kode_produk']);
                    $produk->setJenis($row['jenis']);
                    $produk->setFile($row['file']);
                    $produk->setId_Kategori($row['id_kategori']);
                    $produk->setHarga($row['harga']);
                    return $produk;
                }
            } catch (Throwable $e) {
                // fall through to mock session
            }
        }

        $this->initSessionProducts();
        if (isset($_SESSION['admin_mock_products'][$kode_produk])) {
            $row = $_SESSION['admin_mock_products'][$kode_produk];
            $produk = new TProduk();
            $produk->setKode_produk($row['kode_produk']);
            $produk->setJenis($row['jenis']);
            $produk->setFile($row['file']);
            $produk->setId_Kategori($row['id_kategori']);
            $produk->setHarga($row['harga']);
            return $produk;
        }
        return null;
    }

    public function mengubahProduk(TProduk $produk) {
        $db = $this->getDb();
        if ($db) {
            try {
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
                if ($status) {
                    return true;
                }
            } catch (Throwable $e) {
                // fall through to mock session
            }
        }

        // Mock session update
        $this->initSessionProducts();
        $kode = $produk->getKodeProduk();
        if (isset($_SESSION['admin_mock_products'][$kode])) {
            $_SESSION['admin_mock_products'][$kode]['jenis'] = $produk->getJenis();
            $_SESSION['admin_mock_products'][$kode]['id_kategori'] = $produk->getId_kategori();
            $_SESSION['admin_mock_products'][$kode]['nama_kategori'] = 'CAT-' . sprintf('%02d', $produk->getId_kategori());
            $_SESSION['admin_mock_products'][$kode]['harga'] = $produk->getHarga();
            if ($produk->getFile()) {
                $_SESSION['admin_mock_products'][$kode]['file'] = $produk->getFile();
            }
            return true;
        } else {
            $_SESSION['admin_mock_products'][$kode] = [
                'kode_produk'   => $kode,
                'jenis'         => $produk->getJenis(),
                'file'          => $produk->getFile() ?: 'assets/images/Lomo LC-A.png',
                'id_kategori'   => $produk->getId_kategori(),
                'nama_kategori' => 'CAT-' . sprintf('%02d', $produk->getId_kategori()),
                'harga'         => $produk->getHarga()
            ];
            return true;
        }
    }

    public function menghapusProduk($kode_produk) {
        $db = $this->getDb();
        if ($db) {
            try {
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
                if ($status) {
                    return true;
                }
            } catch (Throwable $e) {
                // fall through to mock session
            }
        }

        $this->initSessionProducts();
        if (isset($_SESSION['admin_mock_products'][$kode_produk])) {
            unset($_SESSION['admin_mock_products'][$kode_produk]);
            return true;
        }
        return false;
    }

    public function ambilSemuaProduk() {
        $db = $this->getDb();
        if ($db) {
            try {
                $sql = "SELECT p.kode_produk, p.jenis, p.file, p.id_kategori, p.harga, k.nama_kategori 
                        FROM produk p 
                        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                        ORDER BY p.kode_produk ASC";
                $data = $db->eksekusiQuerySelect($sql);
                $db->tutupKoneksi();
                if (!empty($data)) {
                    return $data;
                }
            } catch (Throwable $e) {
                // fall through to mock session
            }
        }

        $this->initSessionProducts();
        return array_values($_SESSION['admin_mock_products']);
    }

    public function ambilSemuaKategori() {
        $db = $this->getDb();
        if ($db) {
            try {
                $sql = "SELECT * FROM kategori ORDER BY id_kategori ASC";
                $data = $db->eksekusiQuerySelect($sql);
                $db->tutupKoneksi();
                if (!empty($data)) {
                    return $data;
                }
            } catch (Throwable $e) {
                // fall through
            }
        }

        return [
            ['id_kategori' => 1, 'nama_kategori' => 'CAT-01'],
            ['id_kategori' => 2, 'nama_kategori' => 'CAT-02'],
            ['id_kategori' => 3, 'nama_kategori' => 'CAT-03'],
            ['id_kategori' => 4, 'nama_kategori' => 'CAT-04'],
            ['id_kategori' => 5, 'nama_kategori' => 'CAT-05']
        ];
    }
}

$produkCtrl = new EditDataProduk();

// Handle aksi delete
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

// Data yang diedit di formulir kiri
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

// Jika belum ada pilihan edit spesifik, gunakan nilai default yang persis seperti pada MockUp (LOM-001, Compact, CAT-01, 7.000.000)
if (!$edit_data) {
    $edit_data = [
        'kode_produk'   => 'LOM-001',
        'jenis'         => 'Compact',
        'file'          => 'assets/images/Lomo LC-A+ 35mm Analog Camera.png',
        'id_kategori'   => 1,
        'nama_kategori' => 'CAT-01',
        'harga'         => 7000000
    ];
}

$daftar_kategori = $produkCtrl->ambilSemuaKategori();
$display_rows    = $produkCtrl->ambilSemuaProduk();
$total_data      = count($display_rows);
$admin_active    = isset($_SESSION['user']) ? $_SESSION['user'] : 'Admin';

// Pastikan TIDAK mencetak output HTML jika file ini di-include oleh skrip backend lain (seperti proses_update.php)
$calling_script = basename(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''));
if ($calling_script !== 'data_produk.php') {
    return;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Lomoland System Access</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-shell">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand-box">
                <div class="brand-lens-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="4"></circle>
                        <line x1="12" y1="2" x2="12" y2="4"></line>
                        <line x1="12" y1="20" x2="12" y2="22"></line>
                        <line x1="2" y1="12" x2="4" y2="12"></line>
                        <line x1="20" y1="12" x2="22" y2="12"></line>
                    </svg>
                </div>
                <div class="brand-text-col">
                    <span class="brand-subtitle">System Access</span>
                    <span class="brand-title">LOMOLAND</span>
                </div>
            </div>

            <div class="sidebar-nav-section">
                <div class="nav-section-title">NAVIGASI</div>
                <ul class="sidebar-nav-menu">
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </span>
                            <span>Data Artikel</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item active">
                        <a href="data_produk.php">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </span>
                            <span>Data Produk</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </span>
                            <span>Data Customer</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <span>Kontak</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="6" y1="3" x2="6" y2="15"></line>
                                    <circle cx="18" cy="6" r="3"></circle>
                                    <circle cx="6" cy="18" r="3"></circle>
                                    <path d="M18 9a9 9 0 0 1-9 9"></path>
                                </svg>
                            </span>
                            <span>Kategori</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                            </span>
                            <span>Data Pengguna</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="#">
                            <span class="sidebar-nav-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                            </span>
                            <span>Data Transaksi</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-user-card">
                <div class="user-profile-pill">
                    <div class="user-profile-left">
                        <div class="avatar-badge">A</div>
                        <span class="user-name-text"><?= htmlspecialchars(!empty($admin_active) ? $admin_active : 'Admin') ?></span>
                    </div>
                    <a href="index.php?logout=true" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="logout-icon-btn" title="Keluar">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Topbar Header -->
            <header class="topbar-header">
                <nav class="breadcrumbs-trail">
                    <span>Beranda</span>
                    <span class="breadcrumb-sep">&rsaquo;</span>
                    <span>Produk</span>
                    <span class="breadcrumb-sep">&rsaquo;</span>
                    <span class="crumb-current">Data Produk</span>
                </nav>

                <div class="topbar-right-tools">
                    <div class="date-indicator" title="Tanggal Hari Ini: <?= date('d/m/Y') ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span><?= getTanggalHariIniIndo() ?></span>
                    </div>

                    <div class="topbar-user-avatar" title="Admin Account">
                        <div class="avatar-badge avatar-badge-light">A</div>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="content-body-padding">
                <h1 class="page-headline-serif">Manajemen Produk</h1>
                <div class="page-headline-divider"></div>

                <?php if (!empty($pesan)): ?>
                    <div class="alert-message <?= $tipe_pesan === 'sukses' ? 'alert-success' : 'alert-error' ?>">
                        <?php if ($tipe_pesan === 'sukses'): ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        <?php else: ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($pesan) ?></span>
                    </div>
                <?php endif; ?>

                <div class="admin-data-grid">
                    <!-- Left: Edit Form Card -->
                    <div class="card-edit-form">
                        <div class="edit-form-header">
                            <div class="edit-header-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <div class="edit-header-text-col">
                                <h3 class="edit-header-title">Edit Produk</h3>
                                <p class="edit-header-desc">Perbarui data produk ke sistem</p>
                            </div>
                        </div>

                        <form action="proses_update.php" method="POST" enctype="multipart/form-data">
                            <div class="form-field-item">
                                <label class="form-label">Kode Produk <span class="req-star">*</span></label>
                                <input 
                                    type="text" 
                                    name="kode_produk" 
                                    class="form-control-input" 
                                    value="<?= htmlspecialchars($edit_data['kode_produk']) ?>" 
                                    readonly
                                >
                            </div>

                            <div class="form-field-item">
                                <label class="form-label">Tipe <span class="req-star">*</span></label>
                                <input 
                                    type="text" 
                                    name="jenis" 
                                    class="form-control-input" 
                                    placeholder="Compact" 
                                    value="<?= htmlspecialchars($edit_data['jenis']) ?>" 
                                    required
                                >
                            </div>

                            <div class="form-field-item">
                                <label class="form-label">File / Foto <span class="req-star">*</span></label>
                                <div class="file-upload-styled">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span class="file-upload-text">Upload file/foto</span>
                                    <input type="file" name="file_foto" id="file_foto" accept="image/*">
                                </div>
                            </div>

                            <div class="form-field-item">
                                <label class="form-label">ID Produk <span class="req-star">*</span></label>
                                <select name="id_kategori" class="form-control-input form-control-select" required>
                                    <?php foreach ($daftar_kategori as $kat): ?>
                                        <option value="<?= $kat['id_kategori'] ?>" <?= ($kat['id_kategori'] == $edit_data['id_kategori']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-field-item">
                                <label class="form-label">Harga (IDR) <span class="req-star">*</span></label>
                                <div class="price-input-box">
                                    <span class="price-prefix-text">Rp</span>
                                    <input 
                                        type="text" 
                                        name="harga" 
                                        class="price-input-field" 
                                        placeholder="7.000.000" 
                                        value="<?= number_format($edit_data['harga'], 0, ',', '.') ?>" 
                                        required
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn-update-submit">
                                <span>Update produk</span>
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Right: Table Card -->
                    <div class="card-table-wrap">
                        <div class="table-responsive">
                            <table class="lomoland-table">
                                <thead>
                                    <tr>
                                        <th class="th-code">KODE PRODUK</th>
                                        <th class="th-type">TIPE</th>
                                        <th class="th-photo">FOTO PRODUK</th>
                                        <th class="th-id">ID PRODUK</th>
                                        <th class="th-price">HARGA</th>
                                        <th class="th-action">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($display_rows as $row): 
                                        $cat_label = !empty($row['nama_kategori']) && strpos($row['nama_kategori'], 'CAT-') === 0 
                                            ? $row['nama_kategori'] 
                                            : 'CAT-' . sprintf('%02d', $row['id_kategori']);
                                        $img_src = !empty($row['file']) ? '../' . ltrim($row['file'], '/') : '../assets/images/Lomo LC-A.png';
                                    ?>
                                        <tr>
                                            <td class="cell-code"><?= htmlspecialchars($row['kode_produk']) ?></td>
                                            <td class="cell-type"><?= htmlspecialchars($row['jenis']) ?></td>
                                            <td class="cell-photo">
                                                <div class="thumb-frame">
                                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($row['jenis']) ?>" class="product-thumb-img" onerror="this.src='../assets/images/Lomo LC-A.png'">
                                                </div>
                                            </td>
                                            <td class="cell-id">
                                                <span class="cat-pill-badge"><?= htmlspecialchars($cat_label) ?></span>
                                            </td>
                                            <td class="cell-price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                                            <td class="cell-action">
                                                <div class="actions-cell-wrap">
                                                    <a href="data_produk.php?edit=<?= urlencode($row['kode_produk']) ?>" class="btn-table-edit" title="Edit produk <?= htmlspecialchars($row['kode_produk']) ?>">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                        <span>Edit</span>
                                                    </a>
                                                    <a href="data_produk.php?action=delete&kode=<?= urlencode($row['kode_produk']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" class="btn-table-delete" title="Hapus produk <?= htmlspecialchars($row['kode_produk']) ?>">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        <span>Delete</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination-footer">
                            <span class="pagination-info-text">Menampilkan 1 - <?= count($display_rows) ?> dari <?= count($display_rows) ?> data</span>
                            <div class="pagination-squares">
                                <div class="page-square-active">1</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="copyright-bottom-note">
                    &copy; 2026 Lomoland Official. All rights reserved.
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
