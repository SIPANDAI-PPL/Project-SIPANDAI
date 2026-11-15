{{--
    PBI-15 Dialog tambah alternatif strategi oleh guru.
    Dipakai ulang di Mode Fokus (PBI-16). Butuh: $pilihanStrategi (id, nama, deskripsi).
    Pemicu: elemen dengan data-tambah-alternatif='{"url","anak_id","anak","pola","strategi"}'.
--}}
@php
    $bukaLagi = old('_anak') && $errors->hasAny(['strategi_id', 'catatan']);
    $kelasInput = 'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';
@endphp
<dialog id="dialog-tambah-alternatif" dusk="dialog-tambah-alternatif"
    class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
    @if ($bukaLagi) data-buka-otomatis @endif>
    <form method="POST" data-form-alternatif
        action="{{ $bukaLagi ? route('guru.alternatif-strategi.store', ['anak' => old('_anak')]) : '' }}">
        @csrf
        <input type="hidden" name="_anak" value="{{ old('_anak') }}">
        <input type="hidden" name="_ringkasan_anak" value="{{ old('_ringkasan_anak') }}">
        <input type="hidden" name="_ringkasan_pola" value="{{ old('_ringkasan_pola') }}">
        <input type="hidden" name="_ringkasan_strategi" value="{{ old('_ringkasan_strategi') }}">

        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold">Tambah alternatif strategi</h2>
                    <p class="mt-1 text-sm text-gray-600">Tambahkan gagasan strategi lain yang sesuai dengan kebutuhan belajar anak.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            <dl class="mt-5 space-y-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                <div class="flex flex-wrap gap-x-1">
                    <dt class="text-gray-500">Anak:</dt>
                    <dd class="font-medium" data-ringkasan="anak">{{ old('_ringkasan_anak') }}</dd>
                </div>
                <div class="flex flex-wrap gap-x-1">
                    <dt class="text-gray-500">Pola teramati:</dt>
                    <dd class="font-medium text-orange-800" data-ringkasan="pola">{{ old('_ringkasan_pola') }}</dd>
                </div>
                <div class="flex flex-wrap gap-x-1">
                    <dt class="text-gray-500">Strategi saat ini:</dt>
                    <dd class="font-medium" data-ringkasan="strategi">{{ old('_ringkasan_strategi') }}</dd>
                </div>
            </dl>

            <div class="mt-5">
                <label for="strategi_id" class="block text-sm font-medium">Strategi alternatif <span class="text-red-600">*</span> <span class="font-normal text-gray-400">(wajib)</span></label>
                <select id="strategi_id" name="strategi_id" required dusk="pilih-strategi"
                    class="{{ $kelasInput }} mt-1.5 {{ $bukaLagi && $errors->has('strategi_id') ? $kelasInputSalah : $kelasInputNormal }}">
                    <option value="">Pilih strategi</option>
                    @foreach ($pilihanStrategi as $pilihan)
                        <option value="{{ $pilihan->id }}" @selected($bukaLagi && (string) old('strategi_id') === (string) $pilihan->id)>{{ $pilihan->nama }}</option>
                    @endforeach
                </select>
                @if ($bukaLagi && $errors->has('strategi_id'))
                    <p class="mt-1 text-xs text-red-600" data-pesan-salah dusk="salah-strategi">{{ $errors->first('strategi_id') }}</p>
                @endif
                <p data-pratinjau class="mt-3 hidden rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm leading-relaxed text-gray-700">
                    <strong data-pratinjau-nama class="font-semibold text-indigo-900"></strong>
                    <span data-pratinjau-deskripsi></span>
                </p>
            </div>

            <div class="mt-5">
                <label for="catatan" class="block text-sm font-medium">Alasan atau catatan guru <span class="font-normal text-gray-400">(opsional)</span></label>
                <textarea id="catatan" name="catatan" rows="3" maxlength="1000"
                    placeholder="Contoh: anak lebih antusias jika ada panduan visual konkret."
                    class="{{ $kelasInput }} mt-1.5 {{ $bukaLagi && $errors->has('catatan') ? $kelasInputSalah : $kelasInputNormal }}">{{ $bukaLagi ? old('catatan') : '' }}</textarea>
                @if ($bukaLagi && $errors->has('catatan'))
                    <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $errors->first('catatan') }}</p>
                @endif
            </div>

            <p class="mt-4 text-xs leading-relaxed text-gray-500">
                Alternatif ini ditambahkan oleh guru dan tampil sebagai saran untuk dipertimbangkan. Keputusan tetap di tangan guru.
            </p>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4 sm:px-8">
            <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
            <button type="submit" dusk="simpan-alternatif" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">&#10003; Simpan alternatif</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const DESKRIPSI = @json($pilihanStrategi->pluck('deskripsi', 'id'));
        const dialog = document.getElementById('dialog-tambah-alternatif');
        const form = dialog.querySelector('[data-form-alternatif]');
        const pilihStrategi = form.elements.strategi_id;
        const pratinjau = dialog.querySelector('[data-pratinjau]');

        // Semua teks dari data dimasukkan lewat textContent, bukan innerHTML.
        const perbaruiPratinjau = () => {
            const terpilih = pilihStrategi.value !== '';
            pratinjau.classList.toggle('hidden', !terpilih);
            if (!terpilih) return;
            const deskripsi = DESKRIPSI[pilihStrategi.value] ?? '';
            pratinjau.querySelector('[data-pratinjau-nama]').textContent = pilihStrategi.options[pilihStrategi.selectedIndex].text + (deskripsi ? ':' : '');
            pratinjau.querySelector('[data-pratinjau-deskripsi]').textContent = deskripsi;
        };

        const bersihkanSalah = () => {
            form.querySelectorAll('[data-pesan-salah]').forEach((el) => el.remove());
            form.querySelectorAll('.border-red-500').forEach((el) => {
                el.classList.remove('border-red-500', 'focus:border-red-500');
                el.classList.add('border-gray-300', 'focus:border-indigo-700');
            });
        };

        const buka = (data) => {
            bersihkanSalah();
            form.action = data.url;
            form.elements._anak.value = data.anak_id;
            for (const kunci of ['anak', 'pola', 'strategi']) {
                dialog.querySelector(`[data-ringkasan="${kunci}"]`).textContent = data[kunci];
                form.elements[`_ringkasan_${kunci}`].value = data[kunci];
            }
            pilihStrategi.value = '';
            form.elements.catatan.value = '';
            perbaruiPratinjau();
            dialog.showModal();
        };

        document.addEventListener('click', (event) => {
            const pemicu = event.target.closest('[data-tambah-alternatif]');
            if (pemicu) {
                event.preventDefault();
                buka(JSON.parse(pemicu.dataset.tambahAlternatif));
                return;
            }

            if (event.target.closest('[data-tutup]') && dialog.contains(event.target)) {
                dialog.close();
            }
        });

        pilihStrategi.addEventListener('change', perbaruiPratinjau);
        perbaruiPratinjau();

        if ('bukaOtomatis' in dialog.dataset) {
            dialog.showModal();
        }
    })();
</script>
