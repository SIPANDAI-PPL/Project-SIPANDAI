{{-- PBI-15 Mode Fokus per anak: pola respons dan semua alternatif strategi untuk satu anak. --}}
@php
    $anak = $data['anak'];
    $daftarPola = $data['pola'];
    // Semua saran dari aturan, termasuk saran lama dari aturan yang sama.
    $daftarSaranAturan = $data['saran_aturan'];
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

    // PBI-16: semua alternatif (dari aturan dan buatan guru) dengan semua status.
    // Urutan: dipilih, disarankan, diabaikan, lalu terbaru dulu.
    $urutanStatus = ['dipilih' => 0, 'disarankan' => 1, 'diabaikan' => 2];
    $daftarAlternatif = $daftarSaranAturan->concat($data['manual'])
        ->unique('id')
        ->sortBy([
            fn ($a, $b) => ($urutanStatus[$a->status] ?? 3) <=> ($urutanStatus[$b->status] ?? 3),
            fn ($a, $b) => $b->dibuat_pada <=> $a->dibuat_pada,
            fn ($a, $b) => $b->id <=> $a->id,
        ])
        ->values();
    $adaDipilih = $daftarAlternatif->contains('status', 'dipilih');
    $adaDisarankan = $daftarAlternatif->contains('status', 'disarankan');

    $kelasBadge = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium';
    $kelasLabelKartu = 'text-xs font-semibold uppercase tracking-wide';
@endphp
<x-layouts.app title="Mode Fokus Per Anak">
    <a href="{{ route('guru.alternatif-strategi.index', ['kelas' => $kelas->id]) }}" dusk="kembali-ke-daftar"
        class="inline-flex items-center gap-1 text-sm text-indigo-800 hover:underline">&larr; Kembali ke daftar</a>

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

    @if ($errors->hasAny(['strategi_id', 'catatan']))
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah">
            Alternatif belum tersimpan. Periksa isian pada formulir.
        </p>
    @endif

    @error('status')
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah-status">
            Keputusan belum tersimpan. {{ $message }}
        </p>
    @enderror

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
                    {{-- PBI-16: badge keputusan, diturunkan dari status alternatif. --}}
                    @if ($adaDisarankan)
                        <p class="mt-2"><span class="{{ $kelasBadge }} border-amber-200 bg-amber-50 text-amber-800" dusk="badge-keputusan"><span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>Menunggu keputusan guru</span></p>
                    @elseif ($adaDipilih)
                        <p class="mt-2"><span class="{{ $kelasBadge }} border-emerald-200 bg-emerald-50 text-emerald-800" dusk="badge-keputusan"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>Alternatif dipilih</span></p>
                    @endif
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
                    {{-- Catatan dari observasi terakhir anak dengan strategi ini. --}}
                    @php($observasiStrategi = $data['observasi_strategi_saat_ini']->get($strategiAsal->id))
                    @if ($observasiStrategi)
                        <div class="mt-4 rounded-r-lg border-l-4 border-gray-300 bg-white px-3 py-2.5 text-xs leading-relaxed text-gray-700">
                            <p class="font-semibold uppercase tracking-wide text-gray-800">Catatan observasi terakhir ({{ $observasiStrategi->tanggal->format('d/m/Y') }}):</p>
                            @if (filled($observasiStrategi->catatan_pendukung))
                                <p class="mt-1" dusk="catatan-strategi-saat-ini">{{ $observasiStrategi->catatan_pendukung }}</p>
                            @else
                                <p class="mt-1 italic text-gray-500" dusk="catatan-strategi-saat-ini-kosong">Belum ada catatan.</p>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-5" dusk="kartu-strategi-saat-ini">
                    <p class="{{ $kelasLabelKartu }} border-b border-gray-200 pb-3 text-gray-500">Strategi saat ini</p>
                    <p class="mt-4 text-sm text-gray-600">Belum ada pola dari aturan, jadi strategi saat ini belum tercatat.</p>
                </article>
            @endforelse

            {{-- Alternatif --}}
            @forelse ($daftarAlternatif as $saran)
                @include('alternatif-strategi._kartu-alternatif', ['nomor' => $loop->iteration])
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

    </section>

    @if ($daftarAlternatif->isNotEmpty())
        @include('alternatif-strategi._keputusan')
    @endif

    @include('alternatif-strategi._dialog-tambah')
</x-layouts.app>
