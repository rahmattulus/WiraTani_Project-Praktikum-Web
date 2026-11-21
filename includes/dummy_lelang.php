<?php
/*
 * dummy_lelang.php: data demo UI S10-S11. Hapus saat modul disambungkan ke database.
 * Nama kunci mengikuti kolom hasil_panen, penawaran_lelang, dan musim_tanam.
 */

$dummy_lelang_petani_id = 1;
$dummy_nama_petani = [
    1 => 'Budi Santoso',
    2 => 'Siti Rahayu',
];

$dummy_hasil_panen = [
    [
        'id' => 101,
        'user_id' => 1,
        'komoditas' => 'Padi Ciherang',
        'estimasi_tonase' => 2500,
        'lokasi' => 'Desa Jatinom, Klaten',
        'tanggal_panen' => '2026-12-05',
        'harga_dasar' => 6500,
        'deskripsi' => 'Kualitas gabah baik, panen dilakukan bertahap dan siap diangkut.',
        'status' => 'aktif',
        'created_at' => '2026-11-17 09:00:00',
    ],
    [
        'id' => 102,
        'user_id' => 1,
        'komoditas' => 'Jagung Hibrida',
        'estimasi_tonase' => 1800,
        'lokasi' => 'Dusun Tegalrejo, Klaten',
        'tanggal_panen' => '2026-12-12',
        'harga_dasar' => 5200,
        'deskripsi' => 'Jagung pipilan kering, kadar air diperkirakan 17 persen.',
        'status' => 'aktif',
        'created_at' => '2026-11-19 14:30:00',
    ],
    [
        'id' => 104,
        'user_id' => 2,
        'komoditas' => 'Padi Mekongga',
        'estimasi_tonase' => 3200,
        'lokasi' => 'Desa Tegalrejo, Magelang',
        'tanggal_panen' => '2026-12-03',
        'harga_dasar' => 6300,
        'deskripsi' => 'Gabah dipanen saat matang merata. Pengambilan dapat dijadwalkan setelah penimbangan.',
        'status' => 'aktif',
        'created_at' => '2026-11-20 11:00:00',
    ],
    [
        'id' => 103,
        'user_id' => 1,
        'komoditas' => 'Cabai Merah Keriting',
        'estimasi_tonase' => 750,
        'lokasi' => 'Desa Jatinom, Klaten',
        'tanggal_panen' => '2026-11-08',
        'harga_dasar' => 18000,
        'deskripsi' => 'Cabai segar dipetik pada hari pengambilan.',
        'status' => 'selesai',
        'created_at' => '2026-10-28 08:15:00',
    ],
];

$dummy_penawaran_lelang = [
    [
        'id' => 501,
        'hasil_panen_id' => 101,
        'user_id' => 3,
        'nama_tengkulak' => 'Hendra Wijaya',
        'harga_tawar' => 6900,
        'metode_pembayaran' => 'Tunai saat pengambilan',
        'armada_angkut' => 'Truk bak terbuka',
        'catatan' => 'Siap mengambil seluruh hasil panen.',
        'status' => 'menunggu',
        'created_at' => '2026-11-20 10:15:00',
    ],
    [
        'id' => 502,
        'hasil_panen_id' => 101,
        'user_id' => 4,
        'nama_tengkulak' => 'Agus Prasetyo',
        'harga_tawar' => 7200,
        'metode_pembayaran' => 'Transfer setelah timbang',
        'armada_angkut' => 'Truk engkel',
        'catatan' => 'Penimbangan dilakukan di lokasi panen.',
        'status' => 'menunggu',
        'created_at' => '2026-11-20 13:40:00',
    ],
    [
        'id' => 503,
        'hasil_panen_id' => 101,
        'user_id' => 3,
        'nama_tengkulak' => 'Hendra Wijaya',
        'harga_tawar' => 7050,
        'metode_pembayaran' => 'Tunai saat pengambilan',
        'armada_angkut' => 'Pick-up',
        'catatan' => '',
        'status' => 'menunggu',
        'created_at' => '2026-11-21 08:20:00',
    ],
    [
        'id' => 506,
        'hasil_panen_id' => 104,
        'user_id' => 3,
        'nama_tengkulak' => 'Hendra Wijaya',
        'harga_tawar' => 6500,
        'metode_pembayaran' => 'Transfer setelah timbang',
        'armada_angkut' => 'Truk engkel',
        'catatan' => 'Penimbangan dan pembayaran dilakukan di lokasi.',
        'status' => 'menunggu',
        'created_at' => '2026-11-21 09:30:00',
    ],
    [
        'id' => 504,
        'hasil_panen_id' => 103,
        'user_id' => 4,
        'nama_tengkulak' => 'Agus Prasetyo',
        'harga_tawar' => 19500,
        'metode_pembayaran' => 'Transfer setelah timbang',
        'armada_angkut' => 'Truk engkel',
        'catatan' => 'Pengambilan sudah selesai.',
        'status' => 'menang',
        'created_at' => '2026-11-02 11:10:00',
    ],
    [
        'id' => 505,
        'hasil_panen_id' => 103,
        'user_id' => 3,
        'nama_tengkulak' => 'Hendra Wijaya',
        'harga_tawar' => 18800,
        'metode_pembayaran' => 'Tunai saat pengambilan',
        'armada_angkut' => 'Truk bak terbuka',
        'catatan' => '',
        'status' => 'kalah',
        'created_at' => '2026-11-01 09:45:00',
    ],
];

$dummy_musim_tanam_lelang = [
    [
        'id' => 1,
        'user_id' => 1,
        'nama_musim' => 'Musim Tanam 1 2026',
        'komoditas' => 'Padi',
        'tanggal_mulai' => '2026-08-01',
        'tanggal_selesai' => null,
        'status' => 'berjalan',
    ],
    [
        'id' => 2,
        'user_id' => 1,
        'nama_musim' => 'Tanam Jagung 2026',
        'komoditas' => 'Jagung',
        'tanggal_mulai' => '2026-09-10',
        'tanggal_selesai' => null,
        'status' => 'berjalan',
    ],
    [
        'id' => 3,
        'user_id' => 1,
        'nama_musim' => 'Musim Tanam 2 2025',
        'komoditas' => 'Padi',
        'tanggal_mulai' => '2025-11-01',
        'tanggal_selesai' => '2026-03-15',
        'status' => 'selesai',
    ],
];

function label_status_lelang(string $status): string
{
    return [
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ][$status] ?? $status;
}

function kelas_status_lelang(string $status): string
{
    return [
        'aktif' => 'badge-info',
        'selesai' => 'badge-success',
        'dibatalkan' => 'badge-danger',
    ][$status] ?? 'badge-muted';
}

function label_status_penawaran(string $status): string
{
    return [
        'menunggu' => 'Menunggu',
        'menang' => 'Menang',
        'kalah' => 'Kalah',
    ][$status] ?? $status;
}

function kelas_status_penawaran(string $status): string
{
    return [
        'menunggu' => 'badge-warning',
        'menang' => 'badge-success',
        'kalah' => 'badge-muted',
    ][$status] ?? 'badge-muted';
}
