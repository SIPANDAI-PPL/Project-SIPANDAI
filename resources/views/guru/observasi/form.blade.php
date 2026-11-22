@php
    $observasi = $observasi ?? null;
    $nilaiKelas = old('kelas_id', $observasi?->anak?->kelas_id);
    $nilaiAnak = old('anak_id', $observasi?->anak_id);
    $nilaiRespons = old('kategori_respons', $observasi?->kategori_respons?->value);
    $nilaiTanggal = old('tanggal', $observasi?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d'));

    $deskripsiRespons = [
        'terlibat_aktif' => 'Anak antusias dan melakukan kegiatan dengan mandiri.',
        'terlibat_dengan_pendampingan' => 'Anak ikut serta setelah diarahkan atau didampingi guru.',
        'kurang_tertarik' => 'Anak terlihat kurang bersemangat atau mudah beralih.',
        'belum_terlibat' => 'Anak belum mau atau belum mampu ikut kegiatan.',
    ];

    $warnaRespons = [
        'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'terlibat_dengan_pendampingan' => 'border-blue-200 bg-blue-50 text-blue-800',
        'kurang_tertarik' => 'border-orange-200 bg-orange-50 text-orange-800',
        'belum_terlibat' => 'border-gray-300 bg-gray-50 text-gray-700',
    ];

    // Hanya untuk tampilan: warna & ikon tiap kategori respons
    $tampilanRespons = [
        'terlibat_aktif' => [
            'kelas' => 'resp-hijau',
            'ikon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5l2.47 5 5.53.8-4 3.9.94 5.5L12 16.1l-4.94 2.6.94-5.5-4-3.9 5.53-.8L12 3.5z"/>',
        ],
        'terlibat_dengan_pendampingan' => [
            'kelas' => 'resp-biru',
            'ikon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 00-3.5-3.5h-5A3.5 3.5 0 004 17.5V19M10 10.5a3 3 0 100-6 3 3 0 000 6zM20 19v-1.5a3.5 3.5 0 00-2.5-3.35M15.5 4.6a3 3 0 010 5.8"/>',
        ],
        'kurang_tertarik' => [
            'kelas' => 'resp-oranye',
            'ikon' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M8.5 15.5h7M9 9.75h.01M15 9.75h.01"/>',
        ],
        'belum_terlibat' => [
            'kelas' => 'resp-abu',
            'ikon' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M10 9v6M14 9v6"/>',
        ],
    ];
@endphp

<x-layouts.app title="Tambah Observasi">
    <style>
        .obs {
            --brand: #2E2580;
            --brand-2: #4338CA;
            --text: #111827;
            --text-2: #374151;
            --muted: #6B7280;
            --line: #E5E7EB;
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
        .obs .tone-pink    { --a: #DB2777; --a-soft: #FDF2F8; --a-ring: rgba(219, 39, 119, .16); }

        /* ---------- Header ---------- */
        .obs-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
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

        .obs-hero::before { width: 220px; height: 220px; top: -110px; right: -40px; }
        .obs-hero::after { width: 140px; height: 140px; bottom: -80px; right: 170px; }

        .obs-hero-icon {
            position: relative;
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

        .obs-hero-text { position: relative; min-width: 0; }

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

        /* ---------- Informasi guru ---------- */
        .obs-info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .obs-info-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: #fff;
            border: 1px solid var(--line);
            border-left: 4px solid var(--a);
            border-radius: 16px;
        }

        .obs-info-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            color: var(--a);
            background: var(--a-soft);
            border-radius: 12px;
        }

        .obs-info-icon svg { width: 22px; height: 22px; }

        .obs-info-label {
            margin: 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .obs-info-value {
            margin: 3px 0 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .obs-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            padding: 3px 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: #047857;
            background: #D1FAE5;
            border-radius: 999px;
        }

        .obs-status::before {
            content: "";
            width: 7px;
            height: 7px;
            background: #10B981;
            border-radius: 999px;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .2);
        }

        /* ---------- Alert ---------- */
        .obs-alert {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            font-size: 14px;
            color: #9F1239;
            background: #FFF1F2;
            border: 1px solid #FECDD3;
            border-left: 4px solid #E11D48;
            border-radius: 14px;
        }

        .obs-alert svg { flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px; }
        .obs-alert p { margin: 0; font-weight: 600; }
        .obs-alert ul { margin: 6px 0 0; padding-left: 18px; }
        .obs-alert li + li { margin-top: 2px; }

        /* ---------- Form & kartu bagian ---------- */
        .obs-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .obs-card {
            padding: 20px 24px 22px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        /* Garis warna di atas kartu — hanya dipakai di Respons Anak */
        .obs-card.has-accent { border-top: 4px solid var(--a); }

        .obs-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
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
            line-height: 1.3;
            color: var(--text);
        }

        .obs-card-desc {
            margin: 3px 0 0;
            font-size: 13.5px;
            line-height: 1.45;
            color: var(--muted);
        }

        .obs-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

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

        .obs .obs-control:disabled {
            cursor: not-allowed;
            color: #9CA3AF;
            background-color: #F3F4F6;
        }

        .obs .obs-control.is-invalid {
            border-color: #F87171;
            background-color: #FEF2F2;
        }

        .obs-select { position: relative; }

        .obs .obs-select select {
            padding-right: 42px;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .obs-select svg {
            position: absolute;
            top: 50%;
            right: 14px;
            width: 18px;
            height: 18px;
            color: var(--a);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .obs .obs-control[type="date"] { cursor: pointer; }

        .obs textarea.obs-control {
            height: auto;
            min-height: 112px;
            padding: 12px 14px;
            line-height: 1.5;
            resize: vertical;
        }

        .obs-error {
            margin: 6px 0 0;
            font-size: 12.5px;
            color: #DC2626;
        }

        .obs-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 8px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }

        .obs-hint svg { width: 15px; height: 15px; color: var(--a); }

        /* ---------- Respons anak ---------- */
        .obs .resp-hijau  { --r: #059669; --r-text: #065F46; --r-soft: #ECFDF5; --r-line: #A7F3D0; --r-ring: rgba(5, 150, 105, .16); }
        .obs .resp-biru   { --r: #2563EB; --r-text: #1E40AF; --r-soft: #EFF6FF; --r-line: #BFDBFE; --r-ring: rgba(37, 99, 235, .16); }
        .obs .resp-oranye { --r: #EA580C; --r-text: #9A3412; --r-soft: #FFF7ED; --r-line: #FED7AA; --r-ring: rgba(234, 88, 12, .16); }
        .obs .resp-abu    { --r: #6B7280; --r-text: #374151; --r-soft: #F3F4F6; --r-line: #D1D5DB; --r-ring: rgba(107, 114, 128, .16); }

        .obs-resp-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .obs-resp {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .obs .obs-resp input {
            position: absolute;
            inset: 0;
            z-index: 1;
            width: 100%;
            height: 100%;
            margin: 0;
            opacity: 0;
            cursor: pointer;
        }

        .obs-resp-card {
            display: flex;
            align-items: center;
            gap: 14px;
            height: 100%;
            padding: 14px 16px;
            background: #fff;
            border: 1.5px solid var(--r-line);
            border-radius: 14px;
            transition: background-color .15s, border-color .15s, box-shadow .15s, transform .15s;
        }

        .obs-resp:hover .obs-resp-card {
            background: var(--r-soft);
            transform: translateY(-1px);
        }

        .obs-resp-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            color: var(--r);
            background: var(--r-soft);
            border-radius: 12px;
            transition: background-color .15s, color .15s;
        }

        .obs-resp-icon svg { width: 22px; height: 22px; }

        .obs-resp-text { flex: 1; min-width: 0; }

        .obs-resp-title {
            display: block;
            font-size: 14.5px;
            font-weight: 600;
            line-height: 1.35;
            color: var(--r-text);
        }

        .obs-resp-desc {
            display: block;
            margin-top: 3px;
            font-size: 12.5px;
            line-height: 1.45;
            color: var(--muted);
        }

        .obs-resp-check {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            color: transparent;
            background: #fff;
            border: 2px solid #D1D5DB;
            border-radius: 999px;
            transition: background-color .15s, border-color .15s, color .15s;
        }

        .obs-resp-check svg { width: 13px; height: 13px; }

        .obs .obs-resp input:checked + .obs-resp-card {
            background: var(--r-soft);
            border-color: var(--r);
            box-shadow: 0 0 0 3px var(--r-ring);
        }

        .obs .obs-resp input:checked + .obs-resp-card .obs-resp-icon {
            color: #fff;
            background: var(--r);
        }

        .obs .obs-resp input:checked + .obs-resp-card .obs-resp-check {
            color: #fff;
            background: var(--r);
            border-color: var(--r);
        }

        .obs .obs-resp input:focus-visible + .obs-resp-card {
            outline: 2px solid var(--r);
            outline-offset: 2px;
        }

        /* ---------- Tombol ---------- */
        .obs-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .obs .obs-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 46px;
            padding: 0 22px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            cursor: pointer;
            transition: background .15s, border-color .15s, box-shadow .15s, transform .15s;
        }

        .obs .obs-btn svg { width: 18px; height: 18px; }

        .obs .obs-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 56, 202, .25);
        }

        .obs .obs-btn-outline {
            color: var(--text-2);
            background: #fff;
            border: 1.5px solid #D1D5DB;
        }

        .obs .obs-btn-outline:hover {
            background: var(--soft);
            border-color: #9CA3AF;
        }

        .obs .obs-btn-primary {
            color: #fff;
            background: linear-gradient(120deg, #2E2580, #4338CA);
            border: 0;
            box-shadow: 0 8px 18px -8px rgba(46, 37, 128, .6);
        }

        .obs .obs-btn-primary:hover {
            background: linear-gradient(120deg, #3A3196, #4F46E5);
            transform: translateY(-1px);
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1023px) {
            .obs-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767px) {
            .obs { gap: 12px; }
            .obs-form { gap: 12px; }

            .obs-hero { padding: 18px; border-radius: 16px; }
            .obs-hero-icon { width: 46px; height: 46px; border-radius: 14px; }
            .obs-hero-icon svg { width: 24px; height: 24px; }
            .obs-hero h1 { font-size: 21px; }
            .obs-hero p { font-size: 13.5px; }

            .obs-info,
            .obs-grid,
            .obs-resp-grid { grid-template-columns: 1fr; }

            .obs-card { padding: 16px; border-radius: 16px; }

            .obs-actions { flex-direction: column-reverse; }
            .obs .obs-btn { width: 100%; }
        }
    </style>

    <div class="obs">

        {{-- Judul halaman --}}
        <header class="obs-hero">
            <div class="obs-hero-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="5" y="4" width="14" height="17" rx="2.5" />
                    <path stroke-linecap="round" d="M9 4.5V3.5h6v1M8.5 10h7M8.5 13.5h7M8.5 17h4" />
                </svg>
            </div>

            <div class="obs-hero-text">
                <h1>Formulir Observasi Perkembangan</h1>
                <p>
                    Catat respons anak terhadap kegiatan yang diamati,
                    beserta aspek perkembangan dan strategi yang digunakan.
                </p>
            </div>
        </header>

        {{-- Informasi guru --}}
        <section class="obs-info" aria-label="Informasi guru">
            <div class="obs-info-card tone-indigo">
                <div class="obs-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="8" r="3.5" />
                        <path stroke-linecap="round" d="M5 19.5c.8-3.3 3.6-5 7-5s6.2 1.7 7 5" />
                    </svg>
                </div>
                <div style="min-width: 0;">
                    <p class="obs-info-label">Guru Pengajar</p>
                    <p class="obs-info-value">{{ auth()->user()->nama }}</p>
                </div>
            </div>

            <div class="obs-info-card tone-amber">
                <div class="obs-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="4" y="5.5" width="16" height="14.5" rx="2.5" />
                        <path stroke-linecap="round" d="M4 10h16M8.5 3.5v4M15.5 3.5v4" />
                    </svg>
                </div>
                <div style="min-width: 0;">
                    <p class="obs-info-label">Tahun Ajaran</p>
                    <p class="obs-info-value">{{ $tahunAjaran }}</p>
                </div>
            </div>

            <div class="obs-info-card tone-emerald">
                <div class="obs-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="8.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.2 2.4 2.4 4.6-4.9" />
                    </svg>
                </div>
                <div>
                    <p class="obs-info-label">Status</p>
                    <span class="obs-status">Aktif</span>
                </div>
            </div>
        </section>

        {{-- Pesan kesalahan --}}
        @if ($errors->any())
            <div class="obs-alert" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5" />
                    <path stroke-linecap="round" d="M12 7.5v5.5M12 16.5h.01" />
                </svg>
                <div>
                    <p>Periksa kembali isian Anda:</p>
                    <ul>
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Form pencatatan observasi --}}
        <form class="obs-form" method="POST" action="{{ route('guru.observasi.store') }}">
            @csrf

            {{-- Identitas observasi --}}
            <section class="obs-card tone-blue">
                <div class="obs-card-head">
                    <div class="obs-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3.5" y="5" width="17" height="14" rx="2.5" />
                            <circle cx="9" cy="11" r="2" />
                            <path stroke-linecap="round" d="M6 16c.5-1.5 1.6-2.3 3-2.3s2.5.8 3 2.3M14.5 10h3M14.5 13.5h3" />
                        </svg>
                    </div>
                    <h2 class="obs-card-title">Identitas Observasi</h2>
                </div>

                <div class="obs-grid">
                    <div>
                        <label for="kelas_id" class="obs-label">Pilih Kelas</label>
                        <div class="obs-select">
                            <select id="kelas_id" name="kelas_id" required
                                class="obs-control @error('kelas_id') is-invalid @enderror">
                                <option value="">Pilih kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}"
                                        @selected((string) $nilaiKelas === (string) $k->id)>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>

                        @error('kelas_id')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="anak_id" class="obs-label">Pilih Anak</label>
                        <div class="obs-select">
                            <select id="anak_id" name="anak_id" required
                                class="obs-control @error('anak_id') is-invalid @enderror">
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>

                        @error('anak_id')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal" class="obs-label">Tanggal Observasi</label>
                        <input type="date" id="tanggal" name="tanggal"
                            value="{{ $nilaiTanggal }}"
                            max="{{ now()->format('Y-m-d') }}"
                            required
                            class="obs-control @error('tanggal') is-invalid @enderror">

                        @error('tanggal')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Kegiatan dan strategi --}}
            <section class="obs-card tone-violet">
                <div class="obs-card-head">
                    <div class="obs-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="4" width="7" height="7" rx="1.8" />
                            <rect x="13" y="4" width="7" height="7" rx="1.8" />
                            <rect x="4" y="13" width="7" height="7" rx="1.8" />
                            <circle cx="16.5" cy="16.5" r="3.5" />
                        </svg>
                    </div>
                    <h2 class="obs-card-title">Kegiatan dan Strategi</h2>
                </div>

                <div class="obs-grid">
                    <div>
                        <label for="aktivitas_id" class="obs-label">Aktivitas Observasi</label>
                        <div class="obs-select">
                            <select id="aktivitas_id" name="aktivitas_id" required
                                class="obs-control @error('aktivitas_id') is-invalid @enderror">
                                <option value="">Pilih aktivitas</option>
                                @foreach ($aktivitasList as $a)
                                    <option value="{{ $a->id }}"
                                        @selected((string) old('aktivitas_id') === (string) $a->id)>
                                        {{ $a->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>

                        @error('aktivitas_id')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="aspek_id" class="obs-label">Aspek Perkembangan</label>
                        <div class="obs-select">
                            <select id="aspek_id" name="aspek_id" required
                                class="obs-control @error('aspek_id') is-invalid @enderror">
                                <option value="">Pilih aspek</option>
                                @foreach ($aspekList as $a)
                                    <option value="{{ $a->id }}"
                                        @selected((string) old('aspek_id') === (string) $a->id)>
                                        {{ $a->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>

                        @error('aspek_id')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="strategi_id" class="obs-label">Strategi Pembelajaran</label>
                        <div class="obs-select">
                            <select id="strategi_id" name="strategi_id" required
                                class="obs-control @error('strategi_id') is-invalid @enderror">
                                <option value="">Pilih strategi</option>
                                @foreach ($strategiList as $s)
                                    <option value="{{ $s->id }}"
                                        @selected((string) old('strategi_id') === (string) $s->id)>
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.5 7.75 4.5 4.5 4.5-4.5" />
                            </svg>
                        </div>

                        @error('strategi_id')
                            <p class="obs-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Respons anak --}}
            <section class="obs-card tone-pink has-accent">
                <div class="obs-card-head">
                    <div class="obs-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="8.5" />
                            <path stroke-linecap="round" d="M8.5 14c.8 1.3 2 2 3.5 2s2.7-.7 3.5-2M9 9.75h.01M15 9.75h.01" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="obs-card-title">Respons Anak</h2>
                        <p class="obs-card-desc">
                            Pilih satu respons yang paling menggambarkan keterlibatan anak pada kegiatan ini.
                        </p>
                    </div>
                </div>

                <div class="obs-resp-grid" role="radiogroup" aria-label="Respons anak">
                    @foreach ($responsList as $r)
                        <label class="obs-resp {{ $tampilanRespons[$r->value]['kelas'] ?? 'resp-abu' }}">
                            <input type="radio" name="kategori_respons"
                                value="{{ $r->value }}"
                                @checked($nilaiRespons === $r->value)
                                required>

                            <span class="obs-resp-card">
                                <span class="obs-resp-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        {!! $tampilanRespons[$r->value]['ikon'] ?? '<circle cx="12" cy="12" r="8.5"/>' !!}
                                    </svg>
                                </span>

                                <span class="obs-resp-text">
                                    <span class="obs-resp-title">{{ $r->label() }}</span>
                                    <span class="obs-resp-desc">{{ $deskripsiRespons[$r->value] ?? '' }}</span>
                                </span>

                                <span class="obs-resp-check" aria-hidden="true">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 8.3 2.6 2.6L12 5.5" />
                                    </svg>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('kategori_respons')
                    <p class="obs-error">{{ $message }}</p>
                @enderror
            </section>

            {{-- Catatan pendukung --}}
            <section class="obs-card tone-amber">
                <div class="obs-card-head">
                    <div class="obs-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5.5l4 4M4 20l1-4.5L15.8 4.7a1.8 1.8 0 012.5 0l1 1a1.8 1.8 0 010 2.5L8.5 19 4 20z" />
                        </svg>
                    </div>
                    <h2 class="obs-card-title">Catatan Pendukung</h2>
                </div>

                <label for="catatan_pendukung" class="obs-label">
                    Deskripsi Pengamatan (Opsional)
                </label>

                <textarea id="catatan_pendukung" name="catatan_pendukung"
                    rows="4" maxlength="2000"
                    placeholder="Tuliskan hasil pengamatan terhadap anak..."
                    class="obs-control @error('catatan_pendukung') is-invalid @enderror">{{ old('catatan_pendukung') }}</textarea>

                @error('catatan_pendukung')
                    <p class="obs-error">{{ $message }}</p>
                @enderror

                <p class="obs-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="8.5" />
                        <path stroke-linecap="round" d="M12 11v5M12 8h.01" />
                    </svg>
                    Maksimal 2.000 karakter.
                </p>
            </section>

            {{-- Tombol aksi --}}
            <div class="obs-actions">
                <a href="{{ route('guru.observasi.index') }}" class="obs-btn obs-btn-outline">
                    Batal
                </a>

                <button type="submit" class="obs-btn obs-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12.5 4.2 4.2L19 7" />
                    </svg>
                    Simpan Observasi
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar anak menyesuaikan kelas yang dipilih --}}
    <script>
        (() => {
            const anak = @json($anakList);
            const kelas = document.getElementById('kelas_id');
            const pilih = document.getElementById('anak_id');
            const awal = @json((string) $nilaiAnak);

            const render = (terpilih) => {
                pilih.length = 0;

                pilih.add(new Option(
                    kelas.value ? 'Pilih anak' : 'Pilih kelas terlebih dahulu',
                    ''
                ));

                anak
                    .filter(a => String(a.kelas_id) === kelas.value)
                    .forEach(a => {
                        pilih.add(new Option(
                            a.nama,
                            a.id,
                            false,
                            String(a.id) === String(terpilih)
                        ));
                    });

                pilih.disabled = !kelas.value;
            };

            kelas.addEventListener('change', () => render(''));

            render(awal);
        })();
    </script>
</x-layouts.app>