<?php
/*
 * dummy_saprotan.php (Marvian): DATA DUMMY untuk UI Fase 2 (S12, S13).
 * HAPUS berkas ini di Fase 3 saat halaman disambungkan ke database (S18-S22).
 * Nama kunci sama dengan nama kolom tabel supaya penggantian mudah.
 */

$dummy_user_kios   = 5;   // kios yang dianggap sedang login di halaman kios
$dummy_user_petani = 1;   // petani yang dianggap sedang login di halaman petani

$dummy_produk = [
    ['id' => 1,  'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Pupuk Urea 50 kg',            'kategori' => 'pupuk',     'harga' => 285000, 'satuan' => 'sak',   'stok' => 40,
     'deskripsi' => 'Pupuk nitrogen untuk fase pertumbuhan daun dan batang.'],
    ['id' => 2,  'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Pupuk NPK Phonska 50 kg',     'kategori' => 'pupuk',     'harga' => 310000, 'satuan' => 'sak',   'stok' => 8,
     'deskripsi' => 'Pupuk majemuk untuk padi, jagung, dan palawija.'],
    ['id' => 3,  'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Bibit Padi Inpari 32',        'kategori' => 'bibit',     'harga' => 16000,  'satuan' => 'kg',    'stok' => 120,
     'deskripsi' => 'Varietas umur sekitar 120 hari, tahan wereng.'],
    ['id' => 4,  'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Insektisida Regent 50 SC 100 ml', 'kategori' => 'pestisida', 'harga' => 95000, 'satuan' => 'botol', 'stok' => 6,
     'deskripsi' => 'Pembasmi hama penggerek batang dan wereng.'],
    ['id' => 5,  'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Traktor Tangan (Hand Tractor)', 'kategori' => 'sewa_alat', 'harga' => 350000, 'satuan' => 'hari', 'stok' => 3,
     'deskripsi' => 'Sewa harian, sudah termasuk operator dan bahan bakar.'],
    ['id' => 10, 'user_id' => 5, 'nama_kios' => 'Kios Tani Subur',  'alamat_kios' => 'Desa Jatinom, Klaten',
     'nama_produk' => 'Mesin Perontok Padi (Power Thresher)', 'kategori' => 'sewa_alat', 'harga' => 250000, 'satuan' => 'hari', 'stok' => 2,
     'deskripsi' => 'Sewa harian, operator disediakan.'],
    ['id' => 6,  'user_id' => 6, 'nama_kios' => 'Kios Sari Makmur', 'alamat_kios' => 'Desa Tegalrejo, Magelang',
     'nama_produk' => 'Pupuk Organik Kompos 25 kg',  'kategori' => 'pupuk',     'harga' => 65000,  'satuan' => 'sak',   'stok' => 60,
     'deskripsi' => 'Kompos matang untuk memperbaiki struktur tanah.'],
    ['id' => 7,  'user_id' => 6, 'nama_kios' => 'Kios Sari Makmur', 'alamat_kios' => 'Desa Tegalrejo, Magelang',
     'nama_produk' => 'Benih Jagung Hibrida 5 kg',   'kategori' => 'bibit',     'harga' => 185000, 'satuan' => 'pak',   'stok' => 25,
     'deskripsi' => 'Potensi hasil tinggi, cocok lahan kering.'],
    ['id' => 8,  'user_id' => 6, 'nama_kios' => 'Kios Sari Makmur', 'alamat_kios' => 'Desa Tegalrejo, Magelang',
     'nama_produk' => 'Fungisida Score 250 EC 250 ml', 'kategori' => 'pestisida', 'harga' => 160000, 'satuan' => 'botol', 'stok' => 12,
     'deskripsi' => 'Mengendalikan bercak daun dan blast.'],
    ['id' => 9,  'user_id' => 6, 'nama_kios' => 'Kios Sari Makmur', 'alamat_kios' => 'Desa Tegalrejo, Magelang',
     'nama_produk' => 'Pompa Air Alkon 3 inci',      'kategori' => 'sewa_alat', 'harga' => 75000,  'satuan' => 'hari',  'stok' => 4,
     'deskripsi' => 'Sewa harian untuk pengairan sawah.'],
];

$dummy_musim = [
    ['id' => 1, 'user_id' => 1, 'nama_musim' => 'Musim Tanam 1 2026', 'komoditas' => 'Padi',  'status' => 'berjalan'],
    ['id' => 2, 'user_id' => 1, 'nama_musim' => 'Tanam Jagung 2026',  'komoditas' => 'Jagung', 'status' => 'berjalan'],
    ['id' => 3, 'user_id' => 1, 'nama_musim' => 'Musim Tanam 2 2025', 'komoditas' => 'Padi',  'status' => 'selesai'],
];

$dummy_pesanan = [
    ['id' => 1, 'user_id' => 1, 'nama_petani' => 'Budi Santoso', 'produk_saprotan_id' => 1,  'musim_tanam_id' => 1,
     'jumlah' => 10, 'harga_satuan' => 285000, 'total_harga' => 2850000, 'tanggal_mulai_sewa' => null, 'durasi_hari' => null,
     'catatan' => 'Kirim ke gudang desa.', 'status' => 'menunggu', 'created_at' => '2026-11-17'],
    ['id' => 2, 'user_id' => 2, 'nama_petani' => 'Siti Rahayu',  'produk_saprotan_id' => 5,  'musim_tanam_id' => 2,
     'jumlah' => 1,  'harga_satuan' => 350000, 'total_harga' => 1050000, 'tanggal_mulai_sewa' => '2026-11-25', 'durasi_hari' => 3,
     'catatan' => '', 'status' => 'menunggu', 'created_at' => '2026-11-16'],
    ['id' => 8, 'user_id' => 2, 'nama_petani' => 'Siti Rahayu',  'produk_saprotan_id' => 10, 'musim_tanam_id' => 2,
     'jumlah' => 3,  'harga_satuan' => 250000, 'total_harga' => 1500000, 'tanggal_mulai_sewa' => '2026-11-28', 'durasi_hari' => 2,
     'catatan' => 'Panen bersama tiga petak.', 'status' => 'menunggu', 'created_at' => '2026-11-15'],
    ['id' => 3, 'user_id' => 1, 'nama_petani' => 'Budi Santoso', 'produk_saprotan_id' => 3,  'musim_tanam_id' => 1,
     'jumlah' => 25, 'harga_satuan' => 16000,  'total_harga' => 400000,  'tanggal_mulai_sewa' => null, 'durasi_hari' => null,
     'catatan' => '', 'status' => 'disetujui', 'created_at' => '2026-11-14'],
    ['id' => 4, 'user_id' => 2, 'nama_petani' => 'Siti Rahayu',  'produk_saprotan_id' => 4,  'musim_tanam_id' => 2,
     'jumlah' => 4,  'harga_satuan' => 95000,  'total_harga' => 380000,  'tanggal_mulai_sewa' => null, 'durasi_hari' => null,
     'catatan' => '', 'status' => 'selesai', 'created_at' => '2026-11-05'],
    ['id' => 5, 'user_id' => 1, 'nama_petani' => 'Budi Santoso', 'produk_saprotan_id' => 2,  'musim_tanam_id' => 1,
     'jumlah' => 12, 'harga_satuan' => 310000, 'total_harga' => 3720000, 'tanggal_mulai_sewa' => null, 'durasi_hari' => null,
     'catatan' => '', 'status' => 'ditolak', 'created_at' => '2026-11-03'],
    ['id' => 6, 'user_id' => 1, 'nama_petani' => 'Budi Santoso', 'produk_saprotan_id' => 6,  'musim_tanam_id' => 1,
     'jumlah' => 20, 'harga_satuan' => 65000,  'total_harga' => 1300000, 'tanggal_mulai_sewa' => null, 'durasi_hari' => null,
     'catatan' => '', 'status' => 'selesai', 'created_at' => '2026-10-28'],
    ['id' => 7, 'user_id' => 1, 'nama_petani' => 'Budi Santoso', 'produk_saprotan_id' => 9,  'musim_tanam_id' => 1,
     'jumlah' => 1,  'harga_satuan' => 75000,  'total_harga' => 375000,  'tanggal_mulai_sewa' => '2026-10-20', 'durasi_hari' => 5,
     'catatan' => '', 'status' => 'selesai', 'created_at' => '2026-10-18'],
];

function cari_produk(int $id): ?array
{
    global $dummy_produk;
    foreach ($dummy_produk as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

/* Mode UI: aksi belum tersambung ke database. Fase 3 mengganti pemanggilannya
   dengan query sungguhan (S18-S22). */
function aksi_mode_ui(string $nama_aksi, string $tujuan): void
{
    set_flash('info', $nama_aksi . ': belum tersambung ke database (mode tampilan dengan data dummy).');
    redirect($tujuan);
}
