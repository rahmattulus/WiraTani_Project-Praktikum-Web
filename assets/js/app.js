/* app.js: JavaScript vanilla, tanpa library.
   Isi: laci menu, konfirmasi aksi, penutup flash message, estimasi total pesanan. */

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
