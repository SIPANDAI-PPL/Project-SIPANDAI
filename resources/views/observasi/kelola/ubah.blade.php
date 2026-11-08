@php
    $namaKategori = [
        'BSB' => 'Sangat Baik',
        'BSH' => 'Sesuai Harapan',
        'MB' => 'Mulai Berkembang',
        'BB' => 'Belum Berkembang',
    ];
    $warnaTitik = [
        'BSB' => 'bg-[#16A34A]',
        'BSH' => 'bg-[#1D4ED8]',
        'MB' => 'bg-[#EA580C]',
        'BB' => 'bg-[#9CA3AF]',
    ];
    $gayaOpsi = [
        'BB' => 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
        'MB' => 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
        'BSH' => 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
        'BSB' => 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
    ];
    $jumlahBerubah = collect($observasi['indikator'])
        ->filter(fn (array $indikator) => $indikator['nilai'] !== $indikator['nilai_awal'])
        ->count();
    $demoError = request('demo') === 'error';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Observasi - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F6FA] font-sans text-[#111827] antialiased">
    @include('observasi.kelola.partials.ikon')

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-[#6B7280]" aria-label="Breadcrumb">
            <a href="#" class="transition hover:text-[#4F3BD8]">Observasi</a>
            <svg class="h-3.5 w-3.5 text-[#9CA3AF]"><use href="#ikon-chevron-kanan" /></svg>
            <a href="#" class="transition hover:text-[#4F3BD8]">Riwayat Observasi</a>
            <svg class="h-3.5 w-3.5 text-[#9CA3AF]"><use href="#ikon-chevron-kanan" /></svg>
            <span class="rounded-full bg-[#EDE9FE] px-2.5 py-1 font-semibold text-[#4F3BD8]">Ubah Observasi</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#EDE9FE] text-[#4F3BD8]">
                    <svg class="h-5 w-5"><use href="#ikon-clipboard" /></svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#111827]">Ubah Observasi Perkembangan</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-[#6B7280]">Perbarui hasil evaluasi harian sesuai capaian perkembangan, dan catatan tindak lanjut anak didik.</p>
                </div>
            </div>

            <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-lg border border-[#FCD34D] bg-[#FEF3C7] px-3 py-2 text-xs font-semibold text-[#92400E]">
                <span class="h-2 w-2 rounded-full bg-[#EA580C]"></span>
                <span><span data-jumlah-perubahan>{{ $jumlahBerubah }}</span> Perubahan Indikator Terdeteksi</span>
            </span>
        </div>

        <div class="mt-6 grid grid-cols-1 divide-y divide-[#E5E7EB] overflow-hidden rounded-xl border border-[#E5E7EB] bg-white shadow-sm sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-4 lg:divide-x">
            <div class="p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">ID Observasi</p>
                <p class="mt-1.5 font-mono text-sm font-semibold text-[#111827]"># {{ $observasi['kode'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Dibuat Pada</p>
                <p class="mt-1.5 flex items-center gap-1.5 text-sm font-semibold text-[#111827]">
                    <svg class="h-4 w-4 text-[#9CA3AF]"><use href="#ikon-kalender" /></svg>
                    {{ $observasi['dibuat_pada'] }}
                </p>
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Terakhir Diubah</p>
                <p class="mt-1.5 flex items-center gap-1.5 text-sm font-semibold text-[#111827]">
                    <svg class="h-4 w-4 shrink-0 text-[#9CA3AF]"><use href="#ikon-jam" /></svg>
                    <span>{{ $observasi['terakhir_diubah_pada'] }} <span class="font-medium text-[#6B7280]">oleh {{ $observasi['terakhir_diubah_oleh'] }}</span></span>
                </p>
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Status Data</p>
                <p class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[11px] font-semibold text-[#16A34A]">
                    <svg class="h-3.5 w-3.5"><use href="#ikon-cloud" /></svg>
                    Tersimpan di Cloud
                </p>
            </div>
        </div>

        <form id="form-observasi" class="mt-5" novalidate>
            <section class="rounded-xl border border-[#E5E7EB] bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#F3F4F6] px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EDE9FE] text-[#4F3BD8]">
                            <svg class="h-4 w-4"><use href="#ikon-anak" /></svg>
                        </span>
                        <h2 class="text-sm font-bold text-[#111827]">Data Anak &amp; Konteks Observasi</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F3F4F6] px-2.5 py-1 text-[10px] font-semibold text-[#6B7280]">
                        <svg class="h-3 w-3"><use href="#ikon-kunci" /></svg>
                        Identitas Terkunci
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <label for="rombel" class="text-xs font-semibold text-[#374151]">Rombongan Belajar / Kelas</label>
                            <span class="text-[10px] text-[#9CA3AF]">Tidak dapat diubah</span>
                        </div>
                        <input id="rombel" type="text" value="{{ $observasi['anak']['rombel'] }}" readonly
                            class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-[#E5E7EB] bg-[#F3F4F6] px-3 py-2 text-sm text-[#6B7280]">
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <label for="nama_anak" class="text-xs font-semibold text-[#374151]">Nama Anak Didik</label>
                            <span class="text-[10px] text-[#9CA3AF]">Tidak dapat diubah</span>
                        </div>
                        <input id="nama_anak" type="text" value="{{ $observasi['anak']['nama'] }} (NIS: {{ $observasi['anak']['nis'] }})" readonly
                            class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-[#E5E7EB] bg-[#F3F4F6] px-3 py-2 text-sm text-[#6B7280]">
                    </div>

                    <div>
                        <label for="tanggal_pengamatan" class="text-xs font-semibold text-[#374151]">Tanggal Pengamatan</label>
                        <div class="relative mt-1.5">
                            <input id="tanggal_pengamatan" name="tanggal_pengamatan" type="date" value="{{ $observasi['tanggal'] }}"
                                class="w-full rounded-lg border px-3 py-2 pr-10 text-sm text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20 [&::-webkit-calendar-picker-indicator]:hidden {{ $demoError ? 'border-[#FCA5A5] focus:border-[#B91C1C]' : 'border-[#D1D5DB] focus:border-[#4F3BD8]' }}">
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B7280]"><use href="#ikon-kalender" /></svg>
                        </div>
                        @if ($demoError)
                            <p class="mt-1 text-[11px] font-medium text-[#B91C1C]">Tanggal pengamatan wajib diisi.</p>
                        @endif
                    </div>

                    <div>
                        <label for="aktivitas" class="text-xs font-semibold text-[#374151]">Aktivitas / Kegiatan Pembelajaran</label>
                        <div class="relative mt-1.5">
                            <select id="aktivitas" name="aktivitas"
                                class="w-full appearance-none rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 pr-10 text-sm text-[#111827] focus:border-[#4F3BD8] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20">
                                <option selected>{{ $observasi['aktivitas'] }}</option>
                                <option>Kegiatan Motorik Halus</option>
                                <option>Bercerita Bersama</option>
                                <option>Kegiatan Seni &amp; Kreativitas</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B7280]"><use href="#ikon-chevron-bawah" /></svg>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-5 overflow-hidden rounded-xl border border-[#E5E7EB] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#F3F4F6] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EDE9FE] text-[#4F3BD8]">
                            <svg class="h-4 w-4"><use href="#ikon-aktivitas" /></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#111827]">Penilaian 6 Indikator Capaian PAUD</h2>
                            <p class="mt-0.5 text-[11px] text-[#9CA3AF]">Pilih tingkat capaian untuk tiap aspek perkembangan sesuai hasil pengamatan.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                        @foreach ($namaKategori as $kode => $nama)
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-[#6B7280]">
                                <span class="h-2 w-2 rounded-full {{ $warnaTitik[$kode] }}"></span>
                                <span class="font-bold text-[#374151]">{{ $kode }}</span> {{ $nama }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3 p-5">
                    @foreach ($observasi['indikator'] as $indikator)
                        @php($berubah = $indikator['nilai'] !== $indikator['nilai_awal'])
                        <div data-indikator data-nilai-awal="{{ $indikator['nilai_awal'] }}" data-nilai="{{ $indikator['nilai'] }}"
                            data-judul="{{ $indikator['judul'] }}"
                            class="rounded-xl border p-3.5 transition sm:p-4 {{ $berubah ? 'border-[#FCD34D] bg-[#FFFBEB]' : 'border-[#E5E7EB] bg-white' }}">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#F3F4F6] text-[11px] font-bold text-[#6B7280]">{{ $indikator['nomor'] }}</span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-bold text-[#111827]">{{ $indikator['judul'] }}</h3>
                                            <span data-badge-diubah class="inline-flex items-center rounded-full border border-[#FCD34D] bg-[#FEF3C7] px-2 py-0.5 text-[10px] font-semibold text-[#92400E] {{ $berubah ? '' : 'hidden' }}">Diubah</span>
                                        </div>
                                        <p class="mt-0.5 text-xs leading-relaxed text-[#6B7280]">{{ $indikator['deskripsi'] }}</p>
                                        <p data-nilai-sebelumnya class="mt-1 text-[11px] font-medium text-[#C2410C] {{ $berubah ? '' : 'hidden' }}">
                                            Nilai sebelumnya: {{ $indikator['nilai_awal'] }} ({{ $namaKategori[$indikator['nilai_awal']] }})
                                        </p>
                                    </div>
                                </div>

                                <div class="inline-flex shrink-0 gap-1 self-start rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] p-1 lg:self-auto">
                                    @foreach (['BB', 'MB', 'BSH', 'BSB'] as $opsi)
                                        @php($aktif = $opsi === $indikator['nilai'])
                                        <button type="button" data-nilai-opsi="{{ $opsi }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}"
                                            class="flex items-center gap-1 rounded-md border px-2.5 py-1.5 text-xs font-semibold transition {{ $aktif ? $gayaOpsi[$opsi] : 'border-transparent bg-transparent text-[#6B7280] hover:bg-white' }}">
                                            <svg data-centang class="h-3.5 w-3.5 {{ $aktif ? '' : 'hidden' }}"><use href="#ikon-centang" /></svg>
                                            {{ $opsi }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <section class="rounded-xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EDE9FE] text-[#4F3BD8]">
                            <svg class="h-4 w-4"><use href="#ikon-dokumen" /></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#111827]">Catatan Anekdot Observasi</h2>
                            <p class="text-[11px] text-[#9CA3AF]">Catatan perilaku atau kejadian penting selama pengamatan.</p>
                        </div>
                    </div>
                    <textarea id="catatan_anekdot" name="catatan_anekdot" data-counter maxlength="{{ $observasi['catatan']['maks'] }}" rows="5"
                        class="mt-3 w-full rounded-lg border border-[#D1D5DB] px-3 py-2 text-sm leading-relaxed text-[#111827] focus:border-[#4F3BD8] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20">{{ $observasi['catatan']['isi'] }}</textarea>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-[11px] text-[#9CA3AF]">Karakter: <span data-jumlah-karakter>{{ mb_strlen($observasi['catatan']['isi']) }}</span>/{{ $observasi['catatan']['maks'] }}</p>
                        <p class="inline-flex items-center gap-1 text-[11px] font-medium text-[#16A34A]">
                            <svg class="h-3.5 w-3.5"><use href="#ikon-centang" /></svg>
                            Tersimpan otomatis
                        </p>
                    </div>
                </section>

                <section class="rounded-xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EDE9FE] text-[#4F3BD8]">
                            <svg class="h-4 w-4"><use href="#ikon-aktivitas" /></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#111827]">Rencana Tindak Lanjut Guru</h2>
                            <p class="text-[11px] text-[#9CA3AF]">Rencana stimulasi berikutnya untuk anak didik.</p>
                        </div>
                    </div>
                    <textarea id="rencana_tindak_lanjut" name="rencana_tindak_lanjut" data-counter maxlength="{{ $observasi['rencana']['maks'] }}" rows="5"
                        class="mt-3 w-full rounded-lg border border-[#D1D5DB] px-3 py-2 text-sm leading-relaxed text-[#111827] focus:border-[#4F3BD8] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20">{{ $observasi['rencana']['isi'] }}</textarea>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-[11px] text-[#9CA3AF]">Karakter: <span data-jumlah-karakter>{{ mb_strlen($observasi['rencana']['isi']) }}</span>/{{ $observasi['rencana']['maks'] }}</p>
                        <a href="#" class="text-[11px] font-semibold text-[#4F3BD8] transition hover:text-[#3F2FBF]">Templat rekomendasi</a>
                    </div>
                </section>
            </div>

            <section class="mt-5 rounded-xl border border-[#E5E7EB] border-l-4 border-l-[#4F3BD8] bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="alasan_perubahan" class="text-sm font-bold text-[#111827]">Alasan Perubahan Data Observasi <span class="text-[#B91C1C]">*</span></label>
                    <span class="rounded-full bg-[#F3F4F6] px-2.5 py-1 text-[10px] font-semibold text-[#6B7280]">Wajib diisi untuk audit log riwayat</span>
                </div>
                <p class="mt-1 text-xs text-[#6B7280]">Jelaskan alasan perubahan agar tercatat di riwayat perubahan observasi.</p>
                <textarea id="alasan_perubahan" name="alasan_perubahan" data-alasan-perubahan rows="3" maxlength="300"
                    class="mt-3 w-full rounded-lg border px-3 py-2 text-sm leading-relaxed text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20 {{ $demoError ? 'border-[#FCA5A5] focus:border-[#B91C1C]' : 'border-[#D1D5DB] focus:border-[#4F3BD8]' }}">{{ $observasi['alasan'] }}</textarea>
                @if ($demoError)
                    <p class="mt-1 text-[11px] font-medium text-[#B91C1C]">Alasan perubahan wajib diisi.</p>
                @endif
            </section>
        </form>

        <div class="sticky bottom-4 z-20 mt-6 flex flex-col gap-3 rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between">
            <button type="button" data-buka-modal="modal-hapus"
                class="inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-[#B91C1C] transition hover:bg-[#FEF2F2]">
                <svg class="h-4 w-4"><use href="#ikon-tempat-sampah" /></svg>
                Hapus Observasi
            </button>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <button type="button" data-batal
                    class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-[#F9FAFB]">
                    Batal
                </button>
                <button type="button" data-buka-modal="modal-simpan"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#4F3BD8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3F2FBF]">
                    <svg class="h-4 w-4"><use href="#ikon-centang" /></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </main>

    @include('observasi.kelola.partials.modal-simpan', ['observasi' => $observasi])
    @include('observasi.kelola.partials.modal-hapus', ['observasi' => $observasi])
    @include('observasi.kelola.partials.modal-tinggalkan', ['observasi' => $observasi])
    @include('observasi.kelola.partials.toast', ['observasi' => $observasi])

    <script>
        (function () {
            const kode = @json($observasi['kode']);
            const urlDetail = @json(route('observasi.kelola.show', $observasi['kode']));

            const namaKategori = { BB: 'Belum Berkembang', MB: 'Mulai Berkembang', BSH: 'Sesuai Harapan', BSB: 'Sangat Baik' };
            const gayaOpsi = {
                BB: 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
                MB: 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
                BSH: 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
                BSB: 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
            };
            const gayaBadge = {
                BSB: 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
                BSH: 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
                MB: 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
                BB: 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
            };
            const dasarOpsi = 'flex items-center gap-1 rounded-md border px-2.5 py-1.5 text-xs font-semibold transition';
            const opsiNonaktif = 'border-transparent bg-transparent text-[#6B7280] hover:bg-white';

            const barisIndikator = () => Array.from(document.querySelectorAll('[data-indikator]'));

            function segarkanBaris(row) {
                const awal = row.dataset.nilaiAwal;
                const nilai = row.dataset.nilai;
                const berubah = awal !== nilai;

                row.querySelectorAll('[data-nilai-opsi]').forEach(function (tombol) {
                    const aktif = tombol.dataset.nilaiOpsi === nilai;
                    tombol.className = dasarOpsi + ' ' + (aktif ? gayaOpsi[tombol.dataset.nilaiOpsi] : opsiNonaktif);
                    tombol.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                    const centang = tombol.querySelector('[data-centang]');
                    if (centang) centang.classList.toggle('hidden', !aktif);
                });

                row.classList.toggle('border-[#FCD34D]', berubah);
                row.classList.toggle('bg-[#FFFBEB]', berubah);
                row.classList.toggle('border-[#E5E7EB]', !berubah);
                row.classList.toggle('bg-white', !berubah);

                const badge = row.querySelector('[data-badge-diubah]');
                if (badge) badge.classList.toggle('hidden', !berubah);

                const sebelumnya = row.querySelector('[data-nilai-sebelumnya]');
                if (sebelumnya) {
                    sebelumnya.textContent = 'Nilai sebelumnya: ' + awal + ' (' + (namaKategori[awal] || awal) + ')';
                    sebelumnya.classList.toggle('hidden', !berubah);
                }
            }

            function segarkanSemua() {
                barisIndikator().forEach(segarkanBaris);
                const jumlah = barisIndikator().filter(function (row) {
                    return row.dataset.nilaiAwal !== row.dataset.nilai;
                }).length;
                document.querySelectorAll('[data-jumlah-perubahan]').forEach(function (el) {
                    el.textContent = jumlah;
                });
            }

            function isiRingkasan() {
                const tbody = document.querySelector('[data-ringkasan-perubahan]');
                if (!tbody) return;
                tbody.innerHTML = '';
                const berubah = barisIndikator().filter(function (row) {
                    return row.dataset.nilaiAwal !== row.dataset.nilai;
                });

                if (berubah.length === 0) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="3" class="px-3 py-4 text-center text-[11px] italic text-[#9CA3AF]">Belum ada indikator yang diubah.</td>';
                    tbody.appendChild(tr);
                    return;
                }

                berubah.forEach(function (row) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td class="px-3 py-3 font-medium text-[#374151]">' + row.dataset.judul + '</td>' +
                        '<td class="px-3 py-3"><span class="inline-flex rounded-md border border-[#DDD6FE] bg-[#EDE9FE] px-2 py-0.5 text-[11px] font-semibold text-[#4F3BD8]">' + row.dataset.nilaiAwal + '</span></td>' +
                        '<td class="px-3 py-3"><span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-semibold ' + (gayaBadge[row.dataset.nilai] || '') + '"><svg class="h-3 w-3"><use href="#ikon-centang" /></svg>' + row.dataset.nilai + '</span></td>';
                    tbody.appendChild(tr);
                });
            }

            function bukaModal(id) {
                const modal = document.getElementById(id);
                if (!modal) return;
                if (id === 'modal-simpan') isiRingkasan();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function tutupModal(modal) {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (!document.querySelector('[data-modal].flex')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            document.querySelectorAll('[data-buka-modal]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    bukaModal(tombol.dataset.bukaModal);
                });
            });

            document.querySelectorAll('[data-modal]').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target.closest('[data-tutup-modal]')) tutupModal(modal);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                const terbuka = document.querySelector('[data-modal].flex');
                if (terbuka) tutupModal(terbuka);
            });

            document.querySelectorAll('[data-nilai-opsi]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    const row = tombol.closest('[data-indikator]');
                    if (!row) return;
                    row.dataset.nilai = tombol.dataset.nilaiOpsi;
                    segarkanSemua();
                    tandaiKotor();
                });
            });

            let kotor = false;
            function tandaiKotor() {
                kotor = true;
            }

            const form = document.getElementById('form-observasi');
            if (form) {
                form.addEventListener('input', tandaiKotor);
                form.addEventListener('change', tandaiKotor);
            }

            const tombolBatal = document.querySelector('[data-batal]');
            if (tombolBatal) {
                tombolBatal.addEventListener('click', function () {
                    if (kotor) {
                        bukaModal('modal-tinggalkan');
                    } else {
                        window.location.href = urlDetail;
                    }
                });
            }

            document.querySelectorAll('[data-counter]').forEach(function (area) {
                const wadah = area.closest('section');
                const jumlah = wadah ? wadah.querySelector('[data-jumlah-karakter]') : null;
                const perbarui = function () {
                    if (jumlah) jumlah.textContent = area.value.length;
                };
                area.addEventListener('input', perbarui);
                perbarui();
            });

            const alasanPerubahan = document.querySelector('[data-alasan-perubahan]');
            if (alasanPerubahan) {
                const sinkronAlasan = function () {
                    const isi = alasanPerubahan.value.trim();
                    document.querySelectorAll('[data-alasan-modal]').forEach(function (el) {
                        el.textContent = 'Alasan: ' + (isi || '-');
                    });
                };
                alasanPerubahan.addEventListener('input', sinkronAlasan);
                sinkronAlasan();
            }

            const alasanHapus = document.querySelector('[data-alasan-hapus]');
            const setujuHapus = document.querySelector('[data-setuju-hapus]');
            const tombolHapus = document.querySelector('[data-hapus-permanen]');
            const jumlahAlasanHapus = document.querySelector('[data-jumlah-alasan-hapus]');

            function perbaruiTombolHapus() {
                if (!tombolHapus) return;
                const siap = !!(alasanHapus && alasanHapus.value.trim().length > 0 && setujuHapus && setujuHapus.checked);
                tombolHapus.disabled = !siap;
                tombolHapus.className = 'inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition ' +
                    (siap ? 'cursor-pointer bg-[#B91C1C] hover:bg-[#991B1B]' : 'cursor-not-allowed bg-[#FCA5A5]');
            }

            if (alasanHapus) {
                alasanHapus.addEventListener('input', function () {
                    if (jumlahAlasanHapus) jumlahAlasanHapus.textContent = alasanHapus.value.length;
                    perbaruiTombolHapus();
                });
            }
            if (setujuHapus) setujuHapus.addEventListener('change', perbaruiTombolHapus);
            perbaruiTombolHapus();

            const toast = document.querySelector('[data-toast]');
            let timerToast = null;

            function tampilkanToast(judul, sub) {
                if (!toast) return;
                toast.querySelector('[data-toast-judul]').textContent = judul;
                toast.querySelector('[data-toast-sub]').textContent = sub;
                toast.classList.remove('invisible', 'opacity-0', 'translate-x-4');
                toast.classList.add('visible', 'opacity-100', 'translate-x-0');
                window.clearTimeout(timerToast);
                timerToast = window.setTimeout(sembunyikanToast, 5000);
            }

            function sembunyikanToast() {
                if (!toast) return;
                toast.classList.add('invisible', 'opacity-0', 'translate-x-4');
                toast.classList.remove('visible', 'opacity-100', 'translate-x-0');
            }

            const tutupToast = document.querySelector('[data-tutup-toast]');
            if (tutupToast) tutupToast.addEventListener('click', sembunyikanToast);

            const simpanPerubahan = document.querySelector('[data-simpan-perubahan]');
            if (simpanPerubahan) {
                simpanPerubahan.addEventListener('click', function () {
                    tutupModal(document.getElementById('modal-simpan'));
                    tampilkanToast('Observasi berhasil diperbarui', kode);
                });
            }

            if (tombolHapus) {
                tombolHapus.addEventListener('click', function () {
                    if (tombolHapus.disabled) return;
                    tutupModal(document.getElementById('modal-hapus'));
                    tampilkanToast('Observasi berhasil dihapus', kode);
                });
            }

            segarkanSemua();

            const demo = new URLSearchParams(window.location.search).get('demo');
            if (demo === 'sukses') tampilkanToast('Observasi berhasil diperbarui', kode);
            if (demo === 'sukses-hapus') tampilkanToast('Observasi berhasil dihapus', kode);
        })();
    </script>
</body>
</html>
