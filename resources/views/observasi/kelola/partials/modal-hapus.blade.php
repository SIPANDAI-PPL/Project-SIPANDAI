{{-- Modal konfirmasi hapus observasi (dipakai halaman Detail dan Ubah). --}}
<div id="modal-hapus" data-modal class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-hapus">
    <div class="absolute inset-0 bg-[#111827]/50 backdrop-blur-sm" data-tutup-modal></div>

    <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border-t-4 border-t-[#B91C1C] bg-white shadow-2xl">
        <div class="overflow-y-auto p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FEE2E2] text-[#B91C1C]">
                        <svg class="h-6 w-6"><use href="#ikon-peringatan" /></svg>
                    </span>
                    <div>
                        <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#B91C1C]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#B91C1C]"></span>
                            Tindakan Permanen &amp; Kritis
                        </p>
                        <h2 id="judul-modal-hapus" class="mt-1 text-xl font-bold tracking-tight text-[#111827]">Hapus observasi ini?</h2>
                    </div>
                </div>
                <button type="button" data-tutup-modal class="rounded-lg p-1.5 text-[#9CA3AF] transition hover:bg-[#F3F4F6] hover:text-[#4B5563]" aria-label="Tutup">
                    <svg class="h-5 w-5"><use href="#ikon-silang" /></svg>
                </button>
            </div>

            <div class="mt-5 rounded-xl bg-[#F5F3FF] p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#6D5BD0]">Ringkasan Berkas Siswa</p>
                    <span class="rounded-md border border-[#DDD6FE] bg-white px-2 py-1 font-mono text-[10px] font-semibold text-[#4F3BD8]">{{ $observasi['kode'] }}</span>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-[#9CA3AF]">Nama Siswa</p>
                        <p class="mt-0.5 text-sm font-semibold text-[#111827]">
                            {{ $observasi['anak']['nama'] }}
                            <span class="font-medium text-[#4F3BD8]">({{ $observasi['anak']['kelas'] }})</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-[#9CA3AF]">Tanggal Observasi</p>
                        <p class="mt-0.5 text-sm font-semibold text-[#111827]">{{ $observasi['tanggal_panjang'] }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[10px] uppercase tracking-wider text-[#9CA3AF]">Kegiatan Pengamatan</p>
                        <p class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold text-[#111827]">
                            <svg class="h-4 w-4 text-[#6B7280]"><use href="#ikon-aktivitas" /></svg>
                            {{ $observasi['aktivitas'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-2.5 rounded-xl border border-[#FECACA] bg-[#FEF2F2] p-3.5 text-xs leading-relaxed text-[#991B1B]">
                <svg class="mt-0.5 h-4 w-4 shrink-0"><use href="#ikon-peringatan" /></svg>
                <p>Data observasi yang dihapus tidak dapat dikembalikan dan tidak akan muncul di laporan capaian perkembangan mingguan maupun semester anak.</p>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between gap-3">
                    <label for="alasan-hapus" class="text-sm font-semibold text-[#374151]">
                        Alasan Penghapusan <span class="text-[#B91C1C]">*</span>
                    </label>
                    <span class="text-[11px] text-[#9CA3AF]"><span data-jumlah-alasan-hapus>0</span>/150 karakter</span>
                </div>
                <textarea id="alasan-hapus" data-alasan-hapus rows="3" maxlength="150"
                    placeholder="Tuliskan alasan penghapusan observasi ini, misal: duplikasi data atau salah memilih siswa..."
                    class="mt-1.5 w-full rounded-lg border border-[#D1D5DB] px-3 py-2 text-sm text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#4F3BD8] focus:outline-none focus:ring-2 focus:ring-[#4F3BD8]/20"></textarea>
            </div>

            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-[#F5F3FF] p-3.5">
                <input type="checkbox" data-setuju-hapus
                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-[#C4B5FD] text-[#4F3BD8] focus:ring-[#4F3BD8]">
                <span class="text-xs leading-relaxed text-[#4B5563]">Saya memahami bahwa tindakan ini bersifat destruktif dan tidak dapat dibatalkan.</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-[#F3F4F6] bg-[#F9FAFB] px-5 py-4">
            <button type="button" data-tutup-modal class="rounded-lg bg-[#EEF2FF] px-4 py-2.5 text-sm font-semibold text-[#4338CA] transition hover:bg-[#E0E7FF]">
                Batal
            </button>
            <button type="button" data-hapus-permanen disabled
                class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg bg-[#FCA5A5] px-4 py-2.5 text-sm font-semibold text-white transition">
                <svg class="h-4 w-4"><use href="#ikon-tempat-sampah" /></svg>
                Hapus Permanen
            </button>
        </div>
    </div>
</div>
