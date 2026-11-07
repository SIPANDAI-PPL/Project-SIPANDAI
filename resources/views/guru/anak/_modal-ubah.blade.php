<div id="modal-ubah" data-modal class="fixed inset-0 z-10 hidden items-center justify-center bg-black/50 p-4 [&:not(.hidden)]:flex">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Ubah data anak</h2>
        <p class="mt-1 text-sm text-gray-600">{{ $anak['nama'] }}</p>

        <form action="#" onsubmit="return false" class="mt-4 space-y-4">
            <div>
                <label for="ubah-nama" class="block text-sm font-medium">Nama lengkap</label>
                <input id="ubah-nama" type="text" value="{{ $anak['nama'] }}"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="ubah-panggilan" class="block text-sm font-medium">Nama panggilan</label>
                    <input id="ubah-panggilan" type="text" value="{{ $anak['panggilan'] }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label for="ubah-lahir" class="block text-sm font-medium">Tanggal lahir</label>
                    <input id="ubah-lahir" type="date" value="{{ $anak['tanggal_lahir'] }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label for="ubah-kelas" class="block text-sm font-medium">Kelas</label>
                <select id="ubah-kelas"
                    class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas['id'] }}" @selected($kelas['nama'] === $anak['kelas'])>{{ $kelas['nama'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm">
                <span>Terhubung: <strong>{{ $anak['jumlah_observasi'] }} observasi</strong></span>
                <a href="#" class="font-medium text-indigo-900 underline">Lihat</a>
            </div>

            <div class="flex gap-2">
                <button type="button" data-modal-tutup
                    class="rounded border border-gray-300 px-4 py-2 font-medium hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="rounded bg-indigo-900 px-4 py-2 font-medium text-white hover:bg-indigo-800">Simpan</button>
                @if ($anak['aktif'])
                    <button type="button" data-modal-buka="modal-nonaktifkan"
                        class="rounded bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">Nonaktifkan</button>
                @else
                    <button type="button" data-modal-buka="modal-aktifkan"
                        class="rounded bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">Aktifkan</button>
                @endif
            </div>
        </form>
    </div>
</div>
