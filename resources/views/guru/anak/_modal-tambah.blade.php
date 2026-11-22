@php
    // Buka kembali modal jika simpan gagal validasi, dengan isian sebelumnya.
    $adaSalah = $errors->any() && old('_form') === 'tambah';
    $kelasDipilih = $adaSalah ? (int) old('kelas_id') : $kelasTerpilih['id'];
@endphp

<div id="modal-tambah" data-modal class="fixed inset-0 z-10 {{ $adaSalah ? '' : 'hidden' }} items-center justify-center bg-black/50 p-4 [&:not(.hidden)]:flex">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Tambah anak</h2>
        <p class="mt-1 text-sm text-gray-600">Usia anak harus 24–59 bulan.</p>

        <form method="POST" action="{{ route('guru.anak.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_form" value="tambah">

            <div>
                <label for="tambah-nama" class="block text-sm font-medium">Nama lengkap</label>
                <input id="tambah-nama" name="nama" type="text" maxlength="100" required
                    value="{{ $adaSalah ? old('nama') : '' }}"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                @if ($adaSalah)
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tambah-panggilan" class="block text-sm font-medium">Nama panggilan <span class="font-normal text-gray-500">(opsional)</span></label>
                    <input id="tambah-panggilan" name="nama_panggilan" type="text" maxlength="30"
                        value="{{ $adaSalah ? old('nama_panggilan') : '' }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                    @if ($adaSalah)
                        @error('nama_panggilan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div>
                    <label for="tambah-lahir" class="block text-sm font-medium">Tanggal lahir</label>
                    <input id="tambah-lahir" name="tanggal_lahir" type="date" required
                        value="{{ $adaSalah ? old('tanggal_lahir') : '' }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                    @if ($adaSalah)
                        @error('tanggal_lahir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div>
                <label for="tambah-usia" class="block text-sm font-medium">Usia</label>
                {{-- Dihitung otomatis dari tanggal lahir, tidak ikut dikirim (tanpa name). --}}
                <input id="tambah-usia" type="text" readonly tabindex="-1" data-usia-dari="tambah-lahir"
                    placeholder="Terisi otomatis dari tanggal lahir"
                    class="mt-1 block w-full cursor-not-allowed rounded border border-gray-300 bg-gray-100 px-3 py-2 text-gray-500">
            </div>

            <div>
                <label for="tambah-kelas" class="block text-sm font-medium">Kelas</label>
                <select id="tambah-kelas" name="kelas_id" required
                    class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas['id'] }}" @selected($kelas['id'] === $kelasDipilih)>{{ $kelas['nama'] }}</option>
                    @endforeach
                </select>
                @if ($adaSalah)
                    @error('kelas_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @endif
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
