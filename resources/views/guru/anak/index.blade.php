<x-layouts.app title="Kelola Data Anak">
    <div class="mx-auto w-full max-w-4xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Kelola Data Anak</h1>
            @if ($kelasTerpilih)
                <button type="button" data-modal-buka="modal-tambah" dusk="tombol-tambah-anak"
                    class="rounded bg-indigo-900 px-4 py-2 font-medium text-white hover:bg-indigo-800">
                    + Tambah anak
                </button>
            @else
                <button type="button" disabled dusk="tombol-tambah-anak"
                    class="cursor-not-allowed rounded bg-gray-300 px-4 py-2 font-medium text-gray-500">
                    + Tambah anak
                </button>
            @endif
        </div>

        @if (session('status'))
            <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
        @endif

        @if ($kelasTerpilih === null)
            <p class="mt-6 rounded-lg bg-white px-4 py-10 text-center text-sm text-gray-500 shadow" dusk="pesan-tanpa-kelas">
                Anda belum mengampu kelas, hubungi admin.
            </p>
        @else
            <form method="GET" action="{{ route('guru.anak.index') }}" class="mt-4 space-y-4">
                <input type="hidden" name="status" value="{{ $status }}">
                <div>
                    <label for="kelas" class="block text-sm font-medium">Kelas</label>
                    <select id="kelas" name="kelas" onchange="this.form.submit()" dusk="pilihan-kelas"
                        class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                        @foreach ($daftarKelas as $kelas)
                            <option value="{{ $kelas['id'] }}" @selected($kelas['id'] === $kelasTerpilih['id'])>{{ $kelas['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari nama atau nama panggilan"
                    aria-label="Cari nama atau nama panggilan"
                    class="block w-full rounded border border-gray-300 bg-white px-3 py-2">
            </form>

            @php
                $tautanTab = fn (string $tab) => route('guru.anak.index', array_filter([
                    'kelas' => $kelasTerpilih['id'],
                    'cari' => $cari !== '' ? $cari : null,
                    'status' => $tab === 'nonaktif' ? 'nonaktif' : null,
                ]));
                $kelasTab = fn (string $tab) => $status === $tab
                    ? 'bg-indigo-900 text-white'
                    : 'bg-white text-gray-900 hover:bg-gray-50';
            @endphp

            <section class="mt-4 rounded-lg border border-gray-300 bg-white p-2 shadow">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ $tautanTab('aktif') }}" dusk="tab-aktif" @if ($status === 'aktif') aria-current="page" @endif
                        class="rounded border border-gray-300 px-4 py-2 text-center text-sm font-medium {{ $kelasTab('aktif') }}">
                        Aktif ({{ $jumlahAktif }})
                    </a>
                    <a href="{{ $tautanTab('nonaktif') }}" dusk="tab-nonaktif" @if ($status === 'nonaktif') aria-current="page" @endif
                        class="rounded border border-gray-300 px-4 py-2 text-center text-sm font-medium {{ $kelasTab('nonaktif') }}">
                        Nonaktif ({{ $jumlahNonaktif }})
                    </a>
                </div>

                <div class="px-4 py-2" dusk="daftar-anak">
                    @forelse ($anakTampil as $anak)
                        @include('guru.anak._baris-anak')
                    @empty
                        <p class="py-6 text-center text-sm text-gray-500">
                            @if ($cari !== '')
                                Tidak ada anak yang cocok dengan pencarian.
                            @else
                                {{ $status === 'aktif' ? 'Belum ada anak aktif.' : 'Belum ada anak nonaktif.' }}
                            @endif
                        </p>
                    @endforelse
                </div>
            </section>

            @include('guru.anak._modal-tambah', ['daftarKelas' => $daftarKelas, 'kelasTerpilih' => $kelasTerpilih])
        @endif
    </div>

    @include('guru.anak._script-modal')
</x-layouts.app>
