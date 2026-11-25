{{--
    PBI-17 Pencatatan Tindak Lanjut.
    Butuh: $daftarAnak, $pilihanAlternatif ([anak_id => [[id, nama, rencana], ...]]),
    $anakTerpilih, $alternatifTerpilih, $urlKembali.
    Panel kanan mockup (posisi level dan pratinjau 7 hari) belum dikerjakan di PBI ini.
--}}
@php
    $hariIni = now()->toDateString();
    $nilaiTanggal = old('tanggal', $hariIni);
    $nilaiCatatan = old('catatan', '');

    $pilihanAnakIni = $anakTerpilih ? ($pilihanAlternatif[(int) $anakTerpilih] ?? []) : [];
    $tanpaStrategi = $anakTerpilih && $pilihanAnakIni === [];
    $alternatifIni = collect($pilihanAnakIni)->firstWhere('id', (int) $alternatifTerpilih);
    $rencanaTerpilih = $alternatifIni['rencana'] ?? null;

    // Respons awal terisi dari respons pemicu aturan, kecuali guru sudah memilih sendiri (old input).
    $responsOtomatis = old('kategori_respons') === null ? ($alternatifIni['respons_awal'] ?? null) : null;
    $nilaiRespons = old('kategori_respons', $responsOtomatis);

    // Placeholder __ANAK__ diganti id anak oleh script di bawah.
    $urlFokus = route('guru.alternatif-strategi.anak', ['anak' => '__ANAK__']);
    $urlFokusAnak = $anakTerpilih ? str_replace('__ANAK__', (string) $anakTerpilih, $urlFokus) : route('guru.alternatif-strategi.index');

    $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $nilaiTanggal) === 1;
    $labelStatus = $tanggalValid && $nilaiTanggal > $hariIni ? 'Direncanakan' : 'Berjalan';
    $tanggalTampil = $tanggalValid ? implode('/', array_reverse(explode('-', $nilaiTanggal))) : '-';

    // Tampilan kartu respons disamakan dengan formulir Tambah Observasi (PBI-07): ikon, warna, dan deskripsi.
    // Kelas ditulis utuh agar terbaca Tailwind. Status terpilih memakai has-checked pada label.
    $tampilanRespons = [
        'terlibat_aktif' => [
            'deskripsi' => 'Anak antusias dan melakukan kegiatan dengan mandiri.',
            'kartu' => 'border-emerald-200 hover:bg-emerald-50 has-checked:border-emerald-600 has-checked:bg-emerald-50 has-checked:ring-emerald-600/15 has-focus-visible:outline-emerald-600',
            'ikon' => 'bg-emerald-50 text-emerald-600 group-has-checked:bg-emerald-600 group-has-checked:text-white',
            'judul' => 'text-emerald-800',
            'centang' => 'group-has-checked:border-emerald-600 group-has-checked:bg-emerald-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5l2.47 5 5.53.8-4 3.9.94 5.5L12 16.1l-4.94 2.6.94-5.5-4-3.9 5.53-.8L12 3.5z"/>',
        ],
        'terlibat_dengan_pendampingan' => [
            'deskripsi' => 'Anak ikut serta setelah diarahkan atau didampingi guru.',
            'kartu' => 'border-blue-200 hover:bg-blue-50 has-checked:border-blue-600 has-checked:bg-blue-50 has-checked:ring-blue-600/15 has-focus-visible:outline-blue-600',
            'ikon' => 'bg-blue-50 text-blue-600 group-has-checked:bg-blue-600 group-has-checked:text-white',
            'judul' => 'text-blue-800',
            'centang' => 'group-has-checked:border-blue-600 group-has-checked:bg-blue-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 00-3.5-3.5h-5A3.5 3.5 0 004 17.5V19M10 10.5a3 3 0 100-6 3 3 0 000 6zM20 19v-1.5a3.5 3.5 0 00-2.5-3.35M15.5 4.6a3 3 0 010 5.8"/>',
        ],
        'kurang_tertarik' => [
            'deskripsi' => 'Anak terlihat kurang bersemangat atau mudah beralih.',
            'kartu' => 'border-orange-200 hover:bg-orange-50 has-checked:border-orange-600 has-checked:bg-orange-50 has-checked:ring-orange-600/15 has-focus-visible:outline-orange-600',
            'ikon' => 'bg-orange-50 text-orange-600 group-has-checked:bg-orange-600 group-has-checked:text-white',
            'judul' => 'text-orange-800',
            'centang' => 'group-has-checked:border-orange-600 group-has-checked:bg-orange-600',
            'svg' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M8.5 15.5h7M9 9.75h.01M15 9.75h.01"/>',
        ],
        'belum_terlibat' => [
            'deskripsi' => 'Anak belum mau atau belum mampu ikut kegiatan.',
            'kartu' => 'border-gray-300 hover:bg-gray-50 has-checked:border-gray-500 has-checked:bg-gray-100 has-checked:ring-gray-500/15 has-focus-visible:outline-gray-500',
            'ikon' => 'bg-gray-100 text-gray-500 group-has-checked:bg-gray-500 group-has-checked:text-white',
            'judul' => 'text-gray-700',
            'centang' => 'group-has-checked:border-gray-500 group-has-checked:bg-gray-500',
            'svg' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M10 9v6M14 9v6"/>',
        ],
    ];

    $kelasLabel = 'mb-2 block text-sm font-semibold text-gray-900';
    $kelasKontrol = 'block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500';
    $kelasKontrolSalah = 'border-red-400 bg-red-50';
    $kelasSalah = 'mt-1.5 text-xs text-red-600';
@endphp
<x-layouts.app title="Pencatatan Tindak Lanjut">
    <nav aria-label="Breadcrumb" class="text-sm text-gray-500">
        <a href="{{ route('guru.alternatif-strategi.index') }}" class="hover:text-indigo-800 hover:underline">Alternatif Strategi</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Pencatatan tindak lanjut</span>
    </nav>

    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Pencatatan Tindak Lanjut</h1>
            <p class="mt-1 text-sm text-gray-600">
                Catat penerapan strategi yang sudah dipilih agar dapat dievaluasi kemudian.
                <a href="{{ route('guru.tindak-lanjut.riwayat') }}" dusk="tautan-riwayat" class="font-medium text-indigo-800 hover:underline">Lihat riwayat</a>
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ $urlKembali }}" dusk="tombol-batal" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-indigo-900 bg-white px-5 py-2.5 text-sm font-medium text-indigo-900 hover:bg-indigo-50 sm:flex-none">Batal</a>
            <button type="submit" form="form-tindak-lanjut" dusk="tombol-simpan" @disabled($tanpaStrategi) data-tombol-simpan
                class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50 sm:flex-none">Simpan Catatan</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" dusk="pesan-salah">
            <p class="font-medium">Tindak lanjut belum tersimpan. Periksa kembali isian berikut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-tindak-lanjut" method="POST" action="{{ route('guru.tindak-lanjut.store') }}" novalidate
        class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="anak_id" class="{{ $kelasLabel }}">Anak</label>
                        <select id="anak_id" name="anak_id" required dusk="pilih-anak" @class([$kelasKontrol, $kelasKontrolSalah => $errors->has('anak_id')])>
                            <option value="">Pilih anak</option>
                            @foreach ($daftarAnak as $anak)
                                <option value="{{ $anak->id }}" @selected((string) $anakTerpilih === (string) $anak->id)>{{ $anak->nama }} ({{ $anak->kelas->kelompok }} {{ $anak->kelas->nama_kelas }})</option>
                            @endforeach
                        </select>
                        @error('anak_id')
                            <p class="{{ $kelasSalah }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal" class="{{ $kelasLabel }}">Tanggal mulai</label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ $nilaiTanggal }}" required dusk="isi-tanggal"
                            aria-describedby="keterangan-tanggal" @class([$kelasKontrol, $kelasKontrolSalah => $errors->has('tanggal')])>
                        <p id="keterangan-tanggal" class="mt-1.5 text-xs text-gray-500">
                            <span data-tanggal-tampil>{{ $tanggalTampil }}</span> &middot;
                            Status otomatis: <span class="font-semibold text-gray-700" data-status-otomatis>{{ $labelStatus }}</span>
                        </p>
                        @error('tanggal')
                            <p class="{{ $kelasSalah }}">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="alternatif_id" class="{{ $kelasLabel }}">Alternatif strategi yang diterapkan</label>
                    <select id="alternatif_id" name="alternatif_id" required dusk="pilih-alternatif" @disabled($pilihanAnakIni === [])
                        @class([$kelasKontrol, $kelasKontrolSalah => $errors->has('alternatif_id')])>
                        <option value="">{{ ! $anakTerpilih ? 'Pilih anak terlebih dahulu' : ($tanpaStrategi ? 'Belum ada strategi yang dipilih' : 'Pilih alternatif strategi') }}</option>
                        @foreach ($pilihanAnakIni as $pilihan)
                            <option value="{{ $pilihan['id'] }}" @selected((string) $alternatifTerpilih === (string) $pilihan['id'])>{{ $pilihan['nama'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500">Hanya strategi berstatus dipilih yang belum punya tindak lanjut.</p>
                    @error('alternatif_id')
                        <p class="{{ $kelasSalah }}">{{ $message }}</p>
                    @enderror

                    <div data-rencana @class(['mt-3 rounded-r-lg border-l-4 border-indigo-200 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-700', 'hidden' => $rencanaTerpilih === null])>
                        <p class="font-semibold uppercase tracking-wide text-gray-800">Rencana yang dicatat</p>
                        <p class="mt-1" data-rencana-isi>{{ $rencanaTerpilih }}</p>
                    </div>

                    <div data-arahan role="status" dusk="arahan-alternatif" @class(['mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900', 'hidden' => ! $tanpaStrategi])>
                        <p>Belum ada strategi yang dipilih untuk anak ini. Pilih strategi lebih dulu di menu Alternatif Strategi, lalu kembali ke formulir ini.</p>
                        <a href="{{ $urlFokusAnak }}" data-arahan-tautan class="mt-2 inline-flex font-medium text-indigo-900 underline hover:text-indigo-700">Buka Alternatif Strategi</a>
                    </div>
                </div>

                <div>
                    <label for="catatan" class="{{ $kelasLabel }}">Catatan pendukung <span class="font-normal text-gray-500">(opsional)</span></label>
                    <textarea id="catatan" name="catatan" rows="6" maxlength="1000" dusk="isi-catatan" aria-describedby="keterangan-catatan"
                        placeholder="Tuliskan alasan atau dugaan penyebab, misalnya apa yang terlihat saat kegiatan."
                        @class([$kelasKontrol, $kelasKontrolSalah => $errors->has('catatan')])>{{ $nilaiCatatan }}</textarea>
                    <p id="keterangan-catatan" class="mt-1.5 text-xs text-gray-500"><span data-jumlah-karakter>{{ mb_strlen((string) $nilaiCatatan) }}</span>/1000 karakter. Disimpan sebagai alasan atau dugaan penyebab.</p>
                    @error('catatan')
                        <p class="{{ $kelasSalah }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset>
                <legend class="sr-only">Kategori respons awal</legend>
                <div class="flex items-start gap-3" aria-hidden="true">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M8.5 14c.8 1.3 2 2 3.5 2s2.7-.7 3.5-2M9 9.75h.01M15 9.75h.01"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Kategori respons awal</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-gray-500">Respons anak saat ini, sebelum strategi baru diterapkan. Dipakai sebagai pembanding saat evaluasi.</p>
                    </div>
                </div>
                <p data-respons-otomatis dusk="keterangan-respons-otomatis" @class(['mt-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs text-indigo-900', 'hidden' => $responsOtomatis === null])>
                    Terisi otomatis dari pola respons yang memicu saran ini. Ubah bila kondisi anak sekarang berbeda.
                </p>
                <div class="mt-4 space-y-2.5">
                    @foreach (\App\Enums\KategoriRespons::cases() as $kategori)
                        @php $tampilan = $tampilanRespons[$kategori->value]; @endphp
                        <label class="group flex cursor-pointer items-center gap-3 rounded-xl border-[1.5px] bg-white px-3.5 py-3 transition-colors outline-offset-2 has-checked:ring-[3px] has-focus-visible:outline-2 motion-reduce:transition-none {{ $tampilan['kartu'] }}">
                            <input type="radio" name="kategori_respons" value="{{ $kategori->value }}" class="sr-only" required
                                dusk="respons-{{ $kategori->value }}" @checked($nilaiRespons === $kategori->value)>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors {{ $tampilan['ikon'] }}" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $tampilan['svg'] !!}</svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold {{ $tampilan['judul'] }}">{{ $kategori->label() }}</span>
                                <span class="mt-0.5 block text-xs leading-relaxed text-gray-500">{{ $tampilan['deskripsi'] }}</span>
                            </span>
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 bg-white text-transparent transition-colors group-has-checked:text-white {{ $tampilan['centang'] }}" aria-hidden="true">
                                <svg class="h-3 w-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4 8.3 2.6 2.6L12 5.5"/></svg>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('kategori_respons')
                    <p class="{{ $kelasSalah }}">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>
    </form>

    <script>
        (() => {
            const pilihan = @json((object) $pilihanAlternatif);
            const hariIni = @json($hariIni);
            const urlFokus = @json($urlFokus);

            const anak = document.getElementById('anak_id');
            const alternatif = document.getElementById('alternatif_id');
            const tanggal = document.getElementById('tanggal');
            const catatan = document.getElementById('catatan');
            const simpan = document.querySelector('[data-tombol-simpan]');
            const arahan = document.querySelector('[data-arahan]');
            const tautanArahan = document.querySelector('[data-arahan-tautan]');
            const rencana = document.querySelector('[data-rencana]');
            const isiRencana = document.querySelector('[data-rencana-isi]');

            const radioRespons = document.querySelectorAll('input[name="kategori_respons"]');
            const keteranganOtomatis = document.querySelector('[data-respons-otomatis]');
            // Isi otomatis berhenti setelah guru memilih kategori sendiri.
            let diubahGuru = @json(old('kategori_respons') !== null);
            radioRespons.forEach((radio) => radio.addEventListener('change', () => {
                diubahGuru = true;
                keteranganOtomatis.classList.add('hidden');
            }));

            const daftarUntuk = (id) => pilihan[id] || [];

            const isiResponsAwal = (terpilih) => {
                if (diubahGuru) {
                    return;
                }
                const nilai = terpilih ? terpilih.respons_awal : null;
                radioRespons.forEach((radio) => { radio.checked = radio.value === nilai; });
                keteranganOtomatis.classList.toggle('hidden', !nilai);
            };

            const tampilkanRencana = () => {
                const terpilih = daftarUntuk(anak.value).find((item) => String(item.id) === alternatif.value);
                isiRencana.textContent = terpilih ? terpilih.rencana : '';
                rencana.classList.toggle('hidden', !terpilih);
                isiResponsAwal(terpilih);
            };

            const isiAlternatif = () => {
                const daftar = daftarUntuk(anak.value);
                const kosong = anak.value !== '' && daftar.length === 0;

                alternatif.length = 0;
                alternatif.add(new Option(
                    anak.value === '' ? 'Pilih anak terlebih dahulu' : (kosong ? 'Belum ada strategi yang dipilih' : 'Pilih alternatif strategi'),
                    '',
                ));
                daftar.forEach((item) => alternatif.add(new Option(item.nama, item.id)));
                // Langsung terpilih bila hanya ada satu strategi.
                if (daftar.length === 1) {
                    alternatif.value = String(daftar[0].id);
                }

                alternatif.disabled = daftar.length === 0;
                simpan.disabled = kosong;
                arahan.classList.toggle('hidden', !kosong);
                tautanArahan.href = anak.value === '' ? tautanArahan.href : urlFokus.replace('__ANAK__', anak.value);
                tampilkanRencana();
            };

            const perbaruiStatus = () => {
                const nilai = tanggal.value;
                document.querySelector('[data-tanggal-tampil]').textContent = nilai ? nilai.split('-').reverse().join('/') : '-';
                document.querySelector('[data-status-otomatis]').textContent = nilai && nilai > hariIni ? 'Direncanakan' : 'Berjalan';
            };

            anak.addEventListener('change', isiAlternatif);
            alternatif.addEventListener('change', tampilkanRencana);
            tanggal.addEventListener('input', perbaruiStatus);
            catatan.addEventListener('input', () => {
                document.querySelector('[data-jumlah-karakter]').textContent = catatan.value.length;
            });
        })();
    </script>
</x-layouts.app>
