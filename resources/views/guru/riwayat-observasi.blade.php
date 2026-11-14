{{-- Fitur: PBI-11 Riwayat Observasi Anak dengan Filter --}}
@php
    $koleksiObservasi = collect($observasis);
    $totalObservasi = $totalObservasi ?? $koleksiObservasi->count();

    $labelKategori = collect(\App\Enums\KategoriRespons::cases())
        ->mapWithKeys(fn ($kategori) => [$kategori->value => $kategori->label()]);

    $gayaBadge = [
        'terlibat_aktif' => 'bg-[#DDF3E8] text-emerald-800',
        'terlibat_dengan_pendampingan' => 'bg-[#DCEAFB] text-sky-800',
        'kurang_tertarik' => 'bg-[#FFE6CC] text-amber-800',
        'belum_terlibat' => 'bg-[#EDE9FE] text-violet-800',
    ];

    // Kronologis terbaru di atas, lalu dikelompokkan per tanggal.
    $grupObservasi = $koleksiObservasi
        ->sortByDesc('tanggal')
        ->groupBy('tanggal')
        ->sortKeysDesc();

    // Tampilkan 20 catatan pertama; catatan ke-21 dan seterusnya lewat "Muat lebih banyak".
    // Dengan 14 catatan (data uji PBI-11) seluruh riwayat langsung terlihat.
    // Saat filter aktif: seluruh hasil langsung tampil.
    $jumlahAwalTampil = 20;
    $batasAwalTampil = $filterAktif ? $totalObservasi : min($jumlahAwalTampil, $totalObservasi);
    $nomorObservasi = 0;

    $tautanDataAnak = \Illuminate\Support\Facades\Route::has('guru.anak.index')
        ? route('guru.anak.index')
        : route('guru.dashboard');

    $tautanHalaman = route('guru.riwayat-observasi', $anak['id']);
@endphp
<x-layouts.app title="Riwayat Observasi">
    <div class="mx-auto w-full max-w-6xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-500" aria-label="Breadcrumb">
            <a href="{{ $tautanDataAnak }}" class="inline-flex items-center gap-1 rounded hover:text-[#221177]" aria-label="Kembali ke Data Anak">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            </a>
            <a href="{{ $tautanDataAnak }}" class="hover:text-[#221177] hover:underline">Data Anak</a>
            <svg class="h-3.5 w-3.5 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
            <span>{{ $anak['nama_panggilan'] }}</span>
            <svg class="h-3.5 w-3.5 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
            <span class="font-medium text-gray-700" aria-current="page">Riwayat Observasi</span>
        </nav>

        {{-- Judul --}}
        <div class="mt-3 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Riwayat Observasi</h1>
                <p class="mt-1 text-sm text-gray-500">
                    <span class="font-medium text-gray-700">{{ $anak['nama_panggilan'] }}</span>
                    <span class="mx-1.5 text-gray-300">•</span>
                    <span>{{ $anak['kelas'] }}</span>
                </p>
            </div>
            <p id="penghitung-atas" class="text-sm font-medium text-gray-600" aria-live="polite">
                @if ($filterAktif)
                    {{ $totalObservasi }} dari {{ $totalSemua }} catatan
                @else
                    {{ $totalSemua }} catatan
                @endif
            </p>
        </div>

        {{-- Filter: dikirim ke server lewat GET agar kombinasi filter tetap bekerja tanpa JavaScript. --}}
        @if ($totalSemua > 0)
            <form method="GET" action="{{ $tautanHalaman }}" class="mt-5 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center gap-3">
                    <label class="w-full sm:w-auto sm:flex-1">
                        <span class="sr-only">Periode</span>
                        <select id="filter-periode" name="periode" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                            <option value="30" @selected($filter['periode'] === '30')>30 hari terakhir</option>
                            <option value="7" @selected($filter['periode'] === '7')>7 hari terakhir</option>
                            @foreach ($opsiFilter['periode'] as $nilai => $label)
                                <option value="{{ $nilai }}" @selected($filter['periode'] === $nilai)>{{ $label }}</option>
                            @endforeach
                            <option value="semua" @selected($filter['periode'] === 'semua')>Semua periode</option>
                        </select>
                    </label>

                    <label class="w-full sm:w-auto sm:flex-1">
                        <span class="sr-only">Aspek</span>
                        <select id="filter-aspek" name="aspek" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                            <option value="">Semua aspek</option>
                            @foreach ($opsiFilter['aspek'] as $aspek)
                                <option value="{{ $aspek->id }}" @selected((int) $filter['aspek'] === $aspek->id)>{{ $aspek->nama }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="w-full sm:w-auto sm:flex-1">
                        <span class="sr-only">Aktivitas</span>
                        <select id="filter-aktivitas" name="aktivitas" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                            <option value="">Semua aktivitas</option>
                            @foreach ($opsiFilter['aktivitas'] as $aktivitas)
                                <option value="{{ $aktivitas->id }}" @selected((int) $filter['aktivitas'] === $aktivitas->id)>{{ $aktivitas->nama }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="w-full sm:w-auto sm:flex-1">
                        <span class="sr-only">Strategi</span>
                        <select id="filter-strategi" name="strategi" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                            <option value="">Semua strategi</option>
                            @foreach ($opsiFilter['strategi'] as $strategi)
                                <option value="{{ $strategi->id }}" @selected((int) $filter['strategi'] === $strategi->id)>{{ $strategi->nama }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="w-full sm:w-auto sm:flex-1">
                        <span class="sr-only">Kategori respons</span>
                        <select id="filter-respons" name="respons" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                            <option value="">Semua respons</option>
                            @foreach ($opsiFilter['respons'] as $respons)
                                <option value="{{ $respons->value }}" @selected($filter['respons'] === $respons->value)>{{ $respons->label() }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit" class="sr-only">Terapkan filter</button>

                    <a href="{{ $tautanHalaman }}" class="w-full whitespace-nowrap rounded-lg px-2 py-2 text-sm font-semibold text-[#221177] transition hover:bg-[#221177]/5 hover:underline sm:ml-auto sm:w-auto">
                        Hapus filter
                    </a>
                </div>
            </form>
        @endif

        @if ($totalSemua === 0)
            {{-- TC-11-12: anak belum punya observasi --}}
            <div class="mt-6 rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-black/5">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#EDE9FE] text-[#221177]">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l3 3v13H5V4h3Zm0 0v4h8V4M9 13h6M9 17h4" /></svg>
                </span>
                <h2 class="mt-4 text-base font-semibold text-gray-900">Belum ada observasi</h2>
                <p class="mx-auto mt-1 max-w-md text-sm leading-relaxed text-gray-500">
                    Catatan observasi {{ $anak['nama_panggilan'] }} akan muncul di sini setelah guru mencatat observasi pertama.
                </p>
            </div>
        @elseif ($totalObservasi === 0)
            {{-- TC-11-11: filter tidak menghasilkan data --}}
            <div class="mt-6 rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-black/5">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#EDE9FE] text-[#221177]">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16M7 9h10M9.5 13h5M8 21l4-3 4 3V9H8v12Z" />
                    </svg>
                </span>
                <h2 class="mt-4 text-base font-semibold text-gray-900">Belum ada observasi yang sesuai</h2>
                <p class="mx-auto mt-1 max-w-lg text-sm leading-relaxed text-gray-500">
                    Tidak ada catatan untuk {{ $anak['nama_panggilan'] }} dengan filter yang dipilih.
                    Coba ubah periode atau hapus filter untuk melihat seluruh riwayat.
                </p>
                <a href="{{ $tautanHalaman }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#221177] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2E1F7E] focus:outline-none focus:ring-2 focus:ring-[#221177]/30">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 0 0 6 5.3L4 7m16 10-2 1.7A8 8 0 0 1 4 15" /></svg>
                    Hapus filter
                </a>
            </div>
        @else
            {{-- Daftar observasi --}}
            <div id="daftar-observasi" class="mt-6 space-y-6">
                @foreach ($grupObservasi as $tanggal => $items)
                    @php $indeksAwalGrup = $nomorObservasi + 1; @endphp
                    <section class="date-group space-y-3 {{ $indeksAwalGrup > $batasAwalTampil ? 'hidden' : '' }}" data-grup="{{ $tanggal }}">
                        <h2 class="pl-1 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, j M Y') }}
                        </h2>

                        <div class="space-y-3">
                            @foreach ($items as $observasi)
                                @php
                                    $nomorObservasi++;
                                    $nilaiRespons = $observasi['kategori_respons'];
                                @endphp
                                <article
                                    class="obs-card rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition {{ $nomorObservasi > $batasAwalTampil ? 'hidden' : '' }}"
                                    data-tanggal="{{ $observasi['tanggal'] }}"
                                >
                                    <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-3">
                                        <div class="min-w-0">
                                            <h3 class="text-base font-semibold text-gray-900">{{ $observasi['aktivitas'] }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Aspek: {{ $observasi['aspek'] }}
                                                <span class="mx-1.5 text-gray-300">•</span>
                                                Strategi: {{ $observasi['strategi'] }}
                                            </p>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $gayaBadge[$nilaiRespons] ?? 'bg-gray-100 text-gray-700' }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                            {{ $labelKategori[$nilaiRespons] ?? $nilaiRespons }}
                                        </span>
                                    </div>

                                    <p class="mt-3 text-sm leading-relaxed text-gray-700">
                                        {{ $observasi['catatan_pendukung'] }}
                                    </p>

                                    @if (($observasi['jumlah_perubahan'] ?? 0) > 0)
                                        <p class="mt-3 text-xs text-gray-400">
                                            Diubah {{ $observasi['jumlah_perubahan'] }}x
                                            <span class="mx-1">•</span>
                                            <a href="#" class="underline decoration-dotted underline-offset-2 hover:text-gray-600">lihat perubahan</a>
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- Footer daftar --}}
            <div id="footer-daftar" class="mt-6 flex flex-col items-center gap-3 pb-4">
                <p id="penghitung-bawah" class="text-sm text-gray-500" aria-live="polite">
                    Menampilkan {{ $batasAwalTampil }} dari {{ $totalObservasi }} {{ $filterAktif ? 'hasil' : 'catatan' }}
                </p>
                <button type="button" id="muat-lebih-banyak" class="inline-flex items-center gap-2 rounded-lg border border-[#221177]/20 bg-white px-4 py-2 text-sm font-semibold text-[#221177] shadow-sm transition hover:bg-[#F6F5FA] focus:outline-none focus:ring-2 focus:ring-[#221177]/20 {{ ($filterAktif || $totalObservasi <= $jumlahAwalTampil) ? 'hidden' : '' }}">
                    Muat lebih banyak
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                </button>
            </div>
        @endif
    </div>

    @if ($totalObservasi > 0)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const kartu = Array.from(document.querySelectorAll('.obs-card'));
                const grup = Array.from(document.querySelectorAll('.date-group'));
                const tombol = document.getElementById('muat-lebih-banyak');
                const penghitung = document.getElementById('penghitung-bawah');

                const langkah = {{ $jumlahAwalTampil }};
                const total = kartu.length;
                const satuan = @json($filterAktif ? 'hasil' : 'catatan');
                let jumlahTampil = {{ $batasAwalTampil }};

                const render = () => {
                    kartu.forEach((item, indeks) => {
                        item.classList.toggle('hidden', indeks >= jumlahTampil);
                    });

                    grup.forEach((bagian) => {
                        const adaKartuTampil = Array.from(bagian.querySelectorAll('.obs-card'))
                            .some((item) => ! item.classList.contains('hidden'));
                        bagian.classList.toggle('hidden', ! adaKartuTampil);
                    });

                    penghitung.textContent = `Menampilkan ${Math.min(jumlahTampil, total)} dari ${total} ${satuan}`;
                    tombol.classList.toggle('hidden', jumlahTampil >= total);
                };

                tombol.addEventListener('click', () => {
                    jumlahTampil += langkah;
                    render();
                });
            });
        </script>
    @endif
</x-layouts.app>
