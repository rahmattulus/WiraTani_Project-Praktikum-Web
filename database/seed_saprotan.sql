-- Seed demo untuk modul saprotan, ERP, dan musim tanam.
-- Jalankan setelah schema.sql dan seed_akun.sql.

USE db_pertanian;

INSERT INTO musim_tanam (id, user_id, nama_musim, komoditas, tanggal_mulai, tanggal_selesai, status)
VALUES
  (1, (SELECT id FROM users WHERE email = 'budi@wiratani.test'), 'Musim Tanam 1 2026', 'Padi', '2026-09-10', NULL, 'berjalan'),
  (2, (SELECT id FROM users WHERE email = 'budi@wiratani.test'), 'Tanam Jagung 2026', 'Jagung', '2026-08-15', NULL, 'berjalan'),
  (3, (SELECT id FROM users WHERE email = 'siti@wiratani.test'), 'Musim Tanam 2 2025', 'Padi', '2025-01-05', '2025-05-30', 'selesai')
ON DUPLICATE KEY UPDATE
  nama_musim = VALUES(nama_musim),
  komoditas = VALUES(komoditas),
  tanggal_mulai = VALUES(tanggal_mulai),
  tanggal_selesai = VALUES(tanggal_selesai),
  status = VALUES(status);

INSERT INTO produk_saprotan
  (id, user_id, nama_produk, kategori, harga, satuan, stok, deskripsi)
VALUES
  (1, (SELECT id FROM users WHERE email = 'tanisubur@wiratani.test'), 'Pupuk Urea 50 kg', 'pupuk', 285000, 'sak', 40, 'Pupuk nitrogen untuk fase pertumbuhan daun dan batang.'),
  (2, (SELECT id FROM users WHERE email = 'tanisubur@wiratani.test'), 'Pupuk NPK Phonska 50 kg', 'pupuk', 310000, 'sak', 8, 'Pupuk majemuk untuk padi, jagung, dan palawija.'),
  (3, (SELECT id FROM users WHERE email = 'tanisubur@wiratani.test'), 'Bibit Padi Inpari 32', 'bibit', 16000, 'kg', 120, 'Varietas umur sekitar 120 hari, tahan wereng.'),
  (4, (SELECT id FROM users WHERE email = 'tanisubur@wiratani.test'), 'Insektisida Regent 50 SC 100 ml', 'pestisida', 95000, 'botol', 6, 'Pembasmi hama penggerek batang dan wereng.'),
  (5, (SELECT id FROM users WHERE email = 'tanisubur@wiratani.test'), 'Traktor Tangan', 'sewa_alat', 350000, 'hari', 3, 'Sewa harian hand tractor, termasuk operator.'),
  (6, (SELECT id FROM users WHERE email = 'sarimakmur@wiratani.test'), 'Pupuk Organik Kompos 25 kg', 'pupuk', 65000, 'sak', 60, 'Kompos matang untuk memperbaiki struktur tanah.'),
  (7, (SELECT id FROM users WHERE email = 'sarimakmur@wiratani.test'), 'Benih Jagung Hibrida 5 kg', 'bibit', 185000, 'pak', 25, 'Potensi hasil tinggi, cocok lahan kering.'),
  (8, (SELECT id FROM users WHERE email = 'sarimakmur@wiratani.test'), 'Pompa Air Alkon 3 inci', 'sewa_alat', 75000, 'hari', 4, 'Sewa harian untuk pengairan sawah.')
ON DUPLICATE KEY UPDATE
  nama_produk = VALUES(nama_produk),
  kategori = VALUES(kategori),
  harga = VALUES(harga),
  satuan = VALUES(satuan),
  stok = VALUES(stok),
  deskripsi = VALUES(deskripsi);

INSERT INTO pesanan_saprotan
  (id, user_id, produk_saprotan_id, musim_tanam_id, jumlah, harga_satuan, total_harga,
   tanggal_mulai_sewa, durasi_hari, catatan, status)
VALUES
  (1, (SELECT id FROM users WHERE email = 'budi@wiratani.test'), 1, 1, 10, 285000, 2850000,
   NULL, NULL, 'Kirim ke gudang desa.', 'menunggu'),
  (2, (SELECT id FROM users WHERE email = 'budi@wiratani.test'), 5, 2, 1, 350000, 1050000,
   '2026-11-25', 3, 'Untuk pengolahan lahan jagung.', 'menunggu')
ON DUPLICATE KEY UPDATE
  jumlah = VALUES(jumlah),
  harga_satuan = VALUES(harga_satuan),
  total_harga = VALUES(total_harga),
  tanggal_mulai_sewa = VALUES(tanggal_mulai_sewa),
  durasi_hari = VALUES(durasi_hari),
  catatan = VALUES(catatan),
  status = VALUES(status);
