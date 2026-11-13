@php
    /*
     |--------------------------------------------------------------------------
     | DATA CONTOH (UI saja)
     |--------------------------------------------------------------------------
     | Ganti variabel di bawah dengan data dari controller/database.
     */
    $guru         = auth()->user()->nama ?? 'Mia Stacia';
    $tahunAjaran  = '2024/2025';
    $semester     = 'Ganjil';

    $kelasList = [
        ['id' => 1, 'nama' => 'Kelas A'],
        ['id' => 2, 'nama' => 'Kelas B'],
    ];

    $tahunList    = ['2024/2025', '2023/2024'];
    $semesterList = ['Ganjil', 'Genap'];
    $urutList     = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'Nama anak (A-Z)'];

    $observasi = [
        ['id' => 1, 'kode' => 'OBS-001', 'anak' => 'Dedek Piplup', 'kelas' => 'Kelas A', 'tanggal' => '16 Jul 2024', 'kegiatan' => 'Bermain Bebas di Sentra Balok',  'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
        ['id' => 2, 'kode' => 'OBS-002', 'anak' => 'Alya Putri',   'kelas' => 'Kelas A', 'tanggal' => '16 Jul 2024', 'kegiatan' => 'Menggunting dan Menempel',        'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
        ['id' => 3, 'kode' => 'OBS-003', 'anak' => 'Bima Pratama', 'kelas' => 'Kelas B', 'tanggal' => '16 Jul 2024', 'kegiatan' => 'Bercerita tentang Keluarga',      'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
        ['id' => 4, 'kode' => 'OBS-004', 'anak' => 'Nara Safira',  'kelas' => 'Kelas B', 'tanggal' => '15 Jul 2024', 'kegiatan' => 'Mengenal Angka dan Bentuk',      'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
        ['id' => 5, 'kode' => 'OBS-005', 'anak' => 'Dedek Piplup', 'kelas' => 'Kelas A', 'tanggal' => '15 Jul 2024', 'kegiatan' => 'Bermain Peran di Sentra Drama',  'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
        ['id' => 6, 'kode' => 'OBS-006', 'anak' => 'Alya Putri',   'kelas' => 'Kelas A', 'tanggal' => '15 Jul 2024', 'kegiatan' => 'Melukis dengan Jari',            'guru' => 'Mia Stacia', 'status' => 'Tersimpan'],
    ];

    $total        = count($observasi);
    $anakUnik     = collect($observasi)->pluck('anak')->unique()->count();
    $kelasUnik    = collect($observasi)->pluck('kelas')->unique()->count();
    $kelasNamaStr = collect($observasi)->pluck('kelas')->unique()->sort()->implode(' dan ');

    // Helper URL: aman dipakai walau route belum dibuat (jatuh ke '#')
    $url = fn (string $name, $param = null) => \Illuminate\Support\Facades\Route::has($name)
        ? ($param !== null ? route($name, $param) : route($name))
        : '#';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Observasi Perkembangan · SIPANDAI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ind: #2a2080;
            --ind-dk: #20186a;
            --ink: #161a2b;
            --mut: #6b7280;
            --line: #e3e6ee;
            --bg: #f6f7fb;
            --fld: #f5f6fa;
            --ok: #0f5c55;
            --ok-bg: #e3f5ef;
            --danger: #c0262d;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px; line-height: 1.45; color: var(--ink); background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .wrap { max-width: 1200px; margin: 0 auto; padding: 32px 20px 48px; display: flex; flex-direction: column; gap: 20px; }

        /* Header */
        .top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        h1 { font-size: clamp(24px, 4.2vw, 32px); font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
        .sub { color: var(--mut); font-size: 14px; margin-top: 6px; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 22px; font: inherit; font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer; white-space: nowrap;
            border: 1px solid var(--ind); border-radius: 10px; transition: background .15s, box-shadow .15s;
        }
        .btn.pri { background: var(--ind); color: #fff; box-shadow: 0 6px 16px rgba(42, 32, 128, .18); }
        .btn.pri:hover { background: var(--ind-dk); }
        .btn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }

        /* Stat cards */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .stat { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 16px 22px 18px; }
        .stat small { display: block; font-size: 14px; color: var(--ink); font-weight: 500; }
        .stat .val { display: flex; align-items: baseline; gap: 22px; margin-top: 12px; }
        .stat b { font-size: 32px; font-weight: 700; color: var(--ind); line-height: 1; }
        .stat span { font-size: 13px; color: var(--mut); }

        /* Filters */
        .filters { display: grid; grid-template-columns: minmax(0, 2.6fr) repeat(4, minmax(0, 1fr)); gap: 14px; }
        .f label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 8px; }
        .ctl {
            width: 100%; height: 46px; padding: 0 16px; font: inherit; font-size: 14px; color: var(--ink);
            background-color: #fff; border: 1px solid var(--line); border-radius: 12px;
            transition: border-color .15s, box-shadow .15s;
        }
        select.ctl {
            -webkit-appearance: none; appearance: none; padding-right: 42px; cursor: pointer; font-weight: 500;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23161a2b' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m3 6 5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 16px center;
        }
        .ctl::placeholder { color: #7b8190; }
        .ctl:focus { outline: none; border-color: var(--ind); box-shadow: 0 0 0 3px rgba(42, 32, 128, .15); }
        .hint { font-size: 12.5px; color: var(--mut); margin-top: -4px; }

        /* Table card */
        .card { background: #fff; border: 1px solid var(--line); border-radius: 18px; overflow: hidden; }
        .card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 22px 24px 18px; flex-wrap: wrap; }
        .card-head .left { display: flex; align-items: center; gap: 14px; }
        .card-head h2 { font-size: 18px; font-weight: 600; }
        .badge { font-size: 11.5px; font-weight: 600; color: var(--ind); background: #eceefb; border-radius: 999px; padding: 5px 13px; white-space: nowrap; }
        .note { font-size: 13px; color: var(--mut); }

        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; min-width: 940px; border-collapse: collapse; }
        thead th {
            background: var(--fld); text-align: left; font-size: 13px; font-weight: 500; color: #3b4157;
            padding: 14px 12px; white-space: nowrap;
        }
        thead th:first-child, tbody td:first-child { padding-left: 24px; }
        thead th:last-child, tbody td:last-child { padding-right: 24px; }
        tbody td { padding: 12px; font-size: 13.5px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: var(--fld); }
        .nama { font-weight: 600; font-size: 14px; }
        .kode { font-size: 12.5px; color: var(--mut); margin-top: 1px; }

        .status {
            display: inline-block; padding: 4px 14px; border-radius: 999px;
            font-size: 12px; font-weight: 500; color: var(--ok); background: var(--ok-bg);
        }

        .aksi { display: flex; align-items: center; gap: 8px; }
        .aksi form { display: inline; }
        .abtn {
            display: inline-flex; align-items: center; justify-content: center;
            height: 36px; padding: 0 14px; font: inherit; font-size: 13px; font-weight: 500;
            text-decoration: none; cursor: pointer; border-radius: 8px;
            background: #fff; color: var(--ink); border: 1px solid var(--line);
            transition: background .15s, border-color .15s;
        }
        .abtn.lihat { background: #eceefb; border-color: #eceefb; color: var(--ind); }
        .abtn.lihat:hover { background: #dfe2f5; }
        .abtn.edit:hover { background: var(--fld); }
        .abtn.hapus { color: var(--danger); }
        .abtn.hapus:hover { background: #fdecec; border-color: #f5c2c2; }
        .abtn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }

        .empty { text-align: center; color: var(--mut); padding: 40px 12px !important; }

        /* Footer / pagination */
        .card-foot {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            margin: 0 24px; padding: 20px 0; border-top: 1px solid var(--line);
        }
        .card-foot p { font-size: 13px; color: var(--mut); }
        .pager { display: flex; align-items: center; gap: 8px; }
        .pg {
            display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px;
            padding: 0 16px; font: inherit; font-size: 12.5px; color: var(--mut); text-decoration: none;
            background: #fff; border: 1px solid var(--line); border-radius: 8px;
        }
        .pg.on { background: var(--ind); border-color: var(--ind); color: #fff; font-weight: 600; padding: 0; width: 42px; }
        .pg.off { opacity: .6; pointer-events: none; }

        /* Alerts */
        .alert { border-radius: 12px; padding: 12px 16px; font-size: 13px; background: #e6f4f1; border: 1px solid #bfe0d9; color: var(--ok); }

        /* Responsive */
        @media (max-width: 1000px) {
            .filters { grid-template-columns: 1fr 1fr; }
            .filters .f:first-child { grid-column: 1 / -1; }
        }
        @media (max-width: 560px) {
            .wrap { padding: 20px 14px 36px; }
            .top .btn { width: 100%; }
            .filters { grid-template-columns: 1fr; }
            .card-head, .card-foot { padding-left: 16px; padding-right: 16px; }
            .card-foot { margin: 0; }
            thead th:first-child, tbody td:first-child { padding-left: 16px; }
            .pager { width: 100%; justify-content: space-between; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<main class="wrap">

    {{-- Header halaman --}}
    <header class="top">
        <div>
            <h1>Data Observasi Perkembangan</h1>
            <p class="sub">Lihat dan kelola seluruh hasil pengamatan perkembangan anak.</p>
        </div>
        <a href="{{ $url('guru.observasi.create') }}" class="btn pri">
            <span aria-hidden="true">+</span> Tambah observasi
        </a>
    </header>

    @if (session('sukses'))
        <div class="alert" role="status">{{ session('sukses') }}</div>
    @endif

    {{-- Kartu ringkasan --}}
    <section class="stats" aria-label="Ringkasan observasi">
        <div class="stat">
            <small>Total observasi</small>
            <div class="val"><b>{{ $total }}</b><span>Seluruh data tersimpan</span></div>
        </div>
        <div class="stat">
            <small>Anak terobservasi</small>
            <div class="val"><b>{{ $anakUnik }}</b><span>Dari {{ $kelasNamaStr }}</span></div>
        </div>
        <div class="stat">
            <small>Kelas terobservasi</small>
            <div class="val"><b>{{ $kelasUnik }}</b><span>Tahun ajaran {{ $tahunAjaran }}</span></div>
        </div>
    </section>

    {{-- Filter --}}
    <form method="GET" action="{{ $url('guru.observasi.index') }}" class="filters" role="search">
        <div class="f">
            <label for="q">Cari observasi</label>
            <input class="ctl" type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama anak atau kegiatan...">
        </div>
        <div class="f">
            <label for="kelas">Kelas</label>
            <select class="ctl" id="kelas" name="kelas" onchange="this.form.submit()">
                <option value="">Semua kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k['id'] }}" @selected((string) request('kelas') === (string) $k['id'])>{{ $k['nama'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="f">
            <label for="tahun">Tahun ajaran</label>
            <select class="ctl" id="tahun" name="tahun" onchange="this.form.submit()">
                @foreach ($tahunList as $t)
                    <option value="{{ $t }}" @selected(request('tahun', $tahunAjaran) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="f">
            <label for="semester">Semester</label>
            <select class="ctl" id="semester" name="semester" onchange="this.form.submit()">
                @foreach ($semesterList as $s)
                    <option value="{{ $s }}" @selected(request('semester', $semester) === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="f">
            <label for="urut">Urutkan</label>
            <select class="ctl" id="urut" name="urut" onchange="this.form.submit()">
                @foreach ($urutList as $val => $label)
                    <option value="{{ $val }}" @selected(request('urut', 'terbaru') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <p class="hint">Menampilkan seluruh observasi &bull; Tahun ajaran {{ $tahunAjaran }}, semester {{ strtolower($semester) }}</p>

    {{-- Tabel observasi --}}
    <section class="card" aria-labelledby="judul-tabel">
        <div class="card-head">
            <div class="left">
                <h2 id="judul-tabel">Seluruh observasi</h2>
                <span class="badge">{{ $total }} data</span>
            </div>
            <span class="note">Data contoh untuk rancangan CRUD</span>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Nama anak</th>
                        <th scope="col">Kelas</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kegiatan observasi</th>
                        <th scope="col">Guru pengajar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($observasi as $o)
                        <tr>
                            <td>
                                <div class="nama">{{ $o['anak'] }}</div>
                                <div class="kode">{{ $o['kode'] }}</div>
                            </td>
                            <td>{{ $o['kelas'] }}</td>
                            <td>{{ $o['tanggal'] }}</td>
                            <td>{{ $o['kegiatan'] }}</td>
                            <td>{{ $o['guru'] }}</td>
                            <td><span class="status">{{ $o['status'] }}</span></td>
                            <td>
                                <div class="aksi">
                                    <a href="{{ $url('guru.observasi.show', $o['id']) }}" class="abtn lihat">Lihat</a>
                                    <a href="{{ $url('guru.observasi.edit', $o['id']) }}" class="abtn edit">Edit</a>
                                    <form method="POST" action="{{ $url('guru.observasi.destroy', $o['id']) }}"
                                          onsubmit="return confirm('Hapus observasi {{ $o['kode'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="abtn hapus">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada data observasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-foot">
            <p>Menampilkan 1–{{ $total }} dari {{ $total }} observasi</p>
            <nav class="pager" aria-label="Navigasi halaman">
                <a href="#" class="pg off" aria-disabled="true">Sebelumnya</a>
                <a href="#" class="pg on" aria-current="page">1</a>
                <a href="#" class="pg off" aria-disabled="true">Berikutnya</a>
            </nav>
        </div>
    </section>

</main>
</body>
</html>