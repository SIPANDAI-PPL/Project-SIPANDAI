<x-layouts.app title="Profil Anak">
    <div class="mx-auto w-full max-w-4xl">
        <a href="{{ route('guru.anak.index', ['kelas' => $anak['kelas_id']]) }}" class="text-sm text-indigo-900 underline">← Kembali ke Data Anak</a>

        @if (session('status'))
            <p class="mt-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 transition-opacity duration-500" role="status" data-hilang-otomatis dusk="pesan-status">{{ session('status') }}</p>
        @endif

        <section class="mt-3 rounded-lg bg-white p-6 shadow">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-green-100 text-lg font-semibold text-green-800">
                    {{ $anak['inisial'] }}
                </span>
                <div class="flex-1">
                    <h1 class="text-2xl font-semibold">{{ $anak['panggilan'] }}</h1>
                    <p class="text-sm text-gray-600">{{ $anak['nama'] }} · {{ $anak['usia'] }}</p>
                    <p class="text-sm text-gray-600">{{ $anak['kelas'] }} · {{ $anak['jumlah_observasi'] }} observasi</p>
                    @unless ($anak['aktif'])
                        <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Nonaktif</span>
                    @endunless
                </div>
                <button type="button" data-modal-buka="modal-ubah" aria-label="Ubah data anak" title="Ubah data anak"
                    class="rounded p-2 text-gray-600 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                @if ($anak['aktif'])
                    <a href="{{ route('guru.observasi.create') }}" class="rounded bg-indigo-900 px-4 py-2 text-center font-medium text-white hover:bg-indigo-800">Catat observasi</a>
                @else
                    <span class="cursor-not-allowed rounded bg-gray-200 px-4 py-2 text-center font-medium text-gray-500" title="Anak nonaktif tidak bisa dicatat observasinya">Catat observasi</span>
                @endif
                <a href="{{ route('guru.riwayat-observasi', $anak['id']) }}" class="rounded border border-indigo-900 px-4 py-2 text-center font-medium text-indigo-900 hover:bg-gray-50">Riwayat Observasi</a>
            </div>
        </section>
    </div>

    @include('guru.anak._modal-ubah')
    @include('guru.anak._modal-nonaktifkan')
    @include('guru.anak._modal-aktifkan')
    @include('guru.anak._script-modal')
</x-layouts.app>
