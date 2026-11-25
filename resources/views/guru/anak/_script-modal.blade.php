{{-- Buka/tutup modal. Simpan data lewat form biasa (POST); tab memakai tautan. --}}
<script>
    document.addEventListener('click', function (e) {
        var buka = e.target.closest('[data-modal-buka]');
        if (buka) {
            e.preventDefault();
            document.querySelectorAll('[data-modal]').forEach(function (m) { m.classList.add('hidden'); });
            document.getElementById(buka.dataset.modalBuka).classList.remove('hidden');
            return;
        }

        var tutup = e.target.closest('[data-modal-tutup]');
        if (tutup) {
            e.preventDefault();
            tutup.closest('[data-modal]').classList.add('hidden');
            return;
        }
    });

    // Kolom Usia terisi otomatis dari tanggal lahir dalam tahun penuh, sama dengan
    // Anak::hitungUsiaTahun di server. Batas usia tetap dicek dalam bulan (24–59 bulan).
    document.querySelectorAll('[data-usia-dari]').forEach(function (kolomUsia) {
        var kolomLahir = document.getElementById(kolomUsia.dataset.usiaDari);
        if (!kolomLahir) return;

        function perbarui() {
            var bagian = (kolomLahir.value || '').split('-');
            if (bagian.length !== 3) { kolomUsia.value = ''; return; }

            var lahir = new Date(+bagian[0], +bagian[1] - 1, +bagian[2]);
            var hariIni = new Date();
            hariIni.setHours(0, 0, 0, 0);
            if (isNaN(lahir) || lahir > hariIni) { kolomUsia.value = 'Tanggal lahir tidak valid'; return; }

            var bulan = (hariIni.getFullYear() - lahir.getFullYear()) * 12 + (hariIni.getMonth() - lahir.getMonth());
            if (hariIni.getDate() < lahir.getDate()) bulan--;

            var teks = Math.floor(bulan / 12) + ' tahun';
            if (bulan < 24 || bulan > 59) teks += ' · di luar batas 24–59 bulan';
            kolomUsia.value = teks;
        }

        kolomLahir.addEventListener('input', perbarui);
        kolomLahir.addEventListener('change', perbarui);
        perbarui();
    });

    // Pesan berhasil hilang sendiri setelah 4 detik (memudar 0,5 detik lalu dihapus).
    document.querySelectorAll('[data-hilang-otomatis]').forEach(function (pesan) {
        setTimeout(function () {
            pesan.classList.add('opacity-0');
            setTimeout(function () { pesan.remove(); }, 500);
        }, 4000);
    });
</script>
