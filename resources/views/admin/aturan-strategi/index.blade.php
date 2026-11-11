{{-- PBI-4 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi. Halaman berdiri sendiri karena layout admin belum ada. --}}
@php
    $inisial = fn (string $nama) => collect(preg_split('/\s+/', trim($nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    $formLama = old('_form');
    $salah = in_array($formLama, ['tambah', 'ubah'], true) ? $errors : new \Illuminate\Support\ViewErrorBag;
    $nilai = fn (string $kolom, mixed $bawaan = '') => $formLama ? old($kolom, $bawaan) : $bawaan;

    // Kelas chip dipakai di Blade dan JS (lewat @json) agar tampilan kalimat aturan seragam.
    $kelasChip = [
        'dasar' => 'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium',
        'jumlah' => 'border-gray-200 bg-white text-gray-800 font-semibold',
        'asal' => 'border-blue-200 bg-blue-50 text-blue-800',
        'alternatif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'nonaktif' => 'border-gray-300 bg-gray-100 text-gray-600',
        'kosong' => 'border-dashed border-gray-300 bg-white text-gray-400',
        'respons' => [
            'terlibat_aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'terlibat_dengan_pendampingan' => 'border-amber-200 bg-amber-50 text-amber-800',
            'kurang_tertarik' => 'border-orange-200 bg-orange-50 text-orange-800',
            'belum_terlibat' => 'border-red-200 bg-red-50 text-red-700',
        ],
    ];

    $kelasInput = 'block rounded-lg border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';

    $menuSamping = array_filter([
        ['admin.dashboard', 'Dashboard', false],
        ['admin.guru.index', 'Kelola Akun Guru', false],
        // Data Master dibuka dari tab Aktivitas (PBI-2) jika rutenya sudah ada.
        [\Illuminate\Support\Facades\Route::has('data-master.aktivitas.index') ? 'data-master.aktivitas.index' : 'data-master.aspek.index', 'Data Master', false],
        ['admin.aturan-strategi.index', 'Aturan Strategi', true],
    ], fn ($menu) => \Illuminate\Support\Facades\Route::has($menu[0]));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aturan Strategi - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900">
    <header class="flex items-center justify-between bg-indigo-950 px-6 py-4 text-white">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold tracking-wide">SIPANDAI</a>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 rounded-full border border-white/20 px-3 py-1.5 text-sm">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-xs font-semibold text-indigo-950">{{ $inisial(auth()->user()->nama) }}</span>
                {{ auth()->user()->nama }} <span class="text-white/60">&middot; Admin</span>
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
                    <a href="{{ route($rute) }}" @class([
                        'block rounded-lg px-3 py-2',
                        'bg-indigo-50 font-medium text-indigo-900' => $aktif,
                        'text-gray-700 hover:bg-gray-100' => ! $aktif,
                    ])>{{ $label }}</a>
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-6 lg:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">Aturan Strategi</h1>
                    <p class="mt-1 text-sm text-gray-600">Aturan yang digunakan sistem untuk menyusun saran alternatif strategi bagi guru.</p>
                </div>
                <button type="button" data-aksi="tambah" dusk="tombol-tambah-aturan"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    + Tambah Aturan
                </button>
            </div>

            @if (session('status'))
                <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
            @endif

            @error('hapus')
                <p class="mt-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800" role="alert" dusk="pesan-gagal-hapus">{{ $message }}</p>
            @enderror

            <form method="GET" action="{{ route('admin.aturan-strategi.index') }}" class="mt-6 flex flex-wrap gap-3">
                <label for="q" class="sr-only">Cari aturan atau strategi</label>
                <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="Cari aturan atau strategi"
                    class="min-w-60 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">

                <label for="filter-aspek" class="sr-only">Filter aspek</label>
                <select id="filter-aspek" name="aspek" onchange="this.form.requestSubmit()"
                    class="w-52 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Aspek: Semua</option>
                    @foreach ($pilihanAspek as $pilihan)
                        <option value="{{ $pilihan->id }}" @selected($aspek === $pilihan->id)>Aspek: {{ $pilihan->nama }}</option>
                    @endforeach
                </select>

                <label for="filter-status" class="sr-only">Filter status</label>
                <select id="filter-status" name="status" onchange="this.form.requestSubmit()"
                    class="w-44 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    @foreach (['semua' => 'Status: Semua', 'aktif' => 'Status: Aktif', 'nonaktif' => 'Status: Nonaktif'] as $kunci => $label)
                        <option value="{{ $kunci }}" @selected($status === $kunci)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="sr-only">Terapkan</button>
            </form>

            <div class="mt-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                    <h2 class="font-medium">Daftar Aturan Rekomendasi</h2>
                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">{{ $daftarAturan->total() }} Aturan</span>
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($daftarAturan as $aturan)
                        @php
                            $asal = $aturan->strategiAsal;
                            $alternatif = $aturan->strategiAlternatif;
                            $dataAturan = [
                                'id' => $aturan->id,
                                'respons' => $aturan->respons_pemicu->value,
                                'respons_label' => $aturan->respons_pemicu->label(),
                                'minimal' => $aturan->minimal_kejadian,
                                'asal_id' => $asal->id,
                                'asal_nama' => $asal->nama,
                                'asal_aktif' => $asal->aktif,
                                'alternatif_id' => $alternatif->id,
                                'alternatif_nama' => $alternatif->nama,
                                'alternatif_aktif' => $alternatif->aktif,
                                'aspek_id' => $aturan->aspek_id,
                                'aspek_nama' => $aturan->aspek?->nama ?? 'Semua aspek',
                                'keterangan' => $aturan->keterangan,
                                'aktif' => $aturan->aktif,
                                'jumlah_saran' => (int) $aturan->alternatif_strategi_count,
                                'url_ubah' => route('admin.aturan-strategi.update', $aturan),
                                'url_nonaktifkan' => route('admin.aturan-strategi.nonaktifkan', $aturan),
                                'url_aktifkan' => route('admin.aturan-strategi.aktifkan', $aturan),
                                'url_hapus' => route('admin.aturan-strategi.destroy', $aturan),
                            ];
                        @endphp
                        <li dusk="baris-aturan-{{ $aturan->id }}" @class([
                            'flex items-center gap-4 px-6 py-5',
                            'bg-gray-50/70' => ! $aturan->aktif,
                        ])>
                            <div class="min-w-0 flex-1">
                                <p class="sr-only">{{ $aturan->kalimat() }}</p>
                                <p aria-hidden="true" @class([
                                    'flex flex-wrap items-center gap-x-2 gap-y-1.5 text-[15px]',
                                    'text-gray-800' => $aturan->aktif,
                                    'text-gray-500' => ! $aturan->aktif,
                                ])>
                                    <span>Jika respons</span>
                                    <span class="{{ $kelasChip['dasar'] }} {{ $kelasChip['respons'][$aturan->respons_pemicu->value] }}">{{ $aturan->respons_pemicu->label() }}</span>
                                    <span>muncul</span>
                                    <span class="{{ $kelasChip['dasar'] }} {{ $kelasChip['jumlah'] }}">{{ $aturan->minimal_kejadian }}x</span>
                                    <span>pada strategi</span>
                                    <span class="{{ $kelasChip['dasar'] }} {{ $asal->aktif ? $kelasChip['asal'] : $kelasChip['nonaktif'] }}">{{ \App\Models\AturanStrategi::labelStrategi($asal) }}</span>
                                    <span class="text-gray-400">&rarr;</span>
                                    <span>sarankan</span>
                                    <span class="{{ $kelasChip['dasar'] }} {{ $alternatif->aktif ? $kelasChip['alternatif'] : $kelasChip['nonaktif'] }}">{{ \App\Models\AturanStrategi::labelStrategi($alternatif) }}</span>
                                </p>
                                <p class="mt-2 text-xs text-gray-500">
                                    Aspek: {{ $aturan->aspek?->nama ?? 'Semua aspek' }}
                                    <span class="mx-1">&bull;</span>
                                    @if ($aturan->alternatif_strategi_count > 0)
                                        Dipakai untuk {{ $aturan->alternatif_strategi_count }} saran
                                    @else
                                        Belum menghasilkan saran
                                    @endif
                                </p>
                            </div>

                            @if ($aturan->aktif)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800" dusk="status-aturan-{{ $aturan->id }}"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Aktif</span>
                            @else
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-800" dusk="status-aturan-{{ $aturan->id }}"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Nonaktif</span>
                            @endif

                            <div class="relative shrink-0 text-left" data-menu>
                                <button type="button" data-menu-tombol dusk="menu-aksi-{{ $aturan->id }}"
                                    class="rounded-md px-2.5 py-1 text-lg leading-none text-gray-500 hover:bg-indigo-50 hover:text-indigo-900"
                                    aria-haspopup="true" aria-expanded="false" aria-label="Aksi untuk aturan {{ $aturan->id }}">&#8942;</button>
                                <div data-menu-isi class="absolute right-0 z-10 mt-1 hidden w-44 rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg">
                                    <button type="button" data-aksi="ubah" data-aturan='@json($dataAturan)' dusk="aksi-ubah-{{ $aturan->id }}"
                                        class="block w-full px-4 py-2 text-left hover:bg-gray-50">Ubah</button>
                                    @if ($aturan->aktif)
                                        <button type="button" data-aksi="nonaktifkan" data-aturan='@json($dataAturan)' dusk="aksi-nonaktifkan-{{ $aturan->id }}"
                                            class="block w-full px-4 py-2 text-left text-orange-700 hover:bg-orange-50">Nonaktifkan</button>
                                    @else
                                        <button type="button" data-aksi="aktifkan" data-aturan='@json($dataAturan)' dusk="aksi-aktifkan-{{ $aturan->id }}"
                                            class="block w-full px-4 py-2 text-left text-emerald-700 hover:bg-emerald-50">Aktifkan</button>
                                    @endif
                                    @if ((int) $aturan->alternatif_strategi_count === 0)
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <button type="button" data-aksi="hapus" data-aturan='@json($dataAturan)' dusk="aksi-hapus-{{ $aturan->id }}"
                                            class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">Hapus</button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada aturan yang cocok.</li>
                    @endforelse
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                    <p>
                        @if ($daftarAturan->total() > 0)
                            Menampilkan <strong>{{ $daftarAturan->firstItem() }}-{{ $daftarAturan->lastItem() }}</strong> dari <strong>{{ $daftarAturan->total() }}</strong> aturan
                        @else
                            Menampilkan 0 aturan
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($daftarAturan->onFirstPage())
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Sebelumnya</span>
                        @else
                            <a href="{{ $daftarAturan->previousPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                        @endif
                        <span class="rounded-lg bg-indigo-900 px-3 py-1.5 font-medium text-white">{{ $daftarAturan->currentPage() }}</span>
                        @if ($daftarAturan->hasMorePages())
                            <a href="{{ $daftarAturan->nextPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                        @else
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            </div>

            <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
                Aturan aktif dipakai sistem untuk menyusun saran strategi. Saran tetap ditinjau dan diputuskan oleh guru.
            </p>
        </main>
    </div>

    {{-- Drawer: Tambah / Ubah Aturan Strategi (satu form, mode diatur JS) --}}
    <dialog id="drawer-aturan" dusk="drawer-aturan"
        class="my-0 ml-auto mr-0 h-dvh max-h-none w-full max-w-xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($formLama && $errors->any()) data-buka-otomatis @endif>
        <form method="POST" data-form-aturan class="flex h-full flex-col"
            action="{{ $formLama === 'ubah' && old('_aturan_id') ? route('admin.aturan-strategi.update', old('_aturan_id')) : route('admin.aturan-strategi.store') }}">
            @csrf
            <input type="hidden" name="_method" value="PUT" data-metode @disabled($formLama !== 'ubah')>
            <input type="hidden" name="_form" value="{{ $formLama === 'ubah' ? 'ubah' : 'tambah' }}">
            <input type="hidden" name="_aturan_id" value="{{ $formLama === 'ubah' ? old('_aturan_id') : '' }}">
            <input type="hidden" name="_jumlah_saran" value="{{ $formLama === 'ubah' ? old('_jumlah_saran', 0) : 0 }}">

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <span data-judul-drawer>{{ $formLama === 'ubah' ? 'Ubah Aturan Strategi' : 'Tambah Aturan Strategi' }}</span>
                    <span data-lencana-baru @class(['rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900', 'hidden' => $formLama === 'ubah'])>Baru</span>
                </h2>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <p data-info-saran @class([
                    'rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900',
                    'hidden' => ! ($formLama === 'ubah' && (int) old('_jumlah_saran') > 0),
                ])>Aturan ini sudah menghasilkan <strong data-isi-jumlah-saran>{{ (int) old('_jumlah_saran') }}</strong> saran. Perubahan tidak mengubah saran yang sudah ada.</p>

                @if ($salah->has('aturan'))
                    <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="salah-aturan" data-pesan-salah>{{ $salah->first('aturan') }}</p>
                @endif

                <fieldset class="space-y-4 rounded-2xl border border-indigo-200 bg-indigo-50/60 p-5">
                    <legend class="sr-only">Formulasi kalimat aturan</legend>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-900">&#10022; Formulasi Kalimat Aturan</p>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label for="respons_pemicu" class="text-sm text-gray-700">Jika anak menunjukkan respons</label>
                            <select id="respons_pemicu" name="respons_pemicu" required data-kolom="respons" dusk="pilih-respons"
                                class="{{ $kelasInput }} min-w-48 flex-1 {{ $salah->has('respons_pemicu') ? $kelasInputSalah : $kelasInputNormal }}">
                                <option value="">Pilih respons</option>
                                @foreach ($pilihanRespons as $respons)
                                    <option value="{{ $respons->value }}" @selected($nilai('respons_pemicu') === $respons->value)>{{ $respons->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($salah->has('respons_pemicu'))
                            <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('respons_pemicu') }}</p>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label for="strategi_asal_id" class="text-sm text-gray-700">pada strategi</label>
                            <select id="strategi_asal_id" name="strategi_asal_id" required data-kolom="asal_id" dusk="pilih-strategi-asal"
                                class="{{ $kelasInput }} min-w-40 flex-1 {{ $salah->has('strategi_asal_id') ? $kelasInputSalah : $kelasInputNormal }}">
                                <option value="">Pilih strategi</option>
                                @foreach ($pilihanStrategi as $strategi)
                                    <option value="{{ $strategi->id }}" @selected((string) $nilai('strategi_asal_id') === (string) $strategi->id)>{{ $strategi->nama }}</option>
                                @endforeach
                            </select>
                            <label for="minimal_kejadian" class="text-sm text-gray-700">sebanyak</label>
                            <input id="minimal_kejadian" name="minimal_kejadian" type="number" min="2" max="100" step="1" required
                                value="{{ $nilai('minimal_kejadian', 3) }}" data-kolom="minimal" dusk="isi-minimal"
                                class="{{ $kelasInput }} w-20 {{ $salah->has('minimal_kejadian') ? $kelasInputSalah : $kelasInputNormal }}">
                            <span class="text-sm text-gray-700">kali,</span>
                        </div>
                        @foreach (['strategi_asal_id', 'minimal_kejadian'] as $kolom)
                            @if ($salah->has($kolom))
                                <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first($kolom) }}</p>
                            @endif
                        @endforeach
                        <p class="mt-2 text-xs text-gray-500">Minimal 2 kali agar saran tidak disusun dari satu pengamatan.</p>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label for="aspek_id" class="text-sm text-gray-700">untuk aspek</label>
                            <select id="aspek_id" name="aspek_id" data-kolom="aspek_id" dusk="pilih-aspek"
                                class="{{ $kelasInput }} min-w-48 flex-1 {{ $salah->has('aspek_id') ? $kelasInputSalah : $kelasInputNormal }}">
                                <option value="">Semua aspek</option>
                                @foreach ($pilihanAspek as $pilihan)
                                    <option value="{{ $pilihan->id }}" @selected((string) $nilai('aspek_id') === (string) $pilihan->id)>{{ $pilihan->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($salah->has('aspek_id'))
                            <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('aspek_id') }}</p>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label for="strategi_alternatif_id" class="text-sm font-medium text-indigo-900">&rarr; sarankan strategi</label>
                            <select id="strategi_alternatif_id" name="strategi_alternatif_id" required data-kolom="alternatif_id" dusk="pilih-strategi-alternatif"
                                class="{{ $kelasInput }} min-w-48 flex-1 {{ $salah->has('strategi_alternatif_id') ? $kelasInputSalah : $kelasInputNormal }}">
                                <option value="">Pilih strategi</option>
                                @foreach ($pilihanStrategi as $strategi)
                                    <option value="{{ $strategi->id }}" @selected((string) $nilai('strategi_alternatif_id') === (string) $strategi->id)>{{ $strategi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p data-pesan-alternatif dusk="salah-strategi-alternatif" @class([
                            'mt-1 text-xs text-red-600',
                            'hidden' => ! $salah->has('strategi_alternatif_id'),
                        ])>{{ $salah->first('strategi_alternatif_id') }}</p>
                    </div>
                </fieldset>

                <div data-pratinjau dusk="pratinjau-aturan" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p data-judul-pratinjau class="text-xs font-medium uppercase tracking-wide text-gray-600">Pratinjau Hasil Rumusan</p>
                    <p data-kalimat-pratinjau class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1.5 text-sm text-gray-800"></p>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium">Keterangan <span class="font-normal text-gray-400">(opsional)</span></label>
                    <textarea id="keterangan" name="keterangan" rows="3" data-kolom="keterangan"
                        placeholder="Tuliskan catatan konteks penerapan aturan atau rekomendasi ini..."
                        class="{{ $kelasInput }} mt-1 w-full {{ $salah->has('keterangan') ? $kelasInputSalah : $kelasInputNormal }}">{{ $nilai('keterangan') }}</textarea>
                    @if ($salah->has('keterangan'))
                        <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('keterangan') }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium">Aturan aktif</p>
                        <p class="text-xs text-gray-500">Aktifkan aturan segera setelah disimpan untuk proses rekomendasi.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center">
                        <span class="sr-only">Aturan aktif</span>
                        <input type="hidden" name="aktif" value="0">
                        <input type="checkbox" name="aktif" value="1" class="peer sr-only" data-kolom="aktif" dusk="toggle-aktif"
                            @checked(filter_var($nilai('aktif', true), FILTER_VALIDATE_BOOLEAN))>
                        <span class="relative h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-indigo-900 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-300 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 border-t border-gray-100 px-6 py-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-aturan" data-tombol-simpan
                    class="rounded-lg bg-indigo-900 px-5 py-3 text-sm font-medium text-white hover:bg-indigo-800">{{ $formLama === 'ubah' ? 'Simpan Perubahan' : 'Simpan Aturan' }}</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog: Konfirmasi Nonaktifkan --}}
    <dialog id="modal-nonaktifkan" dusk="modal-nonaktifkan" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi>
            @csrf
            @method('PATCH')
            <div class="p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-50 text-xl text-amber-600">!</span>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold">Nonaktifkan aturan ini?</h2>
                        <p class="mt-1 text-sm text-gray-600">Sistem tidak akan memakai aturan ini untuk menyusun saran baru.</p>
                    </div>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>
                <p data-kalimat class="mt-5 flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800"></p>
                <p data-peringatan-saran class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Aturan ini sudah menghasilkan <strong data-isi="jumlah_saran"></strong> saran, sehingga tidak dapat dihapus. Saran yang sudah ada tetap tersimpan.
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-nonaktifkan" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Nonaktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog: Konfirmasi Aktifkan --}}
    <dialog id="modal-aktifkan" dusk="modal-aktifkan" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi>
            @csrf
            @method('PATCH')
            <div class="p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xl text-emerald-700">&#10003;</span>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold">Aktifkan kembali aturan ini?</h2>
                        <p class="mt-1 text-sm text-gray-600">Sistem akan memakai aturan ini lagi untuk menyusun saran strategi.</p>
                    </div>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>
                <p data-kalimat class="mt-5 flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800"></p>
                <p data-peringatan-strategi class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Strategi <strong data-isi="strategi_nonaktif"></strong> sedang nonaktif, jadi saran dari aturan ini belum akan muncul.
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-aktifkan" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Aktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog: Konfirmasi Hapus (hanya untuk aturan yang belum menghasilkan saran) --}}
    <dialog id="modal-hapus" dusk="modal-hapus" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi>
            @csrf
            @method('DELETE')
            <div class="p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
                    </span>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold">Hapus aturan ini?</h2>
                        <p class="mt-1 text-sm text-gray-600">Aturan ini belum pernah menghasilkan saran. Data akan dihapus permanen dan tidak dapat dikembalikan.</p>
                    </div>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>
                <p data-kalimat class="mt-5 flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800"></p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-hapus" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">Hapus</button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            const KELAS_CHIP = @json($kelasChip);
            const PESAN_STRATEGI_SAMA = 'Strategi asal dan strategi alternatif tidak boleh sama.';
            const URL_SIMPAN = @json(route('admin.aturan-strategi.store'));

            // Semua teks dari data dimasukkan lewat textContent, bukan innerHTML.
            const chip = (teks, kelas) => {
                const el = document.createElement('span');
                el.className = `${KELAS_CHIP.dasar} ${kelas}`;
                el.textContent = teks;
                return el;
            };
            const kata = (teks, kelas = '') => {
                const el = document.createElement('span');
                if (kelas) el.className = kelas;
                el.textContent = teks;
                return el;
            };
            const labelStrategi = (nama, aktif) => (aktif === false ? `${nama} (nonaktif)` : nama);

            /**
             * Isi wadah dengan kalimat aturan dalam bentuk chip.
             * d: { respons, respons_label, minimal, asal_nama, asal_aktif, alternatif_nama, alternatif_aktif, aspek_nama? }
             */
            const susunKalimat = (wadah, d, { konflik = false } = {}) => {
                const kosong = KELAS_CHIP.kosong;
                const bagian = [
                    kata('Jika respons'),
                    d.respons ? chip(d.respons_label, KELAS_CHIP.respons[d.respons] ?? kosong) : chip('pilih respons', kosong),
                    kata('muncul'),
                    chip(`${d.minimal || '…'}x`, KELAS_CHIP.jumlah),
                    kata('pada strategi'),
                    d.asal_nama
                        ? chip(labelStrategi(d.asal_nama, d.asal_aktif), d.asal_aktif === false ? KELAS_CHIP.nonaktif : KELAS_CHIP.asal)
                        : chip('pilih strategi', kosong),
                    kata('→', 'text-gray-400'),
                    kata('sarankan'),
                    d.alternatif_nama
                        ? chip(
                            labelStrategi(d.alternatif_nama, d.alternatif_aktif),
                            konflik ? 'border-red-300 bg-white text-red-700' : (d.alternatif_aktif === false ? KELAS_CHIP.nonaktif : KELAS_CHIP.alternatif),
                        )
                        : chip('pilih strategi', kosong),
                ];
                if (d.aspek_nama !== undefined) {
                    bagian.push(kata(`· Aspek: ${d.aspek_nama}`, konflik ? 'text-red-700' : 'text-gray-600'));
                }
                wadah.replaceChildren(...bagian);
            };

            // ----- Drawer tambah / ubah -----
            const drawer = document.getElementById('drawer-aturan');
            const form = drawer.querySelector('[data-form-aturan]');
            const kolom = (nama) => form.querySelector(`[data-kolom="${nama}"]`);
            const teksPilihan = (select) => (select.value ? select.options[select.selectedIndex].text : '');
            const pesanAlternatif = form.querySelector('[data-pesan-alternatif]');
            const selectAlternatif = kolom('alternatif_id');

            const perbaruiPratinjau = () => {
                const asal = kolom('asal_id');
                const sama = asal.value !== '' && asal.value === selectAlternatif.value;

                susunKalimat(form.querySelector('[data-kalimat-pratinjau]'), {
                    respons: kolom('respons').value,
                    respons_label: teksPilihan(kolom('respons')),
                    minimal: kolom('minimal').value,
                    asal_nama: teksPilihan(asal),
                    alternatif_nama: teksPilihan(selectAlternatif),
                    aspek_nama: teksPilihan(kolom('aspek_id')) || 'Semua aspek',
                }, { konflik: sama });

                const pratinjau = form.querySelector('[data-pratinjau]');
                pratinjau.classList.toggle('border-red-200', sama);
                pratinjau.classList.toggle('bg-red-50', sama);
                pratinjau.classList.toggle('border-gray-200', !sama);
                pratinjau.classList.toggle('bg-gray-50', !sama);
                const judul = form.querySelector('[data-judul-pratinjau]');
                judul.textContent = sama ? 'Pratinjau Hasil Rumusan (Konflik Validasi)' : 'Pratinjau Hasil Rumusan';
                judul.classList.toggle('text-red-700', sama);
                judul.classList.toggle('text-gray-600', !sama);

                selectAlternatif.classList.toggle('border-red-500', sama);
                if (sama) {
                    pesanAlternatif.textContent = PESAN_STRATEGI_SAMA;
                    pesanAlternatif.classList.remove('hidden');
                } else if (pesanAlternatif.textContent.trim() === PESAN_STRATEGI_SAMA) {
                    pesanAlternatif.textContent = '';
                    pesanAlternatif.classList.add('hidden');
                }
            };

            const bersihkanSalah = () => {
                form.querySelectorAll('[data-pesan-salah]').forEach((el) => el.remove());
                pesanAlternatif.textContent = '';
                pesanAlternatif.classList.add('hidden');
                form.querySelectorAll('.border-red-500').forEach((el) => {
                    el.classList.remove('border-red-500', 'focus:border-red-500');
                    el.classList.add('border-gray-300', 'focus:border-indigo-700');
                });
            };

            const siapkanDrawer = (mode, aturan = null) => {
                const ubah = mode === 'ubah';
                bersihkanSalah();
                form.action = ubah ? aturan.url_ubah : URL_SIMPAN;
                form.querySelector('[data-metode]').disabled = !ubah;
                form.elements._form.value = mode;
                form.elements._aturan_id.value = ubah ? aturan.id : '';
                form.elements._jumlah_saran.value = ubah ? aturan.jumlah_saran : 0;
                drawer.querySelector('[data-judul-drawer]').textContent = ubah ? 'Ubah Aturan Strategi' : 'Tambah Aturan Strategi';
                drawer.querySelector('[data-lencana-baru]').classList.toggle('hidden', ubah);
                drawer.querySelector('[data-tombol-simpan]').textContent = ubah ? 'Simpan Perubahan' : 'Simpan Aturan';

                const info = drawer.querySelector('[data-info-saran]');
                info.classList.toggle('hidden', !(ubah && aturan.jumlah_saran > 0));
                info.querySelector('[data-isi-jumlah-saran]').textContent = ubah ? aturan.jumlah_saran : 0;

                kolom('respons').value = ubah ? aturan.respons : '';
                kolom('asal_id').value = ubah ? String(aturan.asal_id) : '';
                kolom('minimal').value = ubah ? aturan.minimal : 3;
                kolom('aspek_id').value = ubah && aturan.aspek_id ? String(aturan.aspek_id) : '';
                // Strategi nonaktif tidak ada di pilihan, jadi select kembali ke "Pilih strategi".
                selectAlternatif.value = ubah ? String(aturan.alternatif_id) : '';
                kolom('keterangan').value = ubah ? (aturan.keterangan ?? '') : '';
                kolom('aktif').checked = ubah ? aturan.aktif : true;

                perbaruiPratinjau();
                drawer.showModal();
            };

            form.addEventListener('input', perbaruiPratinjau);
            form.addEventListener('change', perbaruiPratinjau);

            // ----- Dialog konfirmasi -----
            const isiDialog = (id, aturan, url) => {
                const modal = document.getElementById(id);
                modal.querySelector('[data-form-aksi]').action = url;
                susunKalimat(modal.querySelector('[data-kalimat]'), {
                    respons: aturan.respons,
                    respons_label: aturan.respons_label,
                    minimal: aturan.minimal,
                    asal_nama: aturan.asal_nama,
                    asal_aktif: aturan.asal_aktif,
                    alternatif_nama: aturan.alternatif_nama,
                    alternatif_aktif: aturan.alternatif_aktif,
                });
                return modal;
            };

            const aksi = {
                tambah() {
                    siapkanDrawer('tambah');
                },
                ubah(aturan) {
                    siapkanDrawer('ubah', aturan);
                },
                nonaktifkan(aturan) {
                    const modal = isiDialog('modal-nonaktifkan', aturan, aturan.url_nonaktifkan);
                    modal.querySelector('[data-peringatan-saran]').classList.toggle('hidden', aturan.jumlah_saran === 0);
                    modal.querySelector('[data-isi="jumlah_saran"]').textContent = aturan.jumlah_saran;
                    modal.showModal();
                },
                aktifkan(aturan) {
                    const modal = isiDialog('modal-aktifkan', aturan, aturan.url_aktifkan);
                    const nonaktif = [
                        aturan.asal_aktif ? null : aturan.asal_nama,
                        aturan.alternatif_aktif ? null : aturan.alternatif_nama,
                    ].filter(Boolean);
                    modal.querySelector('[data-peringatan-strategi]').classList.toggle('hidden', nonaktif.length === 0);
                    modal.querySelector('[data-isi="strategi_nonaktif"]').textContent = nonaktif.join(' dan ');
                    modal.showModal();
                },
                hapus(aturan) {
                    isiDialog('modal-hapus', aturan, aturan.url_hapus).showModal();
                },
            };

            const tutupSemuaMenu = (kecuali = null) => {
                document.querySelectorAll('[data-menu]').forEach((menu) => {
                    if (menu === kecuali) return;
                    menu.querySelector('[data-menu-isi]').classList.add('hidden');
                    menu.querySelector('[data-menu-tombol]').setAttribute('aria-expanded', 'false');
                });
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
                    const data = tombolAksi.dataset.aturan ? JSON.parse(tombolAksi.dataset.aturan) : null;
                    aksi[tombolAksi.dataset.aksi](data);
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

            perbaruiPratinjau();
            document.querySelectorAll('dialog[data-buka-otomatis]').forEach((modal) => modal.showModal());
        })();
    </script>
</body>
</html>
