-- Database Schema & Seed Data for Lomoland
-- Based on Software Design Description (SDD) and SRS specifications

CREATE DATABASE IF NOT EXISTS `db_lomo`;
USE `db_lomo`;

-- Table structure for `user` (TUser: Login Administrator)
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `kategori` (TKategori)
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `produk` (TProduk)
CREATE TABLE IF NOT EXISTS `produk` (
  `kode_produk` VARCHAR(10) NOT NULL,
  `jenis` VARCHAR(100) NOT NULL,
  `file` VARCHAR(255) NOT NULL,
  `id_kategori` INT(11) NOT NULL,
  `harga` INT(11) NOT NULL,
  PRIMARY KEY (`kode_produk`),
  KEY `id_kategori` (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `customer` (TCustomer)
CREATE TABLE IF NOT EXISTS `customer` (
  `kode_customer` INT(11) NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `alamat` VARCHAR(255) NOT NULL,
  `provinsi` VARCHAR(50) NOT NULL,
  `kota` VARCHAR(50) NOT NULL,
  `telepon` VARCHAR(20) NOT NULL,
  `via` VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (`kode_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `temp` (Temporary Cart per session)
CREATE TABLE IF NOT EXISTS `temp` (
  `id_temp` VARCHAR(50) NOT NULL,
  `kode_produk` VARCHAR(10) NOT NULL,
  `jumlah` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_temp`, `kode_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `transaksi` (TTransaksi)
CREATE TABLE IF NOT EXISTS `transaksi` (
  `kode_transaksi` INT(11) NOT NULL,
  `kode_produk` VARCHAR(10) NOT NULL,
  `jumlah` INT(11) NOT NULL,
  `kode_customer` INT(11) NOT NULL,
  PRIMARY KEY (`kode_transaksi`, `kode_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data: User (admin: admin / 12345678)
INSERT INTO `user` (`id_user`, `user`, `password`) VALUES
(1, 'admin', MD5('12345678')),
(2, 'admin@lomoland.co.id', MD5('12345678'))
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- Seed Data: Kategori
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Analog Compact'),
(2, 'Medium Format'),
(3, 'Compact'),
(4, 'Roll Film 35mm'),
(5, 'Lensa 55mm')
ON DUPLICATE KEY UPDATE `nama_kategori` = VALUES(`nama_kategori`);

-- Seed Data: Produk
INSERT INTO `produk` (`kode_produk`, `jenis`, `file`, `id_kategori`, `harga`) VALUES
('LOM-001', 'Analog Compact', 'assets/images/Lomo LC-A+ 35mm Analog Camera.png', 1, 4250000),
('LOM-002', 'Medium Format', 'assets/images/Lomo Medium Format Package.png', 2, 1650000),
('LOM-003', 'Compact', 'assets/images/Lomo LC-A.png', 3, 1200000),
('LOM-004', 'Roll Film 35mm', 'assets/images/Lomography Color \'92.png', 4, 230000),
('LOM-005', 'Lensa 55mm', 'assets/images/Lensa manual 55mm.png', 5, 1450000)
ON DUPLICATE KEY UPDATE `jenis` = VALUES(`jenis`), `harga` = VALUES(`harga`), `file` = VALUES(`file`);

