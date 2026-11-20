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
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
