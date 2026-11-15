@php
    $kode = sprintf('OBS-%03d', $observasi->id);
    $warnaRespons = [
        'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'terlibat_dengan_pendampingan' => 'border-blue-200 bg-blue-50 text-blue-800',
        'kurang_tertarik' => 'border-orange-200 bg-orange-50 text-orange-800',
        'belum_terlibat' => 'border-gray-300 bg-gray-50 text-gray-700',
    ];
    $kelas = $observasi->anak?->kelas;
    $namaKelas = $kelas
        ? trim(implode(' - ', array_filter([$kelas->kelompok, $kelas->nama_kelas])))
        : '-';
    $diubahPada = $observasi->diubah_pada
        ? \Illuminate\Support\Carbon::parse($observasi->diubah_pada)->locale('id')->isoFormat('D MMM YYYY, HH.mm')
        : '-';
@endphp

<x-layouts.app title="Detail Observasi">
    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Notifikasi --}}
        @if (session('notifikasi'))
            @php $notif = session('notifikasi'); @endphp
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                <p class="font-semibold">{{ $notif['judul'] }}</p>
                <p class="mt-1">{{ $notif['pesan'] }}</p>
            </div>
        @endif

        @if (session('gagal'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                {{ session('gagal') }}
            </div>
        @endif

        {{-- Judul dan aksi --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">Observasi &rsaquo; {{ $kode }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">
                    Detail Observasi Perkembangan
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Ringkasan hasil pengamatan, respons anak, dan catatan tindak lanjut.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.observasi.riwayat', $observasi) }}"
                    class="rounded-lg border border-indigo-300 px-4 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-50">
                    Lihat Riwayat ({{ $riwayat->count() }})
                </a>

                <a href="{{ route('guru.observasi.edit', $observasi) }}"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    Ubah
                </a>

                <button type="button" data-buka-hapus
                    class="rounded-lg border border-red-300 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50">
                    Hapus
                </button>
            </div>
        </div>

        {{-- Info ringkas --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Informasi observasi">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">ID Observasi</p>
                <p class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $kode }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Tanggal Observasi</p>
                <p class="mt-1 font-semibold text-gray-900">
                    {{ $observasi->tanggal->locale('id')->isoFormat('D MMM YYYY') }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Terakhir Diubah</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $diubahPada }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Guru Pengampu</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $observasi->guru?->nama ?? '-' }}</p>
            </div>
        </section>

        {{-- Data anak dan konteks --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-semibold text-gray-900">Data Anak &amp; Konteks Observasi</h2>

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">Nama Anak</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $observasi->anak?->nama ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Kelas</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $namaKelas }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Aktivitas</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $observasi->aktivitas?->nama ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Aspek Perkembangan</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $observasi->aspek?->nama ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Strategi Pembelajaran</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $observasi->strategi?->nama ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Respons Anak</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $warnaRespons[$observasi->kategori_respons->value] ?? 'border-gray-300 bg-gray-50 text-gray-700' }}">
                            {{ $observasi->kategori_respons->label() }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        {{-- Catatan pendukung --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-semibold text-gray-900">Catatan Pendukung</h2>
            <p class="mt-3 whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">{{ $observasi->catatan_pendukung ?: 'Tidak ada catatan pendukung.' }}</p>
        </section>

        {{-- Riwayat ringkas --}}
        <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold text-gray-900">Riwayat Perubahan</h2>
                <a href="{{ route('guru.observasi.riwayat', $observasi) }}"
                    class="text-sm font-medium text-indigo-900 hover:underline">Lihat semua</a>
            </div>

            @forelse ($riwayat->take(3) as $entri)
                <div class="mt-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $entri->waktu?->locale('id')->isoFormat('D MMM YYYY, HH.mm') }}
                            &bull; {{ $entri->pengguna?->nama ?? '-' }}
                        </p>
                        <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold uppercase text-indigo-900">
                            {{ $entri->aksi }}
                        </span>
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $entri->keterangan ?: '-' }}</p>
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">Belum ada riwayat perubahan untuk observasi ini.</p>
            @endforelse
        </section>
    </div>

    @include('guru.observasi.partials.modal-hapus', ['observasi' => $observasi])
</x-layouts.app>
