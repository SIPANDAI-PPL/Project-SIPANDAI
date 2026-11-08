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
    $gayaBadge = [
        'BSB' => 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
        'BSH' => 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
        'MB' => 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
        'BB' => 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
    ];
    $dihapus = $observasi['dihapus'];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Observasi - SIPANDAI</title>
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
            <span class="rounded-full bg-[#EDE9FE] px-2.5 py-1 font-semibold text-[#4F3BD8]">Detail Observasi</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#EDE9FE] text-[#4F3BD8]">
                    <svg class="h-5 w-5"><use href="#ikon-dokumen" /></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-[#111827]">Detail Observasi Perkembangan</h1>
                        @if ($dihapus)
                            <span class="inline-flex items-center gap-1 rounded-full border border-[#FECACA] bg-[#FEF2F2] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#B91C1C]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#B91C1C]"></span>
                                Dihapus
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-[#6B7280]">Ringkasan hasil evaluasi harian, capaian indikator, dan catatan tindak lanjut anak didik.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" data-buka-modal="modal-riwayat"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#C4B5FD] bg-white px-3.5 py-2.5 text-sm font-semibold text-[#4F3BD8] transition hover:bg-[#F5F3FF]">
                    <svg class="h-4 w-4"><use href="#ikon-jam" /></svg>
                    Lihat Riwayat
                </button>

                @if ($dihapus)
                    <span aria-disabled="true"
                        class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg bg-[#4F3BD8] px-3.5 py-2.5 text-sm font-semibold text-white opacity-50">
                        <svg class="h-4 w-4"><use href="#ikon-pensil" /></svg>
                        Ubah
                    </span>
                @else
                    <a href="{{ route('observasi.kelola.edit', $observasi['kode']) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#4F3BD8] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3F2FBF]">
                        <svg class="h-4 w-4"><use href="#ikon-pensil" /></svg>
                        Ubah
                    </a>
                @endif

                <button type="button" data-buka-modal="modal-hapus" @disabled($dihapus)
                    class="inline-flex items-center gap-2 rounded-lg border border-[#FCA5A5] bg-white px-3.5 py-2.5 text-sm font-semibold text-[#B91C1C] transition hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white">
                    <svg class="h-4 w-4"><use href="#ikon-tempat-sampah" /></svg>
                    Hapus
                </button>
            </div>
        </div>

        @if ($dihapus)
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-[#FECACA] bg-[#FEF2F2] p-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#FEE2E2] text-[#B91C1C]">
                    <svg class="h-4 w-4"><use href="#ikon-peringatan" /></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-[#991B1B]">Data observasi ini sudah dihapus dan tidak dapat diubah</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-[#B91C1C]">Riwayat perubahan tetap tersedia untuk keperluan audit dan pelaporan.</p>
                </div>
            </div>
        @endif

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
                @if ($dihapus)
                    <p class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-[#FEE2E2] px-2.5 py-1 text-[11px] font-semibold text-[#B91C1C]">
                        <svg class="h-3.5 w-3.5"><use href="#ikon-tempat-sampah" /></svg>
                        Dihapus
                    </p>
                @else
                    <p class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[11px] font-semibold text-[#16A34A]">
                        <svg class="h-3.5 w-3.5"><use href="#ikon-cloud" /></svg>
                        Tersimpan di Cloud
                    </p>
                @endif
            </div>
        </div>

        <section class="mt-5 rounded-xl border border-[#E5E7EB] bg-white shadow-sm">
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
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Rombongan Belajar / Kelas</p>
                    <p class="mt-1 text-sm font-semibold text-[#111827]">{{ $observasi['anak']['rombel'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Nama Anak Didik</p>
                    <p class="mt-1 text-sm font-semibold text-[#111827]">{{ $observasi['anak']['nama'] }} <span class="font-medium text-[#6B7280]">(NIS: {{ $observasi['anak']['nis'] }})</span></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Tanggal Pengamatan</p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-[#111827]">
                        <svg class="h-4 w-4 text-[#6B7280]"><use href="#ikon-kalender" /></svg>
                        {{ $observasi['tanggal_tampil'] }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Aktivitas / Kegiatan Pembelajaran</p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-[#111827]">
                        <svg class="h-4 w-4 text-[#6B7280]"><use href="#ikon-aktivitas" /></svg>
                        {{ $observasi['aktivitas'] }}
                    </p>
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
                        <p class="mt-0.5 text-[11px] text-[#9CA3AF]">Hasil capaian perkembangan anak pada kegiatan pengamatan ini.</p>
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

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#F9FAFB] text-[10px] uppercase tracking-wider text-[#9CA3AF]">
                        <tr>
                            <th class="px-5 py-3 font-semibold">No</th>
                            <th class="px-5 py-3 font-semibold">Indikator Capaian</th>
                            <th class="px-5 py-3 text-right font-semibold">Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3F4F6]">
                        @foreach ($observasi['indikator'] as $indikator)
                            <tr class="transition hover:bg-[#F9FAFB]">
                                <td class="px-5 py-3.5 align-top">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F3F4F6] text-[11px] font-bold text-[#6B7280]">{{ $indikator['nomor'] }}</span>
                                </td>
                                <td class="px-5 py-3.5 align-top">
                                    <p class="font-semibold text-[#111827]">{{ $indikator['judul'] }}</p>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#6B7280]">{{ $indikator['deskripsi'] }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right align-top">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $gayaBadge[$indikator['nilai']] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $warnaTitik[$indikator['nilai']] }}"></span>
                                        {{ $indikator['nilai'] }} &middot; {{ $namaKategori[$indikator['nilai']] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                <p class="mt-3 rounded-lg bg-[#F9FAFB] p-3.5 text-sm leading-relaxed text-[#374151]">{{ $observasi['catatan']['isi'] }}</p>
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
                <p class="mt-3 rounded-lg bg-[#F9FAFB] p-3.5 text-sm leading-relaxed text-[#374151]">{{ $observasi['rencana']['isi'] }}</p>
            </section>
        </div>
    </main>

    @include('observasi.kelola.partials.modal-hapus', ['observasi' => $observasi])
    @include('observasi.kelola.partials.modal-riwayat', ['observasi' => $observasi])
    @include('observasi.kelola.partials.toast', ['observasi' => $observasi])

    <script>
        (function () {
            const kode = @json($observasi['kode']);

            function bukaModal(id) {
                const modal = document.getElementById(id);
                if (!modal) return;
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

            if (tombolHapus) {
                tombolHapus.addEventListener('click', function () {
                    if (tombolHapus.disabled) return;
                    tutupModal(document.getElementById('modal-hapus'));
                    tampilkanToast('Observasi berhasil dihapus', kode);
                });
            }

            const demo = new URLSearchParams(window.location.search).get('demo');
            if (demo === 'sukses') tampilkanToast('Observasi berhasil diperbarui', kode);
            if (demo === 'sukses-hapus') tampilkanToast('Observasi berhasil dihapus', kode);
        })();
    </script>
</body>
</html>
