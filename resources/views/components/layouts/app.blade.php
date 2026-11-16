{{--
    PBI-22 Layout bersama (header + sidebar) untuk semua halaman setelah login.
    Pakai: <x-layouts.app title="Judul Halaman"> ...isi halaman... </x-layouts.app>
    Daftar menu sidebar ada di config/menu.php.
--}}
@props(['title' => null])
@php
    $pengguna = auth()->user();
    $peran = $pengguna?->peran;

    $daftarMenu = collect(config("menu.{$peran}", []))
        ->filter(fn (array $item) => \Illuminate\Support\Facades\Route::has($item['rute']))
        ->map(fn (array $item) => $item + ['sedangAktif' => request()->routeIs(...(array) ($item['aktif'] ?? $item['rute']))])
        ->values();
@endphp
<!DOCTYPE html>
<html lang="id" class="bg-[#F4F6F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "{$title} - SIPANDAI" : 'SIPANDAI' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F6F5] font-jakarta text-gray-900 antialiased">
    <header class="sticky top-0 z-30 flex [view-transition-name:header] h-20 items-center justify-between gap-4 bg-[#2E2580] px-4 text-white sm:px-6">
        <div class="flex items-center gap-3">
            <button type="button" id="tombol-sidebar" dusk="tombol-sidebar"
                class="rounded-lg p-2 transition-colors duration-150 hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 motion-reduce:transition-none"
                aria-controls="sidebar" aria-expanded="false" aria-label="Buka atau tutup menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ $pengguna ? route($pengguna->ruteDashboard()) : url('/') }}" class="text-xl font-bold tracking-wide text-white">SIPANDAI</a>
        </div>

        @if ($pengguna)
            <div class="flex min-w-0 items-center gap-3">
                <div class="min-w-0 text-right leading-tight">
                    <p class="truncate text-sm font-semibold" dusk="nama-pengguna">{{ $pengguna->nama }}</p>
                    <p class="text-xs text-white/70" dusk="peran-pengguna">{{ $peran === 'admin' ? 'Admin' : 'Guru' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" dusk="tombol-keluar"
                        class="rounded-lg border border-white/30 px-3 py-1.5 text-sm text-white transition-colors duration-150 hover:bg-white/10 motion-reduce:transition-none">Keluar</button>
                </form>
            </div>
        @endif
    </header>

    <div class="relative flex min-h-[calc(100vh-5rem)]">
        <aside id="sidebar" dusk="sidebar"
            @class([
                // Layar kecil: panel di atas konten, digeser masuk/keluar lewat data-terbuka.
                'invisible absolute inset-y-0 left-0 z-20 [view-transition-name:sidebar] w-[230px] shrink-0 -translate-x-full overflow-hidden border-r border-[#EEF1F0] bg-white p-4 shadow-lg',
                'data-[terbuka]:visible data-[terbuka]:translate-x-0',
                // Desktop: ikut alur halaman, dilipat lebarnya lewat data-tertutup sehingga konten melebar.
                'md:visible md:static md:translate-x-0 md:shadow-none',
                'md:data-[tertutup]:invisible md:data-[tertutup]:w-0 md:data-[tertutup]:border-r-0 md:data-[tertutup]:px-0',
                'transition-[translate,width,padding,visibility] duration-200 ease-out motion-reduce:transition-none',
            ])>
            <nav class="w-[198px] space-y-1 text-sm" aria-label="Menu utama">
                @foreach ($daftarMenu as $item)
                    <a href="{{ route($item['rute']) }}"
                        @class([
                            'block rounded-lg px-3 py-2.5 transition-colors duration-150 motion-reduce:transition-none',
                            'bg-[#EEF0FB] font-semibold text-[#2E2580]' => $item['sedangAktif'],
                            'text-gray-700 hover:bg-gray-100' => ! $item['sedangAktif'],
                        ])
                        @if ($item['sedangAktif']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </aside>
        <script>
            // Terapkan pilihan sidebar desktop sebelum konten digambar supaya tidak berkedip.
            try {
                if (localStorage.getItem('sipandai.sidebar') === 'tertutup') {
                    document.getElementById('sidebar').dataset.tertutup = '';
                }
            } catch (e) {}
        </script>

        <main class="min-w-0 flex-1 [view-transition-name:konten] px-4 py-6 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    <script>
        (() => {
            // Desktop (md ke atas): sidebar tampil kecuali ditutup, pilihan disimpan di localStorage.
            // Layar kecil: sidebar tersembunyi dan dibuka sebagai panel di atas konten, tidak disimpan.
            const tombol = document.getElementById('tombol-sidebar');
            const sidebar = document.getElementById('sidebar');
            const desktop = window.matchMedia('(min-width: 48rem)');

            const sidebarTampil = () => desktop.matches
                ? !('tertutup' in sidebar.dataset)
                : 'terbuka' in sidebar.dataset;

            const perbaruiAria = () => tombol.setAttribute('aria-expanded', String(sidebarTampil()));

            tombol.addEventListener('click', () => {
                if (desktop.matches) {
                    const tutup = !('tertutup' in sidebar.dataset);
                    sidebar.toggleAttribute('data-tertutup', tutup);
                    try {
                        localStorage.setItem('sipandai.sidebar', tutup ? 'tertutup' : 'terbuka');
                    } catch (e) {}
                } else {
                    sidebar.toggleAttribute('data-terbuka');
                }
                perbaruiAria();
            });

            desktop.addEventListener('change', perbaruiAria);
            perbaruiAria();
        })();
    </script>
</body>
</html>
