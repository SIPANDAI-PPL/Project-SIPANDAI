{{-- Modal konfirmasi ketika pengguna meninggalkan halaman dengan perubahan belum disimpan. --}}
<div id="modal-tinggalkan" data-modal class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-tinggalkan">
    <div class="absolute inset-0 bg-[#111827]/50 backdrop-blur-sm" data-tutup-modal></div>

    <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FEE2E2] text-[#B91C1C]">
                    <svg class="h-5 w-5"><use href="#ikon-peringatan" /></svg>
                </span>
                <div>
                    <h2 id="judul-modal-tinggalkan" class="text-base font-bold leading-snug text-[#111827]">Perubahan belum disimpan. Tinggalkan halaman?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-[#6B7280]">Ada data observasi yang telah Anda ubah. Jika Anda keluar sekarang, perubahan tidak akan tersimpan.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-[#F3F4F6] bg-[#F9FAFB] px-5 py-4">
            <button type="button" data-tutup-modal class="rounded-lg bg-[#EEF2FF] px-4 py-2.5 text-sm font-semibold text-[#4338CA] transition hover:bg-[#E0E7FF]">
                Tetap di Sini
            </button>
            <a href="{{ route('observasi.kelola.show', $observasi['kode']) }}"
                class="rounded-lg bg-[#991B1B] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#7F1D1D]">
                Tinggalkan
            </a>
        </div>
    </div>
</div>
