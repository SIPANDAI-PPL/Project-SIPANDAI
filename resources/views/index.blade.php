{{--
    PBI-03: Kelola Kelas, Tahun Ajaran, dan Penugasan Guru Pengampu
    File ini hanya berisi konten utama (tanpa sidebar dan navbar).

    Saat layout terpusat dari lead sudah tersedia, tambahkan di baris paling atas:
        @extends('layouts.app')
        @section('content')
    dan di baris paling bawah:
        @endsection
    (sesuaikan nama layout dan section dengan yang dipakai di project)
--}}

@php
    // Data dummy sementara, belum terhubung ke database.
    // Ganti dengan data dari controller setelah migrasi tersedia.

    $daftarTahunAjaran = ['2026/2027', '2025/2026', '2024/2025'];
    $tahunAjaranAktif  = '2026/2027';

    $daftarKelompok = ['Lebah', 'Kupu-kupu', 'Kelinci', 'Bintang'];

    $daftarGuru = [
        ['id' => 1, 'nama' => 'Mia Stacia',   'email' => 'mia.stacia@tkceria.sch.id',   'aktif' => true],
        ['id' => 2, 'nama' => 'Ayu Rizki',    'email' => 'ayu.rizki@tkceria.sch.id',    'aktif' => true],
        ['id' => 3, 'nama' => 'Dina Nugraha', 'email' => 'dina.nugraha@tkceria.sch.id', 'aktif' => false],
    ];

    $daftarKelas = [
        ['id' => 1, 'nama' => 'Kelas Ceria',  'tahun' => '2026/2027', 'kelompok' => 'Lebah',     'guru' => [1]],
        ['id' => 2, 'nama' => 'Kelas Pelangi', 'tahun' => '2026/2027', 'kelompok' => 'Kupu-kupu', 'guru' => [2]],
        ['id' => 3, 'nama' => 'Kelas Ceria',  'tahun' => '2025/2026', 'kelompok' => 'Lebah',     'guru' => [1, 2]],
    ];

    $riwayatPenugasan = [
        ['tanggal' => '16 Jul 2024, 06:15 WIB', 'kelas' => 'Ceria', 'kelompok' => 'Lebah', 'guru' => 'Mia Stacia',   'status' => 'Berhasil'],
        ['tanggal' => '15 Jul 2024, 06:15 WIB', 'kelas' => 'Ceria', 'kelompok' => 'Lebah', 'guru' => 'Ayu Rizki',    'status' => 'Berhasil'],
        ['tanggal' => '13 Jul 2024, 06:15 WIB', 'kelas' => 'Ceria', 'kelompok' => 'Lebah', 'guru' => 'Dina Nugraha', 'status' => 'Gagal'],
    ];

    // Ambil nama guru berdasarkan id
    $namaGuru = collect($daftarGuru)->pluck('nama', 'id');

    // Inisial dua huruf untuk avatar guru
    $ambilInisial = function ($nama) {
        return mb_strtoupper(
            collect(explode(' ', $nama))->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode('')
        );
    };
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .kelas-halaman {
        --warna-utama: #2b2580;
        --warna-utama-gelap: #211c66;
        --warna-utama-muda: #eceafb;
        --warna-teks: #1a1a2e;
        --warna-teks-redup: #6b7280;
        --warna-garis: #d9dbe3;
        --warna-latar: #f8f9fc;
        --warna-sukses: #166534;
        --warna-sukses-muda: #dcf5e4;
        --warna-peringatan: #b45309;
        --warna-peringatan-muda: #fdebd8;
        --warna-bahaya: #b91c1c;
        --warna-bahaya-muda: #fde4e4;

        box-sizing: border-box;
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 16px 48px;
        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', sans-serif;
        color: var(--warna-teks);
        line-height: 1.5;
    }

    .kelas-halaman *,
    .kelas-halaman *::before,
    .kelas-halaman *::after {
        box-sizing: inherit;
    }

    /* Header halaman */
    .kelas-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .kelas-judul {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
    }

    .kelas-aksi-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
    }

    .kelas-pilih-tahun {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: 0.75rem;
        color: var(--warna-teks-redup);
    }

    /* Kartu */
    .kelas-kartu {
        background: #fff;
        border: 1px solid var(--warna-garis);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .kelas-kartu-judul {
        margin: 0 0 16px;
        font-size: 1rem;
        font-weight: 600;
    }

    /* Form */
    .kelas-grid-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .kelas-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .kelas-field label,
    .kelas-label {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .kelas-input,
    .kelas-select {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--warna-teks);
        background: #fff;
        border: 1px solid var(--warna-garis);
        border-radius: 10px;
    }

    .kelas-input:disabled {
        background: #dcdcdc;
        color: #4b5563;
        cursor: not-allowed;
    }

    .kelas-input:focus,
    .kelas-select:focus,
    .kelas-tombol:focus-visible,
    .kelas-pilih-guru:focus-visible {
        outline: 2px solid var(--warna-utama);
        outline-offset: 2px;
    }

    .kelas-input.kelas-salah,
    .kelas-select.kelas-salah {
        border-color: var(--warna-bahaya);
    }

    .kelas-pesan-salah {
        min-height: 1.1em;
        font-size: 0.78rem;
        color: var(--warna-bahaya);
    }

    /* Pencarian guru */
    .kelas-cari {
        position: relative;
        margin-bottom: 8px;
    }

    .kelas-cari svg {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: var(--warna-teks-redup);
    }

    .kelas-cari .kelas-input {
        padding-left: 42px;
    }

    /* Daftar guru */
    .kelas-daftar-guru {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .kelas-baris-guru {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 8px;
        border-bottom: 1px solid #eceef3;
    }

    .kelas-baris-guru:last-child {
        border-bottom: 0;
    }

    .kelas-avatar {
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--warna-sukses-muda);
        color: var(--warna-sukses);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .kelas-info-guru {
        flex: 1 1 auto;
        min-width: 0;
    }

    .kelas-nama-guru {
        font-weight: 600;
    }

    .kelas-email-guru {
        margin-left: 4px;
        font-size: 0.82rem;
        color: var(--warna-teks-redup);
        overflow-wrap: anywhere;
    }

    .kelas-lencana {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 1px 10px;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 999px;
    }

    .kelas-lencana::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .kelas-lencana-aktif {
        background: var(--warna-sukses-muda);
        color: var(--warna-sukses);
    }

    .kelas-lencana-nonaktif {
        background: var(--warna-peringatan-muda);
        color: var(--warna-peringatan);
    }

    .kelas-pilih-guru {
        flex: 0 0 auto;
        padding: 6px 14px;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--warna-utama);
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px;
        cursor: pointer;
    }

    .kelas-pilih-guru:hover {
        background: var(--warna-utama-muda);
    }

    .kelas-pilih-guru[aria-pressed="true"] {
        background: var(--warna-utama);
        color: #fff;
    }

    .kelas-pilih-guru:disabled {
        color: #9ca3af;
        cursor: not-allowed;
        background: transparent;
    }

    .kelas-kosong {
        padding: 20px 8px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--warna-teks-redup);
    }

    /* Tombol */
    .kelas-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 46px;
        padding: 0 20px;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid var(--warna-utama);
        cursor: pointer;
    }

    .kelas-tombol-utama {
        background: var(--warna-utama);
        color: #fff;
    }

    .kelas-tombol-utama:hover {
        background: var(--warna-utama-gelap);
    }

    .kelas-tombol-batal {
        background: #fff;
        color: var(--warna-teks);
        border-color: var(--warna-garis);
    }

    .kelas-tombol-batal:hover {
        background: var(--warna-latar);
    }

    .kelas-baris-tombol {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 16px;
    }

    /* Notifikasi */
    .kelas-notifikasi {
        margin-bottom: 16px;
        padding: 12px 16px;
        font-size: 0.9rem;
        border-radius: 10px;
    }

    .kelas-notifikasi[hidden] {
        display: none;
    }

    .kelas-notifikasi-sukses {
        background: var(--warna-sukses-muda);
        color: var(--warna-sukses);
    }

    /* Tabel */
    .kelas-tabel-bungkus {
        overflow-x: auto;
        border: 1px solid var(--warna-garis);
        border-radius: 12px;
    }

    .kelas-tabel {
        width: 100%;
        min-width: 560px;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .kelas-tabel th {
        padding: 10px 16px;
        text-align: left;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--warna-teks-redup);
        background: var(--warna-latar);
        white-space: nowrap;
    }

    .kelas-tabel td {
        padding: 12px 16px;
        border-top: 1px solid #eceef3;
        vertical-align: middle;
    }

    .kelas-chip {
        display: inline-block;
        margin: 2px 4px 2px 0;
        padding: 2px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--warna-utama);
        background: var(--warna-utama-muda);
        border-radius: 999px;
    }

    .kelas-status {
        display: inline-block;
        padding: 2px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 999px;
    }

    .kelas-status-berhasil {
        background: var(--warna-sukses-muda);
        color: var(--warna-sukses);
    }

    .kelas-status-gagal {
        background: var(--warna-peringatan-muda);
        color: var(--warna-peringatan);
    }

    /* Tampilan mobile */
    @media (max-width: 640px) {
        .kelas-judul {
            font-size: 1.3rem;
        }

        .kelas-header,
        .kelas-aksi-header {
            width: 100%;
        }

        .kelas-aksi-header > * {
            flex: 1 1 100%;
        }

        .kelas-grid-form,
        .kelas-baris-tombol {
            grid-template-columns: 1fr;
        }

        .kelas-kartu {
            padding: 16px;
        }

        .kelas-baris-guru {
            flex-wrap: wrap;
        }

        .kelas-pilih-guru {
            margin-left: 56px;
        }
    }
</style>

<div class="kelas-halaman" id="halamanKelas">

    {{-- Header halaman --}}
    <div class="kelas-header">
        <h1 class="kelas-judul">Kelola Kelas dan Penugasan Guru</h1>

        <div class="kelas-aksi-header">
            <div class="kelas-pilih-tahun">
                <label for="filterTahun">Tahun Ajaran</label>
                <select id="filterTahun" class="kelas-select">
                    @foreach ($daftarTahunAjaran as $tahun)
                        <option value="{{ $tahun }}" @selected($tahun === $tahunAjaranAktif)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" id="tombolTambahKelas" class="kelas-tombol kelas-tombol-utama">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Kelas
            </button>
        </div>
    </div>

    <div id="notifikasiKelas" class="kelas-notifikasi kelas-notifikasi-sukses" role="status" hidden></div>

    {{-- Form informasi kelas dan penugasan --}}
    <section class="kelas-kartu" id="kartuFormKelas" aria-labelledby="judulFormKelas">
        <h2 class="kelas-kartu-judul" id="judulFormKelas">Informasi kelas dan penugasan</h2>

        <form id="formKelas" action="#" method="POST" novalidate>
            @csrf

            <div class="kelas-grid-form">
                <div class="kelas-field">
                    <label for="namaKelas">Nama Kelas</label>
                    <input type="text" id="namaKelas" name="nama_kelas" class="kelas-input" placeholder="Contoh: Kelas Ceria" autocomplete="off">
                    <span class="kelas-pesan-salah" data-galat="namaKelas"></span>
                </div>

                <div class="kelas-field">
                    <label for="tahunAjaran">Tahun Ajaran</label>
                    <input type="text" id="tahunAjaran" name="tahun_ajaran" class="kelas-input" value="{{ $tahunAjaranAktif }}" disabled>
                    <span class="kelas-pesan-salah"></span>
                </div>
            </div>

            <div class="kelas-field" style="margin-bottom: 16px;">
                <label for="kelompok">Kelompok</label>
                <select id="kelompok" name="kelompok" class="kelas-select">
                    <option value="">Pilih kelompok</option>
                    @foreach ($daftarKelompok as $kelompok)
                        <option value="{{ $kelompok }}">{{ $kelompok }}</option>
                    @endforeach
                </select>
                <span class="kelas-pesan-salah" data-galat="kelompok"></span>
            </div>

            <div class="kelas-field">
                <span class="kelas-label" id="labelGuru">Pilih Guru Pengampu</span>

                <div class="kelas-cari">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" id="cariGuru" class="kelas-input" placeholder="Cari nama atau email guru" aria-labelledby="labelGuru" autocomplete="off">
                </div>
                <span class="kelas-pesan-salah" data-galat="guru"></span>

                <ul class="kelas-daftar-guru" id="daftarGuru">
                    @foreach ($daftarGuru as $guru)
                        <li class="kelas-baris-guru"
                            data-guru-id="{{ $guru['id'] }}"
                            data-cari="{{ mb_strtolower($guru['nama'] . ' ' . $guru['email']) }}">
                            <div class="kelas-avatar" aria-hidden="true">{{ $ambilInisial($guru['nama']) }}</div>

                            <div class="kelas-info-guru">
                                <div>
                                    <span class="kelas-nama-guru">{{ $guru['nama'] }}</span>
                                    <span class="kelas-email-guru">{{ $guru['email'] }}</span>
                                </div>
                                @if ($guru['aktif'])
                                    <span class="kelas-lencana kelas-lencana-aktif">Aktif</span>
                                @else
                                    <span class="kelas-lencana kelas-lencana-nonaktif">Nonaktif</span>
                                @endif
                            </div>

                            {{-- Guru nonaktif tidak bisa dipilih sebagai pengampu --}}
                            <button type="button"
                                    class="kelas-pilih-guru"
                                    aria-pressed="false"
                                    @disabled(! $guru['aktif'])>
                                Pilih
                            </button>
                        </li>
                    @endforeach
                </ul>

                <p class="kelas-kosong" id="guruTidakDitemukan" hidden>Guru tidak ditemukan. Coba kata kunci lain.</p>
            </div>

            <div class="kelas-baris-tombol">
                <button type="submit" class="kelas-tombol kelas-tombol-utama">Simpan</button>
                <button type="button" id="tombolBatal" class="kelas-tombol kelas-tombol-batal">Batal</button>
            </div>
        </form>
    </section>

    {{-- Daftar kelas sesuai tahun ajaran yang dipilih --}}
    <section class="kelas-kartu" aria-labelledby="judulDaftarKelas">
        <h2 class="kelas-kartu-judul" id="judulDaftarKelas">Daftar kelas</h2>

        <div class="kelas-tabel-bungkus">
            <table class="kelas-tabel">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Kelompok</th>
                        <th>Guru Pengampu</th>
                    </tr>
                </thead>
                <tbody id="isiDaftarKelas">
                    @foreach ($daftarKelas as $kelas)
                        <tr data-tahun="{{ $kelas['tahun'] }}">
                            <td>{{ $kelas['nama'] }}</td>
                            <td>{{ $kelas['tahun'] }}</td>
                            <td>{{ $kelas['kelompok'] }}</td>
                            <td>
                                @foreach ($kelas['guru'] as $idGuru)
                                    <span class="kelas-chip">{{ $namaGuru[$idGuru] ?? '-' }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="kelas-kosong" id="kelasKosong" hidden>Belum ada kelas pada tahun ajaran ini.</p>
    </section>

    {{-- Riwayat penugasan --}}
    <section class="kelas-kartu" aria-labelledby="judulRiwayat">
        <h2 class="kelas-kartu-judul" id="judulRiwayat">Riwayat kelas dan Penugasan Aktif</h2>

        <div class="kelas-tabel-bungkus">
            <table class="kelas-tabel">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kelas | Kelompok</th>
                        <th>Guru Pengampu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayatPenugasan as $riwayat)
                        <tr>
                            <td>{{ $riwayat['tanggal'] }}</td>
                            <td>{{ $riwayat['kelas'] }} | {{ $riwayat['kelompok'] }}</td>
                            <td><span class="kelas-chip">{{ $riwayat['guru'] }}</span></td>
                            <td>
                                <span class="kelas-status {{ $riwayat['status'] === 'Berhasil' ? 'kelas-status-berhasil' : 'kelas-status-gagal' }}">
                                    {{ $riwayat['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    (function () {
        // Data guru dan elemen yang dipakai
        const dataGuru = @json(collect($daftarGuru)->pluck('nama', 'id'));
        const halaman = document.getElementById('halamanKelas');
        const form = document.getElementById('formKelas');
        const kartuForm = document.getElementById('kartuFormKelas');
        const inputNama = document.getElementById('namaKelas');
        const inputTahun = document.getElementById('tahunAjaran');
        const pilihKelompok = document.getElementById('kelompok');
        const inputCari = document.getElementById('cariGuru');
        const filterTahun = document.getElementById('filterTahun');
        const notifikasi = document.getElementById('notifikasiKelas');
        const isiDaftarKelas = document.getElementById('isiDaftarKelas');
        const baganGuru = document.querySelectorAll('#daftarGuru .kelas-baris-guru');

        // Guru yang sedang dipilih (menyimpan id)
        const guruTerpilih = new Set();

        function tampilkanGalat(kunci, pesan) {
            const el = halaman.querySelector('[data-galat="' + kunci + '"]');
            if (el) el.textContent = pesan || '';
        }

        function bersihkanGalat() {
            halaman.querySelectorAll('[data-galat]').forEach(function (el) { el.textContent = ''; });
            inputNama.classList.remove('kelas-salah');
            pilihKelompok.classList.remove('kelas-salah');
        }

        // Tampilkan hanya kelas sesuai tahun ajaran yang dipilih
        function perbaruiDaftarKelas() {
            let jumlah = 0;
            isiDaftarKelas.querySelectorAll('tr').forEach(function (baris) {
                const cocok = baris.dataset.tahun === filterTahun.value;
                baris.hidden = !cocok;
                if (cocok) jumlah++;
            });
            document.getElementById('kelasKosong').hidden = jumlah > 0;
            inputTahun.value = filterTahun.value;
        }

        function resetForm() {
            form.reset();
            guruTerpilih.clear();
            baganGuru.forEach(function (baris) {
                baris.hidden = false;
                baris.querySelector('.kelas-pilih-guru').setAttribute('aria-pressed', 'false');
                baris.querySelector('.kelas-pilih-guru').textContent = 'Pilih';
            });
            document.getElementById('guruTidakDitemukan').hidden = true;
            inputTahun.value = filterTahun.value;
            bersihkanGalat();
        }

        function tampilkanNotifikasi(pesan) {
            notifikasi.textContent = pesan;
            notifikasi.hidden = false;
        }

        // Pilih atau batal pilih guru pengampu
        baganGuru.forEach(function (baris) {
            const tombol = baris.querySelector('.kelas-pilih-guru');
            tombol.addEventListener('click', function () {
                const id = baris.dataset.guruId;
                if (guruTerpilih.has(id)) {
                    guruTerpilih.delete(id);
                    tombol.setAttribute('aria-pressed', 'false');
                    tombol.textContent = 'Pilih';
                } else {
                    guruTerpilih.add(id);
                    tombol.setAttribute('aria-pressed', 'true');
                    tombol.textContent = 'Terpilih';
                    tampilkanGalat('guru', '');
                }
            });
        });

        // Cari guru berdasarkan nama atau email
        inputCari.addEventListener('input', function () {
            const kataKunci = inputCari.value.trim().toLowerCase();
            let jumlahTampil = 0;
            baganGuru.forEach(function (baris) {
                const cocok = baris.dataset.cari.indexOf(kataKunci) !== -1;
                baris.hidden = !cocok;
                if (cocok) jumlahTampil++;
            });
            document.getElementById('guruTidakDitemukan').hidden = jumlahTampil > 0;
        });

        filterTahun.addEventListener('change', perbaruiDaftarKelas);

        // Tombol Tambah Kelas: kosongkan form dan arahkan ke form
        document.getElementById('tombolTambahKelas').addEventListener('click', function () {
            resetForm();
            notifikasi.hidden = true;
            kartuForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            inputNama.focus({ preventScroll: true });
        });

        document.getElementById('tombolBatal').addEventListener('click', resetForm);

        // Simpan: validasi sederhana lalu tambahkan baris ke daftar (belum ke database)
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            bersihkanGalat();

            const nama = inputNama.value.trim();
            const kelompok = pilihKelompok.value;
            let valid = true;

            if (nama === '') {
                tampilkanGalat('namaKelas', 'Nama kelas wajib diisi.');
                inputNama.classList.add('kelas-salah');
                valid = false;
            }
            if (kelompok === '') {
                tampilkanGalat('kelompok', 'Kelompok wajib dipilih.');
                pilihKelompok.classList.add('kelas-salah');
                valid = false;
            }
            if (guruTerpilih.size === 0) {
                tampilkanGalat('guru', 'Pilih minimal satu guru pengampu.');
                valid = false;
            }
            if (!valid) return;

            const baris = document.createElement('tr');
            baris.dataset.tahun = filterTahun.value;

            [nama, filterTahun.value, kelompok].forEach(function (teks) {
                const sel = document.createElement('td');
                sel.textContent = teks;
                baris.appendChild(sel);
            });

            const selGuru = document.createElement('td');
            guruTerpilih.forEach(function (id) {
                const chip = document.createElement('span');
                chip.className = 'kelas-chip';
                chip.textContent = dataGuru[id];
                selGuru.appendChild(chip);
            });
            baris.appendChild(selGuru);

            isiDaftarKelas.appendChild(baris);
            perbaruiDaftarKelas();
            resetForm();
            tampilkanNotifikasi('Kelas "' + nama + '" berhasil disimpan.');
            notifikasi.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        perbaruiDaftarKelas();
    })();
</script>