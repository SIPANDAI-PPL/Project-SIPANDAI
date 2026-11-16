{{-- Fitur: PBI-12 Ringkasan Pola Respons Anak --}}
@use('App\Enums\KategoriRespons')
@use('App\Services\RingkasanPolaResponsService')
@php
    $warnaKategori = RingkasanPolaResponsService::WARNA;

    $gayaBadge = [
        'terlibat_aktif' => 'bg-[#DDF3E8] text-emerald-800',
        'terlibat_dengan_pendampingan' => 'bg-[#DCEAFB] text-sky-800',
        'kurang_tertarik' => 'bg-[#FFE6CC] text-amber-800',
        'belum_terlibat' => 'bg-gray-100 text-gray-700',
    ];

    $total = $ringkasan['total'];
    $totalSemua = $ringkasan['totalSemua'];

    $namaTampil = $anak->nama_panggilan ?: $anak->nama;
    $inisial = collect(preg_split('/\s+/', trim($anak->nama)))
        ->filter()
        ->take(2)
        ->map(fn (string $kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->join('');

    $umur = null;
    if ($anak->tanggal_lahir !== null) {
        $selisih = $anak->tanggal_lahir->diff(now());
        $umur = "{$selisih->y} th {$selisih->m} bln";
    }

    $namaKelas = trim(implode(' - ', array_filter([$kelas->kelompok, $kelas->nama_kelas])));
    $tautanHalaman = route('guru.pola-respons.anak', $anak);

    $kelompokTabel = [
        'aspek' => ['judul' => 'Aspek perkembangan', 'baris' => $ringkasan['perAspek']],
        'aktivitas' => ['judul' => 'Aktivitas', 'baris' => $ringkasan['perAktivitas']],
        'strategi' => ['judul' => 'Strategi pembelajaran', 'baris' => $ringkasan['perStrategi']],
    ];

    $kelasSelect = 'w-full rounded-full border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20 sm:w-auto';
@endphp
<x-layouts.app :title="'Pola Respons '.$namaTampil">
    <div class="mx-auto w-full max-w-6xl space-y-5">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('guru.pola-respons.index') }}" class="hover:text-[#221177] hover:underline">Pola Respons</a>
            <svg class="h-3.5 w-3.5 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
            <span class="font-medium text-gray-700" aria-current="page">{{ $namaTampil }}</span>
        </nav>

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Ringkasan Pola Respons</h1>
            <p class="mt-1 text-sm text-gray-500">Frekuensi kategori respons dari catatan observasi anak.</p>
        </div>

        {{-- Identitas anak (selaras mockup profil anak) --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#DDF3E8] text-lg font-bold text-emerald-800" aria-hidden="true">{{ $inisial }}</span>
                <div class="min-w-0">
                    <p class="truncate text-lg font-semibold text-gray-900">{{ $namaTampil }}</p>
                    <p class="truncate text-sm text-gray-500">
                        {{ $anak->nama }}@if ($umur) · {{ $umur }}@endif
                    </p>
                    <p class="text-sm text-gray-500">{{ $namaKelas }} · <span id="jumlah-observasi">{{ $filterAktif ? "{$total} dari {$totalSemua}" : $totalSemua }} observasi</span></p>
                </div>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('guru.observasi.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#2E2580] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/30">Catat observasi</a>
                <a href="{{ route('guru.riwayat-observasi', $anak) }}" class="inline-flex items-center justify-center rounded-lg border border-[#2E2580] px-4 py-2.5 text-sm font-semibold text-[#2E2580] transition hover:bg-[#2E2580]/5 focus:outline-none focus:ring-2 focus:ring-[#221177]/30">Riwayat Observasi</a>
            </div>
        </section>

        @include('guru.pola-respons._penanda')

        @if ($totalSemua === 0)
            {{-- TC-12-04: anak belum punya observasi --}}
            <section id="status-kosong" class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-black/5">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#EDE9FE] text-[#221177]">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 15v-3m4 3V9m4 6v-5" /></svg>
                </span>
                <h2 class="mt-4 text-base font-semibold text-gray-900">Belum ada data observasi</h2>
                <p class="mx-auto mt-1 max-w-md text-sm leading-relaxed text-gray-500">
                    Ringkasan pola respons {{ $namaTampil }} akan muncul setelah observasi pertama dicatat.
                    Catat beberapa observasi pada aktivitas dan strategi yang berbeda agar polanya lebih mudah dibaca.
                </p>
                <a href="{{ route('guru.observasi.create') }}" class="mt-5 inline-flex items-center rounded-lg bg-[#2E2580] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#221177]">Catat observasi pertama</a>
            </section>
        @else
            {{-- Filter (GET) agar kombinasi filter tetap bekerja tanpa JavaScript. --}}
            <form method="GET" action="{{ $tautanHalaman }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5" aria-label="Filter ringkasan">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="w-full sm:w-auto">
                        <span class="sr-only">Periode</span>
                        <select id="filter-periode" name="periode" dusk="filter-periode" onchange="this.form.submit()" class="{{ $kelasSelect }} font-semibold text-[#221177]">
                            @foreach ($opsiFilter['periode'] as $nilai => $label)
                                <option value="{{ $nilai }}" @selected($filter['periode'] === (string) $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="w-full sm:w-auto">
                        <span class="sr-only">Aspek</span>
                        <select id="filter-aspek" name="aspek" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                            <option value="">Semua aspek</option>
                            @foreach ($opsiFilter['aspek'] as $aspek)
                                <option value="{{ $aspek->id }}" @selected((int) $filter['aspek'] === $aspek->id)>{{ $aspek->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="w-full sm:w-auto">
                        <span class="sr-only">Aktivitas</span>
                        <select id="filter-aktivitas" name="aktivitas" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                            <option value="">Semua aktivitas</option>
                            @foreach ($opsiFilter['aktivitas'] as $aktivitas)
                                <option value="{{ $aktivitas->id }}" @selected((int) $filter['aktivitas'] === $aktivitas->id)>{{ $aktivitas->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="w-full sm:w-auto">
                        <span class="sr-only">Strategi</span>
                        <select id="filter-strategi" name="strategi" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                            <option value="">Semua strategi</option>
                            @foreach ($opsiFilter['strategi'] as $strategi)
                                <option value="{{ $strategi->id }}" @selected((int) $filter['strategi'] === $strategi->id)>{{ $strategi->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="w-full sm:w-auto">
                        <span class="sr-only">Kategori respons</span>
                        <select id="filter-respons" name="respons" onchange="this.form.submit()" class="{{ $kelasSelect }}">
                            <option value="">Semua respons</option>
                            @foreach ($opsiFilter['respons'] as $kategori)
                                <option value="{{ $kategori->value }}" @selected($filter['respons'] === $kategori->value)>{{ $kategori->label() }}</option>
                            @endforeach
                        </select>
                    </label>

                    <noscript><button type="submit" class="rounded-full bg-[#2E2580] px-4 py-2 text-sm font-semibold text-white">Terapkan</button></noscript>

                    @if ($filterAktif)
                        <a href="{{ $tautanHalaman }}" class="rounded-lg px-2 py-2 text-sm font-semibold text-[#221177] hover:underline sm:ml-auto">Hapus filter</a>
                    @endif
                </div>
            </form>

            {{-- Kartu frekuensi per kategori --}}
            <section aria-label="Frekuensi kategori respons" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach (KategoriRespons::cases() as $kategori)
                    @php
                        $jumlah = $ringkasan['frekuensi'][$kategori->value];
                        $persen = $total > 0 ? round($jumlah / $total * 100) : 0;
                    @endphp
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5" data-kategori="{{ $kategori->value }}">
                        <p class="flex items-center gap-2 text-xs font-medium text-gray-600">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $warnaKategori[$kategori->value] }}" aria-hidden="true"></span>
                            {{ $kategori->label() }}
                        </p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $jumlah }}</p>
                        <p class="text-xs text-gray-500">{{ $persen }}% dari {{ $total }} catatan</p>
                    </div>
                @endforeach
            </section>

            @if ($total === 0)
                <section class="rounded-2xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-black/5">
                    <h2 class="text-base font-semibold text-gray-900">Tidak ada observasi yang sesuai filter</h2>
                    <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">Coba ubah periode atau hapus sebagian filter untuk melihat ringkasan lainnya.</p>
                    <a href="{{ $tautanHalaman }}" class="mt-4 inline-flex text-sm font-semibold text-[#221177] hover:underline">Hapus filter</a>
                </section>
            @else
                {{-- Grafik Chart.js. Data dibaca dari JSON di bawah oleh resources/js/grafik-pola-respons.js. --}}
                <script type="application/json" id="data-grafik-pola">@json($ringkasan['grafik'])</script>

                <div class="grid gap-5 lg:grid-cols-5">
                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 lg:col-span-2" aria-labelledby="judul-grafik-kategori">
                        <h2 id="judul-grafik-kategori" class="text-base font-semibold text-gray-900">Frekuensi kategori respons</h2>
                        <p class="text-xs text-gray-500">Jumlah catatan per kategori pada filter aktif.</p>
                        <div class="relative mt-4 h-64">
                            <canvas id="grafik-kategori-respons" dusk="grafik-kategori-respons" role="img"
                                aria-label="Grafik batang frekuensi kategori respons: {{ collect(KategoriRespons::cases())->map(fn ($k) => $k->label().' '.$ringkasan['frekuensi'][$k->value])->join(', ') }}"></canvas>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 lg:col-span-3" aria-labelledby="judul-grafik-kelompok">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 id="judul-grafik-kelompok" class="text-base font-semibold text-gray-900">Pola respons per kelompok</h2>
                                <p class="text-xs text-gray-500">Bandingkan respons pada setiap aspek, aktivitas, atau strategi.</p>
                            </div>
                            <div class="inline-flex rounded-full bg-gray-100 p-1 text-xs font-semibold" role="tablist" aria-label="Kelompokkan berdasarkan">
                                @foreach ($kelompokTabel as $jenis => $info)
                                    <button type="button" role="tab" data-tab-kelompok="{{ $jenis }}" dusk="tab-{{ $jenis }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="tabel-{{ $jenis }}"
                                        class="rounded-full px-3 py-1.5 text-gray-600 transition aria-selected:bg-white aria-selected:text-[#221177] aria-selected:shadow-sm">
                                        {{ $jenis === 'strategi' ? 'Strategi' : ucfirst($jenis) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative mt-4 h-64">
                            <canvas id="grafik-pola-kelompok" dusk="grafik-pola-kelompok" role="img"
                                aria-label="Grafik batang bertumpuk frekuensi kategori respons per kelompok. Rincian ada di tabel ringkasan."></canvas>
                        </div>
                    </section>
                </div>

                {{-- Tabel ringkasan frekuensi per aspek/aktivitas/strategi --}}
                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5" aria-labelledby="judul-tabel-ringkasan">
                    <h2 id="judul-tabel-ringkasan" class="px-5 pt-5 text-base font-semibold text-gray-900">Tabel ringkasan frekuensi</h2>
                    @foreach ($kelompokTabel as $jenis => $info)
                        <div id="tabel-{{ $jenis }}" data-tabel-kelompok="{{ $jenis }}" role="tabpanel" class="mt-3 overflow-x-auto pb-2">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <caption class="px-5 pb-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Per {{ strtolower($info['judul']) }}</caption>
                                <thead class="bg-gray-50 text-xs text-gray-500">
                                    <tr>
                                        <th scope="col" class="px-5 py-2 font-medium">{{ $info['judul'] }}</th>
                                        @foreach (KategoriRespons::cases() as $kategori)
                                            <th scope="col" class="px-3 py-2 text-center font-medium">{{ $kategori->label() }}</th>
                                        @endforeach
                                        <th scope="col" class="px-3 py-2 text-center font-medium">Total</th>
                                        <th scope="col" class="px-5 py-2 font-medium">Ringkasan catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($info['baris'] as $baris)
                                        <tr>
                                            <th scope="row" class="px-5 py-2.5 font-medium text-gray-800">{{ $baris['nama'] }}</th>
                                            @foreach ($baris['frekuensi'] as $jumlah)
                                                <td class="px-3 py-2.5 text-center tabular-nums {{ $jumlah === 0 ? 'text-gray-300' : 'text-gray-800' }}">{{ $jumlah }}</td>
                                            @endforeach
                                            <td class="px-3 py-2.5 text-center font-semibold tabular-nums text-gray-900">{{ $baris['total'] }}</td>
                                            <td class="px-5 py-2.5 text-gray-600">{{ $baris['ringkasan'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </section>

                <div class="grid gap-5 lg:grid-cols-2">
                    @include('guru.pola-respons._tren-aspek', [
                        'tren' => $ringkasan['perAspek'],
                        'keteranganPeriode' => $opsiFilter['periode'][$filter['periode']],
                    ])

                    {{-- Rekam jejak observasi terbaru pada filter aktif --}}
                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5" aria-labelledby="judul-rekam-jejak">
                        <div class="flex items-baseline justify-between gap-3">
                            <h2 id="judul-rekam-jejak" class="text-base font-semibold text-gray-900">Rekam jejak observasi</h2>
                            <a href="{{ route('guru.riwayat-observasi', $anak) }}" class="text-xs font-semibold text-[#221177] hover:underline">Lihat semua</a>
                        </div>
                        <ul class="mt-3 divide-y divide-gray-100">
                            @foreach ($ringkasan['terbaru'] as $observasi)
                                <li class="flex items-start justify-between gap-3 py-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800">{{ $observasi->aktivitas->nama }}</p>
                                        <p class="text-xs text-gray-500">{{ $observasi->aspek->nama }} · {{ $observasi->strategi->nama }}</p>
                                        <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $gayaBadge[$observasi->kategori_respons->value] }}">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $warnaKategori[$observasi->kategori_respons->value] }}" aria-hidden="true"></span>
                                            {{ $observasi->kategori_respons->label() }}
                                        </span>
                                    </div>
                                    <time datetime="{{ $observasi->tanggal->toDateString() }}" class="shrink-0 text-xs text-gray-500">{{ $observasi->tanggal->locale('id')->translatedFormat('j M Y') }}</time>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
