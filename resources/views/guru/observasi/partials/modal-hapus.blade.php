{{--
    Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan.
    Dialog konfirmasi hapus observasi. Tombol pemicu memakai atribut data-buka-hapus.
--}}
<dialog id="dialog-hapus-observasi" aria-labelledby="judul-hapus-observasi"
    class="w-[calc(100%-2rem)] max-w-md rounded-xl border border-gray-200 p-0 backdrop:bg-gray-900/50">
    <form method="POST" action="{{ route('guru.observasi.destroy', $observasi) }}">
        @csrf
        @method('DELETE')

        <div class="space-y-4 p-5 sm:p-6">
            <div>
                <h2 id="judul-hapus-observasi" class="text-lg font-semibold text-gray-900">
                    Hapus Observasi?
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Observasi tanggal
                    <span class="font-medium text-gray-900">
                        {{ $observasi->tanggal?->locale('id')->isoFormat('D MMMM YYYY') }}
                    </span>
                    atas nama
                    <span class="font-medium text-gray-900">{{ $observasi->anak?->nama ?? '-' }}</span>
                    akan dihapus dari daftar observasi.
                </p>
                <p class="mt-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-orange-800">
                    Riwayat perubahan observasi ini juga akan ikut terhapus dan tidak dapat
                    dikembalikan.
                </p>
            </div>

            <label class="flex items-start gap-2 text-sm text-gray-700">
                <input type="checkbox" id="konfirmasi-hapus-observasi" required
                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 accent-red-700">
                <span>Saya memahami observasi ini akan dihapus dan tidak dapat dikembalikan.</span>
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" data-tutup-hapus
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </button>

                <button type="submit"
                    class="rounded-lg bg-red-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-800">
                    Hapus Observasi
                </button>
            </div>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('dialog-hapus-observasi');
        const konfirmasi = document.getElementById('konfirmasi-hapus-observasi');

        if (!dialog) {
            return;
        }

        document.querySelectorAll('[data-buka-hapus]').forEach((tombol) => {
            tombol.addEventListener('click', () => {
                if (konfirmasi) {
                    konfirmasi.checked = false;
                }

                dialog.showModal();
            });
        });

        dialog.querySelectorAll('[data-tutup-hapus]').forEach((tombol) => {
            tombol.addEventListener('click', () => dialog.close());
        });
    })();
</script>
