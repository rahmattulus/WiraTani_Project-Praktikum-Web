<?php
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/lelang_helper.php';

$user_id = user_id_login();
$database_error = '';
$pendapatan = 0.0;
$pengeluaran = 0.0;
$laba_rugi = 0.0;
$spark_pendapatan = [15, 20, 18, 28, 24, 32];
$spark_pengeluaran = [12, 18, 24, 16, 22, 20];
$lelang = [
    'aktif' => 0,
    'selesai' => 0,
    'dibatalkan' => 0,
];
$lelang_total = 0;
$panen_terdekat = null;
$tawaran_terbaru = [];
$pesanan_diproses = 0;
$lowongan_buka = 0;

try {
    $stmt = $koneksi->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN t.jenis = 'pendapatan' THEN t.nominal ELSE 0 END), 0) AS pendapatan,
            COALESCE(SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.nominal ELSE 0 END), 0) AS pengeluaran
         FROM musim_tanam m
         LEFT JOIN transaksi_keuangan t ON t.musim_tanam_id = m.id
         WHERE m.user_id = ?"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $ringkasan_keuangan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $pendapatan = (float) ($ringkasan_keuangan['pendapatan'] ?? 0);
    $pengeluaran = (float) ($ringkasan_keuangan['pengeluaran'] ?? 0);
    $laba_rugi = $pendapatan - $pengeluaran;

    $stmt = $koneksi->prepare(
        "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') AS bulan, t.jenis,
                SUM(t.nominal) AS nominal
         FROM transaksi_keuangan t
         INNER JOIN musim_tanam m ON m.id = t.musim_tanam_id
         WHERE m.user_id = ?
           AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
         GROUP BY bulan, t.jenis
         ORDER BY bulan ASC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $data_spark = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $bulan_spark = [];
    for ($i = 5; $i >= 0; $i--) {
        $bulan_spark[date('Y-m', strtotime("-{$i} months"))] = [
            'pendapatan' => 0.0,
            'pengeluaran' => 0.0,
        ];
    }
    foreach ($data_spark as $baris) {
        if (isset($bulan_spark[$baris['bulan']])) {
            $bulan_spark[$baris['bulan']][$baris['jenis']] = (float) $baris['nominal'];
        }
    }
    $spark_pendapatan = array_map(
        static fn(array $nilai): float => $nilai['pendapatan'],
        array_values($bulan_spark)
    );
    $spark_pengeluaran = array_map(
        static fn(array $nilai): float => $nilai['pengeluaran'],
        array_values($bulan_spark)
    );

    $stmt = $koneksi->prepare(
        "SELECT status, COUNT(*) AS jumlah
         FROM hasil_panen
         WHERE user_id = ?
         GROUP BY status"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $baris) {
        $lelang[$baris['status']] = (int) $baris['jumlah'];
    }
    $stmt->close();
    $lelang_total = array_sum($lelang);

    $stmt = $koneksi->prepare(
        "SELECT h.id, h.komoditas, h.estimasi_tonase, h.tanggal_panen, h.harga_dasar,
                MAX(p.harga_tawar) AS tawaran_tertinggi
         FROM hasil_panen h
         LEFT JOIN penawaran_lelang p ON p.hasil_panen_id = h.id
         WHERE h.user_id = ? AND h.status = 'aktif'
         GROUP BY h.id, h.komoditas, h.estimasi_tonase, h.tanggal_panen, h.harga_dasar
         ORDER BY (h.tanggal_panen >= CURDATE()) DESC,
                  ABS(DATEDIFF(h.tanggal_panen, CURDATE())) ASC,
                  h.id DESC
         LIMIT 1"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $panen_terdekat = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT p.id, p.harga_tawar, p.created_at, p.status,
                h.komoditas, u.nama AS nama_tengkulak
         FROM penawaran_lelang p
         INNER JOIN hasil_panen h ON h.id = p.hasil_panen_id
         INNER JOIN users u ON u.id = p.user_id
         WHERE h.user_id = ?
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $tawaran_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*)
         FROM pesanan_saprotan ps
         INNER JOIN musim_tanam m ON m.id = ps.musim_tanam_id
         WHERE m.user_id = ? AND ps.status IN ('menunggu', 'disetujui')"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $pesanan_diproses = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) FROM lowongan_kerja
         WHERE user_id = ? AND status = 'buka'"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $lowongan_buka = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat dashboard petani: ' . $exception->getMessage());
    $database_error = 'Dashboard petani gagal dimuat. Silakan muat ulang halaman.';
}

$spark_pendapatan = array_map(
    static function (float $nilai) use ($spark_pendapatan): int {
        $maksimum = max($spark_pendapatan);
        return $maksimum > 0 ? (int) max(12, round(($nilai / $maksimum) * 100)) : 12;
    },
    $spark_pendapatan
);
$spark_pengeluaran = array_map(
    static function (float $nilai) use ($spark_pengeluaran): int {
        $maksimum = max($spark_pengeluaran);
        return $maksimum > 0 ? (int) max(12, round(($nilai / $maksimum) * 100)) : 12;
    },
    $spark_pengeluaran
);

$judul_halaman = 'Dashboard Petani';
$menu_aktif    = 'dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
          <div class="dash-r1">
            <article class="stat">
              <p class="stat-label">Pendapatan semua musim</p>
              <div class="stat-head">
                <p class="stat-value"><?= e(format_rupiah($pendapatan)) ?></p>
                <div class="spark" aria-label="Grafik pendapatan 6 bulan">
                  <?php foreach ($spark_pendapatan as $tinggi): ?>
                    <i style="--h: <?= $tinggi ?>"></i>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
            <article class="stat">
              <p class="stat-label">Total biaya</p>
              <div class="stat-head">
                <p class="stat-value"><?= e(format_rupiah($pengeluaran)) ?></p>
                <div class="spark stat-down" aria-label="Grafik biaya 6 bulan">
                  <?php foreach ($spark_pengeluaran as $tinggi): ?>
                    <i style="--h: <?= $tinggi ?>"></i>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
            <article class="stat <?= $laba_rugi >= 0 ? 'stat-up' : 'stat-down' ?>">
              <p class="stat-label">Laba/rugi bersih</p>
              <p class="stat-value <?= $laba_rugi >= 0 ? 'stat-up' : 'stat-down' ?>">
                <?= $laba_rugi < 0 ? '-' : '' ?><?= e(format_rupiah(abs($laba_rugi))) ?>
              </p>
            </article>
          </div>

          <div class="dash-r2">
            <section class="card">
              <div class="card-head">
                <h2 class="card-title">Ringkasan lelang</h2>
              </div>
              <div class="progress-legend">
                <span class="progress-legend-item">
                  <i class="progress-dot"></i> Aktif <strong><?= (int) $lelang['aktif'] ?></strong>
                </span>
                <span class="progress-legend-item">
                  <i class="progress-dot complete"></i> Selesai <strong><?= (int) $lelang['selesai'] ?></strong>
                </span>
                <span class="progress-legend-item">
                  <i class="progress-dot cancelled"></i> Dibatalkan <strong><?= (int) $lelang['dibatalkan'] ?></strong>
                </span>
              </div>
              <?php
                $persen_aktif = $lelang_total > 0 ? ($lelang['aktif'] / $lelang_total) * 100 : 0;
                $persen_selesai_bar = $lelang_total > 0 ? ($lelang['selesai'] / $lelang_total) * 100 : 0;
                $persen_dibatalkan = $lelang_total > 0 ? ($lelang['dibatalkan'] / $lelang_total) * 100 : 0;
              ?>
              <div class="progress" aria-label="<?= $lelang_total ?> lelang">
                <span class="progress-segment active" style="width: <?= (float) $persen_aktif ?>%"></span>
                <span class="progress-segment complete" style="width: <?= (float) $persen_selesai_bar ?>%"></span>
                <span class="progress-segment cancelled" style="width: <?= (float) $persen_dibatalkan ?>%"></span>
              </div>
            </section>

            <section class="card-dark panen-terdekat">
              <p class="stat-label" style="color: var(--nav-text);">Panen terdekat</p>
              <?php if ($panen_terdekat): ?>
                <h2><?= e($panen_terdekat['komoditas']) ?></h2>
                <p><?= e(number_format((float) $panen_terdekat['estimasi_tonase'], 0, ',', '.')) ?> kg · <?= e(format_tanggal($panen_terdekat['tanggal_panen'])) ?></p>
                <p>Harga dasar <strong><?= e(format_rupiah($panen_terdekat['harga_dasar'])) ?>/kg</strong></p>
                <p>Tawaran tertinggi:
                  <strong><?= $panen_terdekat['tawaran_tertinggi'] !== null ? e(format_rupiah($panen_terdekat['tawaran_tertinggi'])) . '/kg' : 'Belum ada' ?></strong>
                </p>
                <a class="btn btn-primary btn-sm"
                   href="<?= BASE_URL ?>/petani/lelang/detail.php?id=<?= (int) $panen_terdekat['id'] ?>">Lihat lelang</a>
              <?php else: ?>
                <h2>Belum ada panen aktif</h2>
                <p>Buat lelang untuk hasil panen Anda.</p>
              <?php endif; ?>
            </section>

            <section class="cta lelang-cta">
              <div class="cta-content">
                <h2>Siap panen? Buat lelang panen baru</h2>
                <p>Publikasikan hasil panen kepada tengkulak.</p>
                <a class="btn" href="<?= BASE_URL ?>/petani/lelang/form.php">Buat lelang</a>
              </div>
              <span class="cta-decoration" aria-hidden="true"></span>
            </section>
          </div>

          <div class="dash-r3">
            <section class="card">
              <div class="card-head">
                <h2 class="card-title">Tawaran terbaru</h2>
                <a class="card-link" href="<?= BASE_URL ?>/petani/lelang/index.php">Lihat lelang</a>
              </div>
              <?php if (!$tawaran_terbaru): ?>
                <div class="empty-state">
                  <p>Belum ada tawaran masuk.</p>
                </div>
              <?php else: ?>
                <div class="table-wrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Lelang</th>
                        <th>Penawar</th>
                        <th>Harga/kg</th>
                        <th>Waktu</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($tawaran_terbaru as $tawaran): ?>
                        <tr>
                          <td data-label="Lelang"><?= e($tawaran['komoditas']) ?></td>
                          <td data-label="Penawar"><?= e($tawaran['nama_tengkulak']) ?></td>
                          <td data-label="Harga/kg"><?= e(format_rupiah($tawaran['harga_tawar'])) ?></td>
                          <td data-label="Waktu"><?= e(format_tanggal($tawaran['created_at'])) ?></td>
                          <td data-label="Status">
                            <span class="badge <?= e(kelas_status_penawaran($tawaran['status'])) ?>">
                              <?= e(label_status_penawaran($tawaran['status'])) ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </section>

            <div class="dash-r3-column">
              <div class="mini-grid">
                <a class="stat" href="<?= BASE_URL ?>/petani/saprotan/index.php">
                  <p class="stat-label">Pesanan saprotan diproses</p>
                  <p class="stat-value"><?= $pesanan_diproses ?></p>
                </a>
                <a class="stat" href="<?= BASE_URL ?>/petani/lowongan/index.php">
                  <p class="stat-label">Lowongan masih buka</p>
                  <p class="stat-value"><?= $lowongan_buka ?></p>
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
