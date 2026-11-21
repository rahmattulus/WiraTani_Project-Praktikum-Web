<?php
/* footer.php: penutup layout + skrip JS. Versi SEMENTARA, PIC asli: Agung (S05). */
$layout = $layout ?? 'app';
?>
<?php if ($layout === 'publik'): ?>
  </main>
<?php else: ?>
    </main>
  </div>
</div>
<?php endif; ?>
<dialog class="app-dialog" data-app-dialog role="alertdialog" aria-modal="true"
        aria-labelledby="app-dialog-title" aria-describedby="app-dialog-message">
  <div class="app-dialog-content">
    <div class="app-dialog-heading">
      <span class="app-dialog-icon" data-dialog-icon aria-hidden="true"></span>
      <h2 id="app-dialog-title" data-dialog-title></h2>
    </div>
    <p class="app-dialog-message" id="app-dialog-message" data-dialog-message></p>
    <div class="app-dialog-actions">
      <button class="btn btn-ghost" type="button" data-dialog-cancel>Batal</button>
      <button class="btn btn-primary" type="button" data-dialog-confirm>Oke</button>
    </div>
  </div>
</dialog>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
