{{-- Fitur: PBI-11 Riwayat Observasi Anak dengan Filter --}}
@php
    $koleksiObservasi = collect($observasis);
    $totalObservasi = $totalObservasi ?? $koleksiObservasi->count();

    $labelKategori = collect(\App\Enums\KategoriRespons::cases())
        ->mapWithKeys(fn ($kategori) => [$kategori->value => $kategori->label()]);

    // Sama seperti halaman Observasi: tiap kategori respons punya warna sendiri.
    $gayaRespons = [
        'terlibat_aktif' => 'resp-hijau',
        'terlibat_dengan_pendampingan' => 'resp-biru',
        'kurang_tertarik' => 'resp-oranye',
        'belum_terlibat' => 'resp-abu',
    ];

    // Ringkasan kecil di atas daftar.
    $distribusiRespons = $koleksiObservasi->countBy('kategori_respons');
    $responsTeratas = $distribusiRespons->sortDesc()->keys()->first();
    $jumlahResponsTeratas = $responsTeratas !== null ? (int) $distribusiRespons->get($responsTeratas) : 0;
    $jumlahPerubahan = (int) $koleksiObservasi->sum('jumlah_perubahan');
    $tanggalTerakhir = $koleksiObservasi->max('tanggal');

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
    <style>
        .riw {
            --brand: #2E2580;
            --brand-2: #4338CA;
            --text: #111827;
            --text-2: #374151;
            --muted: #6B7280;
            --line: #E5E7EB;
            --line-2: #F1F2F4;
            --soft: #F9FAFB;

            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
            color: var(--text);
        }

        .riw *,
        .riw *::before,
        .riw *::after { box-sizing: border-box; }

        .riw svg { display: block; }
        .riw .hidden,
        .riw [hidden] { display: none !important; }

        /* Warna respons, identik dengan halaman Observasi */
        .riw .tone-indigo  { --a: #4F46E5; --a-soft: #EEF2FF; --a-ring: rgba(79, 70, 229, .16); }
        .riw .tone-emerald { --a: #059669; --a-soft: #ECFDF5; --a-ring: rgba(5, 150, 105, .16); }
        .riw .tone-amber   { --a: #D97706; --a-soft: #FFF7E6; --a-ring: rgba(217, 119, 6, .16); }
        .riw .tone-violet  { --a: #7C3AED; --a-soft: #F5F3FF; --a-ring: rgba(124, 58, 237, .16); }

        .riw .resp-hijau  { --r: #059669; --r-text: #065F46; --r-soft: #ECFDF5; }
        .riw .resp-biru   { --r: #2563EB; --r-text: #1E40AF; --r-soft: #EFF6FF; }
        .riw .resp-oranye { --r: #EA580C; --r-text: #9A3412; --r-soft: #FFF7ED; }
        .riw .resp-abu    { --r: #6B7280; --r-text: #374151; --r-soft: #F3F4F6; }

        /* Breadcrumb */
        .riw-crumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12.5px;
            color: var(--muted);
        }

        .riw-crumb a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: inherit;
            text-decoration: none;
            border-radius: 6px;
            transition: color .15s;
        }

        .riw-crumb a:hover { color: var(--brand-2); text-decoration: underline; }
        .riw-crumb svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* Hero */
        .riw-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 26px;
            color: #fff;
            background: linear-gradient(120deg, #2E2580 0%, #4338CA 55%, #6D5DF6 100%);
            border-radius: 20px;
            box-shadow: 0 12px 28px -14px rgba(46, 37, 128, .55);
        }

        .riw-hero::before,
        .riw-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            pointer-events: none;
        }

        .riw-hero::before { width: 220px; height: 220px; top: -110px; right: 120px; }
        .riw-hero::after { width: 140px; height: 140px; bottom: -80px; right: 340px; }

        .riw-hero-main {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .riw-hero-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 16px;
        }

        .riw-hero-icon svg { width: 28px; height: 28px; }

        .riw-hero h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.01em;
            color: #fff;
        }

        .riw-hero p {
            margin: 4px 0 0;
            font-size: 14.5px;
            line-height: 1.5;
            color: rgba(255, 255, 255, .85);
        }

        .riw-hero-kanan {
            position: relative;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .riw-hitung {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 999px;
        }

        .riw-hitung svg { width: 16px; height: 16px; }

        .riw .riw-hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 46px;
            padding: 0 18px;
            font-size: 14.5px;
            font-weight: 600;
            color: var(--brand);
            text-decoration: none;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px -6px rgba(17, 12, 70, .45);
            transition: transform .15s, box-shadow .15s, background-color .15s;
        }

        .riw .riw-hero-btn:hover { background: #F5F3FF; transform: translateY(-1px); }
        .riw .riw-hero-btn svg { width: 17px; height: 17px; }

        /* Ringkasan */
        .riw-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .riw-stat {
            padding: 18px 20px;
            background: #fff;
            border: 1px solid var(--line);
            border-left: 4px solid var(--a);
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .riw-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .riw-stat-label {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-2);
        }

        .riw-stat-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            color: var(--a);
            background: var(--a-soft);
            border-radius: 12px;
        }

        .riw-stat-icon svg { width: 21px; height: 21px; }

        .riw-stat-value {
            margin: 6px 0 0;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.15;
            color: var(--a);
        }

        .riw-stat-value.is-teks { font-size: 21px; }

        .riw-stat-desc {
            margin: 6px 0 0;
            font-size: 12.5px;
            color: var(--muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Distribusi respons: bisa diklik untuk menyaring */
        .riw-distribusi {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .riw .riw-pilih-respons {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            font-family: inherit;
            text-align: left;
            color: var(--r-text);
            background: #fff;
            border: 1.5px solid var(--line);
            border-radius: 14px;
            cursor: pointer;
            transition: background-color .15s, border-color .15s, transform .15s, box-shadow .15s;
        }

        .riw .riw-pilih-respons:hover {
            background: var(--r-soft);
            border-color: var(--r);
            transform: translateY(-1px);
        }

        .riw .riw-pilih-respons:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--r-soft); }
        .riw .riw-pilih-respons.is-aktif { background: var(--r-soft); border-color: var(--r); }

        .riw-titik {
            flex-shrink: 0;
            width: 10px;
            height: 10px;
            background: var(--r);
            border-radius: 999px;
        }

        .riw-pilih-respons-teks { min-width: 0; }

        .riw-pilih-respons-jumlah {
            display: block;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.1;
        }

        .riw-pilih-respons-label {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Filter */
        .riw-filter-kartu {
            padding: 20px 24px 22px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .riw-filter {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .riw-filter-actions {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .riw-label {
            display: block;
            margin: 0 0 7px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-2);
        }

        .riw .riw-control {
            display: block;
            width: 100%;
            height: 44px;
            margin: 0;
            padding: 0 36px 0 12px;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.4;
            color: var(--text);
            background-color: var(--soft);
            border: 1.5px solid var(--line);
            border-radius: 10px;
            box-shadow: none;
            outline: none;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }

        .riw .riw-control:hover { border-color: #D1D5DB; }
        .riw .riw-control:focus { background-color: #fff; border-color: var(--a); box-shadow: 0 0 0 4px var(--a-ring); }

        .riw-pilihan { position: relative; }

        .riw-pilihan > svg {
            position: absolute;
            top: 50%;
            right: 12px;
            width: 18px;
            height: 18px;
            color: var(--a);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .riw .riw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 18px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            color: var(--a);
            background: var(--a-soft);
            border: 1.5px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color .15s, border-color .15s, box-shadow .15s;
        }

        .riw .riw-btn:hover { border-color: var(--a); }
        .riw .riw-btn:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--a-ring); }
        .riw .riw-btn svg { width: 17px; height: 17px; }
        .riw .riw-btn.is-netral { background: #F9FAFB; color: #4B5563; border-color: var(--line); }
        .riw .riw-btn.is-netral:hover { border-color: #9CA3AF; }

        .riw-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: -4px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .riw-meta svg { flex-shrink: 0; width: 15px; height: 15px; color: #7C3AED; }

        /* Timeline */
        .riw-timeline { display: flex; flex-direction: column; gap: 18px; }
        .riw-grup { display: flex; flex-direction: column; gap: 10px; }

        .riw-tanggal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 2px 2px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #6D28D9;
        }

        .riw-tanggal::before {
            content: "";
            width: 8px;
            height: 8px;
            background: linear-gradient(120deg, #2E2580, #7C3AED);
            border-radius: 999px;
            box-shadow: 0 0 0 4px #F5F3FF;
        }

        .riw .obs-card {
            position: relative;
            overflow: hidden;
            padding: 18px 20px;
            background: #fff;
            border: 1px solid var(--line);
            border-left: 4px solid var(--r);
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
            transition: transform .18s, box-shadow .18s, border-color .18s;
        }

        .riw .obs-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -16px rgba(46, 37, 128, .5);
            border-color: var(--r);
            border-left-color: var(--r);
        }

        .riw-kartu-kepala {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .riw-kartu-ikon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            color: var(--r);
            background: var(--r-soft);
            border-radius: 12px;
        }

        .riw-kartu-ikon svg { width: 22px; height: 22px; }

        .riw-kartu-identitas { min-width: 0; flex: 1 1 auto; }

        .riw-kartu-judul {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
            color: var(--text);
        }

        .riw-kartu-sub {
            margin: 3px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .riw .obs-resp {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
            padding: 6px 12px;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
            color: var(--r-text);
            background: var(--r-soft);
            border-radius: 999px;
        }

        .riw .obs-resp::before {
            content: "";
            width: 7px;
            height: 7px;
            background: var(--r);
            border-radius: 999px;
        }

        .riw-chips { display: flex; flex-wrap: wrap; gap: 7px; margin: 14px 0 0; }

        .riw-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            font-size: 12.5px;
            font-weight: 600;
            color: #6D28D9;
            background: #F5F3FF;
            border-radius: 8px;
        }

        .riw-catatan {
            margin: 12px 0 0;
            padding: 10px 12px;
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--text-2);
            background: var(--soft);
            border-radius: 10px;
        }

        .riw-perubahan { margin: 12px 0 0; font-size: 12px; color: var(--muted); }
        .riw-perubahan a { font-weight: 600; color: var(--brand-2); text-underline-offset: 2px; }
        .riw-perubahan a:hover { color: var(--brand); }

        /* Kosong & footer */
        .riw-empty {
            padding: 42px 24px;
            text-align: center;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .riw-empty-icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            color: #4F46E5;
            background: #EEF2FF;
            border-radius: 16px;
        }

        .riw-empty-icon svg { width: 28px; height: 28px; }
        .riw-empty h2 { margin: 0; font-size: 16px; font-weight: 700; color: var(--text); }

        .riw-empty p {
            max-width: 480px;
            margin: 6px auto 0;
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--muted);
        }

        .riw-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding-bottom: 4px;
            font-size: 13px;
            color: var(--muted);
        }

        @media (max-width: 1279px) {
            .riw-filter { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 1023px) {
            .riw-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .riw-distribusi { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767px) {
            .riw { gap: 12px; }
            .riw-hero { padding: 18px; border-radius: 16px; }
            .riw-hero-icon { width: 46px; height: 46px; border-radius: 14px; }
            .riw-hero-icon svg { width: 24px; height: 24px; }
            .riw-hero h1 { font-size: 21px; }
            .riw-hero p { font-size: 13.5px; }
            .riw-hero-kanan,
            .riw .riw-hero-btn { width: 100%; }
            .riw-hero-kanan { align-items: stretch; flex-direction: column-reverse; }

            .riw-stats,
            .riw-distribusi,
            .riw-filter { grid-template-columns: 1fr; }

            .riw-filter-kartu { padding: 16px; border-radius: 16px; }
            .riw-filter-actions .riw-btn { width: 100%; }
            .riw .obs-card { padding: 16px; border-radius: 14px; }
            .riw-kartu-kepala { flex-wrap: wrap; }
            .riw .obs-resp { margin-left: 54px; }
        }
    </style>

    <div class="obs riw">
        {{-- Breadcrumb --}}
        <nav class="riw-crumb" aria-label="Breadcrumb">
            <a href="{{ $tautanDataAnak }}" class="rounded hover:text-[#221177]" aria-label="Kembali ke Data Anak">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            </a>
            <a href="{{ $tautanDataAnak }}">Data Anak</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
            <span>{{ $anak['nama_panggilan'] }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="text-gray-300"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
            <span class="font-medium text-gray-700" aria-current="page">Riwayat Observasi</span>
        </nav>

        {{-- Hero --}}
        <header class="riw-hero">
            <div class="riw-hero-main">
                <div class="riw-hero-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 102.3-5.6M4 4.5v3.5h3.5M12 8v4.25l2.75 1.75" />
                    </svg>
                </div>
                <div>
                    <h1>Riwayat Observasi</h1>
                    <p>
                        {{-- Nama lengkap seperti di halaman Observasi, panggilan di dalam kurung. --}}
                        <span class="font-semibold">{{ $anak['nama'] }}</span>
                        @if ($anak['nama_panggilan'] && $anak['nama_panggilan'] !== $anak['nama'])
                            <span>({{ $anak['nama_panggilan'] }})</span>
                        @endif
                        <span class="mx-1.5 opacity-60">•</span>
                        <span>{{ $anak['kelas'] }}</span>
                    </p>
                </div>
            </div>

            <div class="riw-hero-kanan">
                <span id="penghitung-atas" class="riw-hitung" aria-live="polite">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="4" width="14" height="17" rx="2.5" />
                        <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7" />
                    </svg>
                    @if ($filterAktif)
                        {{ $totalObservasi }} dari {{ $totalSemua }} catatan
                    @else
                        {{ $totalSemua }} catatan
                    @endif
                </span>
                <a href="{{ $tautanDataAnak }}" class="riw-hero-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    Kembali ke Data Anak
                </a>
            </div>
        </header>

        {{-- Ringkasan --}}
        @if ($totalSemua > 0)
            <section class="riw-stats" aria-label="Ringkasan riwayat observasi">
                <div class="riw-stat tone-indigo">
                    <div class="riw-stat-top">
                        <p class="riw-stat-label">Total Catatan</p>
                        <div class="riw-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="4" width="14" height="17" rx="2.5" />
                                <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7M8.5 17h4" />
                            </svg>
                        </div>
                    </div>
                    <p class="riw-stat-value">{{ $totalObservasi }}</p>
                    <p class="riw-stat-desc">
                        {{ $filterAktif ? 'dari '.$totalSemua.' catatan keseluruhan' : 'Seluruh catatan tersimpan' }}
                    </p>
                </div>

                <div class="riw-stat tone-emerald">
                    <div class="riw-stat-top">
                        <p class="riw-stat-label">Respons Terbanyak</p>
                        <div class="riw-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V7" />
                            </svg>
                        </div>
                    </div>
                    <p class="riw-stat-value">{{ $jumlahResponsTeratas }}</p>
                    <p class="riw-stat-desc">{{ $responsTeratas !== null ? $labelKategori[$responsTeratas] : 'Belum ada data' }}</p>
                </div>

                <div class="riw-stat tone-amber">
                    <div class="riw-stat-top">
                        <p class="riw-stat-label">Terakhir Dicatat</p>
                        <div class="riw-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="5.5" width="16" height="14.5" rx="2.5" />
                                <path stroke-linecap="round" d="M4 10h16M8.5 3.5v4M15.5 3.5v4" />
                            </svg>
                        </div>
                    </div>
                    <p class="riw-stat-value is-teks">
                        {{ $tanggalTerakhir ? \Illuminate\Support\Carbon::parse($tanggalTerakhir)->locale('id')->translatedFormat('j M Y') : '-' }}
                    </p>
                    <p class="riw-stat-desc">{{ $jumlahPerubahan }} catatan pernah diubah</p>
                </div>
            </section>

            {{-- Distribusi respons: klik untuk menyaring --}}
            <section class="riw-distribusi" aria-label="Distribusi kategori respons">
                @foreach (\App\Enums\KategoriRespons::cases() as $kategori)
                    <button type="button"
                        class="riw-pilih-respons {{ $gayaRespons[$kategori->value] }} {{ $filter['respons'] === $kategori->value ? 'is-aktif' : '' }}"
                        data-pilih-respons="{{ $kategori->value }}"
                        title="Saring berdasarkan {{ $kategori->label() }}">
                        <span class="riw-titik" aria-hidden="true"></span>
                        <span class="riw-pilih-respons-teks">
                            <span class="riw-pilih-respons-jumlah">{{ $distribusiRespons->get($kategori->value, 0) }}</span>
                            <span class="riw-pilih-respons-label">{{ $kategori->label() }}</span>
                        </span>
                    </button>
                @endforeach
            </section>
        @endif

        {{-- Filter: dikirim ke server lewat GET agar kombinasi filter tetap bekerja tanpa JavaScript. --}}
        @if ($totalSemua > 0)
            <form method="GET" action="{{ $tautanHalaman }}" class="riw-filter-kartu tone-violet" role="search">
                <div class="riw-filter">
                    <div>
                        <label for="filter-periode" class="riw-label">Periode</label>
                        <div class="riw-pilihan">
                            <select id="filter-periode" name="periode" onchange="this.form.requestSubmit()" class="riw-control">
                                <option value="30" @selected($filter['periode'] === '30')>30 hari terakhir</option>
                                <option value="7" @selected($filter['periode'] === '7')>7 hari terakhir</option>
                                @foreach ($opsiFilter['periode'] as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($filter['periode'] === $nilai)>{{ $label }}</option>
                                @endforeach
                                <option value="semua" @selected($filter['periode'] === 'semua')>Semua periode</option>
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="filter-aspek" class="riw-label">Aspek</label>
                        <div class="riw-pilihan">
                            <select id="filter-aspek" name="aspek" onchange="this.form.requestSubmit()" class="riw-control">
                                <option value="">Semua aspek</option>
                                @foreach ($opsiFilter['aspek'] as $aspek)
                                    <option value="{{ $aspek->id }}" @selected((int) $filter['aspek'] === $aspek->id)>{{ $aspek->nama }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="filter-aktivitas" class="riw-label">Aktivitas</label>
                        <div class="riw-pilihan">
                            <select id="filter-aktivitas" name="aktivitas" onchange="this.form.requestSubmit()" class="riw-control">
                                <option value="">Semua aktivitas</option>
                                @foreach ($opsiFilter['aktivitas'] as $aktivitas)
                                    <option value="{{ $aktivitas->id }}" @selected((int) $filter['aktivitas'] === $aktivitas->id)>{{ $aktivitas->nama }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="filter-strategi" class="riw-label">Strategi</label>
                        <div class="riw-pilihan">
                            <select id="filter-strategi" name="strategi" onchange="this.form.requestSubmit()" class="riw-control">
                                <option value="">Semua strategi</option>
                                @foreach ($opsiFilter['strategi'] as $strategi)
                                    <option value="{{ $strategi->id }}" @selected((int) $filter['strategi'] === $strategi->id)>{{ $strategi->nama }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="filter-respons" class="riw-label">Respons</label>
                        <div class="riw-pilihan">
                            <select id="filter-respons" name="respons" onchange="this.form.requestSubmit()" class="riw-control">
                                <option value="">Semua respons</option>
                                @foreach ($opsiFilter['respons'] as $respons)
                                    <option value="{{ $respons->value }}" @selected($filter['respons'] === $respons->value)>{{ $respons->label() }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>
                    </div>

                    <div class="riw-filter-actions">
                        <button type="submit" class="riw-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16l-6 7v5l-4 2v-7l-6-7z" />
                            </svg>
                            Terapkan Filter
                        </button>
                        <a href="{{ $tautanHalaman }}" class="riw-btn is-netral">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 0 0 6 5.3L4 7m16 10-2 1.7A8 8 0 0 1 4 15" />
                            </svg>
                            Hapus filter
                        </a>
                    </div>
                </div>
            </form>

            <p class="riw-meta">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16l-6 7v5l-4 2v-7l-6-7z" />
                </svg>
                <span>
                    Menampilkan {{ $filterAktif ? 'hasil penyaringan' : 'seluruh catatan' }}
                    &bull; {{ $anak['nama_panggilan'] }}
                </span>
            </p>
        @endif

        @if ($totalSemua === 0)
            {{-- TC-11-12: anak belum punya observasi --}}
            <div class="riw-empty">
                <span class="riw-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l3 3v13H5V4h3Zm0 0v4h8V4M9 13h6M9 17h4" />
                    </svg>
                </span>
                <h2>Belum ada observasi</h2>
                <p>Catatan observasi {{ $anak['nama_panggilan'] }} akan muncul di sini setelah guru mencatat observasi pertama.</p>
            </div>
        @elseif ($totalObservasi === 0)
            {{-- TC-11-11: filter tidak menghasilkan data --}}
            <div class="riw-empty">
                <span class="riw-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16M7 9h10M9.5 13h5M8 21l4-3 4 3V9H8v12Z" />
                    </svg>
                </span>
                <h2>Belum ada observasi yang sesuai</h2>
                <p>
                    Tidak ada catatan untuk {{ $anak['nama_panggilan'] }} dengan filter yang dipilih.
                    Coba ubah periode atau hapus filter untuk melihat seluruh riwayat.
                </p>
                <a href="{{ $tautanHalaman }}" class="riw-btn tone-violet mt-5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 0 0 6 5.3L4 7m16 10-2 1.7A8 8 0 0 1 4 15" />
                    </svg>
                    Hapus filter
                </a>
            </div>
        @else
            {{-- Daftar observasi --}}
            <div id="daftar-observasi" class="riw-timeline">
                @foreach ($grupObservasi as $tanggal => $items)
                    @php $indeksAwalGrup = $nomorObservasi + 1; @endphp
                    <section class="riw-grup date-group {{ $indeksAwalGrup > $batasAwalTampil ? 'hidden' : '' }}" data-grup="{{ $tanggal }}">
                        <h2 class="riw-tanggal">
                            {{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, j M Y') }}
                        </h2>

                        <div class="flex flex-col gap-3">
                            @foreach ($items as $observasi)
                                @php
                                    $nomorObservasi++;
                                    $nilaiRespons = $observasi['kategori_respons'];
                                    $tanggalPendek = \Illuminate\Support\Carbon::parse($observasi['tanggal'])->locale('id')->translatedFormat('D j M Y');
                                @endphp
                                <article
                                    class="obs-card {{ $gayaRespons[$nilaiRespons] ?? 'resp-abu' }} {{ $nomorObservasi > $batasAwalTampil ? 'hidden' : '' }}"
                                    data-tanggal="{{ $observasi['tanggal'] }}"
                                >
                                    <div class="riw-kartu-kepala">
                                        <span class="riw-kartu-ikon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="5" y="4" width="14" height="17" rx="2.5" />
                                                <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7M8.5 17h4" />
                                            </svg>
                                        </span>
                                        <div class="riw-kartu-identitas">
                                            <h3 class="riw-kartu-judul">{{ $observasi['aktivitas'] }}</h3>
                                            <p class="riw-kartu-sub">{{ $tanggalPendek }}</p>
                                        </div>
                                        <span class="obs-resp">{{ $labelKategori[$nilaiRespons] ?? $nilaiRespons }}</span>
                                    </div>

                                    <div class="riw-chips">
                                        <span class="riw-chip">Aspek: {{ $observasi['aspek'] }}</span>
                                        <span class="riw-chip">Strategi: {{ $observasi['strategi'] }}</span>
                                    </div>

                                    <p class="riw-catatan">{{ $observasi['catatan_pendukung'] }}</p>

                                    {{-- Detail dan Ubah sengaja tidak ada di sini; keduanya bagian dari halaman Observasi. --}}
                                    @if (($observasi['jumlah_perubahan'] ?? 0) > 0)
                                        <p class="riw-perubahan">
                                            Diubah {{ $observasi['jumlah_perubahan'] }}x
                                            @if ($observasi['bisa_lihat_perubahan'] ?? false)
                                                <span class="mx-1">•</span>
                                                <a href="{{ route('guru.observasi.riwayat', $observasi['id']) }}">lihat perubahan</a>
                                            @endif
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- Footer daftar --}}
            <div id="footer-daftar" class="riw-footer">
                <p id="penghitung-bawah" aria-live="polite">
                    Menampilkan {{ $batasAwalTampil }} dari {{ $totalObservasi }} {{ $filterAktif ? 'hasil' : 'catatan' }}
                </p>
                {{-- hidden dan inline-flex dipasang bergantian: jika keduanya ada, inline-flex menang dan tombol tidak pernah tersembunyi. --}}
                <button type="button" id="muat-lebih-banyak" class="riw-btn tone-indigo {{ ($filterAktif || $totalObservasi <= $jumlahAwalTampil) ? 'hidden' : '' }}">
                    Muat lebih banyak
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
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
                    const semuaTampil = jumlahTampil >= total;
                    tombol.classList.toggle('hidden', semuaTampil);
                };

                if (tombol) {
                    tombol.addEventListener('click', () => {
                        jumlahTampil += langkah;
                        render();
                    });
                }

                // Klik kartu distribusi respons untuk menyaring daftar.
                document.querySelectorAll('[data-pilih-respons]').forEach((tombolRespons) => {
                    tombolRespons.addEventListener('click', () => {
                        const pilihan = document.getElementById('filter-respons');
                        pilihan.value = tombolRespons.dataset.pilihRespons;
                        pilihan.form.requestSubmit();
                    });
                });
            });
        </script>
    @endif
</x-layouts.app>
