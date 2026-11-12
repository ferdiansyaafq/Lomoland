# Lomoland

## Deskripsi
Lomoland merupakan sistem e-commerce berbasis web yang digunakan untuk mempermudah proses penjualan dan administrasi kamera Lomo. Customer dapat melihat informasi dan katalog produk, memilih produk, melakukan transaksi, mengisi data pembeli, serta memberikan komentar melalui halaman Contact. Sistem ini menjadi pelengkap proses penjualan kamera Lomo yang sudah ada, sehingga pelanggan dapat memperoleh informasi dan melakukan pembelian melalui web tanpa harus datang langsung ke toko.

## Anggota Tim
- Moh Irsyad Fiqi Ferdiansyah Difa Nanda — Back end
- Muhammad Farin Murtadho Syafiq — Back end
- Nova Reskianti — Database
- Shabrina Putri Az-Zahra — Front end
- Tresia Uyang — Front end

## Teknologi
- PHP
- MySQL
- HTML, CSS, dan Bootstrap

## Struktur Dokumentasi
- docs/SDD: dokumen desain perangkat lunak
- docs/STD: dokumen pengujian perangkat lunak

## Persiapan
1. Clone repository.
2. Jalankan Apache dan MySQL melalui Laragon.
3. Impor `database/lomoland.sql` ke MySQL, misalnya melalui tab **Import** di `http://localhost/phpmyadmin`. Skrip tersebut akan membuat database `db_lomo`, tabel, dan data contoh.
   - Alternatif melalui terminal dari direktori repository: `mysql -u root < database\lomoland.sql`
4. Pastikan pengaturan host, username, password, dan nama database pada `config/config.php` sesuai dengan MySQL lokal. Nilai bawaan menggunakan `localhost`, user `root`, password kosong, dan database `db_lomo`.

## Catatan
Petunjuk instalasi dan penggunaan akan diperbarui sesuai perkembangan proyek.