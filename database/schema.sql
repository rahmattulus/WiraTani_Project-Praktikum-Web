-- =====================================================================
-- schema.sql: Platform Web Pertanian (SDG 8)
-- Mengacu pada PRD-web-pertanian.md (bagian 6, 7, 8)
-- MySQL 5.7+ / MariaDB 10.3+ (InnoDB, utf8mb4)
--
-- Konvensi:
--   * PK semua tabel bernama `id`
--   * FK bernama <tabel_tunggal>_id (mis. hasil_panen_id), kecuali pemilik
--     data yang selalu `user_id`
--   * Nama input form HARUS sama dengan nama kolom
--   * Uang: DECIMAL(15,2) | Tonase: DECIMAL(10,2)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_pertanian
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_pertanian;

-- ---------------------------------------------------------------------
-- Reset (aman dijalankan ulang saat development)
-- ---------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS transaksi_keuangan;
DROP TABLE IF EXISTS pesanan_saprotan;
DROP TABLE IF EXISTS penawaran_lelang;
DROP TABLE IF EXISTS lowongan_kerja;
DROP TABLE IF EXISTS produk_saprotan;
DROP TABLE IF EXISTS musim_tanam;
DROP TABLE IF EXISTS hasil_panen;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. users: akun semua role (Modul 1)
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL,
  password    VARCHAR(255) NOT NULL COMMENT 'hasil password_hash()',
  no_telepon  VARCHAR(20)  NOT NULL,
  alamat      VARCHAR(255) NOT NULL COMMENT 'alamat / desa',
  role        ENUM('petani','tengkulak','kios') NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. hasil_panen: lelang panen oleh petani (Modul 2, 3, 5)
--    status: aktif -> selesai (ada pemenang) | dibatalkan
-- ---------------------------------------------------------------------
CREATE TABLE hasil_panen (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id          INT UNSIGNED NOT NULL COMMENT 'petani pemilik',
  komoditas        VARCHAR(100) NOT NULL,
  estimasi_tonase  DECIMAL(10,2) NOT NULL COMMENT 'dalam kg',
  lokasi           VARCHAR(150) NOT NULL,
  tanggal_panen    DATE NOT NULL,
  harga_dasar      DECIMAL(15,2) NOT NULL COMMENT 'per kg',
  deskripsi        TEXT NULL,
  status           ENUM('aktif','selesai','dibatalkan') NOT NULL DEFAULT 'aktif',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_panen_status (status),
  KEY idx_panen_user (user_id),
  KEY idx_panen_komoditas (komoditas),
  CONSTRAINT fk_panen_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_panen_tonase CHECK (estimasi_tonase > 0),
  CONSTRAINT chk_panen_harga  CHECK (harga_dasar > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. penawaran_lelang: bidding tengkulak (Modul 4, 5)
--    status: menunggu -> menang | kalah
--    Aturan di aplikasi: harga_tawar >= harga_dasar, dan lebih tinggi dari
--    tawaran sebelumnya oleh tengkulak yang sama; hanya 1 'menang' per lelang.
-- ---------------------------------------------------------------------
CREATE TABLE penawaran_lelang (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  hasil_panen_id     INT UNSIGNED NOT NULL,
  user_id            INT UNSIGNED NOT NULL COMMENT 'tengkulak penawar',
  harga_tawar        DECIMAL(15,2) NOT NULL COMMENT 'per kg',
  metode_pembayaran  VARCHAR(50)  NOT NULL,
  armada_angkut      VARCHAR(100) NOT NULL,
  catatan            TEXT NULL,
  status             ENUM('menunggu','menang','kalah') NOT NULL DEFAULT 'menunggu',
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tawar_lelang_harga (hasil_panen_id, harga_tawar),
  KEY idx_tawar_user (user_id),
  CONSTRAINT fk_tawar_panen FOREIGN KEY (hasil_panen_id)
    REFERENCES hasil_panen (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_tawar_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_tawar_harga CHECK (harga_tawar > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. musim_tanam: siklus tanam petani (Modul 6)
-- ---------------------------------------------------------------------
CREATE TABLE musim_tanam (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id          INT UNSIGNED NOT NULL COMMENT 'petani pemilik',
  nama_musim       VARCHAR(100) NOT NULL COMMENT 'mis. Musim Tanam 1 2026',
  komoditas        VARCHAR(100) NOT NULL,
  tanggal_mulai    DATE NOT NULL,
  tanggal_selesai  DATE NULL,
  status           ENUM('berjalan','selesai') NOT NULL DEFAULT 'berjalan',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_musim_user (user_id, status),
  CONSTRAINT fk_musim_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. produk_saprotan: katalog kios (Modul 8)
--    Untuk kategori sewa_alat, `stok` = jumlah unit alat tersedia.
-- ---------------------------------------------------------------------
CREATE TABLE produk_saprotan (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL COMMENT 'kios pemilik',
  nama_produk   VARCHAR(150) NOT NULL,
  kategori      ENUM('pupuk','bibit','pestisida','sewa_alat') NOT NULL,
  harga         DECIMAL(15,2) NOT NULL COMMENT 'per satuan (sewa_alat: per hari)',
  satuan        VARCHAR(20) NOT NULL COMMENT 'kg, sak, liter, hari, dst.',
  stok          INT UNSIGNED NOT NULL DEFAULT 0,
  deskripsi     TEXT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produk_user (user_id),
  KEY idx_produk_kategori (kategori),
  CONSTRAINT fk_produk_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_produk_harga CHECK (harga >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. pesanan_saprotan: pesanan petani ke kios (Modul 7, 8)
--    status: menunggu -> disetujui -> selesai | menunggu -> ditolak
--    harga_satuan dan total_harga disalin saat pesan (snapshot), supaya
--    perubahan harga produk tidak mengubah riwayat.
--    total_harga: produk non-sewa = jumlah x harga_satuan
--                 sewa_alat       = jumlah x harga_satuan x durasi_hari
-- ---------------------------------------------------------------------
CREATE TABLE pesanan_saprotan (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id             INT UNSIGNED NOT NULL COMMENT 'petani pemesan',
  produk_saprotan_id  INT UNSIGNED NOT NULL,
  musim_tanam_id      INT UNSIGNED NOT NULL COMMENT 'biaya dibebankan ke musim ini',
  jumlah              INT UNSIGNED NOT NULL,
  harga_satuan        DECIMAL(15,2) NOT NULL,
  total_harga         DECIMAL(15,2) NOT NULL,
  tanggal_mulai_sewa  DATE NULL COMMENT 'hanya untuk sewa_alat',
  durasi_hari         INT UNSIGNED NULL COMMENT 'hanya untuk sewa_alat',
  catatan             TEXT NULL,
  status              ENUM('menunggu','disetujui','ditolak','selesai') NOT NULL DEFAULT 'menunggu',
  created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pesanan_user (user_id),
  KEY idx_pesanan_produk (produk_saprotan_id, status),
  CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pesanan_produk FOREIGN KEY (produk_saprotan_id)
    REFERENCES produk_saprotan (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pesanan_musim FOREIGN KEY (musim_tanam_id)
    REFERENCES musim_tanam (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_pesanan_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. transaksi_keuangan: ERP sederhana (Modul 6)
--    Tiga sumber baris:
--      a) manual         : hasil_panen_id NULL, pesanan_saprotan_id NULL
--      b) pemenang lelang: hasil_panen_id terisi  (jenis=pendapatan, kategori=hasil_lelang)
--      c) pesanan selesai: pesanan_saprotan_id terisi (jenis=pengeluaran)
--         pemetaan kategori produk -> kategori ERP:
--         pupuk->pupuk, bibit->bibit, pestisida->pestisida, sewa_alat->alat
--    UNIQUE pada kedua kolom sumber mencegah pencatatan ganda otomatis.
--    Laba/rugi musim = SUM(pendapatan) - SUM(pengeluaran).
-- ---------------------------------------------------------------------
CREATE TABLE transaksi_keuangan (
  id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  musim_tanam_id       INT UNSIGNED NOT NULL,
  jenis                ENUM('pendapatan','pengeluaran') NOT NULL,
  kategori             ENUM('hasil_lelang','bibit','pupuk','pestisida','alat','tenaga_kerja','lainnya') NOT NULL,
  nominal              DECIMAL(15,2) NOT NULL,
  tanggal              DATE NOT NULL,
  keterangan           VARCHAR(255) NULL,
  hasil_panen_id       INT UNSIGNED NULL,
  pesanan_saprotan_id  INT UNSIGNED NULL,
  created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_transaksi_panen (hasil_panen_id),
  UNIQUE KEY uq_transaksi_pesanan (pesanan_saprotan_id),
  KEY idx_transaksi_musim (musim_tanam_id, jenis),
  CONSTRAINT fk_transaksi_musim FOREIGN KEY (musim_tanam_id)
    REFERENCES musim_tanam (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_transaksi_panen FOREIGN KEY (hasil_panen_id)
    REFERENCES hasil_panen (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_transaksi_pesanan FOREIGN KEY (pesanan_saprotan_id)
    REFERENCES pesanan_saprotan (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_transaksi_nominal CHECK (nominal > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. lowongan_kerja: bursa kerja buruh tani (Modul 9, 10)
-- ---------------------------------------------------------------------
CREATE TABLE lowongan_kerja (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id          INT UNSIGNED NOT NULL COMMENT 'petani pembuat',
  judul            VARCHAR(150) NOT NULL,
  jenis_pekerjaan  ENUM('tanam','panen','lainnya') NOT NULL,
  lokasi           VARCHAR(150) NOT NULL,
  jumlah_buruh     INT UNSIGNED NOT NULL,
  upah_harian      DECIMAL(15,2) NOT NULL,
  tanggal_mulai    DATE NOT NULL,
  durasi_hari      INT UNSIGNED NOT NULL,
  deskripsi        TEXT NULL,
  status           ENUM('buka','ditutup') NOT NULL DEFAULT 'buka',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_lowongan_status (status, jenis_pekerjaan),
  KEY idx_lowongan_user (user_id),
  CONSTRAINT fk_lowongan_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_lowongan_upah CHECK (upah_harian > 0),
  CONSTRAINT chk_lowongan_jumlah CHECK (jumlah_buruh > 0)
) ENGINE=InnoDB;
