// ===== SMART LOCKER - SCRIPT UTAMA =====

// Data loker disimpan di sini
// Status: 'kosong' = hijau, 'terisi' = merah
let dataLoker = [
  {
    id: 1,
    status: 'kosong',
    pengguna: null,
    tanggal: null,
    alamat: null,
    noHp: null
  },
  {
    id: 2,
    status: 'kosong',
    pengguna: null,
    tanggal: null,
    alamat: null,
    noHp: null
  },
  {
    id: 3,
    status: 'kosong',
    pengguna: null,
    tanggal: null,
    alamat: null,
    noHp: null
  },
  {
    id: 4,
    status: 'kosong',
    pengguna: null,
    tanggal: null,
    alamat: null,
    noHp: null
  }
];

// Simpan data loker ke localStorage
function simpanDataLoker() {
  localStorage.setItem('dataLoker', JSON.stringify(dataLoker));
}

// Ambil data loker dari localStorage
function ambilDataLoker() {
  const data = localStorage.getItem('dataLoker');
  if (data) {
    dataLoker = JSON.parse(data);
  }
}

// Sensor nama: huruf pertama + *** (contoh: Rudi -> R***)
function sensorNama(nama) {
  if (!nama) return '-';
  return nama.charAt(0) + '***';
}

// Sensor alamat: 3 huruf pertama + ***
function sensorAlamat(alamat) {
  if (!alamat) return '-';
  return alamat.substring(0, 3) + '***';
}

// Jalankan saat halaman pertama kali dibuka
ambilDataLoker();