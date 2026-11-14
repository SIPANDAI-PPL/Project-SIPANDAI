@php
    $mode = $observasi ? 'edit' : 'tambah';
    $nilaiKelas = old('kelas_id', $observasi?->anak?->kelas_id);
    $nilaiAnak = old('anak_id', $observasi?->anak_id);
    $nilaiRespons = old('kategori_respons', $observasi?->kategori_respons?->value);
    $nilaiTanggal = old('tanggal', $observasi?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $judulHalaman = $mode === 'edit' ? 'Edit Observasi Perkembangan' : 'Formulir Observasi Perkembangan';

    $deskripsiRespons = [
        'terlibat_aktif' => 'Anak antusias dan melakukan kegiatan dengan mandiri.',
        'terlibat_dengan_pendampingan' => 'Anak ikut serta setelah diarahkan atau didampingi guru.',
        'kurang_tertarik' => 'Anak terlihat kurang bersemangat atau mudah beralih.',
        'belum_terlibat' => 'Anak belum mau atau belum mampu ikut kegiatan.',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judulHalaman }} · SIPANDAI</title>

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

        .wrap { max-width: 1000px; margin: 0 auto; padding: 32px 20px 48px; display: flex; flex-direction: column; gap: 16px; }
        form.frm { display: flex; flex-direction: column; gap: 16px; }
        h1 { font-size: clamp(24px, 4.2vw, 32px); font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
        .sub { color: var(--mut); font-size: 14px; margin-top: 6px; }

        /* Info */
        .info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .info > div { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 10px 16px; }
        .info small { display: block; font-size: 11px; color: var(--mut); }
        .info b { display: block; font-size: 15px; font-weight: 600; margin-top: 2px; }
        .info .ok { color: var(--ok); }

        /* Alert */
        .alert-err { border-radius: 12px; padding: 12px 16px; font-size: 13px; background: #fdecec; border: 1px solid #f5c2c2; color: #8a1c1c; }
        .alert-err ul { margin: 4px 0 0 18px; padding: 0; }

        /* Kartu */
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; }
        .card h2 { font-size: 16px; font-weight: 600; margin-bottom: 14px; }
        .card h2.rapat { margin-bottom: 4px; }
        .card .ket { font-size: 12.5px; color: var(--mut); margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }

        /* Isian */
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
        select.ctl:disabled { color: var(--mut); cursor: not-allowed; }
        input[type=date].ctl { min-width: 0; }
        input[type=date]::-webkit-calendar-picker-indicator { cursor: pointer; }
        textarea.ctl { height: auto; min-height: 110px; padding: 10px 14px; resize: vertical; line-height: 1.45; }
        .ctl::placeholder { color: #7b8190; }
        .ctl:focus { outline: none; border-color: var(--ind); box-shadow: 0 0 0 3px rgba(42, 32, 128, .15); background-color: #fff; }
        .ctl.invalid { border-color: #e0605f; }

        /* Pilihan respons */
        .resp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; }
        .resp {
            display: flex; gap: 12px; align-items: flex-start; cursor: pointer;
            padding: 14px; background: #fff; border: 1.5px solid var(--line); border-radius: 12px;
            transition: border-color .15s, background .15s;
        }
        .resp:hover { border-color: var(--c); }
        .resp:has(input:checked) { border-color: var(--c); background: var(--cbg); }
        .resp:has(input:focus-visible) { outline: 2px solid var(--c); outline-offset: 2px; }
        .resp input {
            -webkit-appearance: none; appearance: none; flex: none; margin: 2px 0 0; cursor: pointer;
            width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 1.5px solid #cfd4e0;
            transition: border-color .15s;
        }
        .resp input:checked { border-color: var(--c); background: radial-gradient(circle, var(--c) 0 4px, #fff 4.5px); }
        .resp b { display: block; font-size: 14px; font-weight: 600; color: var(--c); }
        .resp span { display: block; font-size: 12px; color: var(--mut); margin-top: 2px; }
        .r-terlibat_aktif { --c: #0f5c55; --cbg: #e3f5ef; }
        .r-terlibat_dengan_pendampingan { --c: #1e4a7a; --cbg: #e0eaf6; }
        .r-kurang_tertarik { --c: #9a4a0c; --cbg: #fbe9dc; }
        .r-belum_terlibat { --c: #5b6477; --cbg: #eceff4; }

        /* Aksi */
        .act { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn {
            display: flex; align-items: center; justify-content: center; height: 46px; padding: 0 16px;
            font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;
            border: 1px solid var(--ind); border-radius: 10px; transition: background .15s, box-shadow .15s;
        }
        .btn.sec { background: #fff; color: var(--ind); }
        .btn.sec:hover { background: #f0f0fb; }
        .btn.pri { background: var(--ind); color: #fff; }
        .btn.pri:hover { background: var(--ind-dk); }
        .btn:focus-visible { outline: 2px solid var(--ind); outline-offset: 2px; }

        @media (max-width: 760px) {
            .wrap { padding: 20px 14px 36px; }
            .card { padding: 16px; }
        }
        @media (max-width: 480px) {
            .act { grid-template-columns: 1fr; }
            .btn.pri { order: -1; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<main class="wrap">

    <header>
        <h1>{{ $judulHalaman }}</h1>
        <p class="sub">Catat respons anak terhadap kegiatan yang diamati, beserta aspek perkembangan dan strategi yang dipakai.</p>
    </header>

    <section class="info" aria-label="Informasi guru">
        <div><small>Guru Pengajar</small><b>{{ auth()->user()->nama }}</b></div>
        <div><small>Tahun Ajaran</small><b>{{ $tahunAjaran }}</b></div>
        <div><small>Status</small><b class="ok">Aktif</b></div>
    </section>

    @if ($errors->any())
        <div class="alert-err" role="alert">
            <b>Periksa kembali isian Anda:</b>
            <ul>@foreach ($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
        </div>
    @endif

    <form class="frm" method="POST"
          action="{{ $mode === 'edit' ? route('guru.observasi.update', $observasi) : route('guru.observasi.store') }}">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        {{-- Identitas --}}
        <section class="card">
            <h2>Identitas Observasi</h2>
            <div class="grid">
                <div class="f">
                    <label for="kelas_id">Pilih Kelas</label>
                    <select class="ctl @error('kelas_id') invalid @enderror" id="kelas_id" name="kelas_id" required>
                        <option value="">Pilih kelas</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected((string) $nilaiKelas === (string) $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f">
                    <label for="anak_id">Pilih Anak</label>
                    <select class="ctl @error('anak_id') invalid @enderror" id="anak_id" name="anak_id" required></select>
                </div>
                <div class="f">
                    <label for="tanggal">Tanggal Observasi</label>
                    <input class="ctl @error('tanggal') invalid @enderror" type="date" id="tanggal" name="tanggal"
                           value="{{ $nilaiTanggal }}" max="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
        </section>

        {{-- Kegiatan, aspek, strategi --}}
        <section class="card">
            <h2>Kegiatan dan Strategi</h2>
            <div class="grid">
                <div class="f">
                    <label for="aktivitas_id">Aktivitas Observasi</label>
                    <select class="ctl @error('aktivitas_id') invalid @enderror" id="aktivitas_id" name="aktivitas_id" required>
                        <option value="">Pilih aktivitas</option>
                        @foreach ($aktivitasList as $a)
                            <option value="{{ $a->id }}" @selected((string) old('aktivitas_id', $observasi?->aktivitas_id) === (string) $a->id)>{{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f">
                    <label for="aspek_id">Aspek Perkembangan</label>
                    <select class="ctl @error('aspek_id') invalid @enderror" id="aspek_id" name="aspek_id" required>
                        <option value="">Pilih aspek</option>
                        @foreach ($aspekList as $a)
                            <option value="{{ $a->id }}" @selected((string) old('aspek_id', $observasi?->aspek_id) === (string) $a->id)>{{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f">
                    <label for="strategi_id">Strategi Pembelajaran</label>
                    <select class="ctl @error('strategi_id') invalid @enderror" id="strategi_id" name="strategi_id" required>
                        <option value="">Pilih strategi</option>
                        @foreach ($strategiList as $s)
                            <option value="{{ $s->id }}" @selected((string) old('strategi_id', $observasi?->strategi_id) === (string) $s->id)>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- Respons anak --}}
        <section class="card">
            <h2 class="rapat">Respons Anak</h2>
            <p class="ket">Pilih satu respons yang paling menggambarkan keterlibatan anak pada kegiatan ini.</p>
            <div class="resp-grid" role="radiogroup" aria-label="Respons anak">
                @foreach ($responsList as $r)
                    <label class="resp r-{{ $r->value }}">
                        <input type="radio" name="kategori_respons" value="{{ $r->value }}"
                               @checked($nilaiRespons === $r->value) required>
                        <div>
                            <b>{{ $r->label() }}</b>
                            <span>{{ $deskripsiRespons[$r->value] ?? '' }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Catatan --}}
        <section class="card">
            <h2>Catatan Pendukung</h2>
            <div class="f">
                <label for="catatan_pendukung">Deskripsi pengamatan (opsional)</label>
                <textarea class="ctl @error('catatan_pendukung') invalid @enderror" id="catatan_pendukung" name="catatan_pendukung" rows="4"
                          maxlength="2000"
                          placeholder="Anak menunjukkan antusiasme tinggi saat bermain di sentra balok. Mampu bekerja sama dengan 2 teman sebaya namun perlu bimbingan...">{{ old('catatan_pendukung', $observasi?->catatan_pendukung) }}</textarea>
            </div>
        </section>

        {{-- Aksi --}}
        <div class="act">
            <a href="{{ route('guru.observasi.index') }}" class="btn sec">Batal</a>
            <button type="submit" class="btn pri">{{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Observasi' }}</button>
        </div>
    </form>
</main>

<script>
    // Daftar anak mengikuti kelas yang dipilih
    (() => {
        const anak = @json($anakList);
        const kelas = document.getElementById('kelas_id');
        const pilih = document.getElementById('anak_id');
        const awal = @json((string) $nilaiAnak);

        const render = (terpilih) => {
            pilih.length = 0;
            pilih.add(new Option(kelas.value ? 'Pilih anak' : 'Pilih kelas terlebih dahulu', ''));
            anak.filter(a => String(a.kelas_id) === kelas.value)
                .forEach(a => pilih.add(new Option(a.nama, a.id, false, String(a.id) === String(terpilih))));
            pilih.disabled = !kelas.value;
        };

        kelas.addEventListener('change', () => render(''));
        render(awal);
    })();
</script>
</body>
</html>