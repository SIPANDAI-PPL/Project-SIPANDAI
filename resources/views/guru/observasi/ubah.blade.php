@php
    $kode = sprintf('OBS-%03d', $observasi->id);
    $kelas = $observasi->anak?->kelas;
    $namaKelas = $kelas
        ? trim(implode(' - ', array_filter([$kelas->kelompok, $kelas->nama_kelas])))
        : '-';

    $nilaiTanggal = old('tanggal', $observasi->tanggal?->format('Y-m-d'));
    $nilaiRespons = old('kategori_respons', $observasi->kategori_respons?->value);

    $deskripsiRespons = [
        'terlibat_aktif' => 'Anak antusias dan melakukan kegiatan dengan mandiri.',
        'terlibat_dengan_pendampingan' => 'Anak ikut serta setelah diarahkan atau didampingi guru.',
        'kurang_tertarik' => 'Anak terlihat kurang bersemangat atau mudah beralih.',
        'belum_terlibat' => 'Anak belum mau atau belum mampu ikut kegiatan.',
    ];
@endphp

<x-layouts.app title="Ubah Observasi">
    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Judul halaman --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">Observasi &rsaquo; {{ $kode }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Ubah Observasi</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Perbarui hasil pengamatan. Setiap perubahan tercatat pada riwayat perubahan.
                </p>
            </div>

            <a href="{{ route('guru.observasi.riwayat', $observasi) }}"
                class="rounded-lg border border-indigo-300 px-4 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-50">
                Lihat Riwayat ({{ $riwayat->count() }})
            </a>
        </div>

        {{-- Pesan kesalahan --}}
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <p class="font-semibold">Periksa kembali isian Anda:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="space-y-5" method="POST" action="{{ route('guru.observasi.update', $observasi) }}">
            @csrf
            @method('PUT')

            {{-- Kelas dan anak tidak diubah lewat halaman ini --}}
            <input type="hidden" name="kelas_id" value="{{ old('kelas_id', $kelas?->id) }}">
            <input type="hidden" name="anak_id" value="{{ old('anak_id', $observasi->anak_id) }}">

            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="mb-5 font-semibold text-gray-900">Identitas Observasi</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs text-gray-500">Nama Anak</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $observasi->anak?->nama ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Kelas</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $namaKelas }}</p>
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

                <p class="mt-4 text-xs text-gray-500">
                    Anak dan kelas tidak dapat diubah. Jika salah memilih anak, hapus observasi ini
                    lalu buat observasi baru.
                </p>
            </section>

            {{-- Kegiatan dan strategi --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                <h2 class="mb-5 font-semibold text-gray-900">Kegiatan dan Strategi</h2>

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
                                    @selected((string) old('aktivitas_id', $observasi->aktivitas_id) === (string) $a->id)>
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
                                    @selected((string) old('aspek_id', $observasi->aspek_id) === (string) $a->id)>
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
                                    @selected((string) old('strategi_id', $observasi->strategi_id) === (string) $s->id)>
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

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2" role="radiogroup" aria-label="Respons anak">
                    @foreach ($responsList as $r)
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4 transition hover:border-indigo-400 has-[:checked]:border-indigo-700 has-[:checked]:bg-indigo-50">
                            <input type="radio" name="kategori_respons"
                                value="{{ $r->value }}"
                                @checked($nilaiRespons === $r->value)
                                required
                                class="mt-1 h-4 w-4 shrink-0 accent-indigo-900">

                            <span>
                                <span class="block text-sm font-semibold">{{ $r->label() }}</span>
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
                <h2 class="mb-4 font-semibold text-gray-900">Catatan Pendukung</h2>

                <label for="catatan_pendukung" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Deskripsi Pengamatan (Opsional)
                </label>

                <textarea id="catatan_pendukung" name="catatan_pendukung"
                    rows="4" maxlength="2000"
                    placeholder="Tuliskan hasil pengamatan terhadap anak..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('catatan_pendukung') border-red-500 @enderror">{{ old('catatan_pendukung', $observasi->catatan_pendukung) }}</textarea>

                @error('catatan_pendukung')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <p class="mt-1 text-xs text-gray-500">Maksimal 2.000 karakter.</p>
            </section>

            {{-- Tombol aksi --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                <button type="button" data-buka-hapus
                    class="rounded-lg border border-red-300 px-5 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50">
                    Hapus Observasi
                </button>

                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="{{ route('guru.observasi.show', $observasi) }}"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>

                    <button type="submit"
                        class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    @include('guru.observasi.partials.modal-hapus', ['observasi' => $observasi])
</x-layouts.app>
