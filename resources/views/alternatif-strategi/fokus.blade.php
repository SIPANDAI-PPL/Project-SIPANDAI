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

    $kelasBadgeStatus = 'inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800';
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
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-xl font-semibold text-amber-800">{{ mb_strtoupper(mb_substr($anak->nama, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold">{{ $anak->nama }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $anak->tanggal_lahir->age }} tahun <span class="mx-1">&bull;</span> {{ $labelKelas }}</p>
                </div>
            </div>
            <button type="button" data-tambah-alternatif='@json($dataDialog)' dusk="tombol-tambah-alternatif"
                class="w-full rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-100 sm:w-auto">
                + Tambah alternatif
            </button>
        </div>

        <div class="mt-5 grid gap-3 border-t border-gray-100 pt-5 md:grid-cols-3">
            <div class="rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3">
                <p class="text-xs font-semibold text-amber-900">Pola respons teramati</p>
                @forelse ($daftarPola as $pola)
                    <p class="mt-1 text-sm text-amber-900">{{ $teksPola($pola) }}</p>
                @empty
                    <p class="mt-1 text-sm text-gray-600">Belum ada pola dari aturan.</p>
                @endforelse
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold text-gray-700">Observasi terakhir</p>
                <p class="mt-1 text-sm text-gray-700">{{ $observasiTerakhir ? $observasiTerakhir->format('d/m/Y') : '-' }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold text-gray-700">Strategi saat ini</p>
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

    {{-- Alternatif strategi --}}
    <section class="mt-6">
        <h2 class="text-lg font-semibold">Alternatif strategi</h2>
        <p class="mt-1 text-sm text-gray-600">Saran berikut adalah bahan pertimbangan. Keputusan tetap di tangan guru.</p>

        @if ($data['punya_saran'])
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($daftarSaranAturan as $saran)
                    <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5" dusk="kartu-saran-{{ $saran->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-800">Dari aturan</span>
                            <span class="{{ $kelasBadgeStatus }}"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ ucfirst($saran->status) }}</span>
                        </div>
                        <h3 class="mt-3 font-semibold text-indigo-900">{{ $saran->strategi->nama }}</h3>
                        @if ($saran->strategi->deskripsi)
                            <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                        @endif
                        @if ($saran->dasar_saran)
                            <p class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs leading-relaxed text-gray-600"><span class="font-medium">Dasar saran:</span> {{ $saran->dasar_saran }}</p>
                        @endif
                        {{-- TODO PBI-16: tombol Pilih / Abaikan / Tinjau Ulang di sini. --}}
                    </article>
                @endforeach

                @foreach ($data['manual'] as $saran)
                    <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5" dusk="kartu-saran-{{ $saran->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-900" dusk="penanda-manual">Ditambahkan guru</span>
                            <span class="{{ $kelasBadgeStatus }}"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ ucfirst($saran->status) }}</span>
                        </div>
                        <h3 class="mt-3 font-semibold text-indigo-900">{{ $saran->strategi->nama }}</h3>
                        @if ($saran->strategi->deskripsi)
                            <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
                        @endif
                        @if ($saran->dasar_saran)
                            <p class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs leading-relaxed text-gray-600"><span class="font-medium">Catatan guru:</span> {{ $saran->dasar_saran }}</p>
                        @endif
                        {{-- TODO PBI-16: tombol Pilih / Abaikan / Tinjau Ulang di sini. --}}
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-6" dusk="belum-ada-alternatif">
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
        @endif
    </section>

    @include('alternatif-strategi._dialog-tambah')
</x-layouts.app>
