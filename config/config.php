<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class KoneksiDatabase {
    public $conn;
    private $host     = "localhost";
    private $user     = "root";
    private $password = "";
    private $database = "db_lomo";

    public function __construct() {
        $this->membukaKoneksi();
    }

    public function membukaKoneksi() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Koneksi database gagal: " . $this->conn->connect_error);
        }
        return $this->conn;
    }

    public function eksekusiQuerySelect($sql) {
        $result = $this->conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function eksekusiQueryUpdate($sql) {
        return $this->conn->query($sql);
    }

    public function tutupKoneksi() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

if (!isset($_SESSION['id_temp'])) {
    $_SESSION['id_temp'] = session_id();
}
?>