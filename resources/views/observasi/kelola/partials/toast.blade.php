{{-- Toast sukses: dipakai halaman Ubah dan Detail. --}}
<div data-toast class="invisible fixed right-4 top-4 z-[70] w-[calc(100%-2rem)] max-w-sm translate-x-4 opacity-0 transition duration-300 ease-out" role="status" aria-live="polite">
    <div class="flex items-start gap-3 rounded-xl border border-[#E5E7EB] border-l-4 border-l-[#16A34A] bg-white p-4 shadow-xl">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#DCFCE7] text-[#16A34A]">
            <svg class="h-5 w-5"><use href="#ikon-centang" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p data-toast-judul class="text-sm font-semibold text-[#111827]">Observasi berhasil diperbarui</p>
            <p data-toast-sub class="mt-0.5 truncate text-xs text-[#6B7280]">{{ $observasi['kode'] }}</p>
        </div>
        <button type="button" data-tutup-toast class="rounded-md p-1 text-[#9CA3AF] transition hover:bg-[#F3F4F6] hover:text-[#4B5563]" aria-label="Tutup notifikasi">
            <svg class="h-4 w-4"><use href="#ikon-silang" /></svg>
        </button>
    </div>
</div>
