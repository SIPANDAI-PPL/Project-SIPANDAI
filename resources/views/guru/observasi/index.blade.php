
<x-layouts.app title="Data Observasi">
    <div class="space-y-6">

        {{-- Judul halaman --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Data Observasi Perkembangan</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Lihat seluruh hasil pengamatan perkembangan anak.
                </p>
            </div>

            <a href="{{ route('guru.observasi.create') }}"
                class="rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-800">
                + Tambah Observasi
            </a>
        </div>

        {{-- Notifikasi berhasil --}}
        @if (session('notifikasi'))
            @php $notif = session('notifikasi'); @endphp
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                role="status">
                <p class="font-semibold">{{ $notif['judul'] }}</p>
                <p class="mt-1">{{ $notif['pesan'] }}</p>
            </div>
        @endif

        {{-- Notifikasi gagal --}}
        @if (session('gagal'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                role="alert">
                {{ session('gagal') }}
            </div>
        @endif

        {{-- Ringkasan --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            aria-label="Ringkasan observasi">

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm font-medium text-gray-600">Total Observasi</p>
                <p class="mt-2 text-3xl font-semibold text-indigo-900">{{ $total }}</p>
                <p class="mt-1 text-xs text-gray-500">Seluruh data tersimpan</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm font-medium text-gray-600">Anak Terobservasi</p>
                <p class="mt-2 text-3xl font-semibold text-indigo-900">{{ $anakUnik }}</p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $kelasNamaStr !== '' ? 'Dari ' . $kelasNamaStr : 'Belum ada data' }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm font-medium text-gray-600">Kelas Terobservasi</p>
                <p class="mt-2 text-3xl font-semibold text-indigo-900">{{ $kelasUnik }}</p>
                <p class="mt-1 text-xs text-gray-500">Tahun ajaran {{ $tahunAjaran }}</p>
            </div>
        </section>

        {{-- Filter --}}
        <form method="GET" action="{{ route('guru.observasi.index') }}"
            class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4"
            role="search">

            <div>
                <label for="q" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Cari Observasi
                </label>
                <input type="search" id="q" name="q" value="{{ $cari }}"
                    placeholder="Nama anak atau aktivitas..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div>
                <label for="kelas" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Kelas
                </label>
                <select id="kelas" name="kelas" onchange="this.form.requestSubmit()"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua kelas</option>
                    @foreach ($kelasList as $id => $nama)
                        <option value="{{ $id }}" @selected((int) $kelasId === (int) $id)>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Tahun Ajaran
                </label>
                <select id="tahun" name="tahun" onchange="this.form.requestSubmit()"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    @foreach ($tahunList as $t)
                        <option value="{{ $t }}" @selected($tahunAjaran === $t)>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="urut" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Urutkan
                </label>
                <select id="urut" name="urut" onchange="this.form.requestSubmit()"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    @foreach ($urutList as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($urut === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Terapkan Pencarian
                </button>
            </div>
        </form>

        <p class="text-xs text-gray-500">
            Menampilkan {{ $adaFilter ? 'hasil penyaringan' : 'seluruh observasi' }}
            &bull; Tahun ajaran {{ $tahunAjaran }}
        </p>

        {{-- Tabel Observasi --}}
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            aria-labelledby="judul-tabel">

            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4">
                <h2 id="judul-tabel" class="font-medium">Seluruh Observasi</h2>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-900">
                    {{ $observasi->total() }} data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-medium uppercase text-gray-600">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nama Anak</th>
                            <th scope="col" class="px-4 py-4">Kelas</th>
                            <th scope="col" class="px-4 py-4">Tanggal</th>
                            <th scope="col" class="px-4 py-4">Aktivitas Observasi</th>
                            <th scope="col" class="px-4 py-4">Respons Anak</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($observasi as $o)
                            @php
                                $kode = sprintf('OBS-%03d', $o->id);
                                $namaKelas = $kelasList->get($o->anak->kelas_id, '-');
                                $tanggal = $o->tanggal->locale('id')->isoFormat('D MMM YYYY');

                                $warnaRespons = [
                                    'terlibat_aktif' => 'bg-emerald-50 text-emerald-800',
                                    'terlibat_dengan_pendampingan' => 'bg-blue-50 text-blue-800',
                                    'kurang_tertarik' => 'bg-orange-50 text-orange-800',
                                    'belum_terlibat' => 'bg-gray-100 text-gray-700',
                                ];
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">
                                        {{ $o->anak->nama }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $kode }}</p>
                                </td>

                                <td class="px-4 py-4 text-gray-700">
                                    {{ $namaKelas }}
                                </td>

                                <td class="px-4 py-4 text-gray-700">
                                    {{ $tanggal }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="font-medium text-gray-800">
                                        {{ $o->aktivitas->nama }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $o->aspek->nama }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $warnaRespons[$o->kategori_respons->value] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $o->kategori_respons->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                    {{ $adaFilter
                                        ? 'Tidak ada observasi yang cocok dengan pencarian Anda.'
                                        : 'Belum ada data observasi pada tahun ajaran ini.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 text-xs text-gray-500">
                <p>
                    @if ($observasi->total() > 0)
                        Menampilkan {{ $observasi->firstItem() }}–{{ $observasi->lastItem() }}
                        dari {{ $observasi->total() }} observasi
                    @else
                        Tidak ada data untuk ditampilkan
                    @endif
                </p>

                <nav class="flex flex-wrap items-center gap-2" aria-label="Navigasi halaman">
                    @if ($observasi->onFirstPage())
                        <span class="rounded-lg border border-gray-200 px-3 py-2 text-gray-300">
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $observasi->previousPageUrl() }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50"
                            rel="prev">
                            Sebelumnya
                        </a>
                    @endif

                    @php
                        $sekarang = $observasi->currentPage();
                        $terakhir = $observasi->lastPage();
                    @endphp

                    @for ($p = max(1, $sekarang - 2); $p <= min($terakhir, $sekarang + 2); $p++)
                        @if ($p === $sekarang)
                            <span class="rounded-lg bg-indigo-900 px-3 py-2 font-medium text-white"
                                aria-current="page">
                                {{ $p }}
                            </span>
                        @else
                            <a href="{{ $observasi->url($p) }}"
                                class="rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50">
                                {{ $p }}
                            </a>
                        @endif
                    @endfor

                    @if ($observasi->hasMorePages())
                        <a href="{{ $observasi->nextPageUrl() }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50"
                            rel="next">
                            Berikutnya
                        </a>
                    @else
                        <span class="rounded-lg border border-gray-200 px-3 py-2 text-gray-300">
                            Berikutnya
                        </span>
                    @endif
                </nav>
            </div>
        </section>
    </div>
</x-layouts.app>
