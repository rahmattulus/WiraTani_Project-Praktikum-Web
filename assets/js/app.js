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

  /* Konfirmasi aksi permanen: <form data-confirm="Yakin ...?"> */
  document.addEventListener('submit', function (ev) {
    var pesan = ev.target.getAttribute('data-confirm');
    if (pesan && !window.confirm(pesan)) {
      ev.preventDefault();
    }
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

    var hitung = function () {
      var j = parseInt(jumlah.value, 10) || 0;
      var d = sewa ? (parseInt(durasi && durasi.value, 10) || 0) : 1;
      hasil.textContent = 'Rp ' + Math.round(j * harga * d).toLocaleString('id-ID');
    };

    jumlah.addEventListener('input', hitung);
    if (durasi) { durasi.addEventListener('input', hitung); }
    hitung();
  }
})();
