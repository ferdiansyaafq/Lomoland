<?php
require_once __DIR__ . '/../config/config.php';

class TUser {
    private $id_user;
    private $user;
    private $password;

    public function setIdUser($id_user) { $this->id_user = $id_user; }
    public function getIdUser() { return $this->id_user; }

    public function setUser($user) { $this->user = $user; }
    public function getUser() { return $this->user; }

    public function setPassword($password) { $this->password = $password; }
    public function getPassword() { return $this->password; }
}

class Login {
    public function validasiLogin($username, $password) {
        $username = trim($username);
        $password = trim($password);

        // Kredensial sesuai instruksi mockup admin (database belum ada):
        // username: admin@lomoland.test, password: password
        if (($username === 'admin@lomoland.test' || $username === 'admin') && $password === 'password') {
            $userObj = new TUser();
            $userObj->setIdUser(1);
            $userObj->setUser('Admin');
            $userObj->setPassword(md5('password'));

            $_SESSION['user'] = 'Admin';
            return true;
        }

        // Support juga alternatif visual mockup jika diinputkan
        if ($username === 'admin@lomoland.co.id' && ($password === '12345678' || $password === 'password')) {
            $_SESSION['user'] = 'Admin';
            return true;
        }

        // Verifikasi ke database jika database aktif dan tabel tersedia
        try {
            $db = new KoneksiDatabase();
            if ($db && isset($db->conn) && $db->conn && !$db->conn->connect_error) {
                $conn = $db->conn;
                $stmt = $conn->prepare("SELECT id_user, user, password FROM user WHERE (user = ? OR user = ?) AND password = MD5(?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $username, $username, $password);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($row = $result->fetch_assoc()) {
                        $userObj = new TUser();
                        $userObj->setIdUser($row['id_user']);
                        $userObj->setUser($row['user']);
                        $userObj->setPassword($row['password']);

                        $_SESSION['user'] = $userObj->getUser();
                        $stmt->close();
                        $db->tutupKoneksi();
                        return true;
                    }
                    $stmt->close();
                }
                $db->tutupKoneksi();
            }
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function logout() {
        unset($_SESSION['user']);
        session_destroy();
    }
}

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : (isset($_POST['user']) ? trim($_POST['user']) : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi!";
    } else {
        $loginCtrl = new Login();
        if ($loginCtrl->validasiLogin($username, $password)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password tidak valid!";
        }
    }
}

// Prefill default sesuai spesifikasi agar langsung siap digunakan
$default_username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'admin@lomoland.test';
$default_password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : 'password';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Access - Lomoland</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Hero Pane -->
        <div class="login-hero">
            <div class="system-access-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                <span>SYSTEM ACCESS</span>
            </div>

            <div class="login-hero-content">
                <h1 class="login-hero-title">Sistem Penjualan &amp;<br>Inventaris Lomoland</h1>
                <p class="login-hero-desc">Kelola katalog produk fotografi, data pelanggan, dan transaksi secara real-time dengan aman dan efisien.</p>
            </div>

            <div class="login-hero-footer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                    <circle cx="12" cy="13" r="3"/>
                </svg>
                <span>Solusi Operasional Kamera Analog, Digital &amp; Aksesoris Lomography</span>
            </div>
        </div>

        <!-- Right Form Pane -->
        <div class="login-form-pane">
            <div class="login-header-top">
                <span class="login-version-tag">v 1.0.0</span>
            </div>

            <div class="login-card-inner">
                <h2 class="login-title">Selamat Datang</h2>
                <p class="login-subtitle">Masuk ke System Access Lomoland untuk mengelola sistem.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert-message alert-error">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <div class="label-row">
                            <label for="username" class="label-text">Username atau Email</label>
                            <span class="label-required-badge">Wajib diisi</span>
                        </div>
                        <div class="input-box">
                            <span class="input-box-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"></circle>
                                    <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"></path>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="input-field" 
                                placeholder="admin@lomoland.test"
                                value="<?= $default_username ?>" 
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="label-row">
                            <label for="password" class="label-text">Password</label>
                        </div>
                        <div class="input-box">
                            <span class="input-box-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="input-field" 
                                placeholder="password"
                                value="<?= $default_password ?>" 
                                required
                            >
                            <button type="button" class="toggle-password-btn" data-target="password" title="Lihat/Sembunyikan password">
                                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-action">
                        <span>Masuk ke Dashboard</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="login-footer-copy">
                &copy; 2026 Lomoland Official. All rights reserved.
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
