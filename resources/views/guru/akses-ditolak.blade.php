{{-- Fitur: PBI-11 Riwayat Observasi Anak dengan Filter --}}
{{--
    Halaman 403 khusus fitur ini, dipakai saat guru membuka riwayat anak
    di kelas yang tidak diampu (NFR-04). Sengaja tidak memakai
    resources/views/errors/403.blade.php agar 403 dari fitur lain
    (misalnya middleware peran) tidak ikut berubah.
--}}
@php
    $tautanDataAnak = \Illuminate\Support\Facades\Route::has('guru.anak.index')
        ? route('guru.anak.index')
        : route('guru.dashboard');
@endphp
<x-layouts.app title="Akses Ditolak">
    <div class="mx-auto w-full max-w-6xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-500" aria-label="Breadcrumb">
            <a href="{{ $tautanDataAnak }}" class="inline-flex items-center gap-1 rounded hover:text-[#221177]" aria-label="Kembali ke Data Anak">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            </a>
            <a href="{{ $tautanDataAnak }}" class="hover:text-[#221177] hover:underline">Data Anak</a>
        </nav>

        <div class="mt-6 rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-black/5 sm:py-16">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#EDE9FE] text-[#221177]">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6v-9Z" />
                </svg>
            </span>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.2em] text-[#221177]">
                403 <span aria-hidden="true">•</span> Akses ditolak
            </p>

            <h1 class="mt-2 text-xl font-semibold text-gray-900 sm:text-2xl">Anda tidak memiliki akses ke data anak ini</h1>

            <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-gray-500">
                Riwayat observasi hanya dapat dilihat oleh guru pengampu kelas anak tersebut.
                Hubungi admin bila Anda merasa ini keliru.
            </p>

            <a href="{{ $tautanDataAnak }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-[#221177] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2E1F7E] focus:outline-none focus:ring-2 focus:ring-[#221177]/30">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
                Kembali ke Data Anak
            </a>
        </div>
    </div>
</x-layouts.app>
