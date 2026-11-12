CREATE DATABASE IF NOT EXISTS db_lomo;
USE db_lomo;

CREATE TABLE `user` (
  id_user   INT          NOT NULL AUTO_INCREMENT,
  user      VARCHAR(50)  NOT NULL,
  password  VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_user_user (user)
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id_kategori   INT         NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_kategori),
  UNIQUE KEY uq_kategori_nama (nama_kategori)
) ENGINE=InnoDB;

CREATE TABLE produk (
  kode_produk VARCHAR(10)  NOT NULL,
  jenis       VARCHAR(100) NOT NULL,
  file        VARCHAR(255) NOT NULL,
  id_kategori INT          NOT NULL,
  harga       INT          NOT NULL,
  PRIMARY KEY (kode_produk),
  KEY idx_produk_id_kategori (id_kategori),
  CONSTRAINT fk_produk_kategori
    FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT chk_produk_harga CHECK (harga >= 0)
) ENGINE=InnoDB;

CREATE TABLE customer (
  kode_customer INT          NOT NULL AUTO_INCREMENT,
  nama          VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL,
  alamat        VARCHAR(255) NOT NULL,
  provinsi      VARCHAR(50)  NOT NULL,
  kota          VARCHAR(50)  NOT NULL,
  telepon       VARCHAR(20)  NOT NULL,
  via           VARCHAR(30)  NULL,
  PRIMARY KEY (kode_customer),
  KEY idx_customer_email (email)
) ENGINE=InnoDB;

CREATE TABLE temp (
  id_temp     VARCHAR(50) NOT NULL,
  kode_produk VARCHAR(10) NOT NULL,
  jumlah      INT         NOT NULL DEFAULT 1,
  PRIMARY KEY (id_temp, kode_produk),
  KEY idx_temp_kode_produk (kode_produk),
  CONSTRAINT fk_temp_produk
    FOREIGN KEY (kode_produk) REFERENCES produk (kode_produk)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT chk_temp_jumlah CHECK (jumlah >= 1)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
  kode_transaksi INT         NOT NULL,
  kode_produk    VARCHAR(10) NOT NULL,
  jumlah         INT         NOT NULL,
  kode_customer  INT         NOT NULL,
  PRIMARY KEY (kode_transaksi, kode_produk),
  KEY idx_transaksi_kode_produk (kode_produk),
  KEY idx_transaksi_kode_customer (kode_customer),
  CONSTRAINT fk_transaksi_produk
    FOREIGN KEY (kode_produk) REFERENCES produk (kode_produk)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_transaksi_customer
    FOREIGN KEY (kode_customer) REFERENCES customer (kode_customer)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT chk_transaksi_jumlah CHECK (jumlah >= 1)
) ENGINE=InnoDB;

INSERT INTO `user` (user, password) VALUES
  ('admin', '5f4dcc3b5aa765d61d8327deb882cf99');

INSERT INTO kategori (nama_kategori) VALUES
  ('Analog Compact'),   -- id_kategori = 1  -> tampil sebagai CAT-01
  ('Medium Format'),    -- id_kategori = 2  -> CAT-02
  ('Compact'),          -- id_kategori = 3  -> CAT-03
  ('Roll Film 35mm'),   -- id_kategori = 4  -> CAT-04
  ('Lensa 55mm');       -- id_kategori = 5  -> CAT-05


INSERT INTO produk (kode_produk, jenis, file, id_kategori, harga) VALUES
  ('LOM-001', 'Analog Compact', 'assets/images/Lomo LC-A.png',                    1, 3500000),
  ('LOM-002', 'Medium Format',  'assets/images/Lomo Medium Format Package.png',   2, 8500000),
  ('LOM-003', 'Compact',        'assets/images/Lomo LC-A+ 35mm Analog Camera.png', 3, 4750000),
  ('LOM-004', 'Roll Film 35mm', 'assets/images/Lomography Color ''92.png',        4,  185000),
  ('LOM-005', 'Lensa 55mm',      'assets/images/Lensa manual 55mm.png',           5, 2250000),
  ('LOM-006', 'Analog Compact', 'assets/images/Lomo MC-A 35mm.png',               1, 6900000);


INSERT INTO customer (nama, email, alamat, provinsi, kota, telepon, via) VALUES
  ('Nova Reskianti', 'nova@example.com',  'Jl. H.E.A. Mokodompit No. 12', 'Kalimantan Timur', 'Balikpapan', '081234567890', 'JNE'),
  ('Irsyad Fiqi',    'irsyad@example.com','Jl. Ahmad Yani No. 45',        'Jawa Timur', 'Surabaya', '082198765432', 'Ambil di Toko');


INSERT INTO transaksi (kode_transaksi, kode_produk, jumlah, kode_customer) VALUES
  (1, 'LOM-001', 1, 1),
  (1, 'LOM-004', 3, 1);

SELECT 'user'      AS tabel, COUNT(*) AS jumlah_baris FROM `user`
UNION ALL SELECT 'kategori',  COUNT(*) FROM kategori
UNION ALL SELECT 'produk',    COUNT(*) FROM produk
UNION ALL SELECT 'customer',  COUNT(*) FROM customer
UNION ALL SELECT 'temp',      COUNT(*) FROM temp
UNION ALL SELECT 'transaksi', COUNT(*) FROM transaksi;
