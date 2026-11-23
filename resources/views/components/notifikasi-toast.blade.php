{{--
    Notifikasi pop-up (toast) untuk pesan berhasil.
    Muncul dari kanan atas, lalu hilang otomatis setelah 5 detik
    (lihat script di resources/views/components/layouts/app.blade.php).

    Pakai: <x-notifikasi-toast judul="..." pesan="..." :tautan="route('...')" />
--}}
@props([
    'judul',
    'pesan' => null,
    'tautan' => null,
    'labelTautan' => 'Lihat Data Observasi',
])

<div data-notifikasi-popup
    class="pointer-events-none fixed inset-x-4 top-24 z-50 flex justify-center sm:inset-x-auto sm:right-6 sm:justify-end"
    role="status" aria-live="polite">

    {{-- Tanpa JavaScript pop-up tetap terlihat, hanya tidak hilang otomatis. --}}
    <noscript>
        <style>[data-kartu-popup] { opacity: 1 !important; transform: none !important; }</style>
    </noscript>

    <div data-kartu-popup
        class="pointer-events-auto flex w-full max-w-sm translate-x-4 items-start gap-3 rounded-xl border border-emerald-200 bg-white px-4 py-3.5 text-sm opacity-0 shadow-lg shadow-emerald-900/10 transition-all duration-500 ease-out motion-reduce:transition-none">

        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12.5 4.5 4.5L19 7.5" />
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <p class="font-semibold text-gray-900">{{ $judul }}</p>

            @if ($pesan)
                <p class="mt-0.5 leading-relaxed text-gray-600">{{ $pesan }}</p>
            @endif

            @if ($tautan)
                <a href="{{ $tautan }}"
                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 hover:underline">
                    {{ $labelTautan }}
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                    </svg>
                </a>
            @endif
        </div>

        <button type="button" data-tutup-notifikasi
            class="rounded-md p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300"
            aria-label="Tutup notifikasi">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>
</div>
