
@php
    $observasi = $observasi ?? null;
    $nilaiKelas = old('kelas_id', $observasi?->anak?->kelas_id);
    $nilaiAnak = old('anak_id', $observasi?->anak_id);
    $nilaiRespons = old('kategori_respons', $observasi?->kategori_respons?->value);
    $nilaiTanggal = old('tanggal', $observasi?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d'));

    $deskripsiRespons = [
        'terlibat_aktif' => 'Anak antusias dan melakukan kegiatan dengan mandiri.',
        'terlibat_dengan_pendampingan' => 'Anak ikut serta setelah diarahkan atau didampingi guru.',
        'kurang_tertarik' => 'Anak terlihat kurang bersemangat atau mudah beralih.',
        'belum_terlibat' => 'Anak belum mau atau belum mampu ikut kegiatan.',
    ];

    $warnaRespons = [
        'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'terlibat_dengan_pendampingan' => 'border-blue-200 bg-blue-50 text-blue-800',
        'kurang_tertarik' => 'border-orange-200 bg-orange-50 text-orange-800',
        'belum_terlibat' => 'border-gray-300 bg-gray-50 text-gray-700',
    ];
@endphp

<x-layouts.app title="Data Observasi">
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Judul halaman --}}
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Formulir Observasi Perkembangan
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Catat respons anak terhadap kegiatan yang diamati,
                beserta aspek perkembangan dan strategi yang digunakan.
            </p>
        </div>

        {{-- Informasi guru --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3"
            aria-label="Informasi guru">

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Guru Pengajar</p>
                <p class="mt-1 font-semibold text-gray-900">
                    {{ auth()->user()->nama }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Tahun Ajaran</p>
                <p class="mt-1 font-semibold text-gray-900">
                    {{ $tahunAjaran }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Status</p>
                <p class="mt-1 font-semibold text-emerald-700">Aktif</p>
            </div>
        </section>

        {{-- Pesan kesalahan --}}
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                role="alert">
                <p class="font-semibold">Periksa kembali isian Anda:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form pencatatan observasi --}}
        <form class="space-y-5" method="POST"
            action="{{ route('guru.observasi.store') }}">
            @csrf

            {{-- Identitas observasi --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="mb-5 font-semibold text-gray-900">
                    Identitas Observasi
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="kelas_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Pilih Kelas
                        </label>

                        <select id="kelas_id" name="kelas_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('kelas_id') border-red-500 @enderror">
                            <option value="">Pilih kelas</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}"
                                    @selected((string) $nilaiKelas === (string) $k->id)>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('kelas_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="anak_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Pilih Anak
                        </label>

                        <select id="anak_id" name="anak_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('anak_id') border-red-500 @enderror">
                        </select>

                        @error('anak_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Tanggal Observasi
                        </label>

                        <input type="date" id="tanggal" name="tanggal"
                            value="{{ $nilaiTanggal }}"
                            max="{{ now()->format('Y-m-d') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('tanggal') border-red-500 @enderror">

                        @error('tanggal')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Kegiatan dan strategi --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="mb-5 font-semibold text-gray-900">
                    Kegiatan dan Strategi
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="aktivitas_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Aktivitas Observasi
                        </label>

                        <select id="aktivitas_id" name="aktivitas_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('aktivitas_id') border-red-500 @enderror">
                            <option value="">Pilih aktivitas</option>
                            @foreach ($aktivitasList as $a)
                                <option value="{{ $a->id }}"
                                    @selected((string) old('aktivitas_id') === (string) $a->id)>
                                    {{ $a->nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('aktivitas_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="aspek_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Aspek Perkembangan
                        </label>

                        <select id="aspek_id" name="aspek_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('aspek_id') border-red-500 @enderror">
                            <option value="">Pilih aspek</option>
                            @foreach ($aspekList as $a)
                                <option value="{{ $a->id }}"
                                    @selected((string) old('aspek_id') === (string) $a->id)>
                                    {{ $a->nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('aspek_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="strategi_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Strategi Pembelajaran
                        </label>

                        <select id="strategi_id" name="strategi_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('strategi_id') border-red-500 @enderror">
                            <option value="">Pilih strategi</option>
                            @foreach ($strategiList as $s)
                                <option value="{{ $s->id }}"
                                    @selected((string) old('strategi_id') === (string) $s->id)>
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('strategi_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Respons anak --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="font-semibold text-gray-900">Respons Anak</h2>
                <p class="mb-5 mt-1 text-sm text-gray-500">
                    Pilih satu respons yang paling menggambarkan keterlibatan anak pada kegiatan ini.
                </p>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2"
                    role="radiogroup" aria-label="Respons anak">

                    @foreach ($responsList as $r)
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4 transition hover:border-indigo-400 has-[:checked]:border-indigo-700 has-[:checked]:bg-indigo-50">
                            <input type="radio" name="kategori_respons"
                                value="{{ $r->value }}"
                                @checked($nilaiRespons === $r->value)
                                required
                                class="mt-1 h-4 w-4 shrink-0 accent-indigo-900">

                            <span>
                                <span class="block text-sm font-semibold">
                                    {{ $r->label() }}
                                </span>
                                <span class="mt-1 block text-xs text-gray-600">
                                    {{ $deskripsiRespons[$r->value] ?? '' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('kategori_respons')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </section>

            {{-- Catatan pendukung --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="mb-4 font-semibold text-gray-900">
                    Catatan Pendukung
                </h2>

                <label for="catatan_pendukung" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Deskripsi Pengamatan (Opsional)
                </label>

                <textarea id="catatan_pendukung" name="catatan_pendukung"
                    rows="4" maxlength="2000"
                    placeholder="Tuliskan hasil pengamatan terhadap anak..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('catatan_pendukung') border-red-500 @enderror">{{ old('catatan_pendukung') }}</textarea>

                @error('catatan_pendukung')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <p class="mt-1 text-xs text-gray-500">
                    Maksimal 2.000 karakter.
                </p>
            </section>

            {{-- Tombol aksi --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('guru.observasi.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                    class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    Simpan Observasi
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar anak menyesuaikan kelas yang dipilih --}}
    <script>
        (() => {
            const anak = @json($anakList);
            const kelas = document.getElementById('kelas_id');
            const pilih = document.getElementById('anak_id');
            const awal = @json((string) $nilaiAnak);

            const render = (terpilih) => {
                pilih.length = 0;

                pilih.add(new Option(
                    kelas.value ? 'Pilih anak' : 'Pilih kelas terlebih dahulu',
                    ''
                ));

                anak
                    .filter(a => String(a.kelas_id) === kelas.value)
                    .forEach(a => {
                        pilih.add(new Option(
                            a.nama,
                            a.id,
                            false,
                            String(a.id) === String(terpilih)
                        ));
                    });

                pilih.disabled = !kelas.value;
            };

            kelas.addEventListener('change', () => render(''));

            render(awal);
        })();
    </script>
</x-layouts.app>
