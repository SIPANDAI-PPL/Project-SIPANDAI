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

    {{-- Banner sukses tampil 5 detik, lalu memudar 500 ms dan dihapus. Banner error tidak hilang otomatis. --}}
    @if (session('status'))
        <div data-flash role="status" dusk="pesan-status"
            class="mt-4 flex items-start justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 transition-opacity duration-500">
            <p>{{ session('status') }}</p>
            <button type="button" data-tutup-flash aria-label="Tutup" dusk="tutup-pesan-status"
                class="-my-1 shrink-0 px-1 text-xl leading-none text-green-700 hover:text-green-900">&times;</button>
        </div>
        <script>
            (() => {
                document.querySelectorAll('[data-flash]').forEach((banner) => {
                    let ditutup = false;
                    const tutup = () => {
                        if (ditutup) return;
                        ditutup = true;
                        banner.classList.add('opacity-0');
                        setTimeout(() => banner.remove(), 500);
                    };
                    setTimeout(tutup, 5000);
                    banner.querySelector('[data-tutup-flash]').addEventListener('click', tutup);
                });
            })();
        </script>
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
                        $daftarPolaAnak = $baris['pola'];
                        $daftarSaranAturan = $daftarPolaAnak->pluck('saran')->filter();
                        $daftarStrategiAsal = $daftarPolaAnak->pluck('aturan.strategiAsal')->unique('id');
                    @endphp
                    <li id="baris-anak-{{ $anak->id }}" dusk="baris-anak-{{ $anak->id }}"
                        class="relative grid gap-4 rounded-xl border border-gray-200 p-4 transition-colors hover:bg-gray-50 lg:grid-cols-[1fr_1.2fr_1fr_1.2fr] lg:gap-6 lg:rounded-none lg:border-0 lg:px-6 lg:py-5">
                        {{-- Anak: tautan ke Mode Fokus. after:inset-0 membuat seluruh baris dapat diklik. --}}
                        <div>
                            <p class="{{ $kelasLabelSeluler }}">Anak</p>
                            <a href="{{ route('guru.alternatif-strategi.anak', $anak) }}" dusk="tautan-anak-{{ $anak->id }}"
                                class="group flex items-center gap-3 rounded-lg after:absolute after:inset-0 after:rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 lg:after:rounded-none">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $kelasAvatar[$i % 4] }}">{{ mb_strtoupper(mb_substr($anak->nama, 0, 1)) }}</span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-gray-900 group-hover:text-indigo-900 group-hover:underline">{{ $anak->nama }}</span>
                                    <span class="block text-xs text-gray-500">{{ $anak->tanggal_lahir->age }} tahun <span class="mx-0.5">&bull;</span> {{ $labelKelas($kelas) }}</span>
                                </span>
                            </a>
                        </div>

                        {{-- Pola perilaku anak --}}
                        <div>
                            <p class="{{ $kelasLabelSeluler }}">Pola perilaku anak</p>
                            @forelse ($daftarPolaAnak as $pola)
                                @php
                                    $aturan = $pola['aturan'];
                                    $respons = $aturan->respons_pemicu->value;
                                    $memenuhi = $pola['jumlah'] >= $aturan->minimal_kejadian;
                                @endphp
                                <div @class(['mt-3' => ! $loop->first])>
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium',
                                        $kelasBadge[$respons] => $memenuhi,
                                        'border-gray-200 bg-gray-100 text-gray-600' => ! $memenuhi,
                                    ]) dusk="badge-pola">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $memenuhi ? $kelasTitik[$respons] : 'bg-gray-400' }}"></span>
                                        {{ $aturan->respons_pemicu->label() }} ({{ $pola['jumlah'] }}x)
                                    </span>
                                    @if ($aturan->aspek)
                                        <p class="mt-1.5 text-sm text-gray-700">Aspek {{ $aturan->aspek->nama }}</p>
                                    @endif
                                    <p class="mt-0.5 text-xs text-gray-500">Observasi terakhir {{ $pola['tanggal']->last()->format('d/m/Y') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Belum ada pola dari aturan</p>
                            @endforelse
                        </div>

                        {{-- Strategi yang digunakan --}}
                        <div class="space-y-2">
                            <p class="{{ $kelasLabelSeluler }}">Strategi yang digunakan</p>
                            @forelse ($daftarStrategiAsal as $strategiAsal)
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <p class="text-sm font-semibold">{{ $strategiAsal->nama }}</p>
                                    @if ($strategiAsal->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ $strategiAsal->deskripsi }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">-</p>
                            @endforelse
                        </div>

                        {{-- Rekomendasi strategi: saran dari aturan dan alternatif buatan guru --}}
                        <div class="space-y-2">
                            <p class="{{ $kelasLabelSeluler }}">Rekomendasi strategi</p>
                            @foreach ($daftarSaranAturan as $saran)
                                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2.5" dusk="saran-{{ $saran->id }}">
                                    @include('alternatif-strategi._badge-status', ['status' => $saran->status])
                                    <p class="mt-1.5 text-sm font-semibold text-indigo-900">{{ $saran->strategi->nama }}</p>
                                    @if ($saran->strategi->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                                    @endif
                                </div>
                            @endforeach
                            @foreach ($baris['manual'] as $saran)
                                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2.5" dusk="saran-{{ $saran->id }}">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @include('alternatif-strategi._badge-status', ['status' => $saran->status])
                                        <span class="inline-flex items-center rounded-full border border-indigo-200 bg-white px-2 py-0.5 text-[11px] font-medium text-indigo-900" dusk="penanda-manual">Ditambahkan guru</span>
                                    </div>
                                    <p class="mt-1.5 text-sm font-semibold text-indigo-900">{{ $saran->strategi->nama }}</p>
                                    @if ($saran->strategi->deskripsi)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                                    @endif
                                    @if (filled($saran->dasar_saran))
                                        <p class="mt-1.5 text-xs leading-relaxed text-gray-600"><span class="font-medium">Catatan guru:</span> {{ $saran->dasar_saran }}</p>
                                    @else
                                        <p class="mt-1.5 text-xs leading-relaxed text-gray-600"><span class="font-medium">Catatan guru:</span> <span class="italic text-gray-500" dusk="catatan-kosong">Belum ada catatan.</span></p>
                                    @endif
                                </div>
                            @endforeach
                            @unless ($baris['punya_saran'])
                                <div class="rounded-xl border border-dashed border-gray-300 px-3 py-2.5" dusk="belum-ada-alternatif">
                                    <p class="text-sm font-semibold">Belum ada alternatif</p>
                                    @foreach ($daftarPolaAnak as $pola)
                                        <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                            {{ $pola['aturan']->respons_pemicu->label() }}:
                                            @if ($pola['jumlah'] < $pola['aturan']->minimal_kejadian)
                                                {{ $pola['jumlah'] }} dari minimal {{ $pola['aturan']->minimal_kejadian }} kejadian.
                                            @else
                                                strategi alternatif pada aturan ini sedang nonaktif.
                                            @endif
                                        </p>
                                    @endforeach
                                </div>
                            @endunless
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

    @endif
</x-layouts.app>
