@php
    /*
     |--------------------------------------------------------------------------
     | DATA CONTOH
     |--------------------------------------------------------------------------
     | Ganti variabel di bawah dengan data dari controller/database.
     | Hapus key 'default' pada $indikator jika ingin pilihan awal kosong.
     */
    $guru = auth()->user()->nama ?? 'Mia Stacia';
    $tahunAjaran  = '2024/2025';
    $semester     = 'Ganjil';
    $statusAjaran = 'Aktif';

    $kelasList = [
        ['id' => 1, 'nama' => 'Kelas A'],
        ['id' => 2, 'nama' => 'Kelas B'],
    ];

    $anakList = [
        ['id' => 1, 'nama' => 'Dedek Piplup',  'kelas_id' => 1],
        ['id' => 2, 'nama' => 'Kakak Pikachu', 'kelas_id' => 1],
        ['id' => 3, 'nama' => 'Adik Eevee',    'kelas_id' => 2],
    ];

    $kegiatanList = [
        'Bermain Bebas di Sentra Balok',
        'Sentra Persiapan',
        'Sentra Seni dan Kreativitas',
        'Sentra Imtaq',
        'Circle Time',
    ];

    $kategori = [
        'BSB' => 'Berkembang Sangat Baik',
        'BSH' => 'Berkembang Sesuai Harapan',
        'MB'  => 'Mulai Berkembang',
        'BB'  => 'Belum Berkembang',
    ];

    $indikator = [
        ['id' => 1, 'kode' => 'NAM', 'tone' => 'ind', 'judul' => 'Mengenal nilai agama dan moral',          'deskripsi' => 'Anak berdoa sebelum dan sesudah kegiatan',                         'default' => 'BSB'],
        ['id' => 2, 'kode' => 'FM',  'tone' => 'grn', 'judul' => 'Motorik kasar — keseimbangan tubuh',       'deskripsi' => 'Anak berjalan di atas papan titian tanpa terjatuh',                'default' => 'BSH'],
        ['id' => 3, 'kode' => 'FM',  'tone' => 'grn', 'judul' => 'Motorik halus — koordinasi tangan-mata',   'deskripsi' => 'Anak menyusun balok dengan rapi dan presisi',                      'default' => 'MB'],
        ['id' => 4, 'kode' => 'KOG', 'tone' => 'blu', 'judul' => 'Kemampuan berpikir simbolik',              'deskripsi' => 'Anak mengenal konsep angka 1–10 dan bentuk geometri',              'default' => 'BSH'],
        ['id' => 5, 'kode' => 'BHS', 'tone' => 'org', 'judul' => 'Kemampuan berbicara dan bercerita',        'deskripsi' => 'Anak mampu menceritakan kembali pengalaman dengan kalimat lengkap', 'default' => 'BB'],
        ['id' => 6, 'kode' => 'SE',  'tone' => 'ind', 'judul' => 'Sosial-emosional — kerja sama',            'deskripsi' => 'Anak mau berbagi dan bekerja sama dengan teman',                   'default' => 'BSB'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formulir Observasi Perkembangan · SIPANDAI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ind: #2a2080;
            --ink: #161a2b;
            --mut: #6b7280;
            --line: #e3e6ee;
            --bg: #f6f7fb;
            --fld: #f5f6fa;
            --bsb: #0f5c55;
            --bsh: #1e4a7a;
            --mb: #9a4a0c;
            --bb: #6b7488;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px; line-height: 1.45; color: var(--ink); background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }
        .bsb { --c: var(--bsb); } .bsh { --c: var(--bsh); } .mb { --c: var(--mb); } .bb { --c: var(--bb); }

        .wrap { max-width: 1200px; margin: 0 auto; padding: 32px 20px 48px; display: flex; flex-direction: column; gap: 16px; }
        form { display: flex; flex-direction: column; gap: 16px; }
        h1 { font-size: clamp(24px, 4.2vw, 32px); font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
        .sub { color: var(--mut); font-size: 14px; margin-top: 6px; }

        /* Info strip */
        .info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .info > div { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 10px 16px; }
        .info small { display: block; font-size: 11px; color: var(--mut); }
        .info b { display: block; font-size: 15px; font-weight: 600; margin-top: 2px; }
        .info .ok { color: var(--bsb); }

        /* Alerts */
        .alert { border-radius: 12px; padding: 12px 16px; font-size: 13px; }
        .alert.err { background: #fdecec; border: 1px solid #f5c2c2; color: #8a1c1c; }
        .alert.err ul { margin: 4px 0 0 18px; padding: 0; }
        .alert.ok { background: #e6f4f1; border: 1px solid #bfe0d9; color: var(--bsb); }

        /* Card */
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; }
        .card h2 { font-size: 16px; font-weight: 600; }
        .head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .head h2 { margin: 0; }
        .card > h2 { margin-bottom: 14px; }
        .cnt { font-size: 11px; color: var(--mut); background: var(--fld); border-radius: 999px; padding: 3px 10px; white-space: nowrap; }

        /* Fields */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .mt { margin-top: 14px; }
        .f label { display: block; font-size: 12px; font-weight: 500; margin-bottom: 6px; }
        .ctl {
            width: 100%; height: 42px; padding: 0 14px; font: inherit; font-size: 14px; color: var(--ink);
            background-color: var(--fld); border: 1px solid var(--line); border-radius: 10px;
            transition: border-color .15s, box-shadow .15s;
        }
        select.ctl {
            -webkit-appearance: none; appearance: none; padding-right: 42px; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23161a2b' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m3 6 5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
        }
        input[type=date].ctl { min-width: 0; }
        input[type=date]::-webkit-calendar-picker-indicator { cursor: pointer; }
        textarea.ctl { height: auto; min-height: 96px; padding: 10px 14px; resize: vertical; line-height: 1.45; }
        .ctl::placeholder { color: #7b8190; }
        .ctl:focus { outline: none; border-color: var(--ind); box-shadow: 0 0 0 3px rgba(42, 32, 128, .15); background-color: #fff; }

        /* Legend */
        .legend { display: flex; gap: 12px; align-items: flex-start; padding: 10px 16px; color: var(--ind);
                  background: #eceefb; border: 1px solid #dfe2f5; border-radius: 12px; }
        .legend svg { flex: none; margin-top: 2px; }
        .legend h3 { font-size: 12.5px; font-weight: 600; }
        .legend ul { list-style: none; padding: 0; margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px 22px; font-size: 11.5px; color: #4a5068; }
        .legend li { display: flex; align-items: center; gap: 6px; }
        .legend b { color: var(--ink); font-weight: 600; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--c); flex: none; }

        /* Indicator table */
        .thead, .row { display: grid; grid-template-columns: minmax(0, 2.4fr) repeat(4, minmax(0, 1fr)); align-items: center; }
        .thead { background: var(--fld); border-radius: 8px; padding: 9px 12px; font-size: 11px; font-weight: 600; color: var(--mut); letter-spacing: .03em; text-transform: uppercase; }
        .thead span + span { text-align: center; color: var(--c); }
        .rows { display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }
        .row { padding: 8px 12px; background: var(--fld); border: 1px solid transparent; border-radius: 10px; }
        .row:nth-child(even) { background: #fff; border-color: var(--line); }
        .ttl { display: flex; align-items: center; gap: 8px; font-weight: 500; font-size: 14px; }
        .desc { font-size: 11.5px; color: var(--mut); margin-top: 2px; }
        .tag { font-size: 10px; font-weight: 600; letter-spacing: .03em; padding: 2px 6px; border-radius: 5px; flex: none; }
        .tag.ind { background: #e8eaf8; color: #3a3a8c; }
        .tag.grn { background: #dff3ee; color: #0f5c55; }
        .tag.blu { background: #e0eaf6; color: #1e4a7a; }
        .tag.org { background: #fbe9dc; color: #9a4a0c; }

        .opts { display: contents; }
        .opt { display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; }
        .opt span { display: none; font-size: 12px; font-weight: 600; color: var(--c); }
        .opt input {
            -webkit-appearance: none; appearance: none; flex: none; margin: 0; cursor: pointer;
            width: 22px; height: 22px; border-radius: 50%; background: #fff; border: 1.5px solid #d5d9e3;
            transition: border-color .15s, background .15s;
        }
        .opt input:hover { border-color: var(--c); }
        .opt input:checked { border-color: var(--c); background: radial-gradient(circle, var(--c) 0 3.5px, #fff 4px 6.5px, var(--c) 7px); }
        .opt input:focus-visible { outline: 2px solid var(--c); outline-offset: 2px; }

        /* Actions */
        .act { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn {
            display: flex; align-items: center; justify-content: center; height: 46px; padding: 0 16px;
            font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;
            border: 1px solid var(--ind); border-radius: 10px; transition: background .15s, box-shadow .15s;
        }
        .btn.sec { background: #fff; color: var(--ind); }
        .btn.sec:hover { background: #f0f0fb; }
        .btn.pri { background: var(--ind); color: #fff; }
        .btn.pri:hover { background: #20186a; }
        .btn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }

        /* Responsive */
        @media (max-width: 760px) {
            .wrap { padding: 20px 14px 36px; }
            .card { padding: 16px; }
            .thead { display: none; }
            .row { grid-template-columns: 1fr; gap: 10px; padding: 12px; }
            .opts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .opt { justify-content: flex-start; padding: 8px 10px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
            .opt span { display: inline; }
            .opt:has(input:checked) { border-color: var(--c); }
        }
        @media (max-width: 480px) {
            .act { grid-template-columns: 1fr; }
            .btn.pri { order: -1; }
            .legend ul { flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<main class="wrap">

    <header>
        <h1>Formulir Observasi Perkembangan</h1>
        <p class="sub">Catat hasil observasi perkembangan anak dengan kategori respons yang seragam untuk setiap indikator.</p>
    </header>

    <section class="info" aria-label="Informasi guru">
        <div><small>Guru Pengajar</small><b>{{ $guru }}</b></div>
        <div><small>Tahun Ajaran</small><b>{{ $tahunAjaran }} &bull; {{ $semester }}</b></div>
        <div><small>Status</small><b class="ok">{{ $statusAjaran }}</b></div>
    </section>

    @if (session('sukses'))
        <div class="alert ok" role="status">{{ session('sukses') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert err" role="alert">
            <b>Periksa kembali isian Anda:</b>
            <ul>@foreach ($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('guru.observasi.store') }}">
        @csrf

        {{-- Identitas Observasi --}}
        <section class="card">
            <h2>Identitas Observasi</h2>
            <div class="grid">
                <div class="f">
                    <label for="kelas_id">Pilih Kelas</label>
                    <select class="ctl" id="kelas_id" name="kelas_id" required>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k['id'] }}" @selected((string) old('kelas_id', $kelasList[0]['id']) === (string) $k['id'])>{{ $k['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f">
                    <label for="anak_id">Pilih Anak</label>
                    <select class="ctl" id="anak_id" name="anak_id" required></select>
                </div>
                <div class="f">
                    <label for="tanggal_observasi">Tanggal Observasi</label>
                    <input class="ctl" type="date" id="tanggal_observasi" name="tanggal_observasi"
                           value="{{ old('tanggal_observasi', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="f mt">
                <label for="kegiatan">Kegiatan Observasi</label>
                <select class="ctl" id="kegiatan" name="kegiatan" required>
                    @foreach ($kegiatanList as $kegiatan)
                        <option value="{{ $kegiatan }}" @selected(old('kegiatan', $kegiatanList[0]) === $kegiatan)>{{ $kegiatan }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        {{-- Legenda kategori --}}
        <aside class="legend" aria-label="Kategori respons seragam">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9.5"/><path d="M12 11v5.5M12 7.6v.01"/>
            </svg>
            <div>
                <h3>Kategori Respons Seragam</h3>
                <ul>
                    @foreach ($kategori as $kode => $nama)
                        <li class="{{ strtolower($kode) }}"><i class="dot"></i><span><b>{{ $kode }}</b> &ndash; {{ $nama }}</span></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        {{-- Indikator Perkembangan --}}
        <section class="card">
            <div class="head">
                <h2>Indikator Perkembangan</h2>
                <span class="cnt">{{ count($indikator) }} Indikator</span>
            </div>

            <div class="thead" aria-hidden="true">
                <span>Indikator</span>
                @foreach ($kategori as $kode => $nama)
                    <span class="{{ strtolower($kode) }}">{{ $kode }}</span>
                @endforeach
            </div>

            <div class="rows">
                @foreach ($indikator as $i)
                    @php $nilai = old('indikator.' . $i['id'], $i['default'] ?? null); @endphp
                    <div class="row" role="radiogroup" aria-labelledby="ind-{{ $i['id'] }}">
                        <div>
                            <div class="ttl" id="ind-{{ $i['id'] }}">
                                <span class="tag {{ $i['tone'] }}">{{ $i['kode'] }}</span>{{ $i['judul'] }}
                            </div>
                            <p class="desc">{{ $i['deskripsi'] }}</p>
                        </div>
                        <div class="opts">
                            @foreach ($kategori as $kode => $nama)
                                <label class="opt {{ strtolower($kode) }}">
                                    <input type="radio" name="indikator[{{ $i['id'] }}]" value="{{ $kode }}"
                                           aria-label="{{ $kode }} – {{ $nama }}" @checked($nilai === $kode) required>
                                    <span>{{ $kode }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Catatan Observasi --}}
        <section class="card">
            <h2>Catatan Observasi</h2>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="f">
                    <label for="deskripsi_pengamatan">Deskripsi Pengamatan</label>
                    <textarea class="ctl" id="deskripsi_pengamatan" name="deskripsi_pengamatan" rows="3"
                              placeholder="Anak menunjukkan antusiasme tinggi saat bermain di sentra balok. Mampu bekerja sama dengan 2 teman sebaya namun perlu bimbingan dalam motorik halus...">{{ old('deskripsi_pengamatan') }}</textarea>
                </div>
                <div class="f">
                    <label for="tindak_lanjut">Tindak Lanjut / Rekomendasi</label>
                    <textarea class="ctl" id="tindak_lanjut" name="tindak_lanjut" rows="3"
                              placeholder="Berikan stimulasi motorik halus melalui kegiatan menggunting dan menempel...">{{ old('tindak_lanjut') }}</textarea>
                </div>
            </div>
        </section>

        {{-- Aksi --}}
        <div class="act">
            <a href="{{ route('guru.dashboard') }}" class="btn sec">Batal</a>
            <button type="submit" class="btn pri">Simpan Observasi</button>
        </div>
    </form>
</main>

<script>
    // Daftar anak mengikuti kelas yang dipilih
    (() => {
        const anak = @json($anakList);
        const kelas = document.getElementById('kelas_id');
        const pilihAnak = document.getElementById('anak_id');

        const render = (terpilih) => {
            pilihAnak.length = 0;
            anak.filter(a => String(a.kelas_id) === kelas.value)
                .forEach((a, i) => pilihAnak.add(
                    new Option(a.nama, a.id, false, terpilih ? String(a.id) === String(terpilih) : i === 0)
                ));
        };

        kelas.addEventListener('change', () => render(null));
        render(@json(old('anak_id')));
    })();
</script>
</body>
</html>