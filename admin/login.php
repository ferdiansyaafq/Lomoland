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
        $db = new KoneksiDatabase();
        $conn = $db->conn;
        $stmt = $conn->prepare("SELECT id_user, user, password FROM user WHERE user = ? AND password = MD5(?)");
        $stmt->bind_param("ss", $username, $password);
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
        $db->tutupKoneksi();
        return false;
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Lomoland</title>
</head>
<body>
    <h2>Login Administrator</h2>
    <p><a href="../index.php">&larr; Kembali ke Beranda</a></p>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><b><?= htmlspecialchars($error) ?></b></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div>
            <label for="username">Username atau Email:</label><br>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        <br>
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>
        <br>
        <button type="submit">Masuk ke Dashboard</button>
    </form>
</body>
</html>
