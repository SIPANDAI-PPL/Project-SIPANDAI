<div id="modal-tambah" data-modal class="fixed inset-0 z-10 hidden items-center justify-center bg-black/50 p-4 [&:not(.hidden)]:flex">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Tambah anak</h2>
        <p class="mt-1 text-sm text-gray-600">Usia anak harus 24–59 bulan.</p>

        <form action="#" onsubmit="return false" class="mt-4 space-y-4">
            <div>
                <label for="tambah-nama" class="block text-sm font-medium">Nama lengkap</label>
                <input id="tambah-nama" type="text"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                <p class="mt-1 hidden text-sm text-red-600">Nama lengkap wajib diisi.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tambah-panggilan" class="block text-sm font-medium">Nama panggilan <span class="font-normal text-gray-500">(opsional)</span></label>
                    <input id="tambah-panggilan" type="text"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label for="tambah-lahir" class="block text-sm font-medium">Tanggal lahir</label>
                    <input id="tambah-lahir" type="date"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                    <p class="mt-1 hidden text-sm text-red-600">Usia anak harus 24–59 bulan.</p>
                </div>
            </div>

            <div>
                <label for="tambah-kelas" class="block text-sm font-medium">Kelas</label>
                <select id="tambah-kelas"
                    class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas['id'] }}">{{ $kelas['nama'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 hidden text-sm text-red-600">Kelas wajib dipilih.</p>
            </div>

            <div class="flex gap-2">
                <button type="button" data-modal-tutup
                    class="rounded border border-gray-300 px-4 py-2 font-medium hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="rounded bg-indigo-900 px-4 py-2 font-medium text-white hover:bg-indigo-800">Simpan</button>
            </div>
        </form>
    </div>
</div>
