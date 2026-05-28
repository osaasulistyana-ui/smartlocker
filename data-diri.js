window.onload = function () {
  const loker = localStorage.getItem('lokerDipilih') || '-';
  document.getElementById('namaLoker').textContent = loker;

  // Isi tanggal hari ini otomatis
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('tanggal').value = today;
};

function lanjutkan() {
  const nama    = document.getElementById('nama').value.trim();
  const tanggal = document.getElementById('tanggal').value;
  const alamat  = document.getElementById('alamat').value.trim();
  const nomorHP = document.getElementById('nomorHP').value.trim();

  if (!nama || !tanggal || !alamat || !nomorHP) {
    alert('Semua data harus diisi!');
    return;
  }

  if (nomorHP.length < 10) {
    alert('Nomor HP tidak valid!');
    return;
  }

  // Simpan ke localStorage
  localStorage.setItem('nama', nama);
  localStorage.setItem('tanggal', tanggal);
  localStorage.setItem('alamat', alamat);
  localStorage.setItem('nomorHP', nomorHP);

  window.location.href = 'otp.html';
}