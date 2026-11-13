{{-- PBI-15 Penyusunan Alternatif Strategi Berdasarkan Aturan. --}}
@php
    $kelasRespons = [
        'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'terlibat_dengan_pendampingan' => 'border-amber-200 bg-amber-50 text-amber-800',
        'kurang_tertarik' => 'border-orange-200 bg-orange-50 text-orange-800',
        'belum_terlibat' => 'border-red-200 bg-red-50 text-red-700',
    ];
    $kelasAvatar = [
        'bg-amber-100 text-amber-800',
        'bg-blue-100 text-blue-800',
        'bg-emerald-100 text-emerald-800',
        'bg-teal-100 text-teal-800',
    ];
    $kelasInput = 'block w-full rounded-lg border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';

    $labelKelas = fn ($kelas) => "{$kelas->kelompok} - {$kelas->nama_kelas}";
    $anakLama = old('_anak');
@endphp
<x-layouts.app title="Alternatif Strategi">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Alternatif Strategi</h1>
            <p class="mt-1 text-sm text-gray-600">
                Saran strategi disusun dari aturan aktif dan catatan observasi, sebagai bahan pertimbangan guru.
                @if ($kelas)
                    <span class="mx-1">&bull;</span> {{ $labelKelas($kelas) }}
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($daftarKelas->count() > 1)
                <form method="GET" action="{{ route('guru.alternatif-strategi.index') }}">
                    <label for="pilih-kelas" class="sr-only">Pilih kelas</label>
                    <select id="pilih-kelas" name="kelas" onchange="this.form.requestSubmit()"
                        class="w-52 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        @foreach ($daftarKelas as $pilihan)
                            <option value="{{ $pilihan->id }}" @selected($kelas?->id === $pilihan->id)>{{ $labelKelas($pilihan) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="sr-only">Tampilkan</button>
                </form>
            @endif
            @if ($daftarAnak->isNotEmpty())
                <button type="button" data-aksi="tambah" dusk="tombol-tambah-alternatif"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    + Tambah Alternatif
                </button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
    @endif

    @if (! $kelas)
        <div class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-10 text-center">
            <h2 class="font-medium">Belum ada kelas yang diampu</h2>
            <p class="mt-1 text-sm text-gray-500">Hubungi admin untuk penugasan kelas.</p>
        </div>
    @else
        {{-- Ringkasan --}}
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
                <p class="text-xs text-gray-500">Pola respons tercatat</p>
                <p class="mt-1 text-3xl font-bold" dusk="jumlah-pola">{{ $jumlahPola }}</p>
                <p class="mt-1 text-xs text-gray-500">Cocok dengan aturan aktif</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
                <p class="text-xs text-gray-500">Saran tersedia</p>
                <p class="mt-1 text-3xl font-bold text-indigo-900" dusk="jumlah-saran">{{ $jumlahSaran }}</p>
                <p class="mt-1 text-xs text-gray-500">Siap ditinjau guru</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
                <p class="text-xs text-gray-500">Belum cukup data</p>
                <p class="mt-1 text-3xl font-bold text-amber-700" dusk="jumlah-belum-cukup">{{ $jumlahBelumCukup }}</p>
                <p class="mt-1 text-xs text-gray-500">Menunggu observasi tambahan</p>
            </div>
        </div>

        {{-- Pedoman --}}
        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="text-sm font-semibold">Prinsip penyusunan saran</h2>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Berdasarkan aturan</p>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600">Saran disusun jika respons yang sama tercatat sebanyak minimal kejadian pada aturan aktif.</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-indigo-700"></span>Per anak</p>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600">Setiap anak dilihat dari catatan observasinya sendiri, tidak digabung dengan anak lain.</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>Guru yang memutuskan</p>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600">Saran adalah bahan pertimbangan. Guru dapat menambah alternatif sendiri sesuai kondisi kelas.</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('guru.alternatif-strategi.index') }}" class="mt-6 flex flex-wrap gap-3">
            <input type="hidden" name="kelas" value="{{ $kelas->id }}">
            <label for="q" class="sr-only">Cari nama anak atau strategi</label>
            <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="Cari nama anak atau strategi"
                class="min-w-60 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">

            <label for="filter-aspek" class="sr-only">Filter aspek</label>
            <select id="filter-aspek" name="aspek" onchange="this.form.requestSubmit()"
                class="w-52 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Aspek: Semua</option>
                @foreach ($pilihanAspek as $pilihan)
                    <option value="{{ $pilihan->id }}" @selected($aspek === $pilihan->id)>Aspek: {{ $pilihan->nama }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Cari</button>
            @if ($cari !== '' || $aspek !== null)
                <a href="{{ route('guru.alternatif-strategi.index', ['kelas' => $kelas->id]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                <h2 class="font-medium">Daftar Pola Respons dan Saran</h2>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">{{ $daftarBaris->total() }} baris</span>
            </div>

            <div class="hidden border-b border-gray-100 bg-gray-50 px-6 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:grid lg:grid-cols-4 lg:gap-4">
                <div>Anak</div>
                <div>Pola respons</div>
                <div>Strategi yang digunakan</div>
                <div>Saran untuk dipertimbangkan</div>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse ($daftarBaris as $i => $baris)
                    @php
                        $anak = $baris['anak'];
                        $saran = $baris['saran'];
                        $aturan = $baris['aturan'] ?? null;
                    @endphp
                    <li class="grid gap-4 px-6 py-5 lg:grid-cols-4" dusk="baris-{{ $baris['jenis'] }}-{{ $anak->id }}-{{ $aturan?->id ?? $saran?->id }}">
                        {{-- Anak --}}
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Anak</p>
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $kelasAvatar[$i % 4] }}">{{ mb_strtoupper(mb_substr($anak->nama, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $anak->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ $anak->tanggal_lahir->age }} tahun <span class="mx-1">&bull;</span> {{ $labelKelas($kelas) }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($baris['jenis'] === 'pola')
                            {{-- Pola respons --}}
                            <div>
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Pola respons</p>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $kelasRespons[$aturan->respons_pemicu->value] }}">
                                    {{ $aturan->respons_pemicu->label() }} ({{ $baris['jumlah'] }}x)
                                </span>
                                <p class="mt-2 text-xs text-gray-600">Aspek: {{ $aturan->aspek?->nama ?? 'Semua aspek' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Observasi terakhir: {{ $baris['tanggal']->last()->format('d/m/Y') }}</p>
                            </div>

                            {{-- Strategi yang digunakan --}}
                            <div>
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Strategi yang digunakan</p>
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5">
                                    <p class="text-sm font-semibold">{{ $aturan->strategiAsal->nama }}</p>
                                    @if ($aturan->strategiAsal->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ $aturan->strategiAsal->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div>
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Pola respons</p>
                                <span class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">Ditambahkan guru</span>
                                <p class="mt-2 text-xs text-gray-500">Ditambahkan {{ $saran->dibuat_pada?->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Strategi yang digunakan</p>
                                <div class="rounded-lg border border-dashed border-gray-300 px-3 py-2.5">
                                    <p class="text-xs text-gray-500">Tidak terkait aturan.</p>
                                </div>
                            </div>
                        @endif

                        {{-- Saran --}}
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden">Saran untuk dipertimbangkan</p>
                            @if ($saran)
                                <div class="rounded-lg bg-indigo-50 px-3 py-2.5" dusk="saran-{{ $saran->id }}">
                                    <p class="text-sm font-semibold text-indigo-900">{{ $saran->strategi->nama }}</p>
                                    @if ($saran->strategi->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                                    @endif
                                    @if ($saran->dasar_saran)
                                        <p class="mt-2 border-t border-indigo-100 pt-2 text-xs leading-relaxed text-gray-600">
                                            <span class="font-medium">{{ $baris['jenis'] === 'pola' ? 'Dasar saran:' : 'Catatan:' }}</span> {{ $saran->dasar_saran }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 px-3 py-2.5" dusk="belum-ada-alternatif">
                                    <p class="text-sm font-semibold">Belum ada alternatif</p>
                                    <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                        @if ($baris['jumlah'] < $aturan->minimal_kejadian)
                                            Baru tercatat {{ $baris['jumlah'] }} dari minimal {{ $aturan->minimal_kejadian }} kejadian. Saran disusun setelah jumlah observasi mencukupi.
                                        @else
                                            Strategi alternatif pada aturan ini sedang nonaktif.
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <button type="button" data-aksi="tambah" data-anak="{{ $anak->id }}"
                                class="mt-2 w-full rounded-lg border border-dashed border-indigo-300 px-3 py-1.5 text-xs font-medium text-indigo-900 hover:bg-indigo-50">
                                + Tambah Alternatif
                            </button>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-gray-500">
                        @if ($cari !== '' || $aspek !== null)
                            Tidak ada data yang sesuai dengan pencarian.
                        @else
                            Belum ada pola respons yang cocok dengan aturan aktif di kelas ini.
                        @endif
                    </li>
                @endforelse
            </ul>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                <p>
                    @if ($daftarBaris->total() > 0)
                        Menampilkan <strong>{{ $daftarBaris->firstItem() }}-{{ $daftarBaris->lastItem() }}</strong> dari <strong>{{ $daftarBaris->total() }}</strong> baris
                    @else
                        Menampilkan 0 baris
                    @endif
                </p>
                <div class="flex items-center gap-2">
                    @if ($daftarBaris->onFirstPage())
                        <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Sebelumnya</span>
                    @else
                        <a href="{{ $daftarBaris->previousPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                    @endif
                    <span class="rounded-lg bg-indigo-900 px-3 py-1.5 font-medium text-white">{{ $daftarBaris->currentPage() }}</span>
                    @if ($daftarBaris->hasMorePages())
                        <a href="{{ $daftarBaris->nextPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                    @else
                        <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Selanjutnya</span>
                    @endif
                </div>
            </div>
        </div>

        <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
            Saran ini bukan penilaian terhadap anak. Saran membantu guru menimbang strategi lain, dan keputusan tetap di tangan guru.
        </p>

        {{-- Dialog: Tambah alternatif sendiri --}}
        <dialog id="dialog-alternatif" dusk="dialog-alternatif"
            class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
            data-url-template="{{ route('guru.alternatif-strategi.store', ['anak' => '__ANAK__']) }}"
            @if ($anakLama && $errors->any()) data-buka-otomatis @endif>
            <form method="POST" data-form-alternatif
                action="{{ $anakLama ? route('guru.alternatif-strategi.store', ['anak' => $anakLama]) : '' }}">
                @csrf
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold">Tambah Alternatif Strategi</h2>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label for="_anak" class="block text-sm font-medium">Anak</label>
                        <select id="_anak" name="_anak" required data-pilih-anak dusk="pilih-anak"
                            class="{{ $kelasInput }} {{ $kelasInputNormal }} mt-1">
                            <option value="">Pilih anak</option>
                            @foreach ($daftarAnak as $pilihan)
                                <option value="{{ $pilihan->id }}" @selected((string) $anakLama === (string) $pilihan->id)>{{ $pilihan->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="strategi_id" class="block text-sm font-medium">Strategi</label>
                        <select id="strategi_id" name="strategi_id" required dusk="pilih-strategi"
                            class="{{ $kelasInput }} mt-1 {{ $errors->has('strategi_id') ? $kelasInputSalah : $kelasInputNormal }}">
                            <option value="">Pilih strategi</option>
                            @foreach ($pilihanStrategi as $pilihan)
                                <option value="{{ $pilihan->id }}" @selected((string) old('strategi_id') === (string) $pilihan->id)>{{ $pilihan->nama }}</option>
                            @endforeach
                        </select>
                        @error('strategi_id')
                            <p class="mt-1 text-xs text-red-600" dusk="salah-strategi">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan" class="block text-sm font-medium">Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                        <textarea id="catatan" name="catatan" rows="3" maxlength="1000"
                            placeholder="Contoh: dicoba saat kegiatan pagi bersama kelompok kecil."
                            class="{{ $kelasInput }} mt-1 {{ $errors->has('catatan') ? $kelasInputSalah : $kelasInputNormal }}">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
                    <button type="button" data-tutup class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" dusk="simpan-alternatif" class="rounded-lg bg-indigo-900 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800">Simpan</button>
                </div>
            </form>
        </dialog>

        <script>
            (() => {
                const dialog = document.getElementById('dialog-alternatif');
                const form = dialog.querySelector('[data-form-alternatif]');
                const pilihAnak = dialog.querySelector('[data-pilih-anak]');

                // Alamat simpan mengikuti anak yang dipilih.
                const aturAction = () => {
                    form.action = pilihAnak.value ? dialog.dataset.urlTemplate.replace('__ANAK__', pilihAnak.value) : '';
                };
                pilihAnak.addEventListener('change', aturAction);

                document.querySelectorAll('[data-aksi="tambah"]').forEach((tombol) => {
                    tombol.addEventListener('click', () => {
                        form.reset();
                        pilihAnak.value = tombol.dataset.anak ?? '';
                        aturAction();
                        dialog.showModal();
                    });
                });

                dialog.querySelectorAll('[data-tutup]').forEach((tombol) => tombol.addEventListener('click', () => dialog.close()));
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });

                if ('bukaOtomatis' in dialog.dataset) {
                    dialog.showModal();
                }
            })();
        </script>
    @endif
</x-layouts.app>
