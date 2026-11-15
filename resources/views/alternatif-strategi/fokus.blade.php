{{-- PBI-15 Mode Fokus per anak: pola respons dan semua alternatif strategi untuk satu anak. --}}
@php
    $anak = $data['anak'];
    $daftarPola = $data['pola'];
    $daftarSaranAturan = $daftarPola->pluck('saran')->filter();
    $daftarStrategiAsal = $daftarPola->pluck('aturan.strategiAsal')->unique('id');
    $observasiTerakhir = $daftarPola->map(fn (array $pola) => $pola['tanggal']->last())->max();

    $labelKelas = "{$kelas->kelompok} - {$kelas->nama_kelas}";
    $teksPola = fn (array $pola) => "{$pola['aturan']->respons_pemicu->label()} ({$pola['jumlah']}x)"
        .($pola['aturan']->aspek ? " pada aspek {$pola['aturan']->aspek->nama}" : '');

    $dataDialog = [
        'url' => route('guru.alternatif-strategi.store', $anak),
        'anak_id' => $anak->id,
        'anak' => "{$anak->nama}, {$labelKelas}",
        'pola' => $daftarPola->isEmpty() ? 'Belum ada pola dari aturan' : $daftarPola->map($teksPola)->implode('; '),
        'strategi' => $daftarStrategiAsal->isEmpty() ? '-' : $daftarStrategiAsal->pluck('nama')->implode(', '),
    ];

    // Kartu alternatif: saran dari aturan dulu, lalu alternatif buatan guru.
    $daftarAlternatif = $daftarSaranAturan->map(fn ($saran) => ['saran' => $saran, 'manual' => false])
        ->concat($data['manual']->map(fn ($saran) => ['saran' => $saran, 'manual' => true]))
        ->values();

    $kelasBadge = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium';
    $kelasLabelKartu = 'text-xs font-semibold uppercase tracking-wide';
@endphp
<x-layouts.app title="Mode Fokus Per Anak">
    <a href="{{ route('guru.alternatif-strategi.index', ['kelas' => $kelas->id]) }}" dusk="kembali-ke-daftar"
        class="inline-flex items-center gap-1 text-sm text-indigo-800 hover:underline">&larr; Kembali ke daftar</a>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
    @endif

    @if ($errors->hasAny(['strategi_id', 'catatan']))
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah">
            Alternatif belum tersimpan. Periksa isian pada formulir.
        </p>
    @endif

    {{-- Identitas anak --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-2xl font-semibold text-amber-800">{{ mb_strtoupper(mb_substr($anak->nama, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold">{{ $anak->nama }}</h1>
                    <p class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            {{ $anak->tanggal_lahir->age }} tahun
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                            Kelas {{ $labelKelas }}
                        </span>
                    </p>
                </div>
            </div>
            <button type="button" data-tambah-alternatif='@json($dataDialog)' dusk="tombol-tambah-alternatif"
                class="w-full shrink-0 rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-100 sm:w-auto">
                + Tambah alternatif
            </button>
        </div>

        <div class="mt-5 grid gap-3 border-t border-gray-100 pt-5 md:grid-cols-3">
            <div class="flex gap-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-amber-900">Pola respons teramati</p>
                    @forelse ($daftarPola as $pola)
                        <p class="mt-1 text-sm text-amber-900">{{ $teksPola($pola) }}</p>
                    @empty
                        <p class="mt-1 text-sm text-gray-600">Belum ada pola dari aturan.</p>
                    @endforelse
                </div>
            </div>
            <div class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-800">Observasi terakhir</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $observasiTerakhir ? $observasiTerakhir->format('d/m/Y') : '-' }}</p>
                </div>
            </div>
            <div class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5zM4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-800">Strategi saat ini</p>
                    @forelse ($daftarStrategiAsal as $strategiAsal)
                        <p class="mt-1 text-sm font-medium text-gray-800">{{ $strategiAsal->nama }}</p>
                        @if ($strategiAsal->deskripsi)
                            <p class="text-xs leading-relaxed text-gray-600">{{ $strategiAsal->deskripsi }}</p>
                        @endif
                    @empty
                        <p class="mt-1 text-sm text-gray-600">-</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Perbandingan alternatif strategi --}}
    <section class="mt-8">
        <h2 class="text-xl font-semibold">Perbandingan alternatif strategi</h2>
        <p class="mt-1 text-sm text-gray-600">Bandingkan strategi saat ini dengan alternatif untuk {{ $anak->nama }}. Saran adalah bahan pertimbangan, keputusan tetap di tangan guru.</p>

        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            {{-- Strategi saat ini --}}
            @forelse ($daftarStrategiAsal as $strategiAsal)
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-5" dusk="kartu-strategi-saat-ini">
                    <p class="{{ $kelasLabelKartu }} border-b border-gray-200 pb-3 text-gray-500">Strategi saat ini</p>
                    <h3 class="mt-4 text-lg font-semibold">{{ $strategiAsal->nama }}</h3>
                    @if ($strategiAsal->deskripsi)
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $strategiAsal->deskripsi }}</p>
                    @endif
                </article>
            @empty
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-5" dusk="kartu-strategi-saat-ini">
                    <p class="{{ $kelasLabelKartu }} border-b border-gray-200 pb-3 text-gray-500">Strategi saat ini</p>
                    <p class="mt-4 text-sm text-gray-600">Belum ada pola dari aturan, jadi strategi saat ini belum tercatat.</p>
                </article>
            @endforelse

            {{-- Alternatif --}}
            @forelse ($daftarAlternatif as $alternatif)
                @php($saran = $alternatif['saran'])
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5" dusk="kartu-saran-{{ $saran->id }}">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $kelasLabelKartu }} text-indigo-800">Alternatif {{ $loop->iteration }}</span>
                            @if ($alternatif['manual'])
                                <span class="{{ $kelasBadge }} border-indigo-200 bg-indigo-50 text-indigo-900" dusk="penanda-manual">Ditambahkan guru</span>
                            @else
                                <span class="{{ $kelasBadge }} border-blue-200 bg-blue-50 text-blue-800">Dari aturan</span>
                            @endif
                        </div>
                        <span class="{{ $kelasBadge }} border-amber-200 bg-amber-50 text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>Status: {{ ucfirst($saran->status) }}
                        </span>
                    </div>

                    <h3 class="mt-4 text-lg font-semibold text-indigo-900">{{ $saran->strategi->nama }}</h3>
                    @if ($saran->strategi->deskripsi)
                        <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                    @endif

                    @if ($saran->dasar_saran)
                        <div class="mt-4 rounded-r-lg border-l-4 border-indigo-200 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-700">
                            <p class="font-semibold uppercase tracking-wide text-gray-800">{{ $alternatif['manual'] ? 'Catatan guru:' : 'Dasar saran:' }}</p>
                            <p class="mt-1">{{ $saran->dasar_saran }}</p>
                        </div>
                    @endif

                    {{-- TODO PBI-16: tombol Pilih / Abaikan / Tinjau Ulang di bagian bawah kartu ini. --}}
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-6 lg:col-span-2" dusk="belum-ada-alternatif">
                    <p class="font-semibold">Belum ada alternatif</p>
                    @forelse ($daftarPola as $pola)
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $pola['aturan']->respons_pemicu->label() }}:
                            @if ($pola['jumlah'] < $pola['aturan']->minimal_kejadian)
                                {{ $pola['jumlah'] }} dari minimal {{ $pola['aturan']->minimal_kejadian }} kejadian.
                            @else
                                strategi alternatif pada aturan ini sedang nonaktif.
                            @endif
                        </p>
                    @empty
                        <p class="mt-1 text-sm text-gray-600">Belum ada observasi yang cocok dengan aturan aktif. Guru tetap dapat menambah alternatif sendiri.</p>
                    @endforelse
                </div>
            @endforelse
        </div>

        {{-- TODO PBI-16: bilah keputusan (simpan pilihan strategi) di bawah perbandingan. --}}
    </section>

    @include('alternatif-strategi._dialog-tambah')
</x-layouts.app>
