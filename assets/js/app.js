/* app.js: JavaScript vanilla, tanpa library.
   Isi: laci menu, modal tawaran, konfirmasi aksi, flash message, estimasi pesanan. */

(function () {
  'use strict';

  /* Laci menu (sidebar) di layar kecil */
  var sidebar = document.querySelector('.sidebar');
  var overlay = document.querySelector('.overlay');
  var menuBtn = document.querySelector('.menu-btn');
  if (sidebar && overlay && menuBtn) {
    menuBtn.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }

  /* Menu navigasi publik pada layar kecil */
  var publicNavToggle = document.querySelector('.public-nav-toggle');
  var publicNavLinks = document.querySelector('.public-nav-links');
  if (publicNavToggle && publicNavLinks) {
    function closePublicNav() {
      publicNavLinks.classList.remove('is-open');
      publicNavToggle.setAttribute('aria-expanded', 'false');
      publicNavToggle.setAttribute('aria-label', 'Buka menu');
    }

    publicNavToggle.addEventListener('click', function () {
      var isOpen = publicNavToggle.getAttribute('aria-expanded') === 'true';
      publicNavLinks.classList.toggle('is-open', !isOpen);
      publicNavToggle.setAttribute('aria-expanded', String(!isOpen));
      publicNavToggle.setAttribute('aria-label', isOpen ? 'Buka menu' : 'Tutup menu');
    });

    publicNavLinks.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        closePublicNav();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && publicNavToggle.getAttribute('aria-expanded') === 'true') {
        closePublicNav();
        publicNavToggle.focus();
      }
    });
  }

  /* Dialog aplikasi untuk pesan dan konfirmasi, tanpa dialog bawaan browser. */
  var appDialog = document.querySelector('[data-app-dialog]');
  var appDialogTitle = appDialog && appDialog.querySelector('[data-dialog-title]');
  var appDialogMessage = appDialog && appDialog.querySelector('[data-dialog-message]');
  var appDialogIcon = appDialog && appDialog.querySelector('[data-dialog-icon]');
  var appDialogCancel = appDialog && appDialog.querySelector('[data-dialog-cancel]');
  var appDialogConfirm = appDialog && appDialog.querySelector('[data-dialog-confirm]');
  var appDialogAction = null;

  function closeAppDialog() {
    if (appDialog && typeof appDialog.close === 'function' && appDialog.open) {
      appDialog.close();
    } else if (appDialog) {
      appDialog.removeAttribute('open');
    }
  }

  function showAppDialog(options) {
    if (!appDialog) { return; }

    appDialog.dataset.dialogKind = options.kind || 'info';
    appDialogTitle.textContent = options.title;
    appDialogMessage.textContent = options.message;
    appDialogIcon.textContent = options.kind === 'error' ? '!' : (options.kind === 'confirm' ? '?' : 'i');
    appDialogCancel.hidden = !options.cancelLabel;
    if (options.cancelLabel) {
      appDialogCancel.textContent = options.cancelLabel;
    }
    appDialogConfirm.textContent = options.confirmLabel || 'Oke';
    appDialogAction = options.onConfirm || null;

    if (typeof appDialog.showModal === 'function') {
      if (!appDialog.open) {
        appDialog.showModal();
      }
    } else {
      appDialog.setAttribute('open', '');
    }
    appDialogConfirm.focus();
  }

  if (appDialog) {
    appDialogConfirm.addEventListener('click', function () {
      var action = appDialogAction;
      appDialogAction = null;
      closeAppDialog();
      if (action) { action(); }
    });

    appDialogCancel.addEventListener('click', function () {
      appDialogAction = null;
      closeAppDialog();
    });

    appDialog.addEventListener('cancel', function () {
      appDialogAction = null;
    });

    appDialog.addEventListener('click', function (ev) {
      if (ev.target === appDialog) {
        appDialogAction = null;
        closeAppDialog();
      }
    });

    var popupNotice = document.querySelector('[data-app-notice]');
    if (popupNotice) {
      showAppDialog({
        kind: popupNotice.getAttribute('data-notice-kind'),
        title: popupNotice.getAttribute('data-notice-title'),
        message: popupNotice.getAttribute('data-notice-message')
      });
    }
  }

  /* Modal detail tawaran dan pemilihan pemenang lelang */
  var offerDialog = document.querySelector('[data-offer-dialog]');
  if (offerDialog) {
    var offerButtons = document.querySelectorAll('[data-offer-detail]');
    var winnerForm = offerDialog.querySelector('[data-winner-form]');
    var unavailableMessage = offerDialog.querySelector('[data-modal-unavailable]');

    offerButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        offerDialog.querySelector('[data-modal-name]').textContent = button.getAttribute('data-offer-name');
        offerDialog.querySelector('[data-modal-price]').textContent = button.getAttribute('data-offer-price');
        offerDialog.querySelector('[data-modal-payment]').textContent = button.getAttribute('data-offer-payment');
        offerDialog.querySelector('[data-modal-time]').textContent = button.getAttribute('data-offer-time');
        offerDialog.querySelector('[data-modal-armada]').textContent = button.getAttribute('data-offer-armada');
        offerDialog.querySelector('[data-modal-catatan]').textContent = button.getAttribute('data-offer-catatan');

        var status = offerDialog.querySelector('[data-modal-status]');
        status.className = 'badge ' + button.getAttribute('data-offer-status-class');
        status.textContent = button.getAttribute('data-offer-status');

        if (winnerForm) {
          winnerForm.querySelector('[name="id"]').value = button.getAttribute('data-offer-id');
          winnerForm.hidden = button.getAttribute('data-can-select') !== '1';
        }
        if (unavailableMessage) {
          unavailableMessage.hidden = button.getAttribute('data-can-select') === '1';
        }

        if (typeof offerDialog.showModal === 'function') {
          offerDialog.showModal();
        } else {
          offerDialog.setAttribute('open', '');
        }
      });
    });

    offerDialog.querySelectorAll('[data-modal-close]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (typeof offerDialog.close === 'function') {
          offerDialog.close();
        } else {
          offerDialog.removeAttribute('open');
        }
      });
    });

    offerDialog.addEventListener('click', function (ev) {
      if (ev.target === offerDialog) {
        if (typeof offerDialog.close === 'function') {
          offerDialog.close();
        } else {
          offerDialog.removeAttribute('open');
        }
      }
    });

    offerDialog.addEventListener('close', function () {
      if (winnerForm) {
        winnerForm.reset();
        winnerForm.hidden = true;
      }
      if (unavailableMessage) {
        unavailableMessage.hidden = true;
      }
    });
  }

  /* Konfirmasi aksi permanen memakai dialog aplikasi. */
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!(form instanceof HTMLFormElement)) { return; }
    var pesan = form.getAttribute('data-confirm');
    if (!pesan) { return; }
    if (form.dataset.confirmBypass === 'true') {
      delete form.dataset.confirmBypass;
      return;
    }

    ev.preventDefault();
    var submitter = ev.submitter;
    showAppDialog({
      kind: 'confirm',
      title: 'Konfirmasi tindakan',
      message: pesan,
      cancelLabel: 'Batal',
      confirmLabel: 'Lanjutkan',
      onConfirm: function () {
        form.dataset.confirmBypass = 'true';
        if (typeof form.requestSubmit === 'function') {
          if (submitter) {
            form.requestSubmit(submitter);
          } else {
            form.requestSubmit();
          }
        } else {
          form.submit();
        }
      }
    });
  });

  /* Flash message: klik untuk menutup */
  document.querySelectorAll('.alert').forEach(function (el) {
    el.title = 'Klik untuk menutup';
    el.style.cursor = 'pointer';
    el.addEventListener('click', function () { el.remove(); });
  });

  /* Estimasi total pesanan saprotan.
     <form data-form-pesan data-harga="285000" data-sewa="0"> dengan input
     jumlah, durasi_hari (khusus sewa) dan elemen [data-total]. */
  var form = document.querySelector('[data-form-pesan]');
  if (form) {
    var harga  = parseFloat(form.getAttribute('data-harga')) || 0;
    var sewa   = form.getAttribute('data-sewa') === '1';
    var jumlah = form.querySelector('[name="jumlah"]');
    var durasi = form.querySelector('[name="durasi_hari"]');
    var hasil  = form.querySelector('[data-total]');
    var stokAlert = document.getElementById('jumlah-stok-alert');

    var hitung = function () {
      var j = parseInt(jumlah.value, 10) || 0;
      var d = sewa ? (parseInt(durasi && durasi.value, 10) || 0) : 1;
      hasil.textContent = 'Rp ' + Math.round(j * harga * d).toLocaleString('id-ID');
      if (stokAlert) {
        var maks = parseInt(jumlah.max, 10) || 0;
        stokAlert.hidden = j <= maks;
      }
    };

    jumlah.addEventListener('input', hitung);
    if (durasi) { durasi.addEventListener('input', hitung); }
    hitung();
  }
})();
