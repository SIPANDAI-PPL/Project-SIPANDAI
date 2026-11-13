{{-- Placeholder dari fondasi login (PBI-1). Isi dashboard dikerjakan di PBI dashboard terkait. Jangan ubah nama rute admin.dashboard / guru.dashboard. --}}
<x-layouts.app title="Dashboard Guru">
    <h1 class="text-2xl font-semibold">Dashboard Guru</h1>
    <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ auth()->user()->nama }}</p>

    @if (session('status'))
        <p class="mt-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('status') }}</p>
    @endif
</x-layouts.app>
