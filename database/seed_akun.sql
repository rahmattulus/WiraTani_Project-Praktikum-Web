-- seed_akun.sql (S09, Marvian): akun dummy untuk uji login dan demo.
-- Jalankan SETELAH schema.sql:  mysql -u root db_pertanian < database/seed_akun.sql
-- Aman dijalankan ulang (email unik, baris yang sama diperbarui).
--
-- Semua akun memakai kata sandi:  password123
--
--   Peran      Email                         Nama
--   petani     budi@wiratani.test            Budi Santoso
--   petani     siti@wiratani.test            Siti Rahayu
--   tengkulak  hendra@wiratani.test          Hendra Wijaya
--   tengkulak  agus@wiratani.test            Agus Prasetyo
--   kios       tanisubur@wiratani.test       Kios Tani Subur
--   kios       sarimakmur@wiratani.test      Kios Sari Makmur

USE db_pertanian;

INSERT INTO users (nama, email, password, no_telepon, alamat, role) VALUES
('Budi Santoso',    'budi@wiratani.test',       '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000001', 'Desa Sukamaju, Klaten',    'petani'),
('Siti Rahayu',     'siti@wiratani.test',       '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000002', 'Desa Tegalrejo, Magelang', 'petani'),
('Hendra Wijaya',   'hendra@wiratani.test',     '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000003', 'Kota Surakarta',           'tengkulak'),
('Agus Prasetyo',   'agus@wiratani.test',       '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000004', 'Boyolali',                 'tengkulak'),
('Kios Tani Subur', 'tanisubur@wiratani.test',  '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000005', 'Desa Jatinom, Klaten',     'kios'),
('Kios Sari Makmur','sarimakmur@wiratani.test', '$2y$10$PwdiT2ffPz/u6u1apD8J1OpoMAB6jlsxA8zEWNniDVAq.Wv0Zhe3a', '081200000006', 'Desa Tegalrejo, Magelang', 'kios')
ON DUPLICATE KEY UPDATE
  nama = VALUES(nama), password = VALUES(password), no_telepon = VALUES(no_telepon),
  alamat = VALUES(alamat), role = VALUES(role);
