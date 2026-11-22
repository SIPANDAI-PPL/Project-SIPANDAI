{{--
    PBI-17 Ubah Tindak Lanjut: hanya tanggal mulai dan catatan pendukung.
    Strategi dan kategori respons awal ditampilkan tanpa bisa diubah; status dihitung ulang dari tanggal.
    Butuh: $tindakLanjut (dengan alternatif.anak.kelas dan alternatif.strategi).
--}}
@php
    $anak = $tindakLanjut->alternatif->anak;
    $hariIni = now()->toDateString();
    $nilaiTanggal = old('tanggal', $tindakLanjut->tanggal_mulai?->toDateString());
    $nilaiCatatan = old('catatan', $tindakLanjut->alasan_dugaan_penyebab ?? '');

    $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $nilaiTanggal) === 1;
    $labelStatus = $tanggalValid && $nilaiTanggal > $hariIni ? 'Direncanakan' : 'Berjalan';
    $tanggalTampil = $tanggalValid ? implode('/', array_reverse(explode('-', $nilaiTanggal))) : '-';

    $kelasLabel = 'mb-2 block text-sm font-semibold text-gray-900';
    $kelasKontrol = 'block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasKontrolSalah = 'border-red-400 bg-red-50';
    $kelasSalah = 'mt-1.5 text-xs text-red-600';
    $kelasInfoLabel = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
@endphp
<x-layouts.app title="Ubah Tindak Lanjut">
    <a href="{{ route('guru.tindak-lanjut.riwayat') }}" class="inline-flex items-center gap-1 text-sm text-indigo-800 hover:underline">&larr; Kembali ke riwayat tindak lanjut</a>

    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Ubah Tindak Lanjut</h1>
            <p class="mt-1 text-sm text-gray-600">Hanya tanggal mulai dan catatan pendukung yang dapat diubah.</p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('guru.tindak-lanjut.riwayat') }}" dusk="tombol-batal" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-indigo-900 bg-white px-5 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-50 sm:flex-none">Batal</a>
            <button type="submit" form="form-ubah-tindak-lanjut" dusk="tombol-simpan"
                class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800 sm:flex-none">Simpan Perubahan</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah">
            <p class="font-medium">Perubahan belum tersimpan. Periksa kembali isian berikut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        {{-- Data yang tidak dapat diubah --}}
        <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5 sm:p-6" aria-label="Data tindak lanjut">
            <dl class="space-y-4">
                <div>
                    <dt class="{{ $kelasInfoLabel }}">Anak</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $anak->nama }}</dd>
                    <dd class="text-xs text-gray-500">{{ $anak->kelas->kelompok }} {{ $anak->kelas->nama_kelas }}</dd>
                </div>
                <div>
                    <dt class="{{ $kelasInfoLabel }}">Strategi yang diterapkan</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $tindakLanjut->alternatif->strategi->nama }}</dd>
                    @if ($tindakLanjut->rencana)
                        <dd class="mt-0.5 text-xs leading-relaxed text-gray-600">{{ $tindakLanjut->rencana }}</dd>
                    @endif
                </div>
                <div>
                    <dt class="{{ $kelasInfoLabel }}">Kategori respons awal</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $tindakLanjut->kategori_respons_awal?->label() ?? '-' }}</dd>
                </div>
            </dl>
            <p class="mt-5 border-t border-gray-200 pt-4 text-xs leading-relaxed text-gray-500">
                Strategi dan respons awal tidak dapat diubah agar tetap menjadi pembanding saat evaluasi.
            </p>
        </section>

        {{-- Data yang dapat diubah --}}
        <form id="form-ubah-tindak-lanjut" method="POST" action="{{ route('guru.tindak-lanjut.update', $tindakLanjut) }}" novalidate
            class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="tanggal" class="{{ $kelasLabel }}">Tanggal mulai</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $nilaiTanggal }}" required dusk="isi-tanggal"
                    aria-describedby="keterangan-tanggal" @class([$kelasKontrol, 'sm:max-w-xs', $kelasKontrolSalah => $errors->has('tanggal')])>
                <p id="keterangan-tanggal" class="mt-1.5 text-xs text-gray-500">
                    <span data-tanggal-tampil>{{ $tanggalTampil }}</span> &middot;
                    Status otomatis: <span class="font-semibold text-gray-700" data-status-otomatis>{{ $labelStatus }}</span>
                </p>
                @error('tanggal')
                    <p class="{{ $kelasSalah }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="catatan" class="{{ $kelasLabel }}">Catatan pendukung <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="catatan" name="catatan" rows="7" maxlength="1000" dusk="isi-catatan" aria-describedby="keterangan-catatan"
                    placeholder="Tuliskan alasan atau dugaan penyebab, misalnya apa yang terlihat saat kegiatan."
                    @class([$kelasKontrol, $kelasKontrolSalah => $errors->has('catatan')])>{{ $nilaiCatatan }}</textarea>
                <p id="keterangan-catatan" class="mt-1.5 text-xs text-gray-500"><span data-jumlah-karakter>{{ mb_strlen((string) $nilaiCatatan) }}</span>/1000 karakter. Disimpan sebagai alasan atau dugaan penyebab.</p>
                @error('catatan')
                    <p class="{{ $kelasSalah }}">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </div>

    <script>
        (() => {
            const hariIni = @json($hariIni);
            const tanggal = document.getElementById('tanggal');
            const catatan = document.getElementById('catatan');

            tanggal.addEventListener('input', () => {
                const nilai = tanggal.value;
                document.querySelector('[data-tanggal-tampil]').textContent = nilai ? nilai.split('-').reverse().join('/') : '-';
                document.querySelector('[data-status-otomatis]').textContent = nilai && nilai > hariIni ? 'Direncanakan' : 'Berjalan';
            });
            catatan.addEventListener('input', () => {
                document.querySelector('[data-jumlah-karakter]').textContent = catatan.value.length;
            });
        })();
    </script>
</x-layouts.app>
