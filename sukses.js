window.onload = function() {
  let nama    = localStorage.getItem('namaPengguna');
  let tanggal = localStorage.getItem('tanggalPakai');
  let alamat  = localStorage.getItem('alamatPengguna');
  let nomorHP = localStorage.getItem('nomorHP');
  let loker   = localStorage.getItem('lokerDipilih');

  document.getElementById('tampil-nama').textContent    = nama;
  document.getElementById('tampil-loker').textContent   = 'Loker ' + loker;
  document.getElementById('tampil-tanggal').textContent = tanggal;
  document.getElementById('tampil-hp').textContent      = nomorHP;

  simpanDataLoker(loker, nama, tanggal, alamat, nomorHP);

  // Auto redirect 5 detik
  let hitungan = 5;
  const elBtn  = document.querySelector('.tombol-hijau');
  if (elBtn) elBtn.textContent = `Kembali ke Halaman Utama (${hitungan}s)`;

  const timer = setInterval(() => {
    hitungan--;
    if (elBtn) elBtn.textContent = `Kembali ke Halaman Utama (${hitungan}s)`;
    if (hitungan <= 0) {
      clearInterval(timer);
      kembaliAwal();
    }
  }, 1000);
};

function simpanDataLoker(loker, nama, tanggal, alamat, nomorHP) {
  let dataLoker = JSON.parse(localStorage.getItem('dataLoker')) || {};
  dataLoker[loker] = {
    status: 'terisi',
    nama: nama,
    tanggal: tanggal,
    alamat: alamat,
    nomorHP: nomorHP
  };
  localStorage.setItem('dataLoker', JSON.stringify(dataLoker));
}

function kembaliAwal() {
  localStorage.removeItem('namaPengguna');
  localStorage.removeItem('tanggalPakai');
  localStorage.removeItem('alamatPengguna');
  localStorage.removeItem('nomorHP');
  localStorage.removeItem('lokerDipilih');

  // ✅ URL lengkap supaya pasti benar
  window.location.href = 'https://smartlocker-ta.infinityfree.me/index.html';
}