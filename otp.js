var BASE = 'https://smartlocker-ta.infinityfree.me/pages';
var countdown;
var otpTersimpan = null;
var otpExpiredAt = null;

window.onload = function () {
    var dt = localStorage.getItem('data_titip');
    if (!dt) {
        alert('Data tidak ditemukan. Silakan isi form terlebih dahulu.');
        window.location.href = BASE + '/pilih-loker.html';
        return;
    }
    var data = JSON.parse(dt);
    document.getElementById('infoHp').textContent = 'Nomor: ' + data.no_hp;
    kirimOTP(data.no_hp);
};

function kirimOTP(no_hp) {
    document.getElementById('loadingText').textContent = 'Membuat kode OTP...';
    document.getElementById('otpBox').style.display    = 'none';
    document.getElementById('otpInput').style.display  = 'none';
    document.getElementById('btnVerif').style.display  = 'none';
    document.getElementById('btnUlangi').style.display = 'none';

    var fd = new FormData();
    fd.append('no_hp', no_hp);

    fetch(BASE + '/kirim-otp-titip.php', {
        method: 'POST',
        body: fd
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        document.getElementById('loadingText').textContent = '';
        if (d.status === 'success') {
            otpTersimpan = d.otp;
            otpExpiredAt = Math.floor(Date.now() / 1000) + d.expired;

            var box = document.getElementById('otpBox');
            box.textContent   = 'Kode OTP kamu: ' + d.otp;
            box.style.display = 'block';
            document.getElementById('otpInput').style.display  = 'block';
            document.getElementById('btnVerif').style.display  = 'block';
            mulaiTimer(d.expired);
        } else {
            document.getElementById('loadingText').textContent = 'Gagal: ' + d.message;
            document.getElementById('btnUlangi').style.display = 'block';
        }
    })
    .catch(function (e) {
        console.log('Error kirimOTP:', e);
        document.getElementById('loadingText').textContent = 'Gagal terhubung ke server.';
        document.getElementById('btnUlangi').style.display = 'block';
    });
}

function mulaiTimer(detik) {
    clearInterval(countdown);
    document.getElementById('btnUlangi').style.display = 'none';
    var sisa = detik;
    var el   = document.getElementById('timer');

    countdown = setInterval(function () {
        var m = String(Math.floor(sisa / 60)).padStart(2, '0');
        var s = String(sisa % 60).padStart(2, '0');
        el.textContent = 'OTP berlaku: ' + m + ':' + s;
        sisa--;
        if (sisa < 0) {
            clearInterval(countdown);
            el.textContent = 'Kode OTP kadaluarsa!';
            otpTersimpan   = null;
            document.getElementById('btnUlangi').style.display = 'block';
        }
    }, 1000);
}

function verifikasiOTP() {
    var otp = document.getElementById('otpInput').value.trim();
    if (!otp) { alert('Masukkan kode OTP!'); return; }

    if (!otpTersimpan) {
        alert('OTP tidak ditemukan. Silakan klik Kirim Ulang OTP.');
        return;
    }
    if (Math.floor(Date.now() / 1000) > otpExpiredAt) {
        alert('OTP kadaluarsa. Silakan ulangi.');
        document.getElementById('btnUlangi').style.display = 'block';
        return;
    }

    var dt = JSON.parse(localStorage.getItem('data_titip'));
    var fd = new FormData();
    fd.append('otp',         otp);
    fd.append('otp_benar',   otpTersimpan);
    fd.append('otp_expired', otpExpiredAt);
    fd.append('no_hp',       dt.no_hp);

    fetch(BASE + '/verifikasi-otp.php', {
        method: 'POST',
        body: fd
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.status === 'success') {
            simpanData(dt);
        } else if (d.status === 'expired') {
            alert('OTP kadaluarsa. Silakan ulangi.');
            document.getElementById('btnUlangi').style.display = 'block';
        } else {
            alert(d.message);
        }
    })
    .catch(function () {
        alert('Error: Tidak bisa terhubung ke server.');
    });
}

function simpanData(dt) {
    var fd = new FormData();
    fd.append('nama',          dt.nama);
    fd.append('no_hp',         dt.no_hp);
    fd.append('tanggal_pakai', dt.tanggal_pakai);
    fd.append('alamat',        dt.alamat);
    fd.append('nomor_loker',   dt.nomor_loker);

    fetch(BASE + '/simpan-data.php', {
        method: 'POST',
        body: fd
    })
    .then(function (r) { return r.json(); })
    .then(function (h) {
        clearInterval(countdown);
        localStorage.removeItem('loker_dipilih');
        localStorage.removeItem('otp_no_hp');
        localStorage.removeItem('nama_pengguna');
        localStorage.removeItem('data_titip');

        if (h.status === 'success') {
            window.location.href = 'https://smartlocker-ta.infinityfree.me/index.php';
        } else {
            alert('Gagal simpan data: ' + h.message);
            window.location.href = 'https://smartlocker-ta.infinityfree.me/index.php';
        }
    })
    .catch(function () {
        alert('Error: Gagal menyimpan data.');
    });
}

function ulangiOTP() {
    var dt = localStorage.getItem('data_titip');
    if (!dt) {
        window.location.href = BASE + '/pilih-loker.html';
        return;
    }
    kirimOTP(JSON.parse(dt).no_hp);
}