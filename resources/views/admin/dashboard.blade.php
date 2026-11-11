{{-- Placeholder dari fondasi login (PBI-1). Isi dashboard dikerjakan di PBI dashboard terkait. Jangan ubah nama rute admin.dashboard / guru.dashboard. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 p-6 font-sans text-gray-900">
    <h1 class="text-2xl font-semibold">Dashboard Admin</h1>
    <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ auth()->user()->nama }}</p>

    <nav class="mt-4 flex flex-wrap gap-3 text-sm">
        <a href="{{ route('data-master.aktivitas.index') }}" class="rounded bg-indigo-900 px-4 py-2 text-white hover:bg-indigo-800">Data Master</a>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900">Keluar</button>
    </form>
</body>
</html>
