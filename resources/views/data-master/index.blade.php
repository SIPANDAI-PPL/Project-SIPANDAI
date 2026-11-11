{{--
    PBI-2 Kelola Aktivitas dan Strategi Pembelajaran. Halaman berdiri sendiri karena layout bersama belum ada.
    Satu view untuk dua tab; $jenis = 'aktivitas' | 'strategi'. Dipakai admin (Data Master) dan guru (Aktivitas & Strategi).
--}}
@php
    $pengguna = auth()->user();
    $adalahAdmin = $pengguna->peran === 'admin';

    $inisial = fn (string $nama) => collect(preg_split('/\s+/', trim($nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    $teks = $jenis === 'aktivitas'
        ? [
            'rute' => 'data-master.aktivitas',
            'benda' => 'aktivitas',
            'Benda' => 'Aktivitas',
            'satuan' => 'Aktivitas',
            'judul_daftar' => 'Daftar Aktivitas',
            'kolom_nama' => 'Nama Aktivitas',
            'cari' => 'Cari nama aktivitas atau deskripsi...',
            'contoh_nama' => 'Contoh: Bernyanyi',
            'contoh_deskripsi' => 'Jelaskan singkat kegiatan ini.',
            'tempat' => 'sebagai pilihan di formulir observasi',
        ]
        : [
            'rute' => 'data-master.strategi',
            'benda' => 'strategi',
            'Benda' => 'Strategi',
            'satuan' => 'Strategi',
            'judul_daftar' => 'Daftar Strategi Pembelajaran',
            'kolom_nama' => 'Nama Strategi',
            'cari' => 'Cari nama strategi atau deskripsi...',
            'contoh_nama' => 'Contoh: Tutor sebaya',
            'contoh_deskripsi' => 'Jelaskan singkat cara menerapkan strategi ini.',
            'tempat' => 'di formulir observasi dan pilihan aturan strategi',
        ];

    // Teks kolom "Dipakai di". Kosong berarti belum dipakai dan boleh dihapus.
    $pemakaian = function ($item) use ($jenis): array {
        $bagian = [];
        if ($item->observasi_count > 0) {
            $bagian[] = "{$item->observasi_count} observasi";
        }
        if ($jenis === 'strategi') {
            if ($item->jumlahAturan() > 0) {
                $bagian[] = "{$item->jumlahAturan()} aturan";
            }
            if ($item->alternatif_strategi_count > 0) {
                $bagian[] = "{$item->alternatif_strategi_count} alternatif";
            }
        }

        return $bagian;
    };

    $formLama = old('_form');

    $kelasInput = 'mt-1 block w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';

    $menuSamping = $adalahAdmin
        ? [
            ['admin.dashboard', 'Dashboard', false],
            ['admin.guru.index', 'Kelola Akun Guru', false],
            ['data-master.aktivitas.index', 'Data Master', true],
        ]
        : [
            ['guru.dashboard', 'Dashboard', false],
            ['guru.riwayat-observasi', 'Riwayat Observasi', false],
            ['data-master.aktivitas.index', 'Aktivitas & Strategi', true],
        ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $adalahAdmin ? 'Data Master' : 'Aktivitas & Strategi' }} - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900">
    <header class="flex items-center justify-between bg-indigo-950 px-6 py-4 text-white">
        <a href="{{ route($pengguna->ruteDashboard()) }}" class="text-xl font-bold tracking-wide">SIPANDAI</a>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 rounded-full border border-white/20 px-3 py-1.5 text-sm">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-xs font-semibold text-indigo-950">{{ $inisial($pengguna->nama) }}</span>
                {{ $pengguna->nama }} <span class="text-white/60">&middot; {{ $adalahAdmin ? 'Admin' : 'Guru' }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">Keluar</button>
            </form>
        </div>
    </header>

    <div class="flex">
        <aside class="hidden w-56 shrink-0 border-r border-gray-200 bg-white p-4 md:block md:min-h-[calc(100vh-64px)]">
            <nav class="space-y-1 text-sm">
                @foreach ($menuSamping as [$rute, $label, $aktif])
                    @if (\Illuminate\Support\Facades\Route::has($rute))
                        <a href="{{ route($rute) }}" @class([
                            'block rounded-lg px-3 py-2',
                            'bg-indigo-50 font-medium text-indigo-900' => $aktif,
                            'text-gray-700 hover:bg-gray-100' => ! $aktif,
                        ])>{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-6 lg:p-8">
            <h1 class="text-2xl font-semibold">{{ $adalahAdmin ? 'Data Master' : 'Aktivitas & Strategi' }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $adalahAdmin
                    ? 'Kelola referensi data master pendukung analisis observasi dan strategi pembelajaran bagi guru.'
                    : 'Kelola referensi aktivitas dan strategi pembelajaran pendukung observasi dan rekomendasi guru.' }}
            </p>

            @include('data-master._tabs', ['tabAktif' => $jenis])

            @if (session('status'))
                <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
            @endif

            @error('hapus')
                <p class="mt-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800" role="alert" dusk="pesan-gagal-hapus">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route($teks['rute'].'.index') }}" class="flex w-full max-w-2xl flex-wrap items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <label for="q" class="sr-only">{{ $teks['cari'] }}</label>
                        <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="{{ $teks['cari'] }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <label for="status" class="sr-only">Filter status</label>
                    <select id="status" name="status" onchange="this.form.submit()" dusk="filter-status"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        @foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $kunci => $label)
                            <option value="{{ $kunci }}" @selected($status === $kunci)>Status: {{ $label }} ({{ $jumlah[$kunci] }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="sr-only">Terapkan</button>
                </form>

                <button type="button" data-buka="modal-tambah" dusk="tombol-tambah"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    + Tambah {{ $teks['satuan'] }}
                </button>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                    <h2 class="font-semibold">{{ $teks['judul_daftar'] }}</h2>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs text-indigo-900" dusk="badge-jumlah">{{ $daftar->total() }} {{ $teks['satuan'] }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50 text-xs text-gray-600">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ $teks['kolom_nama'] }}</th>
                                <th class="px-6 py-3 font-medium">Deskripsi</th>
                                <th class="px-6 py-3 font-medium">Dipakai di</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($daftar as $item)
                                @php
                                    $bagianPakai = $pemakaian($item);
                                    $teksPakai = implode(' · ', $bagianPakai);
                                    $dataItem = [
                                        'id' => $item->id,
                                        'nama' => $item->nama,
                                        'deskripsi' => $item->deskripsi,
                                        'pemakaian' => $teksPakai,
                                        'dipakai' => $bagianPakai !== [],
                                        'url_ubah' => route($teks['rute'].'.update', $item),
                                        'url_nonaktifkan' => route($teks['rute'].'.nonaktifkan', $item),
                                        'url_aktifkan' => route($teks['rute'].'.aktifkan', $item),
                                        'url_hapus' => route($teks['rute'].'.destroy', $item),
                                    ];
                                @endphp
                                <tr dusk="baris-{{ $item->id }}" class="align-top">
                                    <td class="px-6 py-4 font-medium">{{ $item->nama }}</td>
                                    <td class="max-w-md px-6 py-4 text-gray-700">{{ $item->deskripsi ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700">
                                        @if ($bagianPakai === [])
                                            <span class="italic text-gray-400">Belum dipakai</span>
                                        @else
                                            {{ $teksPakai }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($item->aktif)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Aktif</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-800"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <button type="button" data-aksi="ubah" data-item='@json($dataItem)' dusk="aksi-ubah-{{ $item->id }}"
                                            class="rounded-md px-2 py-1 font-medium text-indigo-900 hover:bg-indigo-50">Ubah</button>
                                        <div class="relative inline-block text-left" data-menu>
                                            <button type="button" data-menu-tombol dusk="menu-aksi-{{ $item->id }}"
                                                class="rounded-md px-2.5 py-1 text-lg leading-none text-gray-500 hover:bg-indigo-50 hover:text-indigo-900"
                                                aria-haspopup="true" aria-expanded="false" aria-label="Aksi lain untuk {{ $item->nama }}">&#8942;</button>
                                            <div data-menu-isi class="absolute right-0 z-10 mt-1 hidden w-44 rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg">
                                                @if ($item->aktif)
                                                    <button type="button" data-aksi="nonaktifkan" data-item='@json($dataItem)' dusk="aksi-nonaktifkan-{{ $item->id }}"
                                                        class="block w-full px-4 py-2 text-left hover:bg-gray-50">Nonaktifkan</button>
                                                @else
                                                    <button type="button" data-aksi="aktifkan" data-item='@json($dataItem)' dusk="aksi-aktifkan-{{ $item->id }}"
                                                        class="block w-full px-4 py-2 text-left text-emerald-700 hover:bg-emerald-50">Aktifkan</button>
                                                @endif
                                                {{-- Menu Hapus hanya untuk data yang belum dipakai. Server tetap memeriksa ulang. --}}
                                                @if ($bagianPakai === [])
                                                    <div class="my-1 border-t border-gray-100"></div>
                                                    <button type="button" data-aksi="hapus" data-item='@json($dataItem)' dusk="aksi-hapus-{{ $item->id }}"
                                                        class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">Hapus</button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada {{ $teks['benda'] }} yang cocok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                    <p>
                        @if ($daftar->total() > 0)
                            Menampilkan <strong>{{ $daftar->firstItem() }}-{{ $daftar->lastItem() }}</strong> dari <strong>{{ $daftar->total() }}</strong> {{ $teks['benda'] }}
                        @else
                            Menampilkan 0 {{ $teks['benda'] }}
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($daftar->onFirstPage())
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Sebelumnya</span>
                        @else
                            <a href="{{ $daftar->previousPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                        @endif
                        <span class="rounded-lg bg-indigo-900 px-3 py-1.5 font-medium text-white">{{ $daftar->currentPage() }}</span>
                        @if ($daftar->hasMorePages())
                            <a href="{{ $daftar->nextPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                        @else
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            </div>

            <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
                Hanya {{ $teks['benda'] }} berstatus <strong>Aktif</strong> yang muncul {{ $teks['tempat'] }}.
            </p>
        </main>
    </div>

    {{-- Modal: Tambah --}}
    <dialog id="modal-tambah" dusk="modal-tambah" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($formLama === 'tambah' && $errors->any()) data-buka-otomatis @endif>
        <form method="POST" action="{{ route($teks['rute'].'.store') }}" class="p-7">
            @csrf
            <input type="hidden" name="_form" value="tambah">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Tambah {{ $teks['Benda'] }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $teks['Benda'] }} baru otomatis berstatus <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-xs text-emerald-800">Aktif</span>.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            @php $salah = $formLama === 'tambah' ? $errors : new \Illuminate\Support\ViewErrorBag; @endphp
            <div class="mt-5 space-y-4">
                <div>
                    <label for="tambah_nama" class="block text-sm font-medium">Nama {{ $teks['Benda'] }} <span class="text-red-500">*</span></label>
                    <input id="tambah_nama" name="nama" type="text" maxlength="100" placeholder="{{ $teks['contoh_nama'] }}"
                        value="{{ $formLama === 'tambah' ? old('nama') : '' }}"
                        class="{{ $kelasInput }} {{ $salah->has('nama') ? $kelasInputSalah : $kelasInputNormal }}">
                    @if ($salah->has('nama'))
                        <p class="mt-1 text-xs text-red-600" dusk="error-tambah-nama">{{ $salah->first('nama') }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">Maksimal 100 karakter. Tidak boleh sama dengan {{ $teks['benda'] }} lain.</p>
                </div>
                <div>
                    <label for="tambah_deskripsi" class="block text-sm font-medium">Deskripsi <span class="font-normal text-gray-400">(opsional)</span></label>
                    <textarea id="tambah_deskripsi" name="deskripsi" rows="4" placeholder="{{ $teks['contoh_deskripsi'] }}"
                        class="{{ $kelasInput }} {{ $salah->has('deskripsi') ? $kelasInputSalah : $kelasInputNormal }}">{{ $formLama === 'tambah' ? old('deskripsi') : '' }}</textarea>
                    @if ($salah->has('deskripsi'))
                        <p class="mt-1 text-xs text-red-600">{{ $salah->first('deskripsi') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-tambah" class="rounded-lg bg-indigo-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Ubah --}}
    <dialog id="modal-ubah" dusk="modal-ubah" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($formLama === 'ubah' && $errors->any()) data-buka-otomatis @endif>
        <form method="POST" action="{{ $formLama === 'ubah' && old('_id') ? route($teks['rute'].'.update', old('_id')) : '' }}" class="p-7" data-form-ubah>
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="ubah">
            <input type="hidden" name="_id" value="{{ $formLama === 'ubah' ? old('_id') : '' }}">
            <input type="hidden" name="_nama_lama" value="{{ $formLama === 'ubah' ? old('_nama_lama') : '' }}">
            <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-xl font-semibold">Ubah {{ $teks['Benda'] }}</h2>
                    <p class="mt-1 text-sm text-gray-600">Perbarui data {{ $teks['benda'] }} <span data-isi="nama">{{ $formLama === 'ubah' ? old('_nama_lama') : '' }}</span>.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            @php $salah = $formLama === 'ubah' ? $errors : new \Illuminate\Support\ViewErrorBag; @endphp
            <div class="mt-5 space-y-4">
                <div>
                    <label for="ubah_nama" class="block text-sm font-medium">Nama {{ $teks['Benda'] }} <span class="text-red-500">*</span></label>
                    <input id="ubah_nama" name="nama" type="text" maxlength="100" data-kolom="nama"
                        value="{{ $formLama === 'ubah' ? old('nama') : '' }}"
                        class="{{ $kelasInput }} {{ $salah->has('nama') ? $kelasInputSalah : $kelasInputNormal }}">
                    @if ($salah->has('nama'))
                        <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('nama') }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">Maksimal 100 karakter. Tidak boleh sama dengan {{ $teks['benda'] }} lain.</p>
                </div>
                <div>
                    <label for="ubah_deskripsi" class="block text-sm font-medium">Deskripsi <span class="font-normal text-gray-400">(opsional)</span></label>
                    <textarea id="ubah_deskripsi" name="deskripsi" rows="4" data-kolom="deskripsi"
                        class="{{ $kelasInput }} {{ $salah->has('deskripsi') ? $kelasInputSalah : $kelasInputNormal }}">{{ $formLama === 'ubah' ? old('deskripsi') : '' }}</textarea>
                    @if ($salah->has('deskripsi'))
                        <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('deskripsi') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-ubah" class="rounded-lg bg-indigo-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan Perubahan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Nonaktifkan --}}
    <dialog id="modal-nonaktifkan" dusk="modal-nonaktifkan" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi class="p-7">
            @csrf
            @method('PATCH')
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-50 text-lg text-amber-700">!</span>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold">Nonaktifkan {{ $teks['benda'] }} <span data-isi="nama"></span>?</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $teks['Benda'] }} tidak akan muncul lagi {{ $teks['tempat'] }}.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <p data-peringatan-pakai dusk="peringatan-pakai" class="mt-5 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ $teks['Benda'] }} ini sudah dipakai pada <strong data-isi="pemakaian"></strong>, sehingga tidak dapat dihapus. Riwayat observasi tetap tersimpan.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-nonaktifkan" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Nonaktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Aktifkan --}}
    <dialog id="modal-aktifkan" dusk="modal-aktifkan" class="m-auto w-full max-w-md rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi class="p-7 text-center">
            @csrf
            @method('PATCH')
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-xl text-emerald-700">&#10003;</span>
            <h2 class="mt-4 text-lg font-semibold">Aktifkan kembali {{ $teks['benda'] }} <span data-isi="nama"></span>?</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $teks['Benda'] }} akan muncul lagi {{ $teks['tempat'] }}.</p>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-aktifkan" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Aktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Hapus (hanya untuk data yang belum dipakai) --}}
    <dialog id="modal-hapus" dusk="modal-hapus" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi class="p-7">
            @csrf
            @method('DELETE')
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-lg text-red-600">&#128465;</span>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold">Hapus {{ $teks['benda'] }} <span data-isi="nama"></span>?</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $teks['Benda'] }} ini belum pernah dipakai. Data akan dihapus permanen dan tidak dapat dikembalikan.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-hapus" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">Hapus</button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            const tutupSemuaMenu = (kecuali = null) => {
                document.querySelectorAll('[data-menu]').forEach((menu) => {
                    if (menu === kecuali) return;
                    menu.querySelector('[data-menu-isi]').classList.add('hidden');
                    menu.querySelector('[data-menu-tombol]').setAttribute('aria-expanded', 'false');
                });
            };

            const isiTeks = (modal, item) => {
                modal.querySelectorAll('[data-isi]').forEach((el) => {
                    el.textContent = item[el.dataset.isi] ?? '';
                });
            };

            const bukaKonfirmasi = (id, url, item) => {
                const modal = document.getElementById(id);
                modal.querySelector('[data-form-aksi]').action = url;
                isiTeks(modal, item);
                return modal;
            };

            const aksi = {
                ubah(item) {
                    const modal = document.getElementById('modal-ubah');
                    const form = modal.querySelector('[data-form-ubah]');
                    form.action = item.url_ubah;
                    form.elements._id.value = item.id;
                    form.elements._nama_lama.value = item.nama;
                    modal.querySelectorAll('[data-kolom]').forEach((input) => {
                        input.value = item[input.dataset.kolom] ?? '';
                        input.classList.remove('border-red-500', 'focus:border-red-500');
                        input.classList.add('border-gray-300', 'focus:border-indigo-700');
                    });
                    modal.querySelectorAll('[data-pesan-salah]').forEach((el) => el.remove());
                    isiTeks(modal, item);
                    modal.showModal();
                },
                nonaktifkan(item) {
                    const modal = bukaKonfirmasi('modal-nonaktifkan', item.url_nonaktifkan, item);
                    modal.querySelector('[data-peringatan-pakai]').classList.toggle('hidden', !item.dipakai);
                    modal.showModal();
                },
                aktifkan(item) {
                    bukaKonfirmasi('modal-aktifkan', item.url_aktifkan, item).showModal();
                },
                hapus(item) {
                    bukaKonfirmasi('modal-hapus', item.url_hapus, item).showModal();
                },
            };

            document.addEventListener('click', (event) => {
                const tombolMenu = event.target.closest('[data-menu-tombol]');
                if (tombolMenu) {
                    const menu = tombolMenu.closest('[data-menu]');
                    const isi = menu.querySelector('[data-menu-isi]');
                    tutupSemuaMenu(menu);
                    const terbuka = isi.classList.toggle('hidden') === false;
                    tombolMenu.setAttribute('aria-expanded', String(terbuka));
                    return;
                }

                const tombolAksi = event.target.closest('[data-aksi]');
                if (tombolAksi) {
                    tutupSemuaMenu();
                    aksi[tombolAksi.dataset.aksi](JSON.parse(tombolAksi.dataset.item));
                    return;
                }

                const tombolBuka = event.target.closest('[data-buka]');
                if (tombolBuka) {
                    document.getElementById(tombolBuka.dataset.buka)?.showModal();
                    return;
                }

                const tombolTutup = event.target.closest('[data-tutup]');
                if (tombolTutup) {
                    tombolTutup.closest('dialog')?.close();
                    return;
                }

                if (!event.target.closest('[data-menu]')) {
                    tutupSemuaMenu();
                }
            });

            document.querySelectorAll('dialog[data-buka-otomatis]').forEach((modal) => modal.showModal());
        })();
    </script>
</body>
</html>
