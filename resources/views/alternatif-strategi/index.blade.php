@php
    use Illuminate\Pagination\LengthAwarePaginator;

    /*
    |--------------------------------------------------------------------------
    | DATA (diisi langsung di view, tanpa controller)
    |--------------------------------------------------------------------------
    | Ganti isi array ini dengan data asli kapan saja.
    */
    $semuaKelas = ['TKA - Melati', 'TKA - Mawar'];

    $semuaAnak = collect([
        [
            'nama' => 'Dedek', 'usia' => '4 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Kognitif', 'prioritas' => 'Tinggi', 'status' => 'Aktif',
            'kegiatan' => 'Menyusun Balok', 'respons' => 'Kurang tertarik', 'jumlah' => 3, 'minimal' => 3, 'tone' => 'warning',
            'catatan' => 'Cenderung berpindah kegiatan setelah 2–3 menit dan memilih bermain sendiri.',
            'strategi' => ['nama' => 'Media Balok Sesuai Minat Pokok', 'deskripsi' => 'Memakai balok dengan tema yang disukai anak, lalu dibimbing pelan-pelan.'],
            'rekomendasi' => ['nama' => 'Variasi Balok Bercerita Menarik', 'deskripsi' => 'Gunakan balok bernarasi pendek dan beri kebebasan memilih alur cerita.'],
        ],
        [
            'nama' => 'Citra Lestari', 'usia' => '4 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Sosial Emosional', 'prioritas' => 'Sedang', 'status' => 'Aktif',
            'kegiatan' => 'Bercerita Depan Kelas', 'respons' => 'Butuh stimulus berulang', 'jumlah' => 4, 'minimal' => 3, 'tone' => 'primary',
            'catatan' => 'Tampak ragu dan menunduk saat diminta berbicara di depan teman.',
            'strategi' => ['nama' => 'Cerita Terbuka Langsung', 'deskripsi' => 'Memberikan kesempatan bercerita secara langsung di depan kelas setiap hari.'],
            'rekomendasi' => ['nama' => 'Kartu Cerita Bergambar Bertahap', 'deskripsi' => 'Fasilitasi stimulus bertahap, dari bercerita berpasangan lalu kelompok kecil.'],
        ],
        [
            'nama' => 'Bimo Pratama', 'usia' => '4 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Motorik', 'prioritas' => 'Sedang', 'status' => 'Aktif',
            'kegiatan' => 'Permainan Gerak & Keseimbangan', 'respons' => 'Butuh pendampingan', 'jumlah' => 5, 'minimal' => 3, 'tone' => 'primary',
            'catatan' => 'Sering kehilangan keseimbangan dan menolak melanjutkan permainan.',
            'strategi' => ['nama' => 'Bantuan Penuh oleh Guru', 'deskripsi' => 'Guru memegang tangan anak sepanjang lintasan keseimbangan.'],
            'rekomendasi' => ['nama' => 'Gradual Fading Bersama Teman Sebaya', 'deskripsi' => 'Kurangi bantuan guru secara bertahap dan libatkan teman sebaya.'],
        ],
        [
            'nama' => 'Gita Amindya', 'usia' => '4 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Bahasa', 'prioritas' => 'Tinggi', 'status' => 'Aktif',
            'kegiatan' => 'Puzzle & Menyelesaikan Gambar', 'respons' => 'Durasi respons > 5 menit', 'jumlah' => 3, 'minimal' => 3, 'tone' => 'danger',
            'catatan' => 'Tampak kesulitan memusatkan perhatian pada puzzle yang rumit.',
            'strategi' => ['nama' => 'Penyelesaian Mandiri Sekaligus', 'deskripsi' => 'Memberikan seluruh puzzle sekaligus tanpa pembagian langkah.'],
            'rekomendasi' => ['nama' => 'Metode Chunking & Timer Visual Cerita', 'deskripsi' => 'Bagi kegiatan jadi tahap kecil dan beri pengingat waktu visual.'],
        ],
        [
            'nama' => 'Raka Aditya', 'usia' => '5 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Bahasa', 'prioritas' => 'Rendah', 'status' => 'Aktif',
            'kegiatan' => 'Mengenal Huruf', 'respons' => 'Kurang fokus', 'jumlah' => 2, 'minimal' => 3, 'tone' => 'warning',
            'catatan' => 'Belum konsisten mengikuti kegiatan mengenal huruf.',
            'strategi' => null,
            'rekomendasi' => null,
        ],
        [
            'nama' => 'Salsa Nabila', 'usia' => '5 tahun', 'kelas' => 'TKA - Melati',
            'aspek' => 'Sosial Emosional', 'prioritas' => 'Sedang', 'status' => 'Aktif',
            'kegiatan' => 'Bermain Peran', 'respons' => 'Menarik diri', 'jumlah' => 4, 'minimal' => 3, 'tone' => 'primary',
            'catatan' => 'Cenderung menjauh saat bermain kelompok.',
            'strategi' => ['nama' => 'Ajakan Verbal Guru', 'deskripsi' => 'Guru mengajak anak bergabung secara lisan.'],
            'rekomendasi' => ['nama' => 'Peran Kecil dengan Teman Akrab', 'deskripsi' => 'Mulai dari peran sederhana bersama satu teman dekat.'],
        ],
        [
            'nama' => 'Fajar Ramadhan', 'usia' => '4 tahun', 'kelas' => 'TKA - Mawar',
            'aspek' => 'Motorik', 'prioritas' => 'Tinggi', 'status' => 'Aktif',
            'kegiatan' => 'Menggunting Pola', 'respons' => 'Mudah frustrasi', 'jumlah' => 3, 'minimal' => 3, 'tone' => 'danger',
            'catatan' => 'Berhenti saat gunting tidak mengikuti garis.',
            'strategi' => ['nama' => 'Contoh Langsung dari Guru', 'deskripsi' => 'Guru mencontohkan menggunting di depan anak.'],
            'rekomendasi' => ['nama' => 'Garis Tebal & Latihan Meremas Plastisin', 'deskripsi' => 'Perkuat otot jari sebelum menggunting pola tipis.'],
        ],
        [
            'nama' => 'Nayla Putri', 'usia' => '5 tahun', 'kelas' => 'TKA - Mawar',
            'aspek' => 'Kognitif', 'prioritas' => 'Sedang', 'status' => 'Aktif',
            'kegiatan' => 'Mengelompokkan Warna', 'respons' => 'Ragu menjawab', 'jumlah' => 1, 'minimal' => 3, 'tone' => 'warning',
            'catatan' => 'Masih terlihat ragu saat memilih kelompok warna.',
            'strategi' => null,
            'rekomendasi' => null,
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
    $kelasDipilih   = request('kelas', $semuaKelas[0]);
    $pencarian      = trim((string) request('q', ''));
    $filterStatus   = request('status', 'Aktif');
    $filterAspek    = request('aspek', 'Semua');
    $filterPrioritas = request('prioritas', 'Semua');
    $perPage        = 4;
    $halaman        = max((int) request('page', 1), 1);

    $anakKelas = $semuaAnak->where('kelas', $kelasDipilih)->values();

    $anakTersaring = $anakKelas
        ->when($filterStatus !== 'Semua', fn ($c) => $c->where('status', $filterStatus))
        ->when($filterAspek !== 'Semua', fn ($c) => $c->where('aspek', $filterAspek))
        ->when($filterPrioritas !== 'Semua', fn ($c) => $c->where('prioritas', $filterPrioritas))
        ->when($pencarian !== '', function ($c) use ($pencarian) {
            $kunci = mb_strtolower($pencarian);
            return $c->filter(function ($a) use ($kunci) {
                $teks = implode(' ', [
                    data_get($a, 'nama', ''),
                    data_get($a, 'kegiatan', ''),
                    data_get($a, 'respons', ''),
                    data_get($a, 'strategi.nama', ''),
                    data_get($a, 'rekomendasi.nama', ''),
                ]);
                return mb_strpos(mb_strtolower($teks), $kunci) !== false;
            });
        })
        ->values();

    $paginator = new LengthAwarePaginator(
        $anakTersaring->forPage($halaman, $perPage)->values(),
        $anakTersaring->count(),
        $perPage,
        $halaman,
        ['path' => url()->current(), 'query' => request()->query()]
    );

    /*
    |--------------------------------------------------------------------------
    | RINGKASAN
    |--------------------------------------------------------------------------
    */
    $jumlahPola        = $anakKelas->count();
    $jumlahSaran       = $anakKelas->filter(fn ($a) => filled($a['rekomendasi']))->count();
    $jumlahBelumCukup  = $anakKelas->filter(fn ($a) => blank($a['rekomendasi']))->count();

    $daftarAspek = ['Semua', 'Kognitif', 'Bahasa', 'Motorik', 'Sosial Emosional'];
    $daftarPrioritas = ['Semua', 'Tinggi', 'Sedang', 'Rendah'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternatif Strategi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand: #4f46e5;
            --brand-soft: #eef2ff;
            --brand-dark: #312e81;
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
        }
        body { background: #f5f6fa; color: var(--ink); font-size: .9rem; }
        .page-wrap { max-width: 1140px; }
        .text-muted-2 { color: var(--muted) !important; }
        .breadcrumb-mini { font-size: .78rem; color: var(--muted); }
        .breadcrumb-mini .current { color: var(--ink); font-weight: 500; }

        .card-soft { background: #fff; border: 1px solid var(--line); border-radius: 14px; }

        /* Statistik */
        .stat-card { padding: 1rem 1.1rem; display: flex; justify-content: space-between; align-items: center; gap: .75rem; }
        .stat-label { font-size: .75rem; color: var(--muted); margin-bottom: .15rem; }
        .stat-value { font-size: 1.85rem; font-weight: 700; line-height: 1.1; }
        .stat-value small { font-size: .8rem; font-weight: 500; color: var(--muted); }
        .stat-note { font-size: .72rem; color: var(--muted); margin-top: .15rem; }
        .stat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
        .ic-amber { background: #fef3c7; color: #d97706; }
        .ic-indigo { background: var(--brand-soft); color: var(--brand); }
        .ic-gray { background: #f3f4f6; color: #4b5563; }

        /* Pedoman */
        .pedoman-box { border: 1px solid var(--line); border-radius: 12px; padding: .85rem 1rem; height: 100%; background: #fafafa; }
        .pedoman-title { font-weight: 600; font-size: .8rem; display: flex; align-items: center; gap: .4rem; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-amber { background: #f59e0b; } .dot-indigo { background: var(--brand); } .dot-green { background: #10b981; }
        .pedoman-text { font-size: .76rem; color: var(--muted); margin: .35rem 0 0; line-height: 1.5; }

        .btn-brand { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover { background: #4338ca; border-color: #4338ca; color: #fff; }
        .btn-outline-soft { border: 1px solid var(--brand); color: var(--brand); background: var(--brand-soft); font-size: .72rem; font-weight: 500; border-radius: 999px; padding: .2rem .7rem; }
        .link-brand { color: var(--brand); font-size: .78rem; font-weight: 500; text-decoration: none; }
        .link-brand:hover { text-decoration: underline; }

        .badge-status { background: #dcfce7; color: #15803d; font-weight: 500; border-radius: 999px; font-size: .72rem; padding: .3rem .7rem; }

        /* Form */
        .form-control, .form-select { font-size: .82rem; border-color: #d1d5db; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(79,70,229,.12); }
        .filter-select { border-radius: 8px; padding-top: .3rem; padding-bottom: .3rem; width: auto; min-width: 130px; }

        /* Tabel */
        .tbl-head { background: #f9fafb; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); font-size: .68rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--muted); }
        .tbl-row { border-bottom: 1px solid var(--line); }
        .tbl-row:last-child { border-bottom: 0; }
        .mobile-label { font-size: .68rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--muted); margin-bottom: .4rem; }

        .avatar { width: 36px; height: 36px; border-radius: 50%; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .85rem; }
        .av-0 { background: #fef3c7; color: #b45309; }
        .av-1 { background: #dbeafe; color: #1d4ed8; }
        .av-2 { background: #dcfce7; color: #15803d; }
        .av-3 { background: #ccfbf1; color: #0f766e; }

        .pill { display: inline-block; font-size: .72rem; font-weight: 500; padding: .2rem .6rem; border-radius: 999px; margin-top: .35rem; }
        .pill-warning { background: #fef3c7; color: #92400e; }
        .pill-primary { background: #dbeafe; color: #1e40af; }
        .pill-danger  { background: #fee2e2; color: #b91c1c; }

        .box-strategi { background: #f9fafb; border: 1px solid #f0f0f2; border-radius: 10px; padding: .7rem .8rem; }
        .box-rekom { background: var(--brand-soft); border-radius: 10px; padding: .7rem .8rem; }
        .box-kosong { border: 1px dashed #d1d5db; border-radius: 10px; padding: .7rem .8rem; }
        .box-title { font-size: .8rem; font-weight: 600; margin: 0; }
        .box-text { font-size: .75rem; color: #4b5563; margin: .25rem 0 0; line-height: 1.5; }

        .btn-tambah { width: 100%; border: 1px dashed #818cf8; color: var(--brand); background: transparent; border-radius: 8px; font-size: .75rem; font-weight: 500; padding: .35rem .6rem; margin-top: .55rem; transition: background .15s; }
        .btn-tambah:hover { background: var(--brand-soft); }

        .page-link { font-size: .78rem; color: var(--brand); }
        .page-item.active .page-link { background: var(--brand); border-color: var(--brand); color: #fff; }
        .pagination { margin-bottom: 0; }
    </style>
</head>
<body>

<div class="container page-wrap py-4">

    {{-- ========== HEADER HALAMAN ========== --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-3">
        <div>
            <div class="breadcrumb-mini mb-1">
                Rekomendasi <span class="mx-1">/</span> <span class="current">Alternatif Strategi</span>
            </div>
            <h1 class="h4 fw-bold mb-1">Alternatif Strategi</h1>
            <p class="text-muted-2 mb-0" style="font-size:.82rem;">
                Kelola dan tentukan alternatif strategi intervensi berdasarkan aturan dan pola observasi
                <span class="mx-1">•</span> {{ $kelasDipilih }}
            </p>
        </div>

        <form method="GET" action="{{ url()->current() }}">
            <input type="hidden" name="q" value="{{ $pencarian }}">
            <select name="kelas" class="form-select" style="min-width:170px;" onchange="this.form.submit()">
                @foreach ($semuaKelas as $kelas)
                    <option value="{{ $kelas }}" @selected($kelas === $kelasDipilih)>{{ $kelas }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- ========== RINGKASAN ========== --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card-soft stat-card h-100">
                <div>
                    <div class="stat-label">Anak dengan pola</div>
                    <div class="stat-value">{{ $jumlahPola }} <small>anak</small></div>
                    <div class="stat-note">Ambang pemicu terpenuhi</div>
                </div>
                <div class="stat-icon ic-amber"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-soft stat-card h-100">
                <div>
                    <div class="stat-label">Saran tersedia</div>
                    <div class="stat-value" style="color:var(--brand);">{{ $jumlahSaran }} <small>strategi</small></div>
                    <div class="stat-note">Siap ditinjau guru</div>
                </div>
                <div class="stat-icon ic-indigo"><i class="bi bi-lightbulb"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-soft stat-card h-100">
                <div>
                    <div class="stat-label">Belum cukup data</div>
                    <div class="stat-value" style="color:#d97706;">{{ $jumlahBelumCukup }} <small>anak</small></div>
                    <div class="stat-note">Menunggu observasi tambahan</div>
                </div>
                <div class="stat-icon ic-gray"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
    </div>

    {{-- ========== PEDOMAN ========== --}}
    <div class="card-soft p-3 p-md-4 mb-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-3">
            <div>
                <div class="fw-semibold" style="font-size:.9rem;">Ringkasan Pedoman &amp; Standar Intervensi PAUD</div>
                <div class="text-muted-2" style="font-size:.75rem;">Prinsip penyusunan alternatif strategi berbasis data observasi.</div>
            </div>
            <button type="button" class="btn btn-outline-soft align-self-start">Kustomisasi Pedoman</button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="pedoman-box">
                    <div class="pedoman-title"><span class="dot dot-amber"></span> Aturan Pemicu Pola</div>
                    <p class="pedoman-text">Evaluasi hanya dimulai jika respons yang sama muncul minimal 3 kali dalam periode observasi yang ditentukan.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="pedoman-box">
                    <div class="pedoman-title"><span class="dot dot-indigo"></span> Prinsip Diferensiasi</div>
                    <p class="pedoman-text">Pendekatan berbeda untuk setiap anak sesuai kebutuhan, minat, dan perkembangannya masing-masing.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="pedoman-box">
                    <div class="pedoman-title"><span class="dot dot-green"></span> Fleksibilitas Alternatif</div>
                    <p class="pedoman-text">Guru dapat menambah atau mengubah alternatif strategi sesuai kondisi nyata di kelas dan RPP harian.</p>
                </div>
            </div>
        </div>

        <div class="text-end mt-3">
            <a href="#" class="link-brand">Lihat Selengkapnya <i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    {{-- ========== DAFTAR RUMUSAN ========== --}}
    <div class="card-soft overflow-hidden">

        <div class="p-3 p-md-4 pb-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <h2 class="h6 fw-semibold mb-0">Daftar Rumusan Aturan Intervensi</h2>
                <span class="badge-status align-self-start"><i class="bi bi-check-circle-fill me-1"></i>Status: Otomatis Aktif</span>
            </div>

            <form method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="kelas" value="{{ $kelasDipilih }}">

                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" name="q" value="{{ $pencarian }}" class="form-control"
                           placeholder="Cari nama anak atau strategi...">
                    <button class="btn btn-brand" type="submit">Cari</button>
                    @if ($pencarian !== '' || $filterAspek !== 'Semua' || $filterPrioritas !== 'Semua' || $filterStatus !== 'Aktif')
                        <a href="{{ url()->current() }}?kelas={{ urlencode($kelasDipilih) }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select name="status" class="form-select filter-select" onchange="this.form.submit()">
                        @foreach (['Aktif', 'Semua'] as $opsi)
                            <option value="{{ $opsi }}" @selected($filterStatus === $opsi)>Status: {{ $opsi }}</option>
                        @endforeach
                    </select>

                    <select name="aspek" class="form-select filter-select" onchange="this.form.submit()">
                        @foreach ($daftarAspek as $opsi)
                            <option value="{{ $opsi }}" @selected($filterAspek === $opsi)>Aspek: {{ $opsi }}</option>
                        @endforeach
                    </select>

                    <select class="form-select filter-select" disabled>
                        <option>Ambang Pemicu: ≥ 3 observasi</option>
                    </select>

                    <select name="prioritas" class="form-select filter-select" onchange="this.form.submit()">
                        @foreach ($daftarPrioritas as $opsi)
                            <option value="{{ $opsi }}" @selected($filterPrioritas === $opsi)>Prioritas: {{ $opsi }}</option>
                        @endforeach
                    </select>

                    <span class="ms-lg-auto text-muted-2" style="font-size:.75rem;">
                        Menampilkan {{ $paginator->count() }} dari {{ $paginator->total() }} pola pilihan
                    </span>
                </div>
            </form>
        </div>

        {{-- Header tabel (desktop) --}}
        <div class="tbl-head d-none d-lg-flex px-4 py-2">
            <div class="col-lg-3 pe-3">Anak</div>
            <div class="col-lg-3 pe-3">Pola perilaku anak</div>
            <div class="col-lg-3 pe-3">Strategi yang digunakan</div>
            <div class="col-lg-3">Rekomendasi strategi</div>
        </div>

        {{-- Baris --}}
        @forelse ($paginator as $i => $anak)
            @php
                $strategi    = $anak['strategi'];
                $rekomendasi = $anak['rekomendasi'];
            @endphp

            <div class="tbl-row px-3 px-md-4 py-3">
                <div class="row g-3">

                    {{-- Anak --}}
                    <div class="col-12 col-lg-3">
                        <div class="mobile-label d-lg-none">Anak</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar av-{{ $i % 4 }}">{{ mb_strtoupper(mb_substr($anak['nama'], 0, 1)) }}</div>
                            <div class="min-w-0">
                                <div class="fw-semibold" style="font-size:.85rem;">{{ $anak['nama'] }}</div>
                                <div class="text-muted-2" style="font-size:.74rem;">{{ $anak['usia'] }} <span class="mx-1">•</span> {{ $anak['kelas'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Pola --}}
                    <div class="col-12 col-lg-3">
                        <div class="mobile-label d-lg-none">Pola perilaku anak</div>
                        <div class="fw-semibold" style="font-size:.82rem;">{{ $anak['kegiatan'] }}</div>
                        <span class="pill pill-{{ $anak['tone'] }}">
                            <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle;"></i>{{ $anak['respons'] }} ({{ $anak['jumlah'] }}x)
                        </span>
                        @if ($anak['catatan'])
                            <p class="text-muted-2 mt-2 mb-0" style="font-size:.74rem;line-height:1.5;">{{ $anak['catatan'] }}</p>
                        @endif
                    </div>

                    {{-- Strategi digunakan --}}
                    <div class="col-12 col-lg-3">
                        <div class="mobile-label d-lg-none">Strategi yang digunakan</div>
                        @if ($strategi)
                            <div class="box-strategi">
                                <p class="box-title">{{ $strategi['nama'] }}</p>
                                <p class="box-text">{{ $strategi['deskripsi'] }}</p>
                            </div>
                        @else
                            <div class="box-kosong">
                                <p class="box-text mt-0">Belum ada informasi strategi yang digunakan.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Rekomendasi --}}
                    <div class="col-12 col-lg-3">
                        <div class="mobile-label d-lg-none">Rekomendasi strategi</div>
                        @if ($rekomendasi)
                            <div class="box-rekom">
                                <p class="box-title" style="color:var(--brand-dark);">{{ $rekomendasi['nama'] }}</p>
                                <p class="box-text">{{ $rekomendasi['deskripsi'] }}</p>
                            </div>
                        @else
                            <div class="box-kosong">
                                <p class="box-title">Belum ada alternatif</p>
                                <p class="box-text">
                                    Data observasi belum cukup ({{ $anak['jumlah'] }} dari minimal {{ $anak['minimal'] }} kejadian).
                                    Saran akan muncul setelah syarat terpenuhi.
                                </p>
                            </div>
                        @endif

                        <button type="button" class="btn-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Alternatif
                        </button>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center px-4 py-5">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-light" style="width:56px;height:56px;">
                    <i class="bi bi-file-earmark-text text-muted fs-4"></i>
                </div>
                @if ($pencarian !== '')
                    <h3 class="h6 fw-semibold">Data tidak ditemukan</h3>
                    <p class="text-muted-2 mb-0" style="font-size:.82rem;">Tidak ada anak atau strategi yang sesuai dengan pencarian "{{ $pencarian }}".</p>
                @else
                    <h3 class="h6 fw-semibold">Belum ada pola respons</h3>
                    <p class="text-muted-2 mb-0" style="font-size:.82rem;">Belum ada pola respons yang perlu ditinjau di kelas ini.</p>
                @endif
            </div>
        @endforelse

        {{-- Footer --}}
        @if ($paginator->total() > 0)
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 px-3 px-md-4 py-3 border-top">
                <span class="text-muted-2" style="font-size:.75rem;">
                    Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} rumusan tersimpan
                </span>
                @if ($paginator->hasPages())
                    {{ $paginator->links('pagination::bootstrap-5') }}
                @endif
            </div>
        @endif

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>