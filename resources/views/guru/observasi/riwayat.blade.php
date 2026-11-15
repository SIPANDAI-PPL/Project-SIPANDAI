{{-- Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan. --}}
@php
    $kode = sprintf('OBS-%03d', $observasi->id);
@endphp

<x-layouts.app title="Riwayat Perubahan Observasi">
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Judul halaman --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">
                    <a href="{{ route('guru.observasi.show', $observasi) }}" class="hover:underline">
                        Observasi &rsaquo; {{ $kode }}
                    </a>
                </p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Riwayat Perubahan</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Catatan setiap perubahan pada observasi
                    {{ $observasi->anak?->nama ?? '-' }}
                    tanggal {{ $observasi->tanggal?->locale('id')->isoFormat('D MMMM YYYY') }}.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.observasi.show', $observasi) }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Kembali ke Detail
                </a>

                <a href="{{ route('guru.observasi.edit', $observasi) }}"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    Ubah Observasi
                </a>
            </div>
        </div>

        {{-- Daftar riwayat --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6" aria-label="Daftar riwayat perubahan">
            @forelse ($riwayat as $entri)
                @php
                    $aksi = strtoupper((string) $entri->aksi);
                    $labelAksi = match ($aksi) {
                        'UPDATE', 'UBAH' => 'Diubah',
                        'CREATE', 'TAMBAH' => 'Ditambah',
                        'DELETE', 'HAPUS' => 'Dihapus',
                        default => $entri->aksi ?: '-',
                    };
                @endphp

                <article class="relative flex gap-4 border-gray-200 pb-6 pl-1 last:pb-0">
                    {{-- Garis dan titik timeline --}}
                    <div class="relative flex w-6 shrink-0 justify-center" aria-hidden="true">
                        <span class="absolute inset-y-0 w-px bg-gray-200"></span>
                        <span class="relative mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-indigo-900 ring-1 ring-indigo-900"></span>
                    </div>

                    <div class="min-w-0 flex-1 rounded-lg border border-gray-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $entri->waktu?->locale('id')->isoFormat('D MMM YYYY, HH.mm') ?? '-' }}
                                &bull; {{ $entri->pengguna?->nama ?? '-' }}
                            </p>

                            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold uppercase text-indigo-900">
                                {{ $labelAksi }}
                            </span>
                        </div>

                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">
                            {{ $entri->keterangan ?: 'Tidak ada detail perubahan.' }}
                        </p>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 px-4 py-10 text-center">
                    <p class="font-medium text-gray-900">Belum ada riwayat perubahan</p>
                    <p class="mt-1 text-sm text-gray-600">
                        Riwayat akan muncul setelah observasi ini diubah.
                    </p>
                </div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
