{{--
    PBI-16 Bilah keputusan (sticky di bawah konten) dan dialog konfirmasi Abaikan di Mode Fokus.
    Butuh: $daftarAlternatif (semua alternatif anak); yang berstatus dipilih disebut di bilah.
    Pemicu dari _kartu-alternatif: [data-tandai] menandai kartu, [data-abaikan] membuka dialog.
--}}
@php
    $namaDipilih = $daftarAlternatif->where('status', 'dipilih')->map(fn ($saran) => $saran->strategi->nama)->implode(', ');
    $kelasTombolUtama = 'inline-flex min-h-11 items-center justify-center rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50';
    $kelasTombolSekunder = 'inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50';
@endphp
<form method="POST" action="" data-form-keputusan dusk="bilah-keputusan"
    class="sticky bottom-0 z-10 mt-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between sm:px-6">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="dipilih">

    <div class="min-w-0" aria-live="polite">
        <p class="font-semibold">Keputusan guru</p>
        @if ($namaDipilih !== '')
            <p class="mt-0.5 text-sm text-gray-600">Sudah dipilih dan tersimpan: {{ $namaDipilih }}.</p>
        @endif
        <p class="mt-0.5 text-sm text-gray-600" data-teks-keputusan>Belum ada alternatif yang ditandai. Tekan "Pilih Strategi Ini" pada salah satu kartu.</p>
    </div>
    <button type="submit" disabled data-simpan-keputusan dusk="simpan-keputusan" class="{{ $kelasTombolUtama }} w-full shrink-0 sm:w-auto">
        &#10003; Simpan Keputusan
    </button>
</form>

<dialog id="dialog-abaikan-alternatif" dusk="dialog-abaikan-alternatif"
    class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
    <form method="POST" action="" data-form-abaikan>
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="diabaikan">

        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold">Abaikan alternatif?</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Alternatif <strong class="font-semibold text-indigo-900" data-nama-abaikan></strong> akan ditandai diabaikan.
                    </p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <p class="mt-4 text-xs leading-relaxed text-gray-500">
                Alternatif tetap tersimpan dan tampil, tetapi keputusan ini tidak bisa diubah lagi.
            </p>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4 sm:px-8">
            <button type="button" data-tutup class="{{ $kelasTombolSekunder }}">Batal</button>
            <button type="submit" dusk="konfirmasi-abaikan" class="{{ $kelasTombolUtama }}">Ya, abaikan</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const formKeputusan = document.querySelector('[data-form-keputusan]');
        const tombolSimpan = formKeputusan.querySelector('[data-simpan-keputusan]');
        const teksKeputusan = formKeputusan.querySelector('[data-teks-keputusan]');
        const dialog = document.getElementById('dialog-abaikan-alternatif');
        const formAbaikan = dialog.querySelector('[data-form-abaikan]');
        const KELAS_TANDAI = ['border-2', 'border-indigo-900'];
        const KELAS_BIASA = ['border', 'border-gray-200'];

        // Hanya satu kartu boleh ditandai. Teks dari data dimasukkan lewat textContent.
        const tandai = (kartu) => {
            document.querySelectorAll('[data-kartu-alternatif]').forEach((lain) => {
                const aktif = lain === kartu;
                const tombol = lain.querySelector('[data-tandai]');
                if (!tombol) return;
                tombol.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                lain.querySelector('[data-penanda-ditandai]').classList.toggle('hidden', !aktif);
                lain.classList.remove(...(aktif ? KELAS_BIASA : KELAS_TANDAI));
                lain.classList.add(...(aktif ? KELAS_TANDAI : KELAS_BIASA));
            });

            formKeputusan.action = kartu ? kartu.dataset.url : '';
            tombolSimpan.disabled = !kartu;
            if (teksKeputusan) {
                teksKeputusan.textContent = kartu
                    ? `Alternatif ${kartu.dataset.nama} dipilih. Belum tersimpan, tekan Simpan Keputusan.`
                    : 'Belum ada alternatif yang ditandai. Tekan "Pilih Strategi Ini" pada salah satu kartu.';
            }
        };

        document.addEventListener('click', (event) => {
            const tombolTandai = event.target.closest('[data-tandai]');
            if (tombolTandai && !tombolTandai.disabled) {
                const kartu = tombolTandai.closest('[data-kartu-alternatif]');
                tandai(tombolTandai.getAttribute('aria-pressed') === 'true' ? null : kartu);
                return;
            }

            const tombolAbaikan = event.target.closest('[data-abaikan]');
            if (tombolAbaikan) {
                const kartu = tombolAbaikan.closest('[data-kartu-alternatif]');
                formAbaikan.action = kartu.dataset.url;
                dialog.querySelector('[data-nama-abaikan]').textContent = kartu.dataset.nama;
                dialog.showModal();
                return;
            }

            if (event.target.closest('[data-tutup]') && dialog.contains(event.target)) {
                dialog.close();
            }
        });
    })();
</script>
