{{--
    PBI-17 Riwayat Tindak Lanjut (hanya baca).
    Butuh: $daftarTindakLanjut (paginator TindakLanjut dengan alternatif.anak.kelas, alternatif.strategi, riwayatPerubahan).
--}}
@php
    $badgeStatus = [
        'direncanakan' => 'border-sky-200 bg-sky-50 text-sky-800',
        'berjalan' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'selesai' => 'border-gray-300 bg-gray-50 text-gray-700',
    ];
    $kelasBadge = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap';
    $kelasSel = 'px-4 py-3 align-top';
@endphp
<x-layouts.app title="Riwayat Tindak Lanjut">
    @if (session('status'))
        {{-- Pesan berhasil hilang sendiri setelah 3 detik. --}}
        <div data-hilang-otomatis="3000" role="status" dusk="pesan-status"
            class="fixed inset-x-4 top-24 z-50 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg transition-opacity duration-300 motion-reduce:transition-none sm:left-auto sm:right-6 sm:max-w-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if (session('gagal'))
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-gagal">{{ session('gagal') }}</p>
    @endif

    <a href="{{ route('guru.tindak-lanjut.create') }}" dusk="tombol-kembali"
        class="inline-flex items-center gap-1 text-sm text-indigo-800 hover:underline">&larr; Kembali ke pencatatan tindak lanjut</a>

    <h1 class="mt-3 text-2xl font-semibold">Riwayat Tindak Lanjut</h1>
    <p class="mt-1 text-sm text-gray-600">Daftar tindak lanjut yang sudah dicatat untuk anak di kelas Anda, terbaru di atas.</p>

    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @if ($daftarTindakLanjut->isEmpty())
            <div class="px-5 py-10 text-center" dusk="riwayat-kosong">
                <p class="font-semibold">Belum ada tindak lanjut yang dicatat.</p>
                <p class="mt-1 text-sm text-gray-600">Tindak lanjut yang disimpan dari formulir pencatatan akan tampil di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Anak</th>
                            <th scope="col" class="px-4 py-3">Strategi</th>
                            <th scope="col" class="px-4 py-3">Respons awal</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Tanggal mulai</th>
                            <th scope="col" class="px-4 py-3">Catatan pendukung</th>
                            <th scope="col" class="px-4 py-3">Dicatat</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($daftarTindakLanjut as $tindakLanjut)
                            @php
                                $anak = $tindakLanjut->alternatif->anak;
                                $dicatat = $tindakLanjut->riwayatPerubahan->firstWhere('aksi', 'tambah');
                                $diubah = $tindakLanjut->riwayatPerubahan->where('aksi', 'ubah')->last();
                            @endphp
                            <tr dusk="baris-tindak-lanjut-{{ $tindakLanjut->id }}">
                                <td class="{{ $kelasSel }}">
                                    <p class="font-medium text-gray-900">{{ $anak->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ $anak->kelas->kelompok }} {{ $anak->kelas->nama_kelas }}</p>
                                </td>
                                <td class="{{ $kelasSel }} min-w-48">
                                    <p class="font-medium text-gray-900">{{ $tindakLanjut->alternatif->strategi->nama }}</p>
                                    @if ($tindakLanjut->rencana)
                                        <p class="mt-0.5 text-xs leading-relaxed text-gray-500">{{ $tindakLanjut->rencana }}</p>
                                    @endif
                                </td>
                                <td class="{{ $kelasSel }} whitespace-nowrap">{{ $tindakLanjut->kategori_respons_awal?->label() ?? '-' }}</td>
                                <td class="{{ $kelasSel }}">
                                    <span class="{{ $kelasBadge }} {{ $badgeStatus[$tindakLanjut->status] ?? $badgeStatus['selesai'] }}">{{ ucfirst($tindakLanjut->status) }}</span>
                                </td>
                                <td class="{{ $kelasSel }} whitespace-nowrap">{{ $tindakLanjut->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
                                <td class="{{ $kelasSel }} min-w-56 text-gray-700">{{ $tindakLanjut->alasan_dugaan_penyebab ?: '-' }}</td>
                                <td class="{{ $kelasSel }} whitespace-nowrap text-xs text-gray-500">
                                    @if ($dicatat)
                                        <p>{{ $dicatat->waktu?->format('d/m/Y H:i') }}</p>
                                        <p>{{ $dicatat->pengguna?->nama }}</p>
                                    @else
                                        -
                                    @endif
                                    @if ($diubah)
                                        <p class="mt-1.5 text-gray-400">Diubah {{ $diubah->waktu?->format('d/m/Y H:i') }}</p>
                                    @endif
                                </td>
                                <td class="{{ $kelasSel }} whitespace-nowrap text-right">
                                    @if ($tindakLanjut->terkunci())
                                        {{-- Gaya disamakan dengan tombol abu-abu (is-muted) di daftar observasi. --}}
                                        <span dusk="terkunci-{{ $tindakLanjut->id }}" title="Sudah memiliki observasi lanjutan, evaluasi, atau sudah selesai."
                                            class="inline-flex items-center gap-[5px] whitespace-nowrap rounded-lg border border-transparent bg-[#F3F4F6] px-2.5 py-1.5 text-[13px] font-semibold text-[#4B5563]">
                                            <svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                            Terkunci
                                        </span>
                                    @else
                                        {{-- Gaya dan ikon disamakan dengan tombol Ubah (obs-act tone-amber) di daftar observasi. --}}
                                        <a href="{{ route('guru.tindak-lanjut.edit', $tindakLanjut) }}" dusk="ubah-{{ $tindakLanjut->id }}"
                                            aria-label="Ubah tindak lanjut {{ $anak->nama }}"
                                            class="inline-flex items-center gap-[5px] whitespace-nowrap rounded-lg border border-transparent bg-[#FFF7E6] px-2.5 py-1.5 text-[13px] font-semibold text-[#D97706] transition-colors hover:border-[#D97706] focus-visible:shadow-[0_0_0_3px_rgba(217,119,6,.16)] focus-visible:outline-none motion-reduce:transition-none">
                                            <svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5.5l4 4M4 20l1-4.5L15.8 4.7a1.8 1.8 0 012.5 0l1 1a1.8 1.8 0 010 2.5L8.5 19 4 20z" />
                                            </svg>
                                            Ubah
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($daftarTindakLanjut->hasPages())
        <nav class="mt-4 flex flex-wrap items-center gap-1.5 text-sm" aria-label="Halaman">
            @if ($daftarTindakLanjut->onFirstPage())
                <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-300">Sebelumnya</span>
            @else
                <a href="{{ $daftarTindakLanjut->previousPageUrl() }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
            @endif
            <span class="px-2 text-gray-600">Halaman {{ $daftarTindakLanjut->currentPage() }} dari {{ $daftarTindakLanjut->lastPage() }}</span>
            @if ($daftarTindakLanjut->hasMorePages())
                <a href="{{ $daftarTindakLanjut->nextPageUrl() }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
            @else
                <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-300">Selanjutnya</span>
            @endif
        </nav>
    @endif

    <script>
        document.querySelectorAll('[data-hilang-otomatis]').forEach((pesan) => {
            const durasi = Number(pesan.dataset.hilangOtomatis) || 3000;
            setTimeout(() => {
                pesan.style.opacity = '0';
                // Hapus setelah animasi memudar selesai (300 ms).
                setTimeout(() => pesan.remove(), 300);
            }, durasi);
        });
    </script>
</x-layouts.app>
