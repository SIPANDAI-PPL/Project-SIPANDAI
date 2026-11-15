{{-- PBI-3 Kelola Kelas, Tahun Ajaran, dan Penugasan Guru Pengampu. --}}
@php
    // Galat form tambah/ubah kelas memakai bag bawaan; galat ganti guru memakai bag "penugasan".
    $salahForm = $errors->getBag('default');
    $salahPenugasan = $errors->getBag('penugasan');

    $modeForm = old('_form') === 'ubah' ? 'ubah' : 'tambah';
    $ubah = $modeForm === 'ubah' && $salahForm->any();
    $nilai = fn (string $kolom, mixed $bawaan = '') => $salahForm->any() ? old($kolom, $bawaan) : $bawaan;

    // Kelas yang sedang diganti gurunya saat validasi penugasan gagal.
    $kelasPenugasan = $salahPenugasan->any() ? $daftarKelas->firstWhere('id', (int) old('_kelas_id')) : null;

    $kelasInput = 'mt-1 block w-full rounded-lg border bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $kelasInputNormal = 'border-gray-300 focus:border-indigo-700';
    $kelasInputSalah = 'border-red-500 focus:border-red-500';
    $gayaInput = fn (string $kolom) => $kelasInput.' '.($salahForm->has($kolom) ? $kelasInputSalah : $kelasInputNormal);

    $labelGuru = fn ($guru) => $guru->nama.' ('.$guru->kelas_diampu_count.' kelas)';
@endphp
<x-layouts.app title="Kelola Kelas">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Kelola Kelas dan Penugasan Guru</h1>
            <p class="mt-1 text-sm text-gray-600">Atur kelas per tahun ajaran dan tetapkan satu guru pengampu untuk setiap kelas.</p>
        </div>
        <button type="button" data-aksi="tambah" dusk="tombol-tambah-kelas"
            class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
            + Tambah Kelas
        </button>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status" dusk="pesan-status">{{ session('status') }}</p>
    @endif

    {{-- Galat penugasan untuk kelas yang tidak ada di daftar tahun ini (jarang terjadi). --}}
    @if ($salahPenugasan->any() && ! $kelasPenugasan)
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" data-galat-penugasan dusk="pesan-gagal-penugasan">{{ $salahPenugasan->first('guru_id') }}</p>
    @endif

    <form method="GET" action="{{ route('classes.index') }}" class="mt-6 flex flex-wrap items-center gap-3">
        <label for="filter-tahun" class="text-sm text-gray-700">Tahun ajaran</label>
        <select id="filter-tahun" name="tahun_ajaran" onchange="this.form.requestSubmit()" dusk="filter-tahun"
            class="w-44 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @foreach ($daftarTahunAjaran as $tahun)
                <option value="{{ $tahun }}" @selected($tahun === $tahunAjaran)>{{ $tahun }}</option>
            @endforeach
        </select>
        <button type="submit" class="sr-only">Tampilkan</button>
    </form>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
            <h2 class="font-medium">Daftar Kelas {{ $tahunAjaran }}</h2>
            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">{{ $daftarKelas->count() }} Kelas</span>
        </div>

        @if ($daftarKelas->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-500" dusk="kelas-kosong">
                Belum ada kelas pada tahun ajaran {{ $tahunAjaran }}. Klik <strong>+ Tambah Kelas</strong> untuk membuat kelas.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm" dusk="tabel-kelas">
                    <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Kelas</th>
                            <th scope="col" class="px-6 py-3">Kelompok</th>
                            <th scope="col" class="px-6 py-3">Tahun Ajaran</th>
                            <th scope="col" class="px-6 py-3">Guru Pengampu</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($daftarKelas as $kelas)
                            @php
                                $dataKelas = [
                                    'id' => $kelas->id,
                                    'nama_kelas' => $kelas->nama_kelas,
                                    'kelompok' => $kelas->kelompok,
                                    'tahun_ajaran' => $kelas->tahun_ajaran,
                                    'guru_id' => $kelas->guru?->aktif ? $kelas->guru_id : null,
                                    'guru_nama' => $kelas->guru?->nama,
                                    'guru_aktif' => (bool) $kelas->guru?->aktif,
                                    'url_ubah' => route('classes.update', $kelas),
                                    'url_guru' => route('classes.guru-pengampu', $kelas),
                                ];
                            @endphp
                            <tr dusk="baris-kelas-{{ $kelas->id }}">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $kelas->nama_kelas }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">{{ $kelas->kelompok }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $kelas->tahun_ajaran }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $kelas->guru?->nama ?? '-' }}</p>
                                    @if ($kelas->guru)
                                        <p class="text-xs text-gray-500">{{ $kelas->guru->email }}</p>
                                        @unless ($kelas->guru->aktif)
                                            <span class="mt-1 inline-block rounded-md border border-orange-200 bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-800">Akun nonaktif, ganti guru</span>
                                        @endunless
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" data-aksi="ubah" data-kelas='@json($dataKelas)' dusk="ubah-kelas-{{ $kelas->id }}"
                                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Ubah</button>
                                        <button type="button" data-aksi="guru" data-kelas='@json($dataKelas)' dusk="ganti-guru-{{ $kelas->id }}"
                                            class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-900 hover:bg-indigo-100">Ganti Guru</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <p class="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 text-xs text-gray-600">
        Satu kelas diampu satu guru. Guru hanya dapat melihat data anak di kelas yang diampunya. Hanya akun guru aktif yang dapat dipilih.
    </p>

    {{-- Drawer: Tambah / Ubah Kelas (satu form, mode diatur JS) --}}
    <dialog id="drawer-kelas" dusk="drawer-kelas" aria-labelledby="judul-drawer-kelas"
        class="my-0 ml-auto mr-0 h-dvh max-h-none w-full max-w-lg p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($salahForm->any()) data-buka-otomatis @endif>
        <form method="POST" novalidate data-form-kelas class="flex h-full flex-col"
            action="{{ $ubah && old('_kelas_id') ? route('classes.update', old('_kelas_id')) : route('classes.store') }}">
            @csrf
            <input type="hidden" name="_method" value="PUT" data-metode @disabled(! $ubah)>
            <input type="hidden" name="_form" value="{{ $modeForm }}">
            <input type="hidden" name="_kelas_id" value="{{ $ubah ? old('_kelas_id') : '' }}">

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <h2 id="judul-drawer-kelas" class="text-xl font-semibold" data-judul-drawer>{{ $ubah ? 'Ubah Kelas' : 'Tambah Kelas' }}</h2>
                <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <div>
                    <label for="nama_kelas" class="block text-sm font-medium">Nama kelas</label>
                    <input id="nama_kelas" name="nama_kelas" type="text" maxlength="50" autocomplete="off" data-kolom="nama_kelas"
                        value="{{ $nilai('nama_kelas') }}" placeholder="Contoh: Kenanga" class="{{ $gayaInput('nama_kelas') }}"
                        @if ($salahForm->has('nama_kelas')) aria-invalid="true" aria-describedby="galat-nama_kelas" @endif>
                    @error('nama_kelas')
                        <p id="galat-nama_kelas" class="mt-1 text-xs text-red-600" data-galat-form="nama_kelas" dusk="galat-nama_kelas">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="kelompok" class="block text-sm font-medium">Kelompok</label>
                        <select id="kelompok" name="kelompok" data-kolom="kelompok" class="{{ $gayaInput('kelompok') }}"
                            @if ($salahForm->has('kelompok')) aria-invalid="true" aria-describedby="galat-kelompok" @endif>
                            <option value="">Pilih kelompok</option>
                            @foreach ($daftarKelompok as $kelompok)
                                <option value="{{ $kelompok }}" @selected($nilai('kelompok') === $kelompok)>{{ $kelompok }}</option>
                            @endforeach
                        </select>
                        @error('kelompok')
                            <p id="galat-kelompok" class="mt-1 text-xs text-red-600" data-galat-form="kelompok" dusk="galat-kelompok">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tahun_ajaran" class="block text-sm font-medium">Tahun ajaran</label>
                        <input id="tahun_ajaran" name="tahun_ajaran" type="text" maxlength="9" inputmode="numeric" autocomplete="off"
                            list="pilihan-tahun" data-kolom="tahun_ajaran" placeholder="2026/2027"
                            value="{{ $nilai('tahun_ajaran', $tahunAjaran) }}" class="{{ $gayaInput('tahun_ajaran') }}"
                            aria-describedby="bantuan-tahun_ajaran @if ($salahForm->has('tahun_ajaran')) galat-tahun_ajaran @endif"
                            @if ($salahForm->has('tahun_ajaran')) aria-invalid="true" @endif>
                        <datalist id="pilihan-tahun">
                            @foreach ($daftarTahunAjaran as $tahun)
                                <option value="{{ $tahun }}"></option>
                            @endforeach
                        </datalist>
                        <p id="bantuan-tahun_ajaran" class="mt-1 text-xs text-gray-500">Format YYYY/YYYY, contoh 2026/2027.</p>
                        @error('tahun_ajaran')
                            <p id="galat-tahun_ajaran" class="mt-1 text-xs text-red-600" data-galat-form="tahun_ajaran" dusk="galat-tahun_ajaran">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Guru pengampu dipilih saat tambah kelas. Saat ubah, pakai tombol "Ganti Guru". --}}
                <div data-bagian-guru @class(['hidden' => $ubah])>
                    <label for="guru_id_baru" class="block text-sm font-medium">Guru pengampu</label>
                    <select id="guru_id_baru" name="guru_id" data-kolom="guru_id" dusk="pilih-guru" class="{{ $gayaInput('guru_id') }}" @disabled($ubah)
                        @if ($salahForm->has('guru_id')) aria-invalid="true" aria-describedby="galat-guru_id" @endif>
                        <option value="">Pilih guru aktif</option>
                        @foreach ($daftarGuruAktif as $guru)
                            <option value="{{ $guru->id }}" @selected((string) $nilai('guru_id') === (string) $guru->id)>{{ $labelGuru($guru) }}</option>
                        @endforeach
                    </select>
                    @error('guru_id')
                        <p id="galat-guru_id" class="mt-1 text-xs text-red-600" data-galat-form="guru_id" dusk="galat-guru_id">{{ $message }}</p>
                    @enderror
                    @if ($daftarGuruAktif->isEmpty())
                        <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            Belum ada guru aktif. Tambahkan lewat menu <a href="{{ route('admin.guru.index') }}" class="font-medium underline">Kelola Akun Guru</a>.
                        </p>
                    @endif
                </div>

                <p data-info-ubah @class(['rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900', 'hidden' => ! $ubah])>
                    Untuk mengganti guru pengampu, tutup form ini lalu klik <strong>Ganti Guru</strong> pada baris kelas.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 border-t border-gray-100 px-6 py-5">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-kelas" data-tombol-simpan
                    class="rounded-lg bg-indigo-900 px-5 py-3 text-sm font-medium text-white hover:bg-indigo-800">{{ $ubah ? 'Simpan Perubahan' : 'Simpan Kelas' }}</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog: Ganti Guru Pengampu --}}
    <dialog id="modal-guru" dusk="modal-guru" aria-labelledby="judul-modal-guru"
        class="m-auto w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-gray-900/50"
        @if ($kelasPenugasan) data-buka-otomatis @endif>
        <form method="POST" novalidate data-form-guru
            action="{{ $kelasPenugasan ? route('classes.guru-pengampu', $kelasPenugasan) : '' }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_kelas_id" value="{{ $kelasPenugasan?->id }}">

            <div class="p-7">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <h2 id="judul-modal-guru" class="text-lg font-semibold">Ganti Guru Pengampu</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Kelas <strong data-isi="nama_kelas">{{ $kelasPenugasan?->nama_kelas }}</strong>
                            &bull; <span data-isi="kelompok">{{ $kelasPenugasan?->kelompok }}</span>
                            &bull; <span data-isi="tahun_ajaran">{{ $kelasPenugasan?->tahun_ajaran }}</span>
                        </p>
                    </div>
                    <button type="button" data-tutup class="text-xl leading-none text-gray-500 hover:text-gray-800" aria-label="Tutup">&times;</button>
                </div>

                <p class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                    Guru saat ini: <strong data-isi="guru_nama">{{ $kelasPenugasan?->guru?->nama ?? '-' }}</strong>
                </p>

                <label for="guru_id_ganti" class="mt-5 block text-sm font-medium">Guru pengampu baru</label>
                <select id="guru_id_ganti" name="guru_id" data-pilih-guru dusk="pilih-guru-pengampu"
                    class="{{ $kelasInput }} {{ $salahPenugasan->has('guru_id') ? $kelasInputSalah : $kelasInputNormal }}"
                    @if ($salahPenugasan->has('guru_id')) aria-invalid="true" aria-describedby="galat-penugasan" @endif>
                    <option value="">Pilih guru aktif</option>
                    @foreach ($daftarGuruAktif as $guru)
                        <option value="{{ $guru->id }}" @selected((string) old('guru_id') === (string) $guru->id && $kelasPenugasan)>{{ $labelGuru($guru) }}</option>
                    @endforeach
                </select>
                @if ($kelasPenugasan)
                    <p id="galat-penugasan" class="mt-1 text-xs text-red-600" role="alert" data-galat-penugasan dusk="galat-penugasan">{{ $salahPenugasan->first('guru_id') }}</p>
                @endif
                <p class="mt-2 text-xs text-gray-500">Guru lama otomatis dilepas dari kelas ini.</p>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-7 py-4">
                <button type="button" data-tutup class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" dusk="simpan-guru" class="rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">Simpan Guru</button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            const URL_SIMPAN = @json(route('classes.store'));
            const TAHUN_BAWAAN = @json($tahunAjaran);

            // ----- Drawer tambah / ubah kelas -----
            const drawer = document.getElementById('drawer-kelas');
            const form = drawer.querySelector('[data-form-kelas]');
            const kolom = (nama) => form.querySelector(`[data-kolom="${nama}"]`);

            const bersihkanSalah = (wadah) => {
                wadah.querySelectorAll('[data-galat-form], [data-galat-penugasan]').forEach((el) => el.remove());
                wadah.querySelectorAll('.border-red-500').forEach((el) => {
                    el.classList.remove('border-red-500', 'focus:border-red-500');
                    el.classList.add('border-gray-300', 'focus:border-indigo-700');
                    el.removeAttribute('aria-invalid');
                });
            };

            const siapkanDrawer = (mode, kelas = null) => {
                const ubah = mode === 'ubah';
                bersihkanSalah(form);
                form.action = ubah ? kelas.url_ubah : URL_SIMPAN;
                form.querySelector('[data-metode]').disabled = !ubah;
                form.elements._form.value = mode;
                form.elements._kelas_id.value = ubah ? kelas.id : '';
                drawer.querySelector('[data-judul-drawer]').textContent = ubah ? 'Ubah Kelas' : 'Tambah Kelas';
                drawer.querySelector('[data-tombol-simpan]').textContent = ubah ? 'Simpan Perubahan' : 'Simpan Kelas';
                drawer.querySelector('[data-bagian-guru]').classList.toggle('hidden', ubah);
                drawer.querySelector('[data-info-ubah]').classList.toggle('hidden', !ubah);

                kolom('nama_kelas').value = ubah ? kelas.nama_kelas : '';
                kolom('kelompok').value = ubah ? kelas.kelompok : '';
                kolom('tahun_ajaran').value = ubah ? kelas.tahun_ajaran : TAHUN_BAWAAN;
                // Saat ubah, guru tidak ikut dikirim.
                kolom('guru_id').value = '';
                kolom('guru_id').disabled = ubah;

                drawer.showModal();
                kolom('nama_kelas').focus();
            };

            // ----- Dialog ganti guru pengampu -----
            const modalGuru = document.getElementById('modal-guru');
            const formGuru = modalGuru.querySelector('[data-form-guru]');

            const siapkanModalGuru = (kelas) => {
                bersihkanSalah(formGuru);
                formGuru.action = kelas.url_guru;
                formGuru.elements._kelas_id.value = kelas.id;
                ['nama_kelas', 'kelompok', 'tahun_ajaran'].forEach((kunci) => {
                    modalGuru.querySelector(`[data-isi="${kunci}"]`).textContent = kelas[kunci];
                });
                modalGuru.querySelector('[data-isi="guru_nama"]').textContent = kelas.guru_nama
                    ? (kelas.guru_aktif ? kelas.guru_nama : `${kelas.guru_nama} (nonaktif)`)
                    : '-';
                formGuru.querySelector('[data-pilih-guru]').value = kelas.guru_id ? String(kelas.guru_id) : '';
                modalGuru.showModal();
            };

            const aksi = {
                tambah: () => siapkanDrawer('tambah'),
                ubah: (kelas) => siapkanDrawer('ubah', kelas),
                guru: (kelas) => siapkanModalGuru(kelas),
            };

            document.addEventListener('click', (event) => {
                const tombolAksi = event.target.closest('[data-aksi]');
                if (tombolAksi) {
                    const data = tombolAksi.dataset.kelas ? JSON.parse(tombolAksi.dataset.kelas) : null;
                    aksi[tombolAksi.dataset.aksi](data);
                    return;
                }

                const tombolTutup = event.target.closest('[data-tutup]');
                if (tombolTutup) {
                    tombolTutup.closest('dialog')?.close();
                }
            });

            // Buka kembali drawer/dialog yang gagal validasi agar galat terlihat.
            document.querySelectorAll('dialog[data-buka-otomatis]').forEach((modal) => modal.showModal());
        })();
    </script>
</x-layouts.app>
