{{--
    PBI-22 Pesan validasi bawaan browser (atribut required, type="email", min, max, minlength, dll.)
    dalam bahasa Indonesia. Tanpa ini, pesan mengikuti bahasa browser, misalnya
    "Please fill out this field.". Dipasang sekali di layout app dan guest.
--}}
<script>
    (() => {
        const tanggal = (nilai) => {
            const d = new Date(nilai);
            return Number.isNaN(d.getTime())
                ? nilai
                : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        };

        const pesan = (el) => {
            const v = el.validity;
            const jenis = (el.type || '').toLowerCase();
            const angka = (x) => (jenis === 'date' ? tanggal(x) : x);

            if (v.valueMissing) {
                if (jenis === 'radio' || jenis === 'checkbox' || el.tagName === 'SELECT') {
                    return 'Pilih salah satu pilihan ini.';
                }
                if (jenis === 'file') {
                    return 'Pilih berkas terlebih dahulu.';
                }
                return 'Kolom ini wajib diisi.';
            }
            if (v.typeMismatch) {
                if (jenis === 'email') {
                    return 'Masukkan alamat email yang valid, misalnya nama@tkceria.sch.id.';
                }
                if (jenis === 'url') {
                    return 'Masukkan alamat URL yang valid.';
                }
                return 'Format isian tidak sesuai.';
            }
            if (v.tooShort) {
                return `Minimal ${el.minLength} karakter (sekarang ${el.value.length} karakter).`;
            }
            if (v.tooLong) {
                return `Maksimal ${el.maxLength} karakter (sekarang ${el.value.length} karakter).`;
            }
            if (v.rangeUnderflow) {
                return jenis === 'date'
                    ? `Tanggal tidak boleh sebelum ${angka(el.min)}.`
                    : `Nilai minimal ${el.min}.`;
            }
            if (v.rangeOverflow) {
                return jenis === 'date'
                    ? `Tanggal tidak boleh setelah ${angka(el.max)}.`
                    : `Nilai maksimal ${el.max}.`;
            }
            if (v.stepMismatch) {
                return 'Masukkan nilai yang sesuai, misalnya bilangan bulat.';
            }
            if (v.badInput) {
                return jenis === 'number' ? 'Masukkan angka yang valid.' : 'Isian tidak valid.';
            }
            if (v.patternMismatch) {
                return el.title || 'Format isian tidak sesuai.';
            }

            return 'Isian tidak valid.';
        };

        // Bersihkan pesan kustom lama supaya field yang sudah benar bisa dikirim lagi.
        const bersihkan = (el) => {
            if (!el || typeof el.setCustomValidity !== 'function') {
                return;
            }
            if (el.type === 'radio' && el.name && el.form) {
                el.form.querySelectorAll(`input[type="radio"][name="${CSS.escape(el.name)}"]`)
                    .forEach((radio) => radio.setCustomValidity(''));
                return;
            }
            el.setCustomValidity('');
        };

        document.addEventListener('invalid', (event) => {
            const el = event.target;
            el.setCustomValidity('');
            if (!el.validity.valid) {
                el.setCustomValidity(pesan(el));
            }
        }, true);

        ['input', 'change'].forEach((nama) => {
            document.addEventListener(nama, (event) => bersihkan(event.target), true);
        });
    })();
</script>
