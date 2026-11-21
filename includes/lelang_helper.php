<?php
/* Query, validasi, dan operasi lelang petani (S17). */

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

function ambil_daftar_hasil_panen_petani(mysqli $koneksi, int $user_id, string $q, string $status): array
{
    $sql = "SELECT h.*,
                   (SELECT COUNT(*) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS jumlah_tawaran
            FROM hasil_panen h
            WHERE h.user_id = ?
              AND (? = '' OR h.komoditas LIKE ? OR h.lokasi LIKE ?)
              AND (? = '' OR h.status = ?)
            ORDER BY h.created_at DESC, h.id DESC";
    $stmt = $koneksi->prepare($sql);
    $pencarian = '%' . $q . '%';
    $stmt->bind_param('isssss', $user_id, $q, $pencarian, $pencarian, $status, $status);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $hasil;
}

function ambil_ringkasan_hasil_panen_petani(mysqli $koneksi, int $user_id): array
{
    $sql = "SELECT COUNT(*) AS total,
                   COALESCE(SUM(status = 'aktif'), 0) AS aktif,
                   COALESCE(SUM(status = 'selesai'), 0) AS selesai,
                   (SELECT COUNT(*)
                    FROM penawaran_lelang p
                    INNER JOIN hasil_panen hp ON hp.id = p.hasil_panen_id
                    WHERE hp.user_id = ? AND p.status = 'menunggu') AS tawaran_menunggu
            FROM hasil_panen
            WHERE user_id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $hasil;
}

function ambil_hasil_panen_petani(mysqli $koneksi, int $id, int $user_id): ?array
{
    $sql = "SELECT h.*,
                   (SELECT COUNT(*) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS jumlah_tawaran
            FROM hasil_panen h
            WHERE h.id = ? AND h.user_id = ?
            LIMIT 1";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $hasil ?: null;
}

function ambil_penawaran_hasil_panen(mysqli $koneksi, int $hasil_panen_id): array
{
    $stmt = $koneksi->prepare(
        'SELECT p.*, u.nama AS nama_tengkulak
         FROM penawaran_lelang p
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.hasil_panen_id = ?
         ORDER BY p.harga_tawar DESC, p.created_at DESC'
    );
    $stmt->bind_param('i', $hasil_panen_id);
    $stmt->execute();
    $penawaran = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $penawaran;
}

function validasi_input_hasil_panen(array $input): array
{
    $error = [];

    if ($input['komoditas'] === '') {
        $error['komoditas'] = 'Komoditas wajib diisi.';
    } elseif (strlen($input['komoditas']) > 100) {
        $error['komoditas'] = 'Komoditas maksimal 100 karakter.';
    }

    if ($input['estimasi_tonase'] === '' || !is_numeric($input['estimasi_tonase'])
        || !is_finite((float) $input['estimasi_tonase'])
        || (float) $input['estimasi_tonase'] <= 0
        || (float) $input['estimasi_tonase'] > 99999999.99) {
        $error['estimasi_tonase'] = 'Estimasi harus lebih dari 0 dan maksimal 99.999.999,99 kg.';
    }

    if ($input['lokasi'] === '') {
        $error['lokasi'] = 'Lokasi panen wajib diisi.';
    } elseif (strlen($input['lokasi']) > 150) {
        $error['lokasi'] = 'Lokasi maksimal 150 karakter.';
    }

    $tanggal = DateTime::createFromFormat('!Y-m-d', $input['tanggal_panen']);
    $tanggal_error = DateTime::getLastErrors();
    if (!$tanggal
        || $tanggal->format('Y-m-d') !== $input['tanggal_panen']
        || ($tanggal_error !== false && ($tanggal_error['warning_count'] > 0 || $tanggal_error['error_count'] > 0))) {
        $error['tanggal_panen'] = 'Tanggal panen tidak valid.';
    }

    if ($input['harga_dasar'] === '' || !is_numeric($input['harga_dasar'])
        || !is_finite((float) $input['harga_dasar'])
        || (float) $input['harga_dasar'] <= 0
        || (float) $input['harga_dasar'] > 9999999999999.99) {
        $error['harga_dasar'] = 'Harga dasar harus lebih dari 0 dan tidak melebihi batas database.';
    }

    if (strlen($input['deskripsi']) > 2000) {
        $error['deskripsi'] = 'Keterangan maksimal 2.000 karakter.';
    }

    return $error;
}

function buat_hasil_panen(mysqli $koneksi, int $user_id, array $data): void
{
    $stmt = $koneksi->prepare(
        'INSERT INTO hasil_panen
            (user_id, komoditas, estimasi_tonase, lokasi, tanggal_panen, harga_dasar, deskripsi)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $deskripsi = $data['deskripsi'] === '' ? null : $data['deskripsi'];
    $stmt->bind_param(
        'issssss',
        $user_id,
        $data['komoditas'],
        $data['estimasi_tonase'],
        $data['lokasi'],
        $data['tanggal_panen'],
        $data['harga_dasar'],
        $deskripsi
    );
    $stmt->execute();
    $stmt->close();
}

function ubah_hasil_panen(mysqli $koneksi, int $id, int $user_id, array $data): string
{
    $transaksi_dimulai = false;
    try {
        $koneksi->begin_transaction();
        $transaksi_dimulai = true;

        $stmt = $koneksi->prepare(
            'SELECT status FROM hasil_panen WHERE id = ? AND user_id = ? FOR UPDATE'
        );
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$hasil) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_found';
        }
        if ($hasil['status'] !== 'aktif') {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        $stmt = $koneksi->prepare(
            'SELECT COUNT(*) AS jumlah FROM penawaran_lelang WHERE hasil_panen_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $jumlah_tawaran = (int) $stmt->get_result()->fetch_assoc()['jumlah'];
        $stmt->close();
        if ($jumlah_tawaran > 0) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'has_offers';
        }

        $stmt = $koneksi->prepare(
            'UPDATE hasil_panen
             SET komoditas = ?, estimasi_tonase = ?, lokasi = ?, tanggal_panen = ?,
                 harga_dasar = ?, deskripsi = ?
             WHERE id = ? AND user_id = ?'
        );
        $deskripsi = $data['deskripsi'] === '' ? null : $data['deskripsi'];
        $stmt->bind_param(
            'ssssssii',
            $data['komoditas'],
            $data['estimasi_tonase'],
            $data['lokasi'],
            $data['tanggal_panen'],
            $data['harga_dasar'],
            $deskripsi,
            $id,
            $user_id
        );
        $stmt->execute();
        $stmt->close();

        $koneksi->commit();
        $transaksi_dimulai = false;
        return 'updated';
    } catch (mysqli_sql_exception $exception) {
        if ($transaksi_dimulai) {
            $koneksi->rollback();
        }
        error_log('Gagal mengubah lelang petani: ' . $exception->getMessage());
        return 'error';
    }
}

function hapus_hasil_panen(mysqli $koneksi, int $id, int $user_id): string
{
    $transaksi_dimulai = false;
    try {
        $koneksi->begin_transaction();
        $transaksi_dimulai = true;

        $stmt = $koneksi->prepare(
            'SELECT id FROM hasil_panen WHERE id = ? AND user_id = ? FOR UPDATE'
        );
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$hasil) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_found';
        }

        $stmt = $koneksi->prepare(
            'SELECT COUNT(*) AS jumlah FROM penawaran_lelang WHERE hasil_panen_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $jumlah_tawaran = (int) $stmt->get_result()->fetch_assoc()['jumlah'];
        $stmt->close();
        if ($jumlah_tawaran > 0) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'has_offers';
        }

        $stmt = $koneksi->prepare(
            'DELETE FROM hasil_panen WHERE id = ? AND user_id = ?'
        );
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $stmt->close();

        $koneksi->commit();
        $transaksi_dimulai = false;
        return 'deleted';
    } catch (mysqli_sql_exception $exception) {
        if ($transaksi_dimulai) {
            $koneksi->rollback();
        }
        error_log('Gagal menghapus lelang petani: ' . $exception->getMessage());
        return 'error';
    }
}
