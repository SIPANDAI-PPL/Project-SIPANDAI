{{-- Fitur: PBI-11 Riwayat Observasi Anak dengan Filter --}}
{{--
    Catatan frontend:
    - Data di bawah masih dummy agar halaman bisa dipratinjau tanpa backend.
    - Saat controller sudah ada, kirim $anak, $observasis, dan (opsional) $opsiFilter.
    - Filter, penghitung, keadaan kosong, dan "Muat lebih banyak" berjalan di sisi klien
      supaya seluruh keadaan pada mockup PBI-11 bisa dicoba.
--}}
@php
    $anak = $anak ?? [
        'nama' => 'Dedek',
        'nama_panggilan' => 'Dedek',
        'kelas' => 'TK A - Melati',
    ];

    // 14 observasi dummy: 12 Oktober 2026 + 2 September 2026.
    // Komposisi kategori: 4 terlibat aktif, 3 terlibat dengan pendampingan,
    // 6 kurang tertarik, 1 belum terlibat (sesuai data uji PBI-11).
    $observasis = $observasis ?? [
        [
            'tanggal' => '2026-10-15',
            'aktivitas' => 'Menyusun balok',
            'aspek' => 'Kognitif',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Bertahan ±5 menit. Kembali antusias saat diberi pilihan menggambar menara baloknya.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-13',
            'aktivitas' => 'Permainan gerak',
            'aspek' => 'Motorik',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'terlibat_aktif',
            'catatan_pendukung' => 'Antusias, mengikuti semua gerakan sampai selesai.',
            'diubah' => true,
        ],
        [
            'tanggal' => '2026-10-13',
            'aktivitas' => 'Bercerita',
            'aspek' => 'Bahasa',
            'strategi' => 'Media visual',
            'kategori_respons' => 'terlibat_dengan_pendampingan',
            'catatan_pendukung' => 'Mau menjawab pertanyaan setelah ditunjukkan gambar tokoh.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-09',
            'aktivitas' => 'Menyusun balok',
            'aspek' => 'Kognitif',
            'strategi' => 'Media visual',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Berhenti setelah menara jatuh, tidak mau mencoba lagi.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-09',
            'aktivitas' => 'Bermain peran',
            'aspek' => 'Sosial emosional',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Enggan bergabung saat pembagian peran, memilih mengamati dari dekat.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-07',
            'aktivitas' => 'Bernyanyi bersama',
            'aspek' => 'Bahasa',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'terlibat_aktif',
            'catatan_pendukung' => 'Mengikuti lagu dan gerakan tangan dengan semangat.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-05',
            'aktivitas' => 'Menggambar bebas',
            'aspek' => 'Motorik',
            'strategi' => 'Media visual',
            'kategori_respons' => 'terlibat_dengan_pendampingan',
            'catatan_pendukung' => 'Perlu diingatkan untuk memegang krayon dengan benar.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-03',
            'aktivitas' => 'Puzzle gambar',
            'aspek' => 'Kognitif',
            'strategi' => 'Media visual',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Beralih ke kegiatan lain setelah memasang dua potongan.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-03',
            'aktivitas' => 'Bermain peran',
            'aspek' => 'Sosial emosional',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'terlibat_aktif',
            'catatan_pendukung' => 'Berbagi peran dengan teman tanpa diarahkan.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-01',
            'aktivitas' => 'Meronce manik',
            'aspek' => 'Motorik',
            'strategi' => 'Media visual',
            'kategori_respons' => 'belum_terlibat',
            'catatan_pendukung' => 'Belum mau mencoba, hanya mengamati teman.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-01',
            'aktivitas' => 'Meronce manik',
            'aspek' => 'Motorik',
            'strategi' => 'Media visual',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Menyusun tiga manik lalu meninggalkan area kegiatan.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-10-01',
            'aktivitas' => 'Bercerita',
            'aspek' => 'Bahasa',
            'strategi' => 'Media visual',
            'kategori_respons' => 'terlibat_dengan_pendampingan',
            'catatan_pendukung' => 'Mendengarkan cerita sampai selesai dengan bantuan gambar.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-09-28',
            'aktivitas' => 'Menyusun balok',
            'aspek' => 'Kognitif',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'kurang_tertarik',
            'catatan_pendukung' => 'Hanya bertahan sebentar, lalu pindah ke area lain.',
            'diubah' => false,
        ],
        [
            'tanggal' => '2026-09-22',
            'aktivitas' => 'Permainan gerak',
            'aspek' => 'Motorik',
            'strategi' => 'Kelompok kecil',
            'kategori_respons' => 'terlibat_aktif',
            'catatan_pendukung' => 'Mengikuti seluruh rangkaian gerak dengan gembira.',
            'diubah' => false,
        ],
    ];

    $koleksiObservasi = collect($observasis);
    $totalObservasi = $koleksiObservasi->count();

    $labelKategori = collect(\App\Enums\KategoriRespons::cases())
        ->mapWithKeys(fn ($kategori) => [$kategori->value => $kategori->label()]);

    $gayaBadge = [
        'terlibat_aktif' => 'bg-[#DDF3E8] text-emerald-800',
        'terlibat_dengan_pendampingan' => 'bg-[#DCEAFB] text-sky-800',
        'kurang_tertarik' => 'bg-[#FFE6CC] text-amber-800',
        'belum_terlibat' => 'bg-[#EDE9FE] text-violet-800',
    ];

    $opsiAspek = $koleksiObservasi->pluck('aspek')->unique()->sort()->values();
    $opsiAktivitas = $koleksiObservasi->pluck('aktivitas')->unique()->sort()->values();
    $opsiStrategi = $koleksiObservasi->pluck('strategi')->unique()->sort()->values();

    // Urut kronologis terbaru di atas, lalu dikelompokkan per tanggal.
    $grupObservasi = $koleksiObservasi
        ->sortByDesc('tanggal')
        ->groupBy('tanggal')
        ->sortKeysDesc();

    // Untuk mencegah kedipan saat halaman dimuat, 4 catatan pertama dirender
    // tanpa kelas "hidden"; sisanya disembunyikan dan dibuka lewat JavaScript.
    $nomorObservasi = 0;
    $jumlahAwalTampil = 4;

    $tautanDataAnak = \Illuminate\Support\Facades\Route::has('guru.data-anak.index')
        ? route('guru.data-anak.index')
        : route('guru.dashboard');
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
                            {{ $totalObservasi }} catatan
                        </p>
                    </div>

                    {{-- Filter --}}
                    <div class="mt-5 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="w-full sm:w-auto sm:flex-1">
                                <span class="sr-only">Periode</span>
                                <select id="filter-periode" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                                    <option value="30">30 hari terakhir</option>
                                    <option value="7">7 hari terakhir</option>
                                    <option value="1-15-okt-2026">1–15 Okt 2026</option>
                                    <option value="okt-2026">Oktober 2026</option>
                                    <option value="sep-2026">September 2026</option>
                                    <option value="semua">Semua periode</option>
                                </select>
                            </label>

                            <label class="w-full sm:w-auto sm:flex-1">
                                <span class="sr-only">Aspek</span>
                                <select id="filter-aspek" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                                    <option value="semua">Semua aspek</option>
                                    @foreach ($opsiAspek as $aspek)
                                        <option value="{{ $aspek }}">{{ $aspek }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="w-full sm:w-auto sm:flex-1">
                                <span class="sr-only">Aktivitas</span>
                                <select id="filter-aktivitas" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                                    <option value="semua">Semua aktivitas</option>
                                    @foreach ($opsiAktivitas as $aktivitas)
                                        <option value="{{ $aktivitas }}">{{ $aktivitas }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="w-full sm:w-auto sm:flex-1">
                                <span class="sr-only">Strategi</span>
                                <select id="filter-strategi" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                                    <option value="semua">Semua strategi</option>
                                    @foreach ($opsiStrategi as $strategi)
                                        <option value="{{ $strategi }}">{{ $strategi }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="w-full sm:w-auto sm:flex-1">
                                <span class="sr-only">Kategori respons</span>
                                <select id="filter-respons" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-[#221177] focus:outline-none focus:ring-2 focus:ring-[#221177]/20">
                                    <option value="semua">Semua respons</option>
                                    @foreach ($labelKategori as $nilai => $label)
                                        <option value="{{ $nilai }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <button type="button" id="hapus-filter" class="w-full whitespace-nowrap rounded-lg px-2 py-2 text-sm font-semibold text-[#221177] transition hover:bg-[#221177]/5 hover:underline sm:ml-auto sm:w-auto">
                                Hapus filter
                            </button>
                        </div>
                    </div>

                    @if ($totalObservasi === 0)
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
                    @else
                        {{-- Daftar observasi --}}
                        <div id="daftar-observasi" class="mt-6 space-y-6">
                            @foreach ($grupObservasi as $tanggal => $items)
                                @php $indeksAwalGrup = $nomorObservasi + 1; @endphp
                                <section class="date-group space-y-3 {{ $indeksAwalGrup > $jumlahAwalTampil ? 'hidden' : '' }}" data-grup="{{ $tanggal }}">
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
                                                class="obs-card rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition {{ $nomorObservasi > $jumlahAwalTampil ? 'hidden' : '' }}"
                                                data-tanggal="{{ $observasi['tanggal'] }}"
                                                data-aspek="{{ $observasi['aspek'] }}"
                                                data-aktivitas="{{ $observasi['aktivitas'] }}"
                                                data-strategi="{{ $observasi['strategi'] }}"
                                                data-respons="{{ $nilaiRespons }}"
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

                                                @if (! empty($observasi['diubah']))
                                                    <p class="mt-3 text-xs text-gray-400">
                                                        Diubah 1x
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

                        {{-- Keadaan kosong karena filter --}}
                        <div id="keadaan-kosong" class="mt-6 hidden rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-black/5">
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
                            <button type="button" id="hapus-filter-kosong" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#221177] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2E1F7E] focus:outline-none focus:ring-2 focus:ring-[#221177]/30">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 0 0 6 5.3L4 7m16 10-2 1.7A8 8 0 0 1 4 15" /></svg>
                                Hapus filter
                            </button>
                        </div>

                        {{-- Footer daftar --}}
                        <div id="footer-daftar" class="mt-6 flex flex-col items-center gap-3 pb-4">
                            <p id="penghitung-bawah" class="text-sm text-gray-500" aria-live="polite">
                                Menampilkan {{ min($jumlahAwalTampil, $totalObservasi) }} dari {{ $totalObservasi }} catatan
                            </p>
                            <button type="button" id="muat-lebih-banyak" class="inline-flex items-center gap-2 rounded-lg border border-[#221177]/20 bg-white px-4 py-2 text-sm font-semibold text-[#221177] shadow-sm transition hover:bg-[#F6F5FA] focus:outline-none focus:ring-2 focus:ring-[#221177]/20 {{ $totalObservasi <= $jumlahAwalTampil ? 'hidden' : '' }}">
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
                const daftar = document.getElementById('daftar-observasi');
                const kosong = document.getElementById('keadaan-kosong');
                const footer = document.getElementById('footer-daftar');
                const penghitungAtas = document.getElementById('penghitung-atas');
                const penghitungBawah = document.getElementById('penghitung-bawah');
                const tombolMuat = document.getElementById('muat-lebih-banyak');
                const tombolHapus = document.getElementById('hapus-filter');
                const tombolHapusKosong = document.getElementById('hapus-filter-kosong');

                const filter = {
                    periode: document.getElementById('filter-periode'),
                    aspek: document.getElementById('filter-aspek'),
                    aktivitas: document.getElementById('filter-aktivitas'),
                    strategi: document.getElementById('filter-strategi'),
                    respons: document.getElementById('filter-respons'),
                };

                const total = kartu.length;
                const tanggalAcuan = kartu.reduce((maks, item) => item.dataset.tanggal > maks ? item.dataset.tanggal : maks, '0000-00-00');
                const batasAwal = {{ $jumlahAwalTampil }};
                let jumlahTampil = batasAwal;

                const keTanggal = (teks) => new Date(`${teks}T00:00:00`);
                const geserHari = (tanggal, hari) => {
                    const hasil = new Date(tanggal);
                    hasil.setDate(hasil.getDate() + hari);
                    return hasil;
                };

                const dalamPeriode = (tanggalKartu, pilihan) => {
                    if (pilihan === 'semua') return true;

                    const tanggal = keTanggal(tanggalKartu);
                    const acuan = keTanggal(tanggalAcuan);

                    if (pilihan === '30') return tanggal >= geserHari(acuan, -29) && tanggal <= acuan;
                    if (pilihan === '7') return tanggal >= geserHari(acuan, -6) && tanggal <= acuan;

                    if (pilihan === '1-15-okt-2026') {
                        return tanggal >= keTanggal('2026-10-01') && tanggal <= keTanggal('2026-10-15');
                    }
                    if (pilihan === 'okt-2026') {
                        return tanggal >= keTanggal('2026-10-01') && tanggal <= keTanggal('2026-10-31');
                    }
                    if (pilihan === 'sep-2026') {
                        return tanggal >= keTanggal('2026-09-01') && tanggal <= keTanggal('2026-09-30');
                    }

                    return true;
                };

                const adaFilterAktif = () => {
                    return filter.periode.value !== '30'
                        || filter.aspek.value !== 'semua'
                        || filter.aktivitas.value !== 'semua'
                        || filter.strategi.value !== 'semua'
                        || filter.respons.value !== 'semua';
                };

                const cocok = (kartuItem) => {
                    if (! dalamPeriode(kartuItem.dataset.tanggal, filter.periode.value)) return false;
                    if (filter.aspek.value !== 'semua' && kartuItem.dataset.aspek !== filter.aspek.value) return false;
                    if (filter.aktivitas.value !== 'semua' && kartuItem.dataset.aktivitas !== filter.aktivitas.value) return false;
                    if (filter.strategi.value !== 'semua' && kartuItem.dataset.strategi !== filter.strategi.value) return false;
                    if (filter.respons.value !== 'semua' && kartuItem.dataset.respons !== filter.respons.value) return false;
                    return true;
                };

                const render = () => {
                    const aktif = adaFilterAktif();
                    const hasil = kartu.filter(cocok);

                    // Saat ada filter, tampilkan semua hasil agar penghitung sesuai kriteria penerimaan.
                    // Saat tanpa filter, tampilkan bertahap 4 per klik "Muat lebih banyak".
                    const batas = aktif ? hasil.length : Math.min(jumlahTampil, hasil.length);

                    kartu.forEach((item) => {
                        const indeks = hasil.indexOf(item);
                        item.classList.toggle('hidden', indeks === -1 || indeks >= batas);
                    });

                    grup.forEach((bagian) => {
                        const adaKartuTampil = Array.from(bagian.querySelectorAll('.obs-card'))
                            .some((item) => ! item.classList.contains('hidden'));
                        bagian.classList.toggle('hidden', ! adaKartuTampil);
                    });

                    const jumlahHasil = hasil.length;

                    if (jumlahHasil === 0) {
                        daftar.classList.add('hidden');
                        footer.classList.add('hidden');
                        kosong.classList.remove('hidden');
                        penghitungAtas.textContent = `0 dari ${total} catatan`;
                        penghitungBawah.textContent = 'Menampilkan 0 dari 0 hasil';
                        return;
                    }

                    daftar.classList.remove('hidden');
                    kosong.classList.add('hidden');
                    footer.classList.remove('hidden');

                    penghitungAtas.textContent = aktif
                        ? `${jumlahHasil} dari ${total} catatan`
                        : `${total} catatan`;

                    penghitungBawah.textContent = aktif
                        ? `Menampilkan ${batas} dari ${jumlahHasil} hasil`
                        : `Menampilkan ${batas} dari ${total} catatan`;

                    tombolMuat.classList.toggle('hidden', aktif || jumlahTampil >= total);
                };

                const resetFilter = () => {
                    filter.periode.value = '30';
                    filter.aspek.value = 'semua';
                    filter.aktivitas.value = 'semua';
                    filter.strategi.value = 'semua';
                    filter.respons.value = 'semua';
                    jumlahTampil = batasAwal;
                    render();
                };

                Object.values(filter).forEach((select) => {
                    select.addEventListener('change', () => {
                        jumlahTampil = batasAwal;
                        render();
                    });
                });

                tombolHapus.addEventListener('click', resetFilter);
                tombolHapusKosong.addEventListener('click', resetFilter);

                tombolMuat.addEventListener('click', () => {
                    jumlahTampil += batasAwal;
                    render();
                });

                render();
            });
        </script>
    @endif
</x-layouts.app>
