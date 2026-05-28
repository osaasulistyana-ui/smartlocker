// Fungsi sensor teks
function sensor(teks, tampil) {
  if (!teks) return '-';
  return teks.substring(0, tampil) + '*'.repeat(Math.max(teks.length - tampil, 2));
}

// Fungsi sensor nomor HP (tampil 4 digit awal)
function sensorHP(hp) {
  if (!hp) return '-';
  return hp.substring(0, 4) + '****';
}

// Render semua loker
function renderLoker() {
  const dataLoker = JSON.parse(localStorage.getItem('dataLoker')) || {};

  for (let i = 1; i <= 4; i++) {
    const card = document.getElementById(`loker-${i}`);
    const info = document.getElementById(`info-${i}`);
    const btn  = document.getElementById(`btn-${i}`);

    // Jika elemen tidak ditemukan, skip
    if (!card || !info || !btn) continue;

    const data = dataLoker[`Loker ${i}`];

    if (data) {
      // Loker TERISI - merah
      card.className = 'loker-card loker-merah';
      info.innerHTML = `
        <div class="loker-status">🔴 Ada Barang</div>
        <div class="loker-info-item">👤 ${sensor(data.nama, 1)}</div>
        <div class="loker-info-item">🏠 ${sensor(data.alamat, 1)}</div>
        <div class="loker-info-item">📱 ${sensorHP(data.nomorHP)}</div>
        <div class="loker-info-item">📅 ${data.tanggal}</div>
      `;
      btn.textContent = 'Terisi';
      btn.disabled    = true;
      btn.className   = 'loker-btn btn-disabled';
    } else {
      // Loker KOSONG - hijau
      card.className = 'loker-card loker-hijau';
      info.innerHTML = `
        <div class="loker-status">🟢 Kosong</div>
        <div class="loker-info-kosong">Tersedia untuk digunakan</div>
      `;
      btn.textContent = 'Pilih';
      btn.disabled    = false;
      btn.className   = 'loker-btn btn-pilih';
      btn.onclick     = () => pilihLoker(i); // pastikan onclick terpasang
    }
  }
}

// Pilih loker
function pilihLoker(nomor) {
  const namaLoker = `Loker ${nomor}`;
  localStorage.setItem('lokerDipilih', namaLoker);
  window.location.href = 'data-diri.html';
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', renderLoker);