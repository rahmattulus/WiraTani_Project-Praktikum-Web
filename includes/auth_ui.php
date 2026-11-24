<?php
/* Komponen tampilan bersama untuk login.php dan register.php (hanya markup, tanpa logika). */

if (!function_exists('au_ikon')) {
    function au_ikon(string $k): string {
        static $p = [
            'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3.5 7.5l8.5 6 8.5-6"/>',
            'lock'    => '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'user'    => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/>',
            'phone'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
            'pin'     => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
            'eye'     => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'eye-off' => '<path d="M3 3l18 18M10.6 6.2A10 10 0 0 1 12 6c6 0 10 6 10 6a17 17 0 0 1-3.2 3.7M6.6 7.6A16 16 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
            'check'   => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
            'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
            'sprout'  => '<path d="M12 21v-9"/><path d="M12 12c0-4 2.5-6.5 7-6.5 0 4-2.5 6.5-7 6.5z"/><path d="M12 14.5c0-3-2-5-5.5-5 0 3 2 5 5.5 5z"/>',
            'gavel'   => '<rect x="9" y="5.5" width="12" height="7" rx="1.5" transform="rotate(45 15 9)"/><line x1="4.5" y1="19.5" x2="12.5" y2="11.5"/><line x1="3" y1="21" x2="11" y2="21"/>',
            'box'     => '<path d="M12 3l8 4.2v9.6L12 21l-8-4.2V7.2z"/><path d="M4 7.2l8 4.3 8-4.3M12 11.5V21"/>',
        ];
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$k] ?? '') . '</svg>';
    }
}

if (!function_exists('au_toggle_pw')) {
    /** Tombol tampil/sembunyi kata sandi untuk input dengan id tertentu. */
    function au_toggle_pw(string $id): string {
        return '<button type="button" class="au-toggle" data-toggle-pw="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" aria-label="Tampilkan kata sandi">'
             . '<span class="on">' . au_ikon('eye') . '</span><span class="off">' . au_ikon('eye-off') . '</span></button>';
    }
}

if (!function_exists('au_panel')) {
    /** Panel hijau di sisi kiri halaman auth. */
    function au_panel(string $judul, string $teks): void {
        $poin = [
            'Harga panen dibentuk lewat lelang yang terbuka',
            'Keuangan tiap musim tanam tercatat rapi',
            'Lowongan kerja tani dengan upah yang jelas',
        ];
        ?>
        <aside class="au-panel">
          <div class="au-brand">
            <svg viewBox="0 0 128 152" width="30" height="36" aria-hidden="true">
              <path d="M64 0 L128 24 V78 C128 116 96 140 64 152 C32 140 0 116 0 78 V24 Z" fill="#d9f5e3"/>
              <path d="M64 78 C38 78 28 60 28 38 C50 38 64 54 64 78 Z" fill="#0f3d2e"/>
              <path d="M64 78 C90 78 100 60 100 38 C78 38 64 54 64 78 Z" fill="#2fbf6f"/>
              <line x1="64" y1="78" x2="64" y2="116" stroke="#0f3d2e" stroke-width="8" stroke-linecap="round"/>
            </svg>
            <span>WiraTani</span>
          </div>

          <div class="au-panel-body">
            <h2><?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($teks, ENT_QUOTES, 'UTF-8') ?></p>
            <ul>
              <?php foreach ($poin as $t): ?>
                <li><?= au_ikon('check') ?> <?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <p class="au-panel-foot">Harga adil. Kerja layak. Tani berdaya.</p>
        </aside>
        <?php
    }
}