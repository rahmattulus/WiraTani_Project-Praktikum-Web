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

function validasi_input_penawaran(array $input): array
{
    $error = [];
    $harga = $input['harga_tawar'] ?? '';

    if (!is_string($harga) || !preg_match('/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/D', $harga)) {
        $error['harga_tawar'] = 'Masukkan harga tawar yang valid dengan maksimal 2 angka desimal.';
    } else {
        $harga_sen = lelang_harga_ke_sen($harga);
        if ($harga_sen < 1 || $harga_sen > 999999999999999) {
            $error['harga_tawar'] = 'Harga tawar harus lebih dari 0 dan tidak melebihi batas database.';
        }
    }

    foreach ([
        'metode_pembayaran' => ['Metode pembayaran', 50],
        'armada_angkut' => ['Armada angkut', 100],
    ] as $field => $rules) {
        $value = $input[$field] ?? '';
        if (!is_string($value) || trim($value) === '') {
            $error[$field] = $rules[0] . ' wajib diisi.';
        } elseif (strlen($value) > $rules[1]) {
            $error[$field] = $rules[0] . ' maksimal ' . $rules[1] . ' karakter.';
        }
    }

    $catatan = $input['catatan'] ?? '';
    if (!is_string($catatan) || strlen($catatan) > 2000) {
        $error['catatan'] = 'Catatan maksimal 2.000 karakter.';
    }

    return $error;
}

function lelang_harga_ke_sen(string $harga): int
{
    $bagian = explode('.', $harga, 2);
    $rupiah = ltrim($bagian[0], '0');
    $rupiah = $rupiah === '' ? '0' : $rupiah;
    $sen = str_pad($bagian[1] ?? '', 2, '0');
    return ((int) $rupiah * 100) + (int) $sen;
}

function ambil_ringkasan_pasar_lelang(mysqli $koneksi, int $user_id): array
{
    $stmt = $koneksi->prepare(
        "SELECT
            (SELECT COUNT(*) FROM hasil_panen WHERE status = 'aktif') AS lelang_aktif,
            (SELECT COUNT(*) FROM penawaran_lelang WHERE user_id = ?) AS tawaran_saya,
            (SELECT COUNT(DISTINCT komoditas) FROM hasil_panen WHERE status = 'aktif') AS komoditas_tersedia"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $ringkasan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $ringkasan;
}

function ambil_daftar_pasar_lelang(mysqli $koneksi, string $q): array
{
    $stmt = $koneksi->prepare(
        "SELECT h.*,
                u.nama AS nama_petani,
                (SELECT COUNT(*) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS jumlah_tawaran,
                (SELECT MAX(p.harga_tawar) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS tawaran_tertinggi
         FROM hasil_panen h
         INNER JOIN users u ON u.id = h.user_id
         WHERE h.status = 'aktif'
           AND (? = '' OR CONCAT_WS(' ', h.komoditas, h.lokasi, u.nama) LIKE ?)
         ORDER BY h.created_at DESC, h.id DESC"
    );
    $pencarian = '%' . $q . '%';
    $stmt->bind_param('ss', $q, $pencarian);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $hasil;
}

function ambil_detail_pasar_lelang(mysqli $koneksi, int $id): ?array
{
    $stmt = $koneksi->prepare(
        "SELECT h.*,
                u.nama AS nama_petani,
                (SELECT COUNT(*) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS jumlah_tawaran,
                (SELECT MAX(p.harga_tawar) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS tawaran_tertinggi
         FROM hasil_panen h
         INNER JOIN users u ON u.id = h.user_id
         WHERE h.id = ? AND h.status = 'aktif'
         LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $hasil ?: null;
}

function ambil_penawaran_tengkulak(mysqli $koneksi, int $hasil_panen_id, int $user_id): array
{
    $stmt = $koneksi->prepare(
        'SELECT id, hasil_panen_id, user_id, harga_tawar, metode_pembayaran, armada_angkut, catatan, status, created_at
         FROM penawaran_lelang
         WHERE hasil_panen_id = ? AND user_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->bind_param('ii', $hasil_panen_id, $user_id);
    $stmt->execute();
    $penawaran = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $penawaran;
}

function buat_penawaran_lelang(mysqli $koneksi, int $hasil_panen_id, int $user_id, array $data): string
{
    if (validasi_input_penawaran($data)) {
        return 'invalid_input';
    }

    $harga_tawar = lelang_harga_ke_sen($data['harga_tawar']);
    $pecahan = explode('.', $data['harga_tawar'], 2);
    $harga_tawar_sql = ltrim($pecahan[0], '0');
    $harga_tawar_sql = ($harga_tawar_sql === '' ? '0' : $harga_tawar_sql) . '.' . str_pad($pecahan[1] ?? '', 2, '0');
    $transaksi_dimulai = false;

    try {
        $koneksi->begin_transaction();
        $transaksi_dimulai = true;

        $stmt = $koneksi->prepare(
            'SELECT id, harga_dasar FROM hasil_panen WHERE id = ? AND status = \'aktif\' FOR UPDATE'
        );
        $stmt->bind_param('i', $hasil_panen_id);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$hasil) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        if ($harga_tawar < lelang_harga_ke_sen((string) $hasil['harga_dasar'])) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'below_base';
        }

        $stmt = $koneksi->prepare(
            'SELECT harga_tawar
             FROM penawaran_lelang
             WHERE hasil_panen_id = ? AND user_id = ?
             ORDER BY harga_tawar DESC
             LIMIT 1'
        );
        $stmt->bind_param('ii', $hasil_panen_id, $user_id);
        $stmt->execute();
        $tawaran_saya = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($tawaran_saya && $harga_tawar <= lelang_harga_ke_sen((string) $tawaran_saya['harga_tawar'])) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_higher';
        }

        $metode_pembayaran = trim($data['metode_pembayaran']);
        $armada_angkut = trim($data['armada_angkut']);
        $catatan = trim($data['catatan']) === '' ? null : trim($data['catatan']);
        $stmt = $koneksi->prepare(
            'INSERT INTO penawaran_lelang
                (hasil_panen_id, user_id, harga_tawar, metode_pembayaran, armada_angkut, catatan)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iissss',
            $hasil_panen_id,
            $user_id,
            $harga_tawar_sql,
            $metode_pembayaran,
            $armada_angkut,
            $catatan
        );
        $stmt->execute();
        $stmt->close();

        $koneksi->commit();
        $transaksi_dimulai = false;
        return 'created';
    } catch (mysqli_sql_exception $exception) {
        if ($transaksi_dimulai) {
            $koneksi->rollback();
        }
        error_log('Gagal mengirim penawaran lelang: ' . $exception->getMessage());
        return 'error';
    }
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

function ambil_musim_tanam_berjalan(mysqli $koneksi, int $user_id): array
{
    $stmt = $koneksi->prepare(
        "SELECT id, nama_musim, komoditas
         FROM musim_tanam
         WHERE user_id = ? AND status = 'berjalan'
         ORDER BY tanggal_mulai DESC, id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $musim = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $musim;
}

function tetapkan_pemenang_lelang(
    mysqli $koneksi,
    int $hasil_panen_id,
    int $user_id,
    int $penawaran_id,
    int $musim_tanam_id
): string {
    $transaksi_dimulai = false;
    try {
        $koneksi->begin_transaction();
        $transaksi_dimulai = true;

        $stmt = $koneksi->prepare(
            'SELECT id, komoditas, estimasi_tonase, tanggal_panen, harga_dasar
             FROM hasil_panen
             WHERE id = ? AND user_id = ? AND status = \'aktif\'
             FOR UPDATE'
        );
        $stmt->bind_param('ii', $hasil_panen_id, $user_id);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$hasil) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        $stmt = $koneksi->prepare(
            'SELECT id, harga_tawar
             FROM penawaran_lelang
             WHERE id = ? AND hasil_panen_id = ? AND status = \'menunggu\'
             FOR UPDATE'
        );
        $stmt->bind_param('ii', $penawaran_id, $hasil_panen_id);
        $stmt->execute();
        $penawaran = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$penawaran) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'invalid_offer';
        }
        if (lelang_harga_ke_sen((string) $penawaran['harga_tawar']) < lelang_harga_ke_sen((string) $hasil['harga_dasar'])) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'invalid_offer';
        }

        $stmt = $koneksi->prepare(
            'SELECT id
             FROM musim_tanam
             WHERE id = ? AND user_id = ? AND status = \'berjalan\'
             FOR UPDATE'
        );
        $stmt->bind_param('ii', $musim_tanam_id, $user_id);
        $stmt->execute();
        $musim = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$musim) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'invalid_season';
        }

        $nominal = round((float) $penawaran['harga_tawar'] * (float) $hasil['estimasi_tonase'], 2);
        if (!is_finite($nominal) || $nominal <= 0 || $nominal > 9999999999999.99) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'amount_too_large';
        }

        $keterangan = 'Pendapatan lelang ' . $hasil['komoditas'];
        if (!catat_transaksi(
            $koneksi,
            $musim_tanam_id,
            'pendapatan',
            'hasil_lelang',
            $nominal,
            date('Y-m-d'),
            $keterangan,
            $hasil_panen_id
        )) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'already_recorded';
        }

        $stmt = $koneksi->prepare(
            'UPDATE penawaran_lelang
             SET status = \'kalah\'
             WHERE hasil_panen_id = ? AND id <> ?'
        );
        $stmt->bind_param('ii', $hasil_panen_id, $penawaran_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $koneksi->prepare(
            'UPDATE penawaran_lelang SET status = \'menang\' WHERE id = ? AND hasil_panen_id = ? AND status = \'menunggu\''
        );
        $stmt->bind_param('ii', $penawaran_id, $hasil_panen_id);
        $stmt->execute();
        $penawaran_diperbarui = $stmt->affected_rows;
        $stmt->close();
        if ($penawaran_diperbarui !== 1) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'invalid_offer';
        }

        $stmt = $koneksi->prepare(
            'UPDATE hasil_panen SET status = \'selesai\' WHERE id = ? AND user_id = ? AND status = \'aktif\''
        );
        $stmt->bind_param('ii', $hasil_panen_id, $user_id);
        $stmt->execute();
        $lelang_diperbarui = $stmt->affected_rows;
        $stmt->close();
        if ($lelang_diperbarui !== 1) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        $koneksi->commit();
        $transaksi_dimulai = false;
        return 'selected';
    } catch (mysqli_sql_exception $exception) {
        if ($transaksi_dimulai) {
            $koneksi->rollback();
        }
        error_log('Gagal menetapkan pemenang lelang: ' . $exception->getMessage());
        return 'error';
    }
}

function batalkan_lelang(mysqli $koneksi, int $hasil_panen_id, int $user_id): string
{
    $transaksi_dimulai = false;
    try {
        $koneksi->begin_transaction();
        $transaksi_dimulai = true;

        $stmt = $koneksi->prepare(
            'SELECT id FROM hasil_panen WHERE id = ? AND user_id = ? AND status = \'aktif\' FOR UPDATE'
        );
        $stmt->bind_param('ii', $hasil_panen_id, $user_id);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$hasil) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        $stmt = $koneksi->prepare(
            'UPDATE penawaran_lelang
             SET status = \'kalah\'
             WHERE hasil_panen_id = ? AND status = \'menunggu\''
        );
        $stmt->bind_param('i', $hasil_panen_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $koneksi->prepare(
            'UPDATE hasil_panen SET status = \'dibatalkan\' WHERE id = ? AND user_id = ? AND status = \'aktif\''
        );
        $stmt->bind_param('ii', $hasil_panen_id, $user_id);
        $stmt->execute();
        $diperbarui = $stmt->affected_rows;
        $stmt->close();
        if ($diperbarui !== 1) {
            $koneksi->rollback();
            $transaksi_dimulai = false;
            return 'not_active';
        }

        $koneksi->commit();
        $transaksi_dimulai = false;
        return 'cancelled';
    } catch (mysqli_sql_exception $exception) {
        if ($transaksi_dimulai) {
            $koneksi->rollback();
        }
        error_log('Gagal membatalkan lelang: ' . $exception->getMessage());
        return 'error';
    }
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
