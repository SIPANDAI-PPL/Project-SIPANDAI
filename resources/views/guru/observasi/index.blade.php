@php
    // Hanya untuk tampilan: warna tiap kategori respons (sama seperti formulir observasi)
    $toneRespons = [
        'terlibat_aktif' => 'resp-hijau',
        'terlibat_dengan_pendampingan' => 'resp-biru',
        'kurang_tertarik' => 'resp-oranye',
        'belum_terlibat' => 'resp-abu',
    ];
@endphp

<x-layouts.app title="Data Observasi">
    <style>
        .obs {
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

        .obs *,
        .obs *::before,
        .obs *::after { box-sizing: border-box; }

        .obs svg { display: block; }

        /* ---------- Warna (tone) ---------- */
        .obs .tone-indigo  { --a: #4F46E5; --a-soft: #EEF2FF; --a-ring: rgba(79, 70, 229, .16); }
        .obs .tone-amber   { --a: #D97706; --a-soft: #FFF7E6; --a-ring: rgba(217, 119, 6, .16); }
        .obs .tone-emerald { --a: #059669; --a-soft: #ECFDF5; --a-ring: rgba(5, 150, 105, .16); }
        .obs .tone-blue    { --a: #2563EB; --a-soft: #EFF6FF; --a-ring: rgba(37, 99, 235, .16); }
        .obs .tone-violet  { --a: #7C3AED; --a-soft: #F5F3FF; --a-ring: rgba(124, 58, 237, .16); }

        .obs .resp-hijau  { --r: #059669; --r-text: #065F46; --r-soft: #ECFDF5; }
        .obs .resp-biru   { --r: #2563EB; --r-text: #1E40AF; --r-soft: #EFF6FF; }
        .obs .resp-oranye { --r: #EA580C; --r-text: #9A3412; --r-soft: #FFF7ED; }
        .obs .resp-abu    { --r: #6B7280; --r-text: #374151; --r-soft: #F3F4F6; }

        /* ---------- Header ---------- */
        .obs-hero {
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

        .obs-hero::before,
        .obs-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            pointer-events: none;
        }

        .obs-hero::before { width: 220px; height: 220px; top: -110px; right: 120px; }
        .obs-hero::after { width: 140px; height: 140px; bottom: -80px; right: 340px; }

        .obs-hero-main {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .obs-hero-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 16px;
        }

        .obs-hero-icon svg { width: 28px; height: 28px; }

        .obs-hero h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.01em;
            color: #fff;
        }

        .obs-hero p {
            margin: 4px 0 0;
            font-size: 14.5px;
            line-height: 1.5;
            color: rgba(255, 255, 255, .85);
        }

        .obs .obs-hero-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 46px;
            padding: 0 20px;
            font-size: 15px;
            font-weight: 600;
            color: var(--brand);
            text-decoration: none;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px -6px rgba(17, 12, 70, .45);
            transition: transform .15s, box-shadow .15s, background-color .15s;
        }

        .obs .obs-hero-btn:hover {
            background: #F5F3FF;
            transform: translateY(-1px);
        }

        .obs .obs-hero-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, .45);
        }

        .obs-hero-btn svg { width: 18px; height: 18px; }

        /* ---------- Notifikasi ---------- */
        .obs-alert {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            font-size: 14px;
            border: 1px solid;
            border-left-width: 4px;
            border-radius: 14px;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .obs-alert.is-hidden {
            opacity: 0;
            transform: translateY(-6px);
        }

        .obs-alert svg { flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px; }
        .obs-alert p { margin: 0; }
        .obs-alert .obs-alert-title { font-weight: 600; }
        .obs-alert .obs-alert-title + p { margin-top: 3px; }

        .obs-alert.is-success {
            color: #065F46;
            background: #ECFDF5;
            border-color: #A7F3D0;
            border-left-color: #059669;
        }

        .obs-alert.is-danger {
            color: #9F1239;
            background: #FFF1F2;
            border-color: #FECDD3;
            border-left-color: #E11D48;
        }

        /* ---------- Ringkasan ---------- */
        .obs-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .obs-stat {
            padding: 18px 20px;
            background: #fff;
            border: 1px solid var(--line);
            border-left: 4px solid var(--a);
            border-radius: 16px;
        }

        .obs-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .obs-stat-label {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-2);
        }

        .obs-stat-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            color: var(--a);
            background: var(--a-soft);
            border-radius: 12px;
        }

        .obs-stat-icon svg { width: 21px; height: 21px; }

        .obs-stat-value {
            margin: 6px 0 0;
            font-size: 32px;
            font-weight: 700;
            line-height: 1.1;
            color: var(--a);
        }

        .obs-stat-desc {
            margin: 6px 0 0;
            font-size: 12.5px;
            color: var(--muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ---------- Filter ---------- */
        .obs-card {
            padding: 20px 24px 22px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .obs-filter {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .obs-filter-actions { grid-column: 1 / -1; }

        .obs-label {
            display: block;
            margin: 0 0 7px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-2);
        }

        .obs .obs-control {
            display: block;
            width: 100%;
            height: 46px;
            margin: 0;
            padding: 0 14px;
            font-family: inherit;
            font-size: 15px;
            line-height: 1.4;
            color: var(--text);
            background-color: var(--soft);
            background-image: none;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            box-shadow: none;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }

        .obs .obs-control::placeholder { color: #9CA3AF; }
        .obs .obs-control:hover { border-color: #D1D5DB; }

        .obs .obs-control:focus {
            background-color: #fff;
            border-color: var(--a);
            box-shadow: 0 0 0 4px var(--a-ring);
        }

        .obs-field-icon { position: relative; }

        .obs .obs-field-icon .obs-control { padding-left: 42px; }

        .obs-field-icon > svg {
            position: absolute;
            top: 50%;
            left: 14px;
            width: 18px;
            height: 18px;
            color: var(--a);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .obs-select { position: relative; }

        .obs .obs-select select {
            padding-right: 42px;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .obs-select > svg {
            position: absolute;
            top: 50%;
            right: 14px;
            width: 18px;
            height: 18px;
            color: var(--a);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .obs .obs-btn {
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
            border-radius: 10px;
            cursor: pointer;
            transition: background-color .15s, border-color .15s, box-shadow .15s;
        }

        .obs .obs-btn svg { width: 17px; height: 17px; }

        .obs .obs-btn-soft {
            color: var(--a);
            background: var(--a-soft);
            border: 1.5px solid transparent;
        }

        .obs .obs-btn-soft:hover { border-color: var(--a); }

        .obs .obs-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px var(--a-ring);
        }

        .obs-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: -4px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .obs-meta svg { flex-shrink: 0; width: 15px; height: 15px; color: #7C3AED; }

        /* ---------- Tabel ---------- */
        .obs-table-card {
            overflow: hidden;
            padding: 0;
        }

        .obs-table-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--line-2);
        }

        .obs-card-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            color: var(--a);
            background: var(--a-soft);
            border-radius: 12px;
        }

        .obs-card-icon svg { width: 21px; height: 21px; }

        .obs-card-title {
            margin: 0;
            font-size: 17px;
            font-weight: 600;
            color: var(--text);
        }

        .obs-count {
            padding: 3px 10px;
            font-size: 12.5px;
            font-weight: 600;
            color: #4338CA;
            background: #EEF2FF;
            border-radius: 999px;
        }

        .obs-table-wrap { overflow-x: auto; }

        .obs-table {
            width: 100%;
            min-width: 880px;
            font-size: 14px;
            text-align: left;
            border-collapse: collapse;
        }

        .obs-table th {
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
            color: var(--muted);
            background: var(--soft);
            border-bottom: 1px solid var(--line-2);
        }

        .obs-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--line-2);
            transition: background-color .15s;
        }

        .obs-table th:first-child,
        .obs-table td:first-child { padding-left: 24px; }

        .obs-table th:last-child,
        .obs-table td:last-child { padding-right: 24px; }

        .obs-table tbody tr:last-child td { border-bottom: 0; }
        .obs-table tbody tr:hover td { background: #FAFAFF; }

        .obs-anak {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .obs-avatar {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 999px;
        }

        .obs-avatar.av-0 { color: #4338CA; background: #E0E7FF; }
        .obs-avatar.av-1 { color: #047857; background: #D1FAE5; }
        .obs-avatar.av-2 { color: #B45309; background: #FEF3C7; }
        .obs-avatar.av-3 { color: #BE185D; background: #FCE7F3; }
        .obs-avatar.av-4 { color: #1D4ED8; background: #DBEAFE; }

        .obs-strong {
            margin: 0;
            font-weight: 600;
            color: var(--text);
        }

        .obs-sub {
            margin: 2px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .obs-code {
            display: inline-block;
            margin-top: 3px;
            padding: 1px 7px;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--muted);
            background: #F3F4F6;
            border-radius: 6px;
        }

        .obs-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            color: #6D28D9;
            background: #F5F3FF;
            border-radius: 8px;
        }

        .obs-date {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            color: var(--text-2);
        }

        .obs-date svg { width: 16px; height: 16px; color: #D97706; }

        .obs-resp {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
            color: var(--r-text);
            background: var(--r-soft);
            border-radius: 999px;
        }

        .obs-resp::before {
            content: "";
            width: 7px;
            height: 7px;
            background: var(--r);
            border-radius: 999px;
        }

        .obs-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }

        .obs .obs-act {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            color: var(--a);
            background: var(--a-soft);
            border: 1px solid transparent;
            border-radius: 8px;
            transition: border-color .15s, background-color .15s;
        }

        .obs .obs-act:hover { border-color: var(--a); }

        .obs .obs-act:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px var(--a-ring);
        }

        .obs .obs-act.is-muted { --a: #4B5563; --a-soft: #F3F4F6; --a-ring: rgba(75, 85, 99, .18); }

        .obs-act svg { width: 15px; height: 15px; }

        .obs-empty {
            padding: 48px 24px;
            text-align: center;
        }

        .obs-empty-icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            color: #4F46E5;
            background: #EEF2FF;
            border-radius: 16px;
        }

        .obs-empty-icon svg { width: 28px; height: 28px; }

        .obs-empty p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
        }

        /* ---------- Pagination ---------- */
        .obs-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 24px;
            border-top: 1px solid var(--line-2);
        }

        .obs-pagination > p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }

        .obs-pages {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        .obs .obs-page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-2);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            transition: background-color .15s, border-color .15s, color .15s;
        }

        .obs .obs-page svg { width: 15px; height: 15px; }

        .obs a.obs-page:hover {
            color: #4338CA;
            background: #EEF2FF;
            border-color: #C7D2FE;
        }

        .obs .obs-page.is-current {
            color: #fff;
            background: linear-gradient(120deg, #2E2580, #4338CA);
            border-color: transparent;
            box-shadow: 0 6px 14px -6px rgba(46, 37, 128, .55);
        }

        .obs .obs-page.is-disabled {
            color: #D1D5DB;
            background: var(--soft);
            border-color: var(--line-2);
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1023px) {
            .obs-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .obs-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767px) {
            .obs { gap: 12px; }

            .obs-hero { padding: 18px; border-radius: 16px; }
            .obs-hero-icon { width: 46px; height: 46px; border-radius: 14px; }
            .obs-hero-icon svg { width: 24px; height: 24px; }
            .obs-hero h1 { font-size: 21px; }
            .obs-hero p { font-size: 13.5px; }
            .obs .obs-hero-btn { width: 100%; }

            .obs-stats,
            .obs-filter { grid-template-columns: 1fr; }

            .obs-card { padding: 16px; border-radius: 16px; }
            .obs-table-card { padding: 0; }
            .obs-table-head { padding: 14px 16px; }
            .obs .obs-btn { width: 100%; }

            .obs-pagination { flex-direction: column; align-items: stretch; padding: 14px 16px; }
            .obs-pages { justify-content: center; }
        }
    </style>

    <div class="obs">

        {{-- Notifikasi berhasil (pop-up, hilang otomatis setelah 5 detik) --}}
        @if (session('notifikasi'))
            @php $notif = session('notifikasi'); @endphp
            <x-notifikasi-toast
                :judul="$notif['judul']"
                :pesan="$notif['pesan'] ?? null" />
        @endif

        {{-- Judul halaman --}}
        <header class="obs-hero">
            <div class="obs-hero-main">
                <div class="obs-hero-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="5" y="4" width="14" height="17" rx="2.5" />
                        <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7M8.5 17h4" />
                    </svg>
                </div>

                <div>
                    <h1>Data Observasi Perkembangan</h1>
                    <p>Lihat seluruh hasil pengamatan perkembangan anak.</p>
                </div>
            </div>

            <a href="{{ route('guru.observasi.create') }}" class="obs-hero-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Tambah Observasi
            </a>
        </header>

        {{-- Notifikasi gagal --}}
        @if (session('gagal'))
            <div class="obs-alert is-danger" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5" />
                    <path stroke-linecap="round" d="M12 7.5v5.5M12 16.5h.01" />
                </svg>
                <p>{{ session('gagal') }}</p>
            </div>
        @endif

        {{-- Ringkasan --}}
        <section class="obs-stats" aria-label="Ringkasan observasi">
            <div class="obs-stat tone-indigo">
                <div class="obs-stat-top">
                    <p class="obs-stat-label">Total Observasi</p>
                    <div class="obs-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="5" y="4" width="14" height="17" rx="2.5" />
                            <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7M8.5 17h4" />
                        </svg>
                    </div>
                </div>
                <p class="obs-stat-value">{{ $total }}</p>
                <p class="obs-stat-desc">Seluruh data tersimpan</p>
            </div>

            <div class="obs-stat tone-emerald">
                <div class="obs-stat-top">
                    <p class="obs-stat-label">Anak Terobservasi</p>
                    <div class="obs-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 00-3.5-3.5h-5A3.5 3.5 0 004 17.5V19M10 10.5a3 3 0 100-6 3 3 0 000 6zM20 19v-1.5a3.5 3.5 0 00-2.5-3.35M15.5 4.6a3 3 0 010 5.8" />
                        </svg>
                    </div>
                </div>
                <p class="obs-stat-value">{{ $anakUnik }}</p>
                <p class="obs-stat-desc">
                    {{ $kelasNamaStr !== '' ? 'Dari ' . $kelasNamaStr : 'Belum ada data' }}
                </p>
            </div>

            <div class="obs-stat tone-amber">
                <div class="obs-stat-top">
                    <p class="obs-stat-label">Kelas Terobservasi</p>
                    <div class="obs-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9.5 12 4.5l8.5 5M5.5 10.5V19h13v-8.5M10 19v-4.5h4V19" />
                        </svg>
                    </div>
                </div>
                <p class="obs-stat-value">{{ $kelasUnik }}</p>
                <p class="obs-stat-desc">Tahun ajaran {{ $tahunAjaran }}</p>
            </div>
        </section>

        {{-- Filter --}}
        <form method="GET" action="{{ route('guru.observasi.index') }}"
            class="obs-card obs-filter tone-violet" role="search">

            <div>
                <label for="q" class="obs-label">Cari Observasi</label>
                <div class="obs-field-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $cari }}"
                        placeholder="Nama anak atau aktivitas..."
                        class="obs-control">
                </div>
            </div>

            <div>
                <label for="kelas" class="obs-label">Kelas</label>
                <div class="obs-select">
                    <select id="kelas" name="kelas" onchange="this.form.requestSubmit()" class="obs-control">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $id => $nama)
                            <option value="{{ $id }}" @selected((int) $kelasId === (int) $id)>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                    </svg>
                </div>
            </div>

            <div>
                <label for="tahun" class="obs-label">Tahun Ajaran</label>
                <div class="obs-select">
                    <select id="tahun" name="tahun" onchange="this.form.requestSubmit()" class="obs-control">
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}" @selected($tahunAjaran === $t)>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                    </svg>
                </div>
            </div>

            <div>
                <label for="urut" class="obs-label">Urutkan</label>
                <div class="obs-select">
                    <select id="urut" name="urut" onchange="this.form.requestSubmit()" class="obs-control">
                        @foreach ($urutList as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($urut === $nilai)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                    </svg>
                </div>
            </div>

            <div class="obs-filter-actions">
                <button type="submit" class="obs-btn obs-btn-soft">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    Terapkan Pencarian
                </button>
            </div>
        </form>

        <p class="obs-meta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16l-6 7v5l-4 2v-7l-6-7z" />
            </svg>
            <span>
                Menampilkan {{ $adaFilter ? 'hasil penyaringan' : 'seluruh observasi' }}
                &bull; Tahun ajaran {{ $tahunAjaran }}
            </span>
        </p>

        {{-- Tabel Observasi --}}
        <section class="obs-card obs-table-card" aria-labelledby="judul-tabel">

            <div class="obs-table-head tone-indigo">
                <div class="obs-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" />
                        <path stroke-linecap="round" d="M3.5 9.5h17M9.5 9.5v10" />
                    </svg>
                </div>
                <h2 id="judul-tabel" class="obs-card-title">Seluruh Observasi</h2>
                <span class="obs-count">{{ $observasi->total() }} data</span>
            </div>

            <div class="obs-table-wrap">
                <table class="obs-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama Anak</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aktivitas Observasi</th>
                            <th scope="col">Respons Anak</th>
                            <th scope="col" style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($observasi as $o)
                            @php
                                $kode = sprintf('OBS-%03d', $o->id);
                                $namaKelas = $kelasList->get($o->anak->kelas_id, '-');
                                $tanggal = $o->tanggal->locale('id')->isoFormat('D MMM YYYY');

                                $warnaRespons = [
                                    'terlibat_aktif' => 'bg-emerald-50 text-emerald-800',
                                    'terlibat_dengan_pendampingan' => 'bg-blue-50 text-blue-800',
                                    'kurang_tertarik' => 'bg-orange-50 text-orange-800',
                                    'belum_terlibat' => 'bg-gray-100 text-gray-700',
                                ];

                                // Inisial nama anak untuk avatar (hanya tampilan)
                                $kataNama = preg_split('/\s+/u', trim((string) $o->anak->nama));
                                $inisial = mb_strtoupper(mb_substr($kataNama[0] ?? '', 0, 1) . mb_substr($kataNama[1] ?? '', 0, 1));
                            @endphp

                            <tr>
                                <td>
                                    <div class="obs-anak">
                                        <span class="obs-avatar av-{{ $o->id % 5 }}" aria-hidden="true">{{ $inisial }}</span>
                                        <div>
                                            <p class="obs-strong">{{ $o->anak->nama }}</p>
                                            <span class="obs-code">{{ $kode }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="obs-chip">{{ $namaKelas }}</span>
                                </td>

                                <td>
                                    <span class="obs-date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <rect x="4" y="5.5" width="16" height="14.5" rx="2.5" />
                                            <path stroke-linecap="round" d="M4 10h16M8.5 3.5v4M15.5 3.5v4" />
                                        </svg>
                                        {{ $tanggal }}
                                    </span>
                                </td>

                                <td>
                                    <p class="obs-strong">{{ $o->aktivitas->nama }}</p>
                                    <p class="obs-sub">{{ $o->aspek->nama }}</p>
                                </td>

                                <td>
                                    <span class="obs-resp {{ $toneRespons[$o->kategori_respons->value] ?? 'resp-abu' }}">
                                        {{ $o->kategori_respons->label() }}
                                    </span>
                                </td>

                                <td>
                                    <div class="obs-actions">
                                        <a href="{{ route('guru.observasi.show', $o) }}" class="obs-act tone-indigo">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
                                                <circle cx="12" cy="12" r="2.75" />
                                            </svg>
                                            Detail
                                        </a>
                                        <a href="{{ route('guru.observasi.edit', $o) }}" class="obs-act tone-amber">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5.5l4 4M4 20l1-4.5L15.8 4.7a1.8 1.8 0 012.5 0l1 1a1.8 1.8 0 010 2.5L8.5 19 4 20z" />
                                            </svg>
                                            Ubah
                                        </a>
                                        <a href="{{ route('guru.observasi.riwayat', $o) }}" class="obs-act is-muted">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 102.3-5.6M4 4.5v3.5h3.5M12 8v4.25l2.75 1.75" />
                                            </svg>
                                            Riwayat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="obs-empty">
                                    <div class="obs-empty-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="5" y="4" width="14" height="17" rx="2.5" />
                                            <path stroke-linecap="round" d="M9 4.5V3.5h6v1M9.5 12h5M9.5 15.5h3" />
                                        </svg>
                                    </div>
                                    <p>
                                        {{ $adaFilter
                                            ? 'Tidak ada observasi yang cocok dengan pencarian Anda.'
                                            : 'Belum ada data observasi pada tahun ajaran ini.' }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="obs-pagination">
                <p>
                    @if ($observasi->total() > 0)
                        Menampilkan {{ $observasi->firstItem() }}–{{ $observasi->lastItem() }}
                        dari {{ $observasi->total() }} observasi
                    @else
                        Tidak ada data untuk ditampilkan
                    @endif
                </p>

                <nav class="obs-pages" aria-label="Navigasi halaman">
                    @if ($observasi->onFirstPage())
                        <span class="obs-page is-disabled">Sebelumnya</span>
                    @else
                        <a href="{{ $observasi->previousPageUrl() }}" class="obs-page" rel="prev">Sebelumnya</a>
                    @endif

                    @php
                        $sekarang = $observasi->currentPage();
                        $terakhir = $observasi->lastPage();
                    @endphp

                    @for ($p = max(1, $sekarang - 2); $p <= min($terakhir, $sekarang + 2); $p++)
                        @if ($p === $sekarang)
                            <span class="obs-page is-current" aria-current="page">{{ $p }}</span>
                        @else
                            <a href="{{ $observasi->url($p) }}" class="obs-page">{{ $p }}</a>
                        @endif
                    @endfor

                    @if ($observasi->hasMorePages())
                        <a href="{{ $observasi->nextPageUrl() }}" class="obs-page" rel="next">Berikutnya</a>
                    @else
                        <span class="obs-page is-disabled">Berikutnya</span>
                    @endif
                </nav>
            </div>
        </section>
    </div>
    <script>
        const notifikasiBerhasil = document.querySelector('.obs-alert.is-success');

        if (notifikasiBerhasil) {
            setTimeout(() => {
                notifikasiBerhasil.classList.add('is-hidden');

                setTimeout(() => {
                    notifikasiBerhasil.remove();
                }, 300);
            }, 5000);
        }
    </script>
</x-layouts.app>