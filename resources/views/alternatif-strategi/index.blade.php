{{-- PBI-15 Penyusunan Alternatif Strategi Berdasarkan Aturan. --}}
@php
    // Badge pola berwarna hanya jika jumlah kejadian sudah memenuhi minimal pada aturan.
    $kelasBadge = [
        'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'terlibat_dengan_pendampingan' => 'border-blue-200 bg-blue-50 text-blue-800',
        'kurang_tertarik' => 'border-amber-200 bg-amber-50 text-amber-800',
        'belum_terlibat' => 'border-red-200 bg-red-50 text-red-700',
    ];
    $kelasTitik = [
        'terlibat_aktif' => 'bg-emerald-500',
        'terlibat_dengan_pendampingan' => 'bg-blue-500',
        'kurang_tertarik' => 'bg-amber-500',
        'belum_terlibat' => 'bg-red-500',
    ];
    $kelasAvatar = [
        'bg-amber-100 text-amber-800',
        'bg-blue-100 text-blue-800',
        'bg-emerald-100 text-emerald-800',
        'bg-teal-100 text-teal-800',
    ];
    $kelasSelect = 'rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasLabelSeluler = 'mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:hidden';

    $labelKelas = fn ($kelas) => "{$kelas->kelompok} - {$kelas->nama_kelas}";
@endphp
<x-layouts.app title="Alternatif Strategi">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold">Alternatif Strategi</h1>
            <p class="mt-1 text-sm text-gray-600">
                Saran strategi disusun dari aturan dan catatan observasi. Saran hanya bahan pertimbangan, keputusan ada di tangan guru.
                @if ($kelas)
                    <span class="mx-1">&bull;</span>{{ $labelKelas($kelas) }}
                @endif
            </p>
        </div>

        @if ($daftarKelas->count() > 1)
            <form method="GET" action="{{ route('guru.alternatif-strategi.index') }}" class="w-full sm:w-auto">
                <label for="pilih-kelas" class="sr-only">Pilih kelas</label>
                <select id="pilih-kelas" name="kelas" onchange="this.form.requestSubmit()" class="{{ $kelasSelect }} w-full sm:w-52">
                    @foreach ($daftarKelas as $pilihan)
                        <option value="{{ $pilihan->id }}" @selected($kelas?->id === $pilihan->id)>{{ $labelKelas($pilihan) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="sr-only">Tampilkan</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah">
            <p class="font-medium">Alternatif belum tersimpan.</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $kelas)
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white px-6 py-10 text-center">
            <h2 class="font-medium">Belum ada kelas yang diampu</h2>
            <p class="mt-1 text-sm text-gray-500">Hubungi admin untuk penugasan kelas.</p>
        </div>
    @else
        {{-- Ringkasan --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-3 xl:max-w-4xl">
            <div class="flex items-start justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-5">
                <div>
                    <p class="text-xs text-gray-500">Anak dengan pola</p>
                    <p class="mt-1 text-2xl font-bold" dusk="jumlah-pola">{{ $jumlahPola }}</p>
                    <p class="mt-1 text-xs text-gray-500">Cocok dengan aturan aktif</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg>
                </span>
            </div>
            <div class="flex items-start justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-5">
                <div>
                    <p class="text-xs text-gray-500">Saran tersedia</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-900" dusk="jumlah-saran">{{ $jumlahSaran }}</p>
                    <p class="mt-1 text-xs text-gray-500">Siap ditinjau guru</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg>
                </span>
            </div>
            <div class="flex items-start justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-5">
                <div>
                    <p class="text-xs text-gray-500">Belum cukup data</p>
                    <p class="mt-1 text-2xl font-bold text-gray-700" dusk="jumlah-belum-cukup">{{ $jumlahBelumCukup }}</p>
                    <p class="mt-1 text-xs text-gray-500">Menunggu observasi tambahan</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
            </div>
        </div>

        {{-- Daftar --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="px-4 pt-5 sm:px-6">
                <h2 class="font-semibold">Daftar Pola Respons Anak</h2>
                <p class="mt-1 text-xs text-indigo-800">Klik baris anak untuk meninjau alternatif strategi.</p>

                <form method="GET" action="{{ route('guru.alternatif-strategi.index') }}" class="mt-4 space-y-3 pb-4">
                    <input type="hidden" name="kelas" value="{{ $kelas->id }}">
                    <label for="q" class="sr-only">Cari nama anak atau strategi</label>
                    <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="Cari nama anak atau strategi..."
                        class="{{ $kelasSelect }} w-full">

                    <div class="flex flex-wrap items-center gap-3">
                        <label for="filter-aspek" class="sr-only">Filter aspek</label>
                        <select id="filter-aspek" name="aspek" onchange="this.form.requestSubmit()" class="{{ $kelasSelect }} w-full py-2 sm:w-56">
                            <option value="">Aspek: Semua</option>
                            @foreach ($pilihanAspek as $pilihan)
                                <option value="{{ $pilihan->id }}" @selected($aspek === $pilihan->id)>Aspek: {{ $pilihan->nama }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="sr-only">Cari</button>
                        @if ($cari !== '' || $aspek !== null)
                            <a href="{{ route('guru.alternatif-strategi.index', ['kelas' => $kelas->id]) }}" class="text-sm text-indigo-800 hover:underline">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="hidden border-y border-gray-100 bg-gray-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 lg:grid lg:grid-cols-[1fr_1.2fr_1fr_1.2fr] lg:gap-6">
                <div>Anak</div>
                <div>Pola perilaku anak</div>
                <div>Strategi yang digunakan</div>
                <div>Rekomendasi strategi</div>
            </div>

            <ul class="space-y-3 border-t border-gray-100 p-3 lg:space-y-0 lg:divide-y lg:divide-gray-100 lg:border-t-0 lg:p-0">
                @forelse ($daftarBaris as $i => $baris)
                    @php
                        $anak = $baris['anak'];
                        $saran = $baris['saran'];
                        $aturan = $baris['aturan'] ?? null;
                        $idBaris = "baris-{$baris['jenis']}-{$anak->id}-".($aturan?->id ?? $saran->id);

                        if ($aturan) {
                            $respons = $aturan->respons_pemicu->value;
                            $memenuhi = $baris['jumlah'] >= $aturan->minimal_kejadian;
                            $teksPola = "{$aturan->respons_pemicu->label()} ({$baris['jumlah']}x)".($aturan->aspek ? " pada aspek {$aturan->aspek->nama}" : '');
                        }

                        $dataDialog = [
                            'url' => route('guru.alternatif-strategi.store', $anak),
                            'anak_id' => $anak->id,
                            'anak' => "{$anak->nama}, {$labelKelas($kelas)}",
                            'pola' => $aturan ? $teksPola : 'Ditambahkan guru',
                            'strategi' => $aturan?->strategiAsal->nama ?? '-',
                        ];
                    @endphp
                    <li id="{{ $idBaris }}" dusk="{{ $idBaris }}"
                        class="relative grid gap-4 rounded-xl border border-gray-200 p-4 transition-colors hover:bg-gray-50 lg:grid-cols-[1fr_1.2fr_1fr_1.2fr] lg:gap-6 lg:rounded-none lg:border-0 lg:px-6 lg:py-5">
                        {{-- TODO PBI-16: arahkan ke route Mode Fokus anak ini setelah route tersedia. --}}
                        <a href="#{{ $idBaris }}" class="absolute inset-0 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 lg:rounded-none"
                            aria-label="Tinjau alternatif strategi untuk {{ $anak->nama }}"></a>

                        {{-- Anak --}}
                        <div>
                            <p class="{{ $kelasLabelSeluler }}">Anak</p>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $kelasAvatar[$i % 4] }}">{{ mb_strtoupper(mb_substr($anak->nama, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $anak->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ $anak->tanggal_lahir->age }} tahun <span class="mx-0.5">&bull;</span> {{ $labelKelas($kelas) }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($aturan)
                            {{-- Pola perilaku anak --}}
                            <div>
                                <p class="{{ $kelasLabelSeluler }}">Pola perilaku anak</p>
                                <span @class([
                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium',
                                    $kelasBadge[$respons] => $memenuhi,
                                    'border-gray-200 bg-gray-100 text-gray-600' => ! $memenuhi,
                                ]) dusk="badge-pola">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $memenuhi ? $kelasTitik[$respons] : 'bg-gray-400' }}"></span>
                                    {{ $aturan->respons_pemicu->label() }} ({{ $baris['jumlah'] }}x)
                                </span>
                                @if ($aturan->aspek)
                                    <p class="mt-2 text-sm text-gray-700">Aspek {{ $aturan->aspek->nama }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Observasi terakhir {{ $baris['tanggal']->last()->format('d/m/Y') }}</p>
                            </div>

                            {{-- Strategi yang digunakan --}}
                            <div>
                                <p class="{{ $kelasLabelSeluler }}">Strategi yang digunakan</p>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <p class="text-sm font-semibold">{{ $aturan->strategiAsal->nama }}</p>
                                    @if ($aturan->strategiAsal->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ $aturan->strategiAsal->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Alternatif buatan guru: tanpa pola dan strategi asal --}}
                            <div class="lg:col-span-2">
                                <p class="{{ $kelasLabelSeluler }}">Catatan guru</p>
                                <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-900" dusk="penanda-manual">Ditambahkan guru</span>
                                <p class="mt-2 text-sm text-gray-700">{{ $saran->dasar_saran ?: 'Tanpa catatan.' }}</p>
                            </div>
                        @endif

                        {{-- Rekomendasi strategi --}}
                        <div>
                            <p class="{{ $kelasLabelSeluler }}">Rekomendasi strategi</p>
                            @if ($saran)
                                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2.5" dusk="saran-{{ $saran->id }}">
                                    <p class="text-sm font-semibold text-indigo-900">{{ $saran->strategi->nama }}</p>
                                    @if ($saran->strategi->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                                    @endif
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-gray-300 px-3 py-2.5" dusk="belum-ada-alternatif">
                                    <p class="text-sm font-semibold">Belum ada alternatif</p>
                                    <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                        @if ($memenuhi)
                                            Strategi alternatif pada aturan ini sedang nonaktif.
                                        @else
                                            {{ $baris['jumlah'] }} dari minimal {{ $aturan->minimal_kejadian }} kejadian.
                                        @endif
                                    </p>
                                </div>
                            @endif

                            {{-- TODO PBI-16: pindahkan pemicu dialog ini ke halaman Mode Fokus. --}}
                            <button type="button" data-tambah-alternatif='@json($dataDialog)'
                                class="relative z-10 mt-2 w-full rounded-lg border border-dashed border-indigo-300 bg-white px-3 py-1.5 text-xs font-medium text-indigo-900 hover:bg-indigo-50">
                                + Tambah alternatif
                            </button>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-gray-500">
                        @if ($cari !== '' || $aspek !== null)
                            Tidak ada data yang sesuai dengan pencarian.
                        @else
                            Belum ada pola respons yang perlu ditinjau di kelas ini.
                        @endif
                    </li>
                @endforelse
            </ul>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50 px-4 py-4 text-xs text-gray-500 sm:px-6">
                <p>
                    @if ($daftarBaris->total() > 0)
                        Menampilkan <strong>{{ $daftarBaris->firstItem() }}-{{ $daftarBaris->lastItem() }}</strong> dari <strong>{{ $daftarBaris->total() }}</strong> anak
                    @else
                        Menampilkan 0 anak
                    @endif
                </p>
                @if ($daftarBaris->hasPages())
                    <nav class="flex flex-wrap items-center gap-1.5" aria-label="Halaman">
                        @if ($daftarBaris->onFirstPage())
                            <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-300">Sebelumnya</span>
                        @else
                            <a href="{{ $daftarBaris->previousPageUrl() }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                        @endif
                        @foreach ($daftarBaris->getUrlRange(1, $daftarBaris->lastPage()) as $nomor => $url)
                            @if ($nomor === $daftarBaris->currentPage())
                                <span class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-1.5 font-medium text-indigo-900" aria-current="page">{{ $nomor }}</span>
                            @else
                                <a href="{{ $url }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 hover:bg-gray-50">{{ $nomor }}</a>
                            @endif
                        @endforeach
                        @if ($daftarBaris->hasMorePages())
                            <a href="{{ $daftarBaris->nextPageUrl() }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                        @else
                            <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-300">Selanjutnya</span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>

        @include('alternatif-strategi._dialog-tambah')
    @endif
</x-layouts.app>
