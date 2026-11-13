{{-- PBI-22 Landing page (route "/" bernama beranda). Tidak ada pendaftaran mandiri: akun guru dibuat admin (PBI-1). --}}
@php
    $sudahMasuk = auth()->check();
    $tujuan = $sudahMasuk ? route('dashboard') : route('login');

    $kelasTombolHijau = 'inline-flex w-full items-center justify-center gap-3 rounded-[14px] bg-[#58CC02] px-8 py-4 text-base font-bold uppercase tracking-wide text-white shadow-[0_5px_0_#46A302] transition-[background-color,translate,box-shadow] duration-150 hover:bg-[#61DA06] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#58CC02]/40 active:translate-y-[2px] active:shadow-[0_3px_0_#46A302] sm:w-auto motion-reduce:transition-none';
    $kelasJudulBagian = 'font-poppins text-3xl font-extrabold leading-tight tracking-[-0.02em] text-[#1E1B4B] sm:text-4xl';
    $kelasKartu = 'h-full rounded-2xl border border-[#E4E7F2] bg-white p-6 transition-[translate,box-shadow,border-color] duration-200 ease-out hover:-translate-y-1 hover:border-[#D5D9EE] hover:shadow-[0_12px_28px_-12px_rgba(46,37,128,0.25)] motion-reduce:transition-none motion-reduce:hover:translate-y-0';

    // Ikon garis 24x24 (stroke currentColor). Isi = elemen SVG statis milik halaman ini.
    $ikon = [
        'catatan' => '<path d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1Z"/><path d="M8 5H6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2"/><path d="M9 11h6M9 15h4"/>',
        'pola' => '<path d="M4 19h16"/><path d="m5 15 4-4 3 3 6-6"/><path d="M14 8h4v4"/>',
        'saran' => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.6 10.8c.4.3.6.8.6 1.3V16h6v-.9c0-.5.2-1 .6-1.3A6 6 0 0 0 12 3Z"/>',
        'kunci' => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v2"/>',
        'anak' => '<circle cx="12" cy="7" r="3"/><path d="M6 20a6 6 0 0 1 12 0"/>',
        'riwayat' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v4h4"/><path d="M12 8v4l3 2"/>',
        'evaluasi' => '<path d="M9 11.5 11 13.5 15.5 9"/><rect x="4" y="4" width="16" height="16" rx="3"/>',
        'unggah' => '<path d="M12 15V4"/><path d="m7.5 8.5 4.5-4.5 4.5 4.5"/><path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/>',
        'kelola' => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
        'akun' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M18 8v6M15 11h6"/>',
    ];

    $manfaat = [
        ['catatan', 'Catatan rapi dan seragam', 'Setiap observasi memakai kategori respons yang sama, jadi catatan mudah dibaca dan dibandingkan.'],
        ['pola', 'Pola respons tiap anak terlihat', 'Observasi tersusun per anak dan per tanggal, sehingga perubahan respons dari waktu ke waktu mudah dipantau.'],
        ['saran', 'Saran strategi, guru yang memutuskan', 'Sistem menyusun saran alternatif strategi berdasarkan aturan. Guru tetap meninjau dan memilih strategi yang dipakai.'],
        ['kunci', 'Data anak terjaga', 'Data anak hanya bisa dibuka oleh guru pengampu kelasnya.'],
    ];

    $fitur = [
        ['anak', 'Pencatatan observasi per anak', 'Respons anak dicatat dengan kategori respons yang seragam.'],
        ['riwayat', 'Riwayat observasi', 'Saring riwayat dengan filter, ubah atau hapus catatan, beserta riwayat perubahannya.'],
        ['saran', 'Saran alternatif strategi', 'Saran disusun berdasarkan aturan yang tersimpan di sistem.'],
        ['evaluasi', 'Tindak lanjut dan evaluasi strategi', 'Catat strategi yang diterapkan dan evaluasi hasilnya.'],
        ['unggah', 'Unggah RPP dan laporan', 'Simpan RPP dan laporan di satu tempat.'],
        ['kelola', 'Kelola data pembelajaran', 'Kelas, data anak, aktivitas, strategi, dan aspek perkembangan.'],
        ['akun', 'Kelola akun guru', 'Admin membuat dan mengatur akun guru.'],
    ];

    $langkah = [
        ['Catat observasi', 'Pilih anak dan aktivitas, lalu catat responsnya dengan kategori yang tersedia.'],
        ['Pantau pola respons', 'Buka riwayat observasi untuk melihat pola respons anak dari waktu ke waktu.'],
        ['Terapkan dan evaluasi strategi', 'Tinjau saran alternatif strategi, terapkan yang sesuai, lalu catat hasilnya.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="bg-[#2E2580] scroll-pt-[88px] motion-safe:scroll-smooth sm:scroll-pt-[104px]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPANDAI - Sistem Pendukung Pembelajaran untuk Guru TK</title>
    {{-- Tanda JS aktif: animasi muncul saat di-scroll hanya dipakai jika JS berjalan. --}}
    <script>document.documentElement.classList.add('js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-x-hidden bg-[#2E2580] font-jakarta text-white antialiased">
    <header id="header-beranda" dusk="header-beranda"
        class="sticky top-0 z-40 transition-[background-color,box-shadow] duration-200 data-[tergulir]:bg-[#2E2580] data-[tergulir]:shadow-[0_6px_20px_-8px_rgba(12,8,48,0.55)] motion-reduce:transition-none">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-6 sm:px-10 sm:py-8 lg:px-20">
            <a href="{{ route('beranda') }}" class="text-2xl font-bold tracking-tight text-white">SIPANDAI</a>
            <div class="flex items-center gap-8">
                <nav class="hidden items-center gap-7 text-sm font-medium md:flex" aria-label="Bagian halaman">
                    <a href="#manfaat" class="text-white/80 transition-colors duration-150 hover:text-white motion-reduce:transition-none">Manfaat</a>
                    <a href="#fitur" class="text-white/80 transition-colors duration-150 hover:text-white motion-reduce:transition-none">Fitur</a>
                    <a href="#cara-kerja" class="text-white/80 transition-colors duration-150 hover:text-white motion-reduce:transition-none">Cara Kerja</a>
                </nav>
                <a href="{{ $tujuan }}" dusk="tombol-atas"
                    class="rounded-[10px] bg-white px-5 py-2.5 text-sm font-bold text-[#1E1B4B] shadow-sm transition-colors duration-150 hover:bg-[#EEF0FB] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/40 motion-reduce:transition-none">
                    {{ $sudahMasuk ? 'Ke Dashboard' : 'Masuk' }}
                </a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero: mengisi sisa tinggi layar setelah header (88px, 104px mulai sm). --}}
        <section class="mx-auto grid min-h-[calc(100dvh-88px)] w-full max-w-7xl content-center items-center gap-8 px-5 pb-12 pt-2 sm:min-h-[calc(100dvh-104px)] sm:px-10 lg:grid-cols-[auto_minmax(0,1fr)] lg:gap-10 lg:px-20 lg:pb-16 lg:pt-0">
            {{-- Maskot: di layar kecil tampil di atas teks. --}}
            <div class="relative mx-auto flex w-full max-w-[280px] items-center justify-center sm:max-w-[360px] lg:order-2 lg:max-w-[540px] lg:scale-[1.55]">
                <div aria-hidden="true"
                    class="absolute inset-[-12%] rounded-full bg-[radial-gradient(circle,rgba(255,255,255,0.55)_0%,rgba(196,186,255,0.35)_32%,rgba(46,37,128,0)_68%)] motion-safe:animate-denyut"></div>
                <div class="relative motion-safe:animate-panda-muncul motion-safe:[animation-delay:120ms]">
                    <img src="{{ asset('images/maskot-panda.png') }}" alt="Maskot SIPANDAI" width="701" height="701"
                        class="h-auto w-full drop-shadow-[0_18px_30px_rgba(17,12,64,0.35)] motion-safe:animate-melayang">
                </div>
            </div>

            <div class="text-center lg:order-1 lg:text-left">
                <p class="inline-flex items-center gap-2 rounded-full bg-[#C9DBF5] px-3.5 py-1.5 text-xs font-medium text-[#1E1B4B] sm:text-sm motion-safe:animate-naik">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-[#2BD67B]" aria-hidden="true"></span>
                    Sistem Pendukung Pembelajaran untuk Guru TK
                </p>

                <h1 class="mt-5 font-poppins text-[2.5rem] font-extrabold leading-[1.1] tracking-[-0.02em] sm:text-5xl lg:whitespace-nowrap xl:text-[64px] motion-safe:animate-naik motion-safe:[animation-delay:70ms]">
                    <span class="block">Catat hari ini,</span>
                    <span class="block">Ajar lebih tepat besok.</span>
                </h1>

                <p class="mx-auto mt-5 max-w-[34rem] text-base leading-relaxed text-white/85 sm:text-lg lg:mx-0 motion-safe:animate-naik motion-safe:[animation-delay:140ms]">
                    Catat observasi, pantau pola respons anak, dan temukan alternatif strategi pembelajaran yang sesuai, semua dalam satu tempat.
                </p>

                <div class="mt-9 motion-safe:animate-naik motion-safe:[animation-delay:210ms]">
                    <a href="{{ $tujuan }}" dusk="tombol-utama" class="{{ $kelasTombolHijau }}">
                        {{ $sudahMasuk ? 'Buka Dashboard' : 'Masuk ke SIPANDAI' }}
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                    @guest
                        <p class="mt-4 text-sm text-white/70">Belum punya akun? Hubungi admin sekolah.</p>
                    @endguest
                </div>
            </div>
        </section>

        {{-- 1. Masalah --}}
        <section id="masalah" aria-labelledby="judul-masalah" class="bg-white text-[#1E1B4B]">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 py-20 sm:px-10 lg:grid-cols-2 lg:gap-16 lg:px-20 lg:py-28">
                <div data-muncul>
                    <h2 id="judul-masalah" class="{{ $kelasJudulBagian }}">Catatan observasi sering tercecer</h2>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-[#4B5068] sm:text-lg">
                        Banyak guru TK mencatat observasi di buku, kertas lepas, atau tempat yang berbeda-beda. Catatan yang terpencar membuat pola respons anak dari waktu ke waktu sulit terlihat.
                    </p>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-[#4B5068] sm:text-lg">
                        Saat sebuah aktivitas belum berhasil melibatkan anak, guru juga sulit menentukan strategi pengganti yang sesuai.
                    </p>
                </div>
                <figure data-muncul style="--urutan: 1" class="group overflow-hidden rounded-3xl bg-[#F4F6F5]">
                    <img src="{{ asset('images/landing/guru-1.webp') }}" width="1200" height="657" loading="lazy"
                        alt="Guru membungkuk mengambil wadah buah di meja, ditemani beberapa anak bertopi mahkota kertas di dalam kelas"
                        class="aspect-[4/3] h-full w-full object-cover object-[60%_40%] transition-transform duration-500 ease-out group-hover:scale-[1.03] motion-reduce:transition-none motion-reduce:group-hover:scale-100">
                </figure>
            </div>
        </section>

        {{-- 2. Manfaat --}}
        <section id="manfaat" aria-labelledby="judul-manfaat" class="bg-[#F4F6F5] text-[#1E1B4B]">
            <div class="mx-auto max-w-7xl px-5 py-20 sm:px-10 lg:px-20 lg:py-28">
                <div data-muncul class="max-w-2xl">
                    <h2 id="judul-manfaat" class="{{ $kelasJudulBagian }}">Manfaat untuk guru</h2>
                    <p class="mt-4 text-base leading-relaxed text-[#4B5068] sm:text-lg">SIPANDAI membantu guru mencatat, melihat pola, dan memilih strategi dengan lebih tenang.</p>
                </div>
                <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($manfaat as $i => [$kunciIkon, $judul, $isi])
                        <li data-muncul style="--urutan: {{ $i }}">
                            <article class="{{ $kelasKartu }}">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#EEF0FB] text-[#2E2580]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$kunciIkon] !!}</svg>
                                </span>
                                <h3 class="mt-5 text-lg font-bold leading-snug">{{ $judul }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-[#4B5068]">{{ $isi }}</p>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- 3. Fitur --}}
        <section id="fitur" aria-labelledby="judul-fitur" class="bg-white text-[#1E1B4B]">
            <div class="mx-auto max-w-7xl px-5 py-20 sm:px-10 lg:px-20 lg:py-28">
                <div data-muncul class="max-w-2xl">
                    <h2 id="judul-fitur" class="{{ $kelasJudulBagian }}">Fitur SIPANDAI</h2>
                    <p class="mt-4 text-base leading-relaxed text-[#4B5068] sm:text-lg">Yang tersedia untuk guru dan admin sekolah.</p>
                </div>
                <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($fitur as $i => [$kunciIkon, $judul, $isi])
                        <li data-muncul style="--urutan: {{ $i % 3 }}">
                            <article class="{{ $kelasKartu }} flex gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E9F8DC] text-[#3E8E02]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$kunciIkon] !!}</svg>
                                </span>
                                <div>
                                    <h3 class="font-bold leading-snug">{{ $judul }}</h3>
                                    <p class="mt-1.5 text-sm leading-relaxed text-[#4B5068]">{{ $isi }}</p>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- 4. Cara kerja --}}
        <section id="cara-kerja" aria-labelledby="judul-cara-kerja" class="bg-[#F4F6F5] text-[#1E1B4B]">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-20 sm:px-10 lg:grid-cols-2 lg:gap-16 lg:px-20 lg:py-28">
                <div>
                    <h2 id="judul-cara-kerja" data-muncul class="{{ $kelasJudulBagian }}">Cara kerja</h2>
                    <ol class="mt-10">
                        @foreach ($langkah as $i => [$judul, $isi])
                            <li data-muncul style="--urutan: {{ $i + 1 }}" class="relative flex gap-5 pb-10 last:pb-0">
                                @unless ($loop->last)
                                    {{-- Garis penghubung ke langkah berikutnya. --}}
                                    <span aria-hidden="true" class="absolute bottom-1 left-5 top-12 w-0.5 -translate-x-1/2 rounded-full bg-[#C9CCE6]"></span>
                                @endunless
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2E2580] font-poppins text-base font-extrabold text-white">{{ $i + 1 }}</span>
                                <div class="pt-1.5">
                                    <h3 class="text-lg font-bold leading-snug">{{ $judul }}</h3>
                                    <p class="mt-1.5 max-w-md text-sm leading-relaxed text-[#4B5068] sm:text-base">{{ $isi }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
                <figure data-muncul style="--urutan: 1" class="group overflow-hidden rounded-3xl bg-white">
                    <img src="{{ asset('images/landing/guru-2.webp') }}" width="1200" height="798" loading="lazy"
                        alt="Guru mendampingi tiga anak menyiram tanaman dengan gembor di taman bermain sekolah"
                        class="aspect-[4/3] h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03] motion-reduce:transition-none motion-reduce:group-hover:scale-100">
                </figure>
            </div>
        </section>

        {{-- 5. Ajakan penutup --}}
        <section id="mulai" aria-labelledby="judul-mulai" class="relative isolate overflow-hidden bg-[#2E2580]">
            <img src="{{ asset('images/landing/anak-1.webp') }}" width="1200" height="798" loading="lazy"
                alt="Anak-anak berseragam bermain ayunan ban di halaman sekolah"
                class="absolute inset-0 -z-10 h-full w-full object-cover object-[45%_35%]">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[#2E2580]/85"></div>
            <div data-muncul class="mx-auto max-w-3xl px-5 py-24 text-center sm:px-10 lg:py-32">
                <h2 id="judul-mulai" class="font-poppins text-4xl font-extrabold leading-tight tracking-[-0.02em] text-white sm:text-5xl">Siap mulai?</h2>
                <div class="mt-9">
                    <a href="{{ $tujuan }}" dusk="tombol-penutup" class="{{ $kelasTombolHijau }}">
                        {{ $sudahMasuk ? 'Buka Dashboard' : 'Masuk ke SIPANDAI' }}
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                    @guest
                        <p class="mt-4 text-sm text-white/75">Belum punya akun? Hubungi admin sekolah.</p>
                    @endguest
                </div>
            </div>
        </section>
    </main>

    {{-- 6. Footer --}}
    <footer class="bg-[#231C66] text-white/80">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-10 text-sm sm:px-10 lg:px-20">
            <p class="font-semibold text-white">SIPANDAI – Sistem Pendukung Penyesuaian Pembelajaran Anak Usia Dini</p>
            <p>Tugas Besar Proyek Perangkat Lunak – Kelompok A-C SI4801, Telkom University</p>
            <p class="mt-2 text-xs text-white/55">Foto: Irwan Zahuri, Nasirun Khan (Pexels)</p>
        </div>
    </footer>

    <script>
        (() => {
            // Header: latar solid + bayangan setelah halaman di-scroll.
            const header = document.getElementById('header-beranda');
            const perbaruiHeader = () => header.toggleAttribute('data-tergulir', window.scrollY > 8);
            perbaruiHeader();
            window.addEventListener('scroll', perbaruiHeader, { passive: true });

            // Bagian muncul saat masuk layar. Tanpa IntersectionObserver, semua langsung tampil.
            const target = document.querySelectorAll('[data-muncul]');
            if (!('IntersectionObserver' in window)) {
                target.forEach((el) => el.classList.add('terlihat'));
                return;
            }
            const pengamat = new IntersectionObserver((entri) => {
                entri.forEach((e) => {
                    if (e.isIntersecting) {
                        e.target.classList.add('terlihat');
                        pengamat.unobserve(e.target);
                    }
                });
            }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });
            target.forEach((el) => pengamat.observe(el));
        })();
    </script>
</body>
</html>
