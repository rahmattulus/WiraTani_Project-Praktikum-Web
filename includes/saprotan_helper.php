<?php
/*
 * saprotan_helper.php (Marvian): label, badge, dan hitung total untuk modul saprotan.
 * Berkas pembantu yang dipakai oleh katalog, pesanan, dan riwayat saprotan.
 */

const KATEGORI_SAPROTAN = [
    'pupuk'     => 'Pupuk',
    'bibit'     => 'Bibit',
    'pestisida' => 'Pestisida',
    'sewa_alat' => 'Sewa alat',
];

const STATUS_PESANAN = [
    'menunggu'  => 'Menunggu',
    'disetujui' => 'Disetujui',
    'ditolak'   => 'Ditolak',
    'selesai'   => 'Selesai',
];

const STOK_MENIPIS = 10;

function label_kategori(string $kategori): string
{
    return KATEGORI_SAPROTAN[$kategori] ?? $kategori;
}

/* Badge status pesanan: menunggu=warning, disetujui=info, selesai=success, ditolak=danger. */
function badge_pesanan(string $status): string
{
    $kelas = [
        'menunggu'  => 'badge-warning',
        'disetujui' => 'badge-info',
        'selesai'   => 'badge-success',
        'ditolak'   => 'badge-danger',
    ][$status] ?? 'badge-muted';
    return '<span class="badge ' . $kelas . '">' . e(STATUS_PESANAN[$status] ?? $status) . '</span>';
}

/* Teks stok: sewa alat dihitung per unit, produk lain per satuannya. */
function teks_stok(array $produk): string
{
    $satuan = $produk['kategori'] === 'sewa_alat' ? 'unit' : $produk['satuan'];
    return (int) $produk['stok'] . ' ' . $satuan;
}

/* Total pesanan: harga x jumlah, dan khusus sewa alat dikali durasi hari.
   Dipakai ulang di Fase 3 (S22) untuk snapshot total_harga saat pesanan dibuat. */
function hitung_total_pesanan(string $kategori, float $harga, int $jumlah, ?int $durasi_hari): float
{
    $total = $harga * $jumlah;
    if ($kategori === 'sewa_alat') {
        $total *= max(1, (int) $durasi_hari);
    }
    return $total;
}

/* Kategori produk -> kategori transaksi_keuangan (S21). Nilai enum berbeda:
   produk 'sewa_alat' dicatat di ERP sebagai 'alat'. */
function kategori_keuangan(string $kategori_produk): string
{
    return [
        'pupuk'     => 'pupuk',
        'bibit'     => 'bibit',
        'pestisida' => 'pestisida',
        'sewa_alat' => 'alat',
    ][$kategori_produk] ?? 'lainnya';
}
