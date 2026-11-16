{{--
    PBI-22 Layout halaman tanpa sidebar (login dan ganti kata sandi).
    Pakai: <x-layouts.guest title="Masuk"> ...isi kartu... </x-layouts.guest>
    Tombol lihat kata sandi: <button type="button" data-lihat-sandi="id_input">.
--}}
@props(['title' => null])
<!DOCTYPE html>
<html lang="id" class="bg-[#2E2580]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "{$title} - SIPANDAI" : 'SIPANDAI' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#2E2580] font-jakarta text-gray-900 antialiased">
    <header class="px-6 py-6 sm:px-10">
        <p class="text-xl font-bold tracking-wide text-white">SIPANDAI</p>
    </header>

    <div class="flex flex-1 items-center justify-center px-4 pb-16">
        <main class="w-full max-w-[424px] rounded-2xl bg-white px-6 py-8 shadow-xl shadow-black/20 sm:px-10 sm:py-10 motion-safe:animate-muncul">
            {{ $slot }}
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-lihat-sandi]').forEach((tombol) => {
            const input = document.getElementById(tombol.dataset.lihatSandi);
            tombol.addEventListener('click', () => {
                const tampil = input.type === 'password';
                input.type = tampil ? 'text' : 'password';
                tombol.setAttribute('aria-pressed', String(tampil));
                tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                tombol.querySelector('[data-ikon="mata"]').classList.toggle('hidden', tampil);
                tombol.querySelector('[data-ikon="mata-coret"]').classList.toggle('hidden', !tampil);
            });
        });
    </script>
</body>
</html>
