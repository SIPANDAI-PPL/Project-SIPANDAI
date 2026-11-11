{{-- PBI-1 Kelola Akun Guru dan Reset Kata Sandi. Halaman berdiri sendiri karena layout admin belum ada. --}}
@php
    $inisial = fn (string $nama) => collect(preg_split('/\s+/', trim($nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    $formLama = old('_form');
    $sandiSementara = session('sandi_sementara');

    $kelasInput = 'mt-1 block w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akun Guru - SIPANDAI</title>
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
                <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
                <a href="{{ route('admin.guru.index') }}" class="block rounded-lg bg-indigo-50 px-3 py-2 font-medium text-indigo-900">Kelola Akun Guru</a>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-6 lg:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">Kelola Akun Guru</h1>
                    <p class="mt-1 text-sm text-gray-600">Kelola data guru dan akses login sistem.</p>
                </div>
                <button type="button" data-buka="modal-tambah" dusk="tombol-tambah-guru"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    + Tambah Guru
                </button>
            </div>

            @if (session('status'))
                <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @error('nonaktifkan')
                <p class="mt-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800" role="alert" dusk="pesan-gagal-nonaktifkan">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.guru.index') }}" class="w-full max-w-sm">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label for="q" class="sr-only">Cari guru</label>
                    <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="Cari nama, email, atau NIP..."
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </form>

                <nav class="flex rounded-lg bg-gray-100 p-1 text-sm" aria-label="Filter status">
                    @foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $kunci => $label)
                        <a href="{{ route('admin.guru.index', array_filter(['q' => $cari, 'status' => $kunci === 'semua' ? null : $kunci])) }}"
                            @class([
                                'rounded-md px-4 py-1.5',
                                'bg-white font-medium text-indigo-900 shadow-sm' => $status === $kunci,
                                'text-gray-500 hover:text-gray-800' => $status !== $kunci,
                            ])
                            @if ($status === $kunci) aria-current="page" @endif>{{ $label }} ({{ $jumlah[$kunci] }})</a>
                    @endforeach
                </nav>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Guru</th>
                                <th class="px-6 py-4 font-medium">NIP</th>
                                <th class="px-6 py-4 font-medium">Kelas Diampu</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($daftarGuru as $guru)
                                @php
                                    $kelas = $guru->kelasDiampu->map(fn ($item) => "{$item->kelompok} - {$item->nama_kelas}")->values();
                                    $dataGuru = [
                                        'id' => $guru->id,
                                        'nama' => $guru->nama,
                                        'nama_pengguna' => $guru->nama_pengguna,
                                        'email' => $guru->email,
                                        'nip' => $guru->nip,
                                        'inisial' => $inisial($guru->nama),
                                        'kelas' => $kelas->all(),
                                        'url_ubah' => route('admin.guru.update', $guru),
                                        'url_nonaktifkan' => route('admin.guru.nonaktifkan', $guru),
                                        'url_aktifkan' => route('admin.guru.aktifkan', $guru),
                                        'url_reset' => route('admin.guru.reset-kata-sandi', $guru),
                                    ];
                                @endphp
                                <tr dusk="baris-guru-{{ $guru->id }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span @class([
                                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                                'bg-emerald-50 text-emerald-800' => $guru->aktif,
                                                'bg-orange-50 text-orange-800' => ! $guru->aktif,
                                            ])>{{ $inisial($guru->nama) }}</span>
                                            <div>
                                                <p class="font-medium">{{ $guru->nama }}</p>
                                                <p class="text-xs text-gray-500">{{ $guru->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $guru->nip ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @forelse ($kelas as $label)
                                            <span class="mr-1 inline-block rounded-md border border-gray-300 px-2.5 py-1 text-xs">{{ $label }}</span>
                                        @empty
                                            <span class="text-xs italic text-gray-500">Tanpa Kelas</span>
                                        @endforelse
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($guru->aktif)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Aktif</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-800"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="relative inline-block text-left" data-menu>
                                            <button type="button" data-menu-tombol dusk="menu-aksi-{{ $guru->id }}"
                                                class="rounded-md px-2.5 py-1 text-lg leading-none text-gray-500 hover:bg-indigo-50 hover:text-indigo-900"
                                                aria-haspopup="true" aria-expanded="false" aria-label="Aksi untuk {{ $guru->nama }}">&#8942;</button>
                                            <div data-menu-isi class="absolute right-0 z-10 mt-1 hidden w-48 rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg">
                                                <button type="button" data-aksi="ubah" data-guru='@json($dataGuru)' dusk="aksi-ubah-{{ $guru->id }}"
                                                    class="block w-full px-4 py-2 text-left hover:bg-gray-50">Ubah</button>
                                                <button type="button" data-aksi="reset" data-guru='@json($dataGuru)' dusk="aksi-reset-{{ $guru->id }}"
                                                    class="block w-full px-4 py-2 text-left hover:bg-gray-50">Reset Kata Sandi</button>
                                                <div class="my-1 border-t border-gray-100"></div>
                                                @if ($guru->aktif)
                                                    <button type="button" data-aksi="nonaktifkan" data-guru='@json($dataGuru)' dusk="aksi-nonaktifkan-{{ $guru->id }}"
                                                        class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">Nonaktifkan</button>
                                                @else
                                                    <button type="button" data-aksi="aktifkan" data-guru='@json($dataGuru)' dusk="aksi-aktifkan-{{ $guru->id }}"
                                                        class="block w-full px-4 py-2 text-left text-emerald-700 hover:bg-emerald-50">Aktifkan</button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada guru yang cocok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                    <p>
                        @if ($daftarGuru->total() > 0)
                            Menampilkan <strong>{{ $daftarGuru->firstItem() }} - {{ $daftarGuru->lastItem() }}</strong> dari <strong>{{ $daftarGuru->total() }}</strong> guru
                        @else
                            Menampilkan 0 guru
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($daftarGuru->onFirstPage())
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Sebelumnya</span>
                        @else
                            <a href="{{ $daftarGuru->previousPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                        @endif
                        <span class="rounded-lg bg-indigo-900 px-3 py-1.5 font-medium text-white">{{ $daftarGuru->currentPage() }}</span>
                        @if ($daftarGuru->hasMorePages())
                            <a href="{{ $daftarGuru->nextPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                        @else
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            </div>

            <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
                Akun guru berstatus <strong>Aktif</strong> dapat login dan mencatat observasi untuk kelas yang diampunya.
            </p>
        </main>
    </div>

    {{-- Modal: Tambah Akun Guru --}}
    <dialog id="modal-tambah" dusk="modal-tambah" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($formLama === 'tambah' && $errors->any()) data-buka-otomatis @endif>
        <form method="POST" action="{{ route('admin.guru.store') }}" class="p-7">
            @csrf
            <input type="hidden" name="_form" value="tambah">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Tambah Akun Guru</h2>
                    <p class="mt-1 text-sm text-gray-600">Akun baru otomatis berstatus Aktif dengan peran Guru.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            @php $salah = $formLama === 'tambah' ? $errors : new \Illuminate\Support\ViewErrorBag; @endphp
            <div class="mt-5 space-y-4">
                @foreach ([
                    ['nama', 'Nama Lengkap', true, 'Contoh: Rina Kartika', 'text', null],
                    ['nama_pengguna', 'Nama Pengguna', true, 'Contoh: rinakartika', 'text', 'Dipakai untuk login. Tidak boleh sama dengan akun lain.'],
                    ['email', 'Email', true, 'nama@tkceria.sch.id', 'email', null],
                    ['nip', 'NIP (opsional)', false, '18 digit NIP', 'text', null],
                ] as [$nama, $label, $wajib, $contoh, $tipe, $bantuan])
                    <div>
                        <label for="tambah_{{ $nama }}" class="block text-sm font-medium">{{ $label }} @if ($wajib)<span class="text-red-500">*</span>@endif</label>
                        <input id="tambah_{{ $nama }}" name="{{ $nama }}" type="{{ $tipe }}" placeholder="{{ $contoh }}"
                            value="{{ $formLama === 'tambah' ? old($nama) : '' }}" @if ($wajib) required @endif
                            class="{{ $kelasInput }} {{ $salah->has($nama) ? $kelasInputSalah : $kelasInputNormal }} {{ $nama === 'nip' ? 'font-mono' : '' }}">
                        @if ($salah->has($nama))
                            <p class="mt-1 text-xs text-red-600">{{ $salah->first($nama) }}</p>
                        @elseif ($bantuan)
                            <p class="mt-1 text-xs text-gray-500">{{ $bantuan }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="mt-5 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">Kata sandi sementara dibuat otomatis oleh sistem setelah akun disimpan.</p>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-tambah" class="rounded-lg bg-indigo-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Ubah Akun Guru --}}
    <dialog id="modal-ubah" dusk="modal-ubah" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($formLama === 'ubah' && $errors->any()) data-buka-otomatis @endif>
        <form method="POST" action="{{ $formLama === 'ubah' && old('_guru_id') ? route('admin.guru.update', old('_guru_id')) : '' }}" class="p-7" data-form-ubah>
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="ubah">
            <input type="hidden" name="_guru_id" value="{{ $formLama === 'ubah' ? old('_guru_id') : '' }}">
            <input type="hidden" name="_guru_nama" value="{{ $formLama === 'ubah' ? old('_guru_nama') : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Ubah Akun Guru</h2>
                    <p class="mt-1 text-sm text-gray-600">Perbarui data akun <span data-isi="nama">{{ $formLama === 'ubah' ? old('_guru_nama') : '' }}</span>.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            @php $salah = $formLama === 'ubah' ? $errors : new \Illuminate\Support\ViewErrorBag; @endphp
            <div class="mt-5 space-y-4">
                @foreach ([
                    ['nama', 'Nama Lengkap', true, 'text', null],
                    ['nama_pengguna', 'Nama Pengguna', true, 'text', 'Dipakai untuk login. Tidak boleh sama dengan akun lain.'],
                    ['email', 'Email', true, 'email', null],
                    ['nip', 'NIP (opsional)', false, 'text', null],
                ] as [$nama, $label, $wajib, $tipe, $bantuan])
                    <div>
                        <label for="ubah_{{ $nama }}" class="block text-sm font-medium">{{ $label }} @if ($wajib)<span class="text-red-500">*</span>@endif</label>
                        <input id="ubah_{{ $nama }}" name="{{ $nama }}" type="{{ $tipe }}" data-kolom="{{ $nama }}"
                            value="{{ $formLama === 'ubah' ? old($nama) : '' }}" @if ($wajib) required @endif
                            class="{{ $kelasInput }} {{ $salah->has($nama) ? $kelasInputSalah : $kelasInputNormal }} {{ $nama === 'nip' ? 'font-mono' : '' }}">
                        @if ($salah->has($nama))
                            <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first($nama) }}</p>
                        @elseif ($bantuan)
                            <p class="mt-1 text-xs text-gray-500">{{ $bantuan }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="mt-5 rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-600">Perubahan data tidak mengubah kata sandi. Gunakan Reset Kata Sandi jika guru lupa kata sandi.</p>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-ubah" class="rounded-lg bg-indigo-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan Perubahan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Nonaktifkan --}}
    <dialog id="modal-nonaktifkan" dusk="modal-nonaktifkan" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi>
            @csrf
            @method('PATCH')
            <div class="p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-50 text-xl text-orange-700">!</span>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold">Nonaktifkan akun <span data-isi="nama"></span>?</h2>
                        <p class="mt-1 text-sm text-gray-600">Guru yang dinonaktifkan <strong>tidak bisa login</strong> ke dalam aplikasi SIPANDAI.</p>
                        <p class="mt-2 text-xs text-gray-500">Seluruh riwayat pengamatan dan data observasi terdahulu tetap tersimpan aman di sistem.</p>
                    </div>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>
                <p data-peringatan-kelas dusk="peringatan-kelas" class="mt-5 hidden rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                    <strong>Perhatian:</strong> Guru ini masih mengampu <strong data-isi="jumlah_kelas"></strong> (<strong data-isi="daftar_kelas"></strong>).
                    Alihkan kelas ke guru lain melalui menu <strong>Kelola Kelas &amp; Penugasan</strong> sebelum menonaktifkan akun.
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" data-tombol-nonaktifkan dusk="konfirmasi-nonaktifkan"
                    class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-500">Nonaktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Aktifkan --}}
    <dialog id="modal-aktifkan" dusk="modal-aktifkan" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi>
            @csrf
            @method('PATCH')
            <div class="flex items-start gap-4 p-7">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xl text-emerald-700">&#10003;</span>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold">Aktifkan kembali akun <span data-isi="nama"></span>?</h2>
                    <p class="mt-1 text-sm text-gray-600">Guru akan dapat login kembali ke aplikasi SIPANDAI menggunakan kata sandi terakhirnya. Seluruh data sebelumnya tetap tersimpan.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-aktifkan" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Aktifkan</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Konfirmasi Reset Kata Sandi --}}
    <dialog id="modal-reset" dusk="modal-reset" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-aksi class="p-7">
            @csrf
            <div class="flex items-start justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-xl text-indigo-900">&#9919;</span>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <h2 class="mt-3 text-lg font-semibold">Reset Kata Sandi Guru</h2>
            <p class="mt-1 text-sm text-gray-600">Sistem akan membuat kata sandi sementara untuk guru berikut. Kata sandi lama tidak akan berlaku lagi.</p>

            <div class="mt-4 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-50 text-xs font-semibold text-orange-800" data-isi="inisial"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium" data-isi="nama"></p>
                    <p class="truncate text-xs text-gray-500" data-isi="email"></p>
                </div>
                <span class="rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs" data-isi="label_kelas"></span>
            </div>

            <p class="mt-4 rounded-lg bg-indigo-50 px-4 py-3 text-xs text-indigo-900">Kata sandi sementara hanya ditampilkan satu kali. Guru wajib mengganti kata sandi saat pertama kali login.</p>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="konfirmasi-reset" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Reset Kata Sandi</button>
            </div>
        </form>
    </dialog>

    {{-- Modal: Kata Sandi Sementara (akun baru atau hasil reset). Isi hanya dari flash session, jadi tampil satu kali. --}}
    @if (is_array($sandiSementara))
        <dialog id="modal-sandi-sementara" dusk="modal-sandi-sementara" data-buka-otomatis
            class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
            <div class="p-7">
                <div class="flex items-start justify-between">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-xl text-emerald-700">&#10003;</span>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>
                <h2 class="mt-3 text-lg font-semibold">{{ $sandiSementara['judul'] }}</h2>

                <div class="mt-4 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-xs font-semibold text-emerald-800">{{ $inisial($sandiSementara['nama']) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">{{ $sandiSementara['nama'] }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $sandiSementara['email'] }}</p>
                    </div>
                    <span class="rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs">{{ $sandiSementara['kelas'] }}</span>
                </div>

                <p class="mt-5 text-xs font-medium text-gray-700">Kata Sandi Sementara</p>
                <div class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <code id="teks-sandi-sementara" dusk="teks-sandi-sementara" class="font-mono text-base font-semibold tracking-wider">{{ $sandiSementara['kata_sandi'] }}</code>
                    <button type="button" data-salin="teks-sandi-sementara" dusk="salin-sandi"
                        class="rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-900 hover:bg-indigo-100">Salin</button>
                </div>
                <p class="mt-2 text-xs text-red-700">Catat atau salin sekarang. Kata sandi ini tidak akan ditampilkan lagi setelah dialog ditutup.</p>
                <p class="mt-4 rounded-lg bg-indigo-50 px-4 py-3 text-xs text-indigo-900">Guru akan diminta mengubah kata sandi saat pertama kali login.</p>

                <div class="mt-6 border-t border-gray-100 pt-5">
                    <button type="button" data-tutup dusk="selesai-sandi" class="w-full rounded-lg bg-indigo-900 px-5 py-3 text-sm font-medium text-white hover:bg-indigo-800">Selesai</button>
                </div>
            </div>
        </dialog>
    @endif

    <script>
        (() => {
            const tutupSemuaMenu = (kecuali = null) => {
                document.querySelectorAll('[data-menu]').forEach((menu) => {
                    if (menu === kecuali) return;
                    menu.querySelector('[data-menu-isi]').classList.add('hidden');
                    menu.querySelector('[data-menu-tombol]').setAttribute('aria-expanded', 'false');
                });
            };

            const isiTeks = (modal, guru) => {
                const kelas = guru.kelas ?? [];
                const nilai = {
                    nama: guru.nama,
                    email: guru.email,
                    inisial: guru.inisial,
                    label_kelas: kelas.length ? kelas.join(', ') : 'Tanpa Kelas',
                    jumlah_kelas: `${kelas.length} kelas`,
                    daftar_kelas: kelas.join(', '),
                };
                modal.querySelectorAll('[data-isi]').forEach((el) => {
                    el.textContent = nilai[el.dataset.isi] ?? '';
                });
            };

            const bukaModal = (id) => document.getElementById(id)?.showModal();

            const aksi = {
                ubah(guru) {
                    const modal = document.getElementById('modal-ubah');
                    const form = modal.querySelector('[data-form-ubah]');
                    form.action = guru.url_ubah;
                    form.elements._guru_id.value = guru.id;
                    form.elements._guru_nama.value = guru.nama;
                    modal.querySelectorAll('[data-kolom]').forEach((input) => {
                        input.value = guru[input.dataset.kolom] ?? '';
                        input.classList.remove('border-red-500', 'focus:border-red-500');
                        input.classList.add('border-gray-300', 'focus:border-indigo-700');
                    });
                    modal.querySelectorAll('[data-pesan-salah]').forEach((el) => el.remove());
                    isiTeks(modal, guru);
                    modal.showModal();
                },
                nonaktifkan(guru) {
                    const modal = document.getElementById('modal-nonaktifkan');
                    const masihMengampu = (guru.kelas ?? []).length > 0;
                    modal.querySelector('[data-form-aksi]').action = guru.url_nonaktifkan;
                    modal.querySelector('[data-peringatan-kelas]').classList.toggle('hidden', !masihMengampu);
                    modal.querySelector('[data-tombol-nonaktifkan]').disabled = masihMengampu;
                    isiTeks(modal, guru);
                    modal.showModal();
                },
                aktifkan(guru) {
                    const modal = document.getElementById('modal-aktifkan');
                    modal.querySelector('[data-form-aksi]').action = guru.url_aktifkan;
                    isiTeks(modal, guru);
                    modal.showModal();
                },
                reset(guru) {
                    const modal = document.getElementById('modal-reset');
                    modal.querySelector('[data-form-aksi]').action = guru.url_reset;
                    isiTeks(modal, guru);
                    modal.showModal();
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
                    aksi[tombolAksi.dataset.aksi](JSON.parse(tombolAksi.dataset.guru));
                    return;
                }

                const tombolBuka = event.target.closest('[data-buka]');
                if (tombolBuka) {
                    bukaModal(tombolBuka.dataset.buka);
                    return;
                }

                const tombolTutup = event.target.closest('[data-tutup]');
                if (tombolTutup) {
                    tombolTutup.closest('dialog')?.close();
                    return;
                }

                const tombolSalin = event.target.closest('[data-salin]');
                if (tombolSalin) {
                    const teks = document.getElementById(tombolSalin.dataset.salin).textContent.trim();
                    const tandai = () => {
                        tombolSalin.textContent = 'Tersalin';
                        setTimeout(() => { tombolSalin.textContent = 'Salin'; }, 2000);
                    };
                    if (navigator.clipboard?.writeText) {
                        navigator.clipboard.writeText(teks).then(tandai).catch(() => {});
                    }
                    return;
                }

                if (!event.target.closest('[data-menu]')) {
                    tutupSemuaMenu();
                }
            });

            // Kata sandi sementara tidak boleh tersisa di DOM setelah dialog ditutup.
            document.getElementById('modal-sandi-sementara')?.addEventListener('close', (event) => event.target.remove());

            document.querySelectorAll('dialog[data-buka-otomatis]').forEach((modal) => modal.showModal());
        })();
    </script>
</body>
</html>
