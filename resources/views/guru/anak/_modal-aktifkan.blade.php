<div id="modal-aktifkan" data-modal class="fixed inset-0 z-20 hidden items-center justify-center bg-black/50 p-4 [&:not(.hidden)]:flex">
    <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Aktifkan kembali anak?</h2>
        <p class="mt-2 text-sm text-gray-600">
            <strong>{{ $anak['nama'] }}</strong> akan muncul lagi sebagai pilihan pada formulir observasi.
        </p>

        <div class="mt-6 flex gap-2">
            <button type="button" data-modal-tutup
                class="rounded border border-gray-300 px-4 py-2 font-medium hover:bg-gray-50">Batal</button>
            <button type="button" data-modal-tutup
                class="rounded bg-indigo-900 px-4 py-2 font-medium text-white hover:bg-indigo-800">Aktifkan</button>
        </div>
    </div>
</div>
