{{-- PBI-4 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi. Halaman berdiri sendiri karena layout admin belum ada. --}}
@php
    $inisial = fn (string $nama) => collect(preg_split('/\s+/', trim($nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    $formLama = old('_form');

    $kelasInput = 'mt-1 block w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';

    $menuSamping = array_filter([
        ['admin.dashboard', 'Dashboard', false],
        ['admin.guru.index', 'Kelola Akun Guru', false],
        // Data Master dibuka dari tab Aktivitas (PBI-2) jika rutenya sudah ada.
        [\Illuminate\Support\Facades\Route::has('data-master.aktivitas.index') ? 'data-master.aktivitas.index' : 'data-master.aspek.index', 'Data Master', true],
        ['admin.aturan-strategi.index', 'Aturan Strategi', false],
    ], fn ($menu) => \Illuminate\Support\Facades\Route::has($menu[0]));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aspek Perkembangan - SIPANDAI</title>
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
            <div>
                <h1 class="text-2xl font-semibold">Data Master</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola referensi data master pendukung analisis observasi dan strategi pembelajaran bagi guru.</p>
            </div>

            @includeIf('data-master._tabs', ['tabAktif' => 'aspek'])

            @if (session('status'))
                <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
            @endif

            @error('hapus')
                <p class="mt-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800" role="alert" dusk="pesan-gagal-hapus">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('data-master.aspek.index') }}" class="w-full max-w-md">
                    <label for="q" class="sr-only">Cari aspek</label>
                    <input id="q" name="q" type="search" value="{{ $cari }}" placeholder="Cari nama aspek atau deskripsi..."
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </form>
                <button type="button" data-buka="modal-tambah" dusk="tombol-tambah-aspek"
                    class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                    + Tambah Aspek
                </button>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                    <h2 class="font-medium">Daftar Aspek Perkembangan</h2>
                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">{{ $daftarAspek->total() }} Aspek</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Nama Aspek</th>
                                <th class="px-6 py-4 font-medium">Deskripsi</th>
                                <th class="px-6 py-4 font-medium">Dipakai di</th>
                                <th class="px-6 py-4 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($daftarAspek as $aspek)
                                @php
                                    $dipakai = (int) $aspek->observasi_count + (int) $aspek->aturan_strategi_count;
                                    $dataAspek = [
                                        'id' => $aspek->id,
                                        'nama' => $aspek->nama,
                                        'deskripsi' => $aspek->deskripsi,
                                        'url_ubah' => route('data-master.aspek.update', $aspek),
                                        'url_hapus' => route('data-master.aspek.destroy', $aspek),
                                    ];
                                @endphp
                                <tr dusk="baris-aspek-{{ $aspek->id }}">
                                    <td class="px-6 py-4 align-top font-medium">{{ $aspek->nama }}</td>
                                    <td class="px-6 py-4 align-top text-gray-700">{{ $aspek->deskripsi ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 align-top text-gray-600">
                                        @if ($dipakai === 0)
                                            <span class="italic text-gray-400">Belum dipakai</span>
                                        @else
                                            {{ collect([
                                                $aspek->observasi_count > 0 ? "{$aspek->observasi_count} observasi" : null,
                                                $aspek->aturan_strategi_count > 0 ? "{$aspek->aturan_strategi_count} aturan" : null,
                                            ])->filter()->implode(' · ') }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right align-top">
                                        <button type="button" data-aksi="ubah" data-aspek='@json($dataAspek)' dusk="aksi-ubah-{{ $aspek->id }}"
                                            class="rounded-md px-2 py-1 font-medium text-indigo-900 hover:bg-indigo-50">Ubah</button>
                                        @if ($dipakai === 0)
                                            <div class="relative inline-block text-left" data-menu>
                                                <button type="button" data-menu-tombol dusk="menu-aksi-{{ $aspek->id }}"
                                                    class="rounded-md px-2.5 py-1 text-lg leading-none text-gray-500 hover:bg-indigo-50 hover:text-indigo-900"
                                                    aria-haspopup="true" aria-expanded="false" aria-label="Aksi lain untuk {{ $aspek->nama }}">&#8942;</button>
                                                <div data-menu-isi class="absolute right-0 z-10 mt-1 hidden w-36 rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg">
                                                    <button type="button" data-aksi="hapus" data-aspek='@json($dataAspek)' dusk="aksi-hapus-{{ $aspek->id }}"
                                                        class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">Hapus</button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-block px-2.5 py-1 text-lg leading-none text-gray-300" title="Aspek sudah dipakai sehingga tidak dapat dihapus.">&#8942;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada aspek yang cocok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                    <p>
                        @if ($daftarAspek->total() > 0)
                            Menampilkan <strong>{{ $daftarAspek->firstItem() }}-{{ $daftarAspek->lastItem() }}</strong> dari <strong>{{ $daftarAspek->total() }}</strong> aspek
                        @else
                            Menampilkan 0 aspek
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($daftarAspek->onFirstPage())
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Sebelumnya</span>
                        @else
                            <a href="{{ $daftarAspek->previousPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Sebelumnya</a>
                        @endif
                        <span class="rounded-lg bg-indigo-900 px-3 py-1.5 font-medium text-white">{{ $daftarAspek->currentPage() }}</span>
                        @if ($daftarAspek->hasMorePages())
                            <a href="{{ $daftarAspek->nextPageUrl() }}" class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Selanjutnya</a>
                        @else
                            <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-gray-300">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            </div>

            <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
                Aspek yang sudah dipakai pada observasi atau aturan tidak dapat dihapus.
            </p>
        </main>
    </div>

    {{-- Modal: Tambah dan Ubah Aspek memakai struktur yang sama --}}
    @foreach (['tambah' => 'Tambah Aspek Perkembangan', 'ubah' => 'Ubah Aspek Perkembangan'] as $mode => $judul)
        @php
            $salah = $formLama === $mode ? $errors : new \Illuminate\Support\ViewErrorBag;
            $aksiForm = $mode === 'tambah'
                ? route('data-master.aspek.store')
                : ($formLama === 'ubah' && old('_aspek_id') ? route('data-master.aspek.update', old('_aspek_id')) : '');
        @endphp
        <dialog id="modal-{{ $mode }}" dusk="modal-{{ $mode }}" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
            @if ($formLama === $mode && $errors->any()) data-buka-otomatis @endif>
            <form method="POST" action="{{ $aksiForm }}" class="p-7" data-form-{{ $mode }}>
                @csrf
                @if ($mode === 'ubah')
                    @method('PUT')
                    <input type="hidden" name="_aspek_id" value="{{ $formLama === 'ubah' ? old('_aspek_id') : '' }}">
                @endif
                <input type="hidden" name="_form" value="{{ $mode }}">
                <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                    <h2 class="text-xl font-semibold">{{ $judul }}</h2>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="{{ $mode }}_nama" class="block text-sm font-medium">Nama Aspek <span class="text-red-500">*</span></label>
                        <input id="{{ $mode }}_nama" name="nama" type="text" maxlength="100" placeholder="Contoh: Kemampuan literasi awal"
                            data-kolom="nama" value="{{ $formLama === $mode ? old('nama') : '' }}"
                            class="{{ $kelasInput }} {{ $salah->has('nama') ? $kelasInputSalah : $kelasInputNormal }}">
                        @if ($salah->has('nama'))
                            <p class="mt-1 text-xs text-red-600" data-pesan-salah dusk="salah-nama-{{ $mode }}">{{ $salah->first('nama') }}</p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">Maksimal 100 karakter. Tidak boleh sama dengan aspek lain.</p>
                        @endif
                    </div>
                    <div>
                        <label for="{{ $mode }}_deskripsi" class="block text-sm font-medium">Deskripsi <span class="font-normal text-gray-400">(opsional)</span></label>
                        <textarea id="{{ $mode }}_deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan singkat aspek ini."
                            data-kolom="deskripsi"
                            class="{{ $kelasInput }} {{ $salah->has('deskripsi') ? $kelasInputSalah : $kelasInputNormal }}">{{ $formLama === $mode ? old('deskripsi') : '' }}</textarea>
                        @if ($salah->has('deskripsi'))
                            <p class="mt-1 text-xs text-red-600" data-pesan-salah>{{ $salah->first('deskripsi') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" dusk="simpan-{{ $mode }}" class="rounded-lg bg-indigo-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan</button>
                </div>
            </form>
        </dialog>
    @endforeach

    {{-- Modal: Konfirmasi Hapus --}}
    <dialog id="modal-hapus" dusk="modal-hapus" class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50">
        <form method="POST" action="" data-form-hapus>
            @csrf
            @method('DELETE')
            <div class="flex items-start gap-4 p-7">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
                </span>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold">Hapus aspek <span data-isi="nama"></span>?</h2>
                    <p class="mt-1 text-sm text-gray-600">Aspek ini belum pernah dipakai. Data akan dihapus permanen dan tidak dapat dikembalikan.</p>
                </div>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
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

            const aksi = {
                ubah(aspek) {
                    const modal = document.getElementById('modal-ubah');
                    const form = modal.querySelector('[data-form-ubah]');
                    form.action = aspek.url_ubah;
                    form.elements._aspek_id.value = aspek.id;
                    modal.querySelectorAll('[data-kolom]').forEach((input) => {
                        input.value = aspek[input.dataset.kolom] ?? '';
                        input.classList.remove('border-red-500', 'focus:border-red-500');
                        input.classList.add('border-gray-300', 'focus:border-indigo-700');
                    });
                    modal.querySelectorAll('[data-pesan-salah]').forEach((el) => el.remove());
                    modal.showModal();
                },
                hapus(aspek) {
                    const modal = document.getElementById('modal-hapus');
                    modal.querySelector('[data-form-hapus]').action = aspek.url_hapus;
                    modal.querySelectorAll('[data-isi="nama"]').forEach((el) => { el.textContent = aspek.nama; });
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
                    aksi[tombolAksi.dataset.aksi](JSON.parse(tombolAksi.dataset.aspek));
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
