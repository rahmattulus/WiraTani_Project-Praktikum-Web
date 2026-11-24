<?php
/* Petani: form pesan saprotan (S13/S22, Marvian).
   Dibuka dari katalog: saprotan_pesan.php?produk_id=1 */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/saprotan_helper.php';
require_once __DIR__ . '/../../config/koneksi.php';

$produk_id = (int) input_teks($_GET, 'produk_id');
$stmt_produk = $koneksi->prepare(
    'SELECT p.*, u.nama AS nama_kios, u.alamat AS alamat_kios
     FROM produk_saprotan p JOIN users u ON u.id = p.user_id
     WHERE p.id = ?'
);
$stmt_produk->bind_param('i', $produk_id);
$stmt_produk->execute();
$produk = $stmt_produk->get_result()->fetch_assoc();
if (!$produk || (int) $produk['stok'] <= 0) {
    set_flash('error', 'Produk tidak ditemukan atau stok habis.');
    redirect('index.php');
}
$sewa = $produk['kategori'] === 'sewa_alat';

$stmt_musim = $koneksi->prepare(
    'SELECT id, nama_musim, komoditas
     FROM musim_tanam
     WHERE user_id = ? AND status = "berjalan"
     ORDER BY tanggal_mulai DESC, id DESC'
);
$stmt_musim->bind_param('i', $_SESSION['user_id']);
$stmt_musim->execute();
$musim_berjalan = $stmt_musim->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('saprotan_pesan.php?produk_id=' . (int) $produk['id']);
    $musim_tanam_id = (int) input_teks($_POST, 'musim_tanam_id');
    $jumlah = (int) input_teks($_POST, 'jumlah');
    $tanggal_sewa = input_teks($_POST, 'tanggal_mulai_sewa');
    $durasi_hari = (int) input_teks($_POST, 'durasi_hari');
    $catatan = input_teks($_POST, 'catatan');
    $error = [];

    $cek_musim = $koneksi->prepare(
        'SELECT id FROM musim_tanam WHERE id = ? AND user_id = ? AND status = "berjalan"'
    );
    $cek_musim->bind_param('ii', $musim_tanam_id, $_SESSION['user_id']);
    $cek_musim->execute();
    if (!$cek_musim->get_result()->fetch_assoc()) {
        $error['musim_tanam_id'] = 'Musim tanam tidak valid atau sudah selesai.';
    }
    if ($jumlah < 1) {
        $error['jumlah'] = 'Jumlah harus tersedia dalam stok.';
    }
    if ($sewa) {
        $tanggal_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_sewa);
        if (!$tanggal_valid || $tanggal_sewa < date('Y-m-d')) {
            $error['tanggal_mulai_sewa'] = 'Tanggal mulai sewa harus hari ini atau sesudahnya.';
        }
        if ($durasi_hari < 1) {
            $error['durasi_hari'] = 'Durasi sewa minimal 1 hari.';
        }
    } else {
        $tanggal_sewa = null;
        $durasi_hari = null;
    }
    if (mb_strlen($catatan) > 255) {
        $error['catatan'] = 'Catatan maksimal 255 karakter.';
    }

    if (!$error) {
        $transaksi_dimulai = false;
        try {
            $koneksi->begin_transaction();
            $transaksi_dimulai = true;

            $lock = $koneksi->prepare(
                'SELECT harga, kategori, stok
                 FROM produk_saprotan
                 WHERE id = ?
                 FOR UPDATE'
            );
            $lock->bind_param('i', $produk_id);
            $lock->execute();
            $produk_terkini = $lock->get_result()->fetch_assoc();

            if (!$produk_terkini) {
                throw new RuntimeException('Produk sudah tidak tersedia.');
            }
            if ($produk_terkini['kategori'] !== $produk['kategori']) {
                throw new RuntimeException('Data produk berubah. Muat ulang halaman sebelum memesan.');
            }
            if ($jumlah > (int) $produk_terkini['stok']) {
                $produk['stok'] = (int) $produk_terkini['stok'];
                throw new RuntimeException('Stok tersedia tidak cukup. Periksa kembali jumlah pesanan.');
            }

            $kurang = $koneksi->prepare(
                'UPDATE produk_saprotan
                 SET stok = stok - ?
                 WHERE id = ? AND stok >= ?'
            );
            $kurang->bind_param('iii', $jumlah, $produk_id, $jumlah);
            $kurang->execute();
            if ($kurang->affected_rows !== 1) {
                throw new RuntimeException('Stok berubah saat pesanan diproses. Silakan coba lagi.');
            }

            $harga_satuan = (float) $produk_terkini['harga'];
            $total_harga = hitung_total_pesanan(
                $produk_terkini['kategori'],
                $harga_satuan,
                $jumlah,
                $durasi_hari
            );
            if ($sewa) {
                $insert = $koneksi->prepare(
                    'INSERT INTO pesanan_saprotan
                     (user_id, produk_saprotan_id, musim_tanam_id, jumlah, harga_satuan, total_harga,
                      tanggal_mulai_sewa, durasi_hari, catatan, status, stok_direservasi)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "menunggu", 1)'
                );
                $insert->bind_param(
                    'iiiiddsis',
                    $_SESSION['user_id'], $produk_id, $musim_tanam_id, $jumlah,
                    $harga_satuan, $total_harga, $tanggal_sewa, $durasi_hari, $catatan
                );
            } else {
                $insert = $koneksi->prepare(
                    'INSERT INTO pesanan_saprotan
                     (user_id, produk_saprotan_id, musim_tanam_id, jumlah, harga_satuan, total_harga,
                      tanggal_mulai_sewa, durasi_hari, catatan, status, stok_direservasi)
                     VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, ?, "menunggu", 1)'
                );
                $insert->bind_param(
                    'iiiidds',
                    $_SESSION['user_id'], $produk_id, $musim_tanam_id, $jumlah,
                    $harga_satuan, $total_harga, $catatan
                );
            }
            $insert->execute();

            $koneksi->commit();
            $transaksi_dimulai = false;
            set_flash('success', 'Pesanan berhasil dikirim dan stok telah direservasi.');
            redirect('saprotan_riwayat.php');
        } catch (Throwable $ex) {
            if ($transaksi_dimulai) {
                $koneksi->rollback();
            }
            if ($ex instanceof RuntimeException) {
                $error['form'] = $ex->getMessage();
            } else {
                error_log('Gagal membuat pesanan saprotan: ' . $ex->getMessage());
                $error['form'] = 'Pesanan gagal dikirim. Silakan coba lagi.';
            }
        }
    }
}

$judul_halaman = 'Pesan Saprotan';
$menu_aktif    = 'saprotan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title"><?= e($produk['nama_produk']) ?></h2>
            <span class="badge badge-muted"><?= e(label_kategori($produk['kategori'])) ?></span>
          </div>
          <p><?= e($produk['deskripsi']) ?></p>
          <p>
            <strong><?= e(format_rupiah($produk['harga'])) ?> / <?= e($produk['satuan']) ?></strong><br>
            <?= e($produk['nama_kios']) ?>, <?= e($produk['alamat_kios']) ?><br>
            Stok tersedia: <?= e(teks_stok($produk)) ?>
          </p>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Detail pesanan</h2>
          </div>
          <?php if (isset($error['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($error['form']) ?></div>
          <?php endif; ?>
          <div class="alert alert-error" id="jumlah-stok-alert" role="alert"
               <?= !isset($error['jumlah']) || $error['jumlah'] !== 'Jumlah yang anda butuhkan melebihi stock yang tersedia' ? 'hidden' : '' ?>>
            Jumlah yang anda butuhkan melebihi stock yang tersedia
          </div>

          <?php if (!$musim_berjalan): ?>
            <div class="empty-state">
              <p>Pesanan harus dikaitkan dengan musim tanam yang sedang berjalan, dan Anda belum punya.</p>
              <a class="btn btn-primary" href="../musim/form.php">Buat musim tanam</a>
            </div>
          <?php else: ?>
            <form method="post" action="" data-form-pesan
                  data-harga="<?= (float) $produk['harga'] ?>" data-sewa="<?= $sewa ? '1' : '0' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="produk_saprotan_id" value="<?= (int) $produk['id'] ?>">

              <div class="form-grid">
                <div class="form-group">
                  <label for="musim_tanam_id">Untuk musim tanam</label>
                  <select class="select" id="musim_tanam_id" name="musim_tanam_id" required>
                    <?php foreach ($musim_berjalan as $m): ?>
                      <option value="<?= (int) $m['id'] ?>"><?= e($m['nama_musim']) ?> (<?= e($m['komoditas']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($error)) tampil_error($error, 'musim_tanam_id'); ?>
                </div>

                <div class="form-group">
                  <label for="jumlah"><?= $sewa ? 'Jumlah unit' : 'Jumlah (' . e($produk['satuan']) . ')' ?></label>
                  <input class="input" type="number" id="jumlah" name="jumlah" min="1" max="<?= (int) $produk['stok'] ?>"
                         step="1" value="1" required>
                  <?php if (isset($error)) tampil_error($error, 'jumlah'); ?>
                </div>

                <?php if ($sewa): ?>
                  <div class="form-group">
                    <label for="tanggal_mulai_sewa">Tanggal mulai sewa</label>
                    <input class="input" type="date" id="tanggal_mulai_sewa" name="tanggal_mulai_sewa"
                           min="<?= date('Y-m-d') ?>" required>
                    <?php if (isset($error)) tampil_error($error, 'tanggal_mulai_sewa'); ?>
                  </div>

                  <div class="form-group">
                    <label for="durasi_hari">Durasi (hari)</label>
                    <input class="input" type="number" id="durasi_hari" name="durasi_hari" min="1" step="1" value="1" required>
                    <?php if (isset($error)) tampil_error($error, 'durasi_hari'); ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <label for="catatan">Catatan untuk kios (opsional)</label>
                <textarea class="textarea" id="catatan" name="catatan" maxlength="255"></textarea>
                <?php if (isset($error)) tampil_error($error, 'catatan'); ?>
              </div>

              <p>Perkiraan total: <strong data-total><?= e(format_rupiah($produk['harga'])) ?></strong></p>

              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Kirim pesanan</button>
                <a class="btn btn-ghost" href="index.php">Batal</a>
              </div>
            </form>
          <?php endif; ?>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
