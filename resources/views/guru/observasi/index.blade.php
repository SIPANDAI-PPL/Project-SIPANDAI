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
            --ind: #2a2080; --ind-dk: #20186a; --ink: #161a2b; --mut: #6b7280;
            --line: #e3e6ee; --bg: #f6f7fb; --fld: #f5f6fa;
            --ok: #0f5c55; --danger: #b42318;
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
        .btn.sec { background: #fff; color: var(--ind); }
        .btn.sec:hover { background: #f0f0fb; }
        .btn.dgr { background: var(--danger); border-color: var(--danger); color: #fff; }
        .btn.dgr:hover { background: #912018; }
        .btn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }

        /* Notifikasi */
        .notif {
            display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-radius: 12px;
            background: #e6f6ef; border: 1px solid #bfe3d4; color: var(--ok);
        }
        .notif svg { flex: none; margin-top: 1px; }
        .notif b { display: block; font-size: 13px; font-weight: 600; }
        .notif p { font-size: 11.5px; margin-top: 1px; opacity: .9; }
        .notif.gagal { background: #fdecec; border-color: #f5c2c2; color: #8a1c1c; }

        /* Kartu ringkasan */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .stat { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 16px 22px 18px; }
        .stat small { display: block; font-size: 13px; font-weight: 500; }
        .stat .val { display: flex; align-items: baseline; gap: 18px; margin-top: 12px; }
        .stat b { font-size: 32px; font-weight: 700; color: var(--ind); line-height: 1; }
        .stat span { font-size: 12.5px; color: var(--mut); }

        /* Filter */
        .filters { display: grid; grid-template-columns: minmax(0, 2.6fr) repeat(3, minmax(0, 1fr)); gap: 14px; }
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
        .hint { font-size: 12.5px; color: var(--mut); margin-top: -8px; }

        /* Tabel */
        .tabel { background: #fff; border: 1px solid var(--line); border-radius: 18px; overflow: hidden; }
        .tabel-head { display: flex; align-items: center; gap: 14px; padding: 22px 24px 18px; }
        .tabel-head h2 { font-size: 18px; font-weight: 600; }
        .badge { font-size: 11.5px; font-weight: 600; color: var(--ind); background: #eceefb; border-radius: 999px; padding: 5px 13px; white-space: nowrap; }
        .scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; min-width: 880px; border-collapse: collapse; }
        thead th { background: var(--fld); text-align: left; font-size: 13px; font-weight: 500; color: #3b4157; padding: 14px 12px; white-space: nowrap; }
        thead th:first-child, tbody td:first-child { padding-left: 24px; }
        thead th:last-child, tbody td:last-child { padding-right: 24px; }
        tbody td { padding: 12px; font-size: 13.5px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: var(--fld); }
        .nama { font-weight: 600; font-size: 14px; }
        .kode, .aspek { font-size: 12.5px; color: var(--mut); margin-top: 1px; }

        /* Badge respons anak */
        .badge-r { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 500; white-space: nowrap; color: var(--c); background: var(--cbg); }
        .r-terlibat_aktif { --c: #0f5c55; --cbg: #e3f5ef; }
        .r-terlibat_dengan_pendampingan { --c: #1e4a7a; --cbg: #e0eaf6; }
        .r-kurang_tertarik { --c: #9a4a0c; --cbg: #fbe9dc; }
        .r-belum_terlibat { --c: #5b6477; --cbg: #eceff4; }

        .aksi { display: flex; align-items: center; gap: 8px; }
        .abtn {
            display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 16px;
            font: inherit; font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer;
            background: #fff; color: var(--ink); border: 1px solid var(--line); border-radius: 8px;
            transition: background .15s, border-color .15s;
        }
        .abtn.edit:hover { background: var(--fld); }
        .abtn.hapus { color: var(--danger); }
        .abtn.hapus:hover { background: #fdecec; border-color: #f5c2c2; }
        .abtn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }
        .kosong { text-align: center; color: var(--mut); padding: 40px 12px !important; }

        .foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 0 24px; padding: 20px 0; border-top: 1px solid var(--line); }
        .foot p { font-size: 13px; color: var(--mut); }
        .pager { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .pg {
            display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 14px;
            font-size: 12.5px; color: var(--mut); text-decoration: none; background: #fff; border: 1px solid var(--line); border-radius: 8px;
        }
        a.pg:hover { background: var(--fld); }
        .pg.on { background: var(--ind); border-color: var(--ind); color: #fff; font-weight: 600; }
        .pg.off { opacity: .6; pointer-events: none; }

        /* Modal hapus */
        dialog.modal { margin: auto; border: 0; padding: 0; border-radius: 16px; width: min(440px, calc(100% - 32px)); box-shadow: 0 24px 60px rgba(22, 26, 43, .3); color: var(--ink); }
        dialog.modal::backdrop { background: rgba(22, 26, 43, .5); }
        .modal form { padding: 24px; }
        .eyebrow { font-size: 10.5px; font-weight: 700; letter-spacing: .04em; color: var(--danger); text-transform: uppercase; }
        .modal h2 { font-size: 20px; font-weight: 700; margin-top: 8px; }
        .modal .lead { font-size: 12.5px; color: var(--mut); margin-top: 6px; }
        .ringkas { background: var(--fld); border-radius: 10px; padding: 16px; margin-top: 16px; }
        .ringkas .n { font-size: 15px; font-weight: 700; }
        .ringkas .m { font-size: 11.5px; color: var(--mut); margin-top: 2px; }
        .ringkas small { display: block; font-size: 11.5px; color: var(--mut); margin-top: 14px; }
        .ringkas .a { font-size: 13px; font-weight: 600; margin-top: 2px; }
        .peringatan { background: #fff1f0; border-radius: 10px; padding: 12px 14px; margin-top: 14px; color: var(--danger); }
        .peringatan b { display: block; font-size: 12.5px; }
        .peringatan p { font-size: 11.5px; margin-top: 2px; }
        .modal-act { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .modal-act .btn { height: 42px; padding: 0 22px; }

        /* Responsif */
        @media (max-width: 1000px) {
            .filters { grid-template-columns: 1fr 1fr; }
            .filters .f:first-child { grid-column: 1 / -1; }
        }
        @media (max-width: 560px) {
            .wrap { padding: 20px 14px 36px; }
            .top .btn { width: 100%; }
            .filters { grid-template-columns: 1fr; }
            .tabel-head, .foot { padding-left: 16px; padding-right: 16px; }
            .foot { margin: 0; }
            thead th:first-child, tbody td:first-child { padding-left: 16px; }
            .modal-act { flex-direction: column-reverse; }
            .modal-act .btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<main class="wrap">

    <header class="top">
        <div>
            <h1>Data Observasi Perkembangan</h1>
            <p class="sub">Lihat dan kelola seluruh hasil pengamatan perkembangan anak.</p>
        </div>
        <a href="{{ route('guru.observasi.create') }}" class="btn pri"><span aria-hidden="true">+</span> Tambah observasi</a>
    </header>

    @if (session('notifikasi'))
        @php $notif = session('notifikasi'); @endphp
        <div class="notif" role="status">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
            <div>
                <b>{{ $notif['judul'] }}</b>
                <p>{{ $notif['pesan'] }}</p>
            </div>
        </div>
    @endif
    @if (session('gagal'))
        <div class="notif gagal" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.4v.01"/></svg>
            <div><b>{{ session('gagal') }}</b></div>
        </div>
    @endif

    {{-- Ringkasan --}}
    <section class="stats" aria-label="Ringkasan observasi">
        <div class="stat">
            <small>Total observasi</small>
            <div class="val"><b>{{ $total }}</b><span>Seluruh data tersimpan</span></div>
        </div>
        <div class="stat">
            <small>Anak terobservasi</small>
            <div class="val"><b>{{ $anakUnik }}</b><span>{{ $kelasNamaStr !== '' ? 'Dari ' . $kelasNamaStr : 'Belum ada data' }}</span></div>
        </div>
        <div class="stat">
            <small>Kelas terobservasi</small>
            <div class="val"><b>{{ $kelasUnik }}</b><span>Tahun ajaran {{ $tahunAjaran }}</span></div>
        </div>
    </section>

    {{-- Filter --}}
    <form method="GET" action="{{ route('guru.observasi.index') }}" class="filters" role="search">
        <div class="f">
            <label for="q">Cari observasi</label>
            <input class="ctl" type="search" id="q" name="q" value="{{ $cari }}" placeholder="Cari nama anak atau aktivitas...">
        </div>
        <div class="f">
            <label for="kelas">Kelas</label>
            <select class="ctl" id="kelas" name="kelas" onchange="this.form.submit()">
                <option value="">Semua kelas</option>
                @foreach ($kelasList as $id => $nama)
                    <option value="{{ $id }}" @selected((int) $kelasId === (int) $id)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="f">
            <label for="tahun">Tahun ajaran</label>
            <select class="ctl" id="tahun" name="tahun" onchange="this.form.submit()">
                @foreach ($tahunList as $t)
                    <option value="{{ $t }}" @selected($tahunAjaran === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="f">
            <label for="urut">Urutkan</label>
            <select class="ctl" id="urut" name="urut" onchange="this.form.submit()">
                @foreach ($urutList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($urut === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <p class="hint">Menampilkan {{ $adaFilter ? 'hasil penyaringan' : 'seluruh observasi' }} &bull; Tahun ajaran {{ $tahunAjaran }}</p>

    {{-- Tabel --}}
    <section class="tabel" aria-labelledby="judul-tabel">
        <div class="tabel-head">
            <h2 id="judul-tabel">Seluruh observasi</h2>
            <span class="badge">{{ $observasi->total() }} data</span>
        </div>

        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Nama anak</th>
                        <th scope="col">Kelas</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Aktivitas observasi</th>
                        <th scope="col">Respons anak</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($observasi as $o)
                        @php
                            $kode = sprintf('OBS-%03d', $o->id);
                            $namaKelas = $kelasList->get($o->anak->kelas_id, '-');
                            $tanggal = $o->tanggal->locale('id')->isoFormat('D MMM YYYY');
                        @endphp
                        <tr>
                            <td>
                                <div class="nama">{{ $o->anak->nama }}</div>
                                <div class="kode">{{ $kode }}</div>
                            </td>
                            <td>{{ $namaKelas }}</td>
                            <td>{{ $tanggal }}</td>
                            <td>
                                {{ $o->aktivitas->nama }}
                                <div class="aspek">{{ $o->aspek->nama }}</div>
                            </td>
                            <td><span class="badge-r r-{{ $o->kategori_respons->value }}">{{ $o->kategori_respons->label() }}</span></td>
                            <td>
                                <div class="aksi">
                                    <a href="{{ route('guru.observasi.edit', $o) }}" class="abtn edit">Edit</a>
                                    <button type="button" class="abtn hapus" data-hapus
                                            data-url="{{ route('guru.observasi.destroy', $o) }}"
                                            data-nama="{{ $o->anak->nama }}"
                                            data-meta="{{ $kode }} • {{ $namaKelas }} • {{ $tanggal }}"
                                            data-aktivitas="{{ $o->aktivitas->nama }}">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="kosong">
                                {{ $adaFilter ? 'Tidak ada observasi yang cocok dengan pencarian Anda.' : 'Belum ada data observasi pada tahun ajaran ini.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="foot">
            <p>
                @if ($observasi->total() > 0)
                    Menampilkan {{ $observasi->firstItem() }}&ndash;{{ $observasi->lastItem() }} dari {{ $observasi->total() }} observasi
                @else
                    Tidak ada data untuk ditampilkan
                @endif
            </p>

            <nav class="pager" aria-label="Navigasi halaman">
                @if ($observasi->onFirstPage())
                    <span class="pg off" aria-disabled="true">Sebelumnya</span>
                @else
                    <a href="{{ $observasi->previousPageUrl() }}" class="pg" rel="prev">Sebelumnya</a>
                @endif

                @php
                    $sekarang = $observasi->currentPage();
                    $terakhir = $observasi->lastPage();
                @endphp
                @for ($p = max(1, $sekarang - 2); $p <= min($terakhir, $sekarang + 2); $p++)
                    @if ($p === $sekarang)
                        <span class="pg on" aria-current="page">{{ $p }}</span>
                    @else
                        <a href="{{ $observasi->url($p) }}" class="pg">{{ $p }}</a>
                    @endif
                @endfor

                @if ($observasi->hasMorePages())
                    <a href="{{ $observasi->nextPageUrl() }}" class="pg" rel="next">Berikutnya</a>
                @else
                    <span class="pg off" aria-disabled="true">Berikutnya</span>
                @endif
            </nav>
        </div>
    </section>
</main>

{{-- Modal konfirmasi hapus --}}
<dialog class="modal" id="modal-hapus" aria-labelledby="judul-hapus">
    <form method="POST" id="form-hapus" action="">
        @csrf
        @method('DELETE')
        <p class="eyebrow">Hapus observasi</p>
        <h2 id="judul-hapus">Hapus data observasi ini?</h2>
        <p class="lead">Pastikan data yang dipilih sudah benar sebelum menghapus.</p>

        <div class="ringkas">
            <p class="n" id="h-nama"></p>
            <p class="m" id="h-meta"></p>
            <small>Aktivitas observasi</small>
            <p class="a" id="h-aktivitas"></p>
        </div>

        <div class="peringatan" role="alert">
            <b>Tindakan ini tidak dapat dibatalkan.</b>
            <p>Hasil penilaian dan catatan observasi ini akan dihapus.</p>
        </div>

        <div class="modal-act">
            <button type="button" class="btn sec" data-tutup>Batal</button>
            <button type="submit" class="btn dgr">Ya, hapus observasi</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const modal = document.getElementById('modal-hapus');
        const form = document.getElementById('form-hapus');

        document.querySelectorAll('[data-hapus]').forEach((tombol) => {
            tombol.addEventListener('click', () => {
                form.action = tombol.dataset.url;
                document.getElementById('h-nama').textContent = tombol.dataset.nama;
                document.getElementById('h-meta').textContent = tombol.dataset.meta;
                document.getElementById('h-aktivitas').textContent = tombol.dataset.aktivitas;
                modal.showModal();
            });
        });

        modal.querySelector('[data-tutup]').addEventListener('click', () => modal.close());
        // Klik di area gelap (di luar kotak modal) menutup modal.
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.close(); });
    })();
</script>
</body>
</html>