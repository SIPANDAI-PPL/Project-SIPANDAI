@php
    // Buka kembali modal jika simpan gagal validasi, dengan isian sebelumnya.
    $adaSalah = $errors->any() && old('_form') === 'ubah';
    $nilai = fn (string $kolom, mixed $bawaan) => $adaSalah ? old($kolom, $bawaan) : $bawaan;
    $kelasDipilih = (int) $nilai('kelas_id', $anak['kelas_id']);
@endphp

<div id="modal-ubah" data-modal class="fixed inset-0 z-10 {{ $adaSalah ? '' : 'hidden' }} items-center justify-center bg-black/50 p-4 [&:not(.hidden)]:flex">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold">Ubah data anak</h2>
        <p class="mt-1 text-sm text-gray-600">{{ $anak['nama'] }}</p>

        <form method="POST" action="{{ route('guru.anak.update', $anak['id']) }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="ubah">

            <div>
                <label for="ubah-nama" class="block text-sm font-medium">Nama lengkap</label>
                <input id="ubah-nama" name="nama" type="text" maxlength="100" required value="{{ $nilai('nama', $anak['nama']) }}"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                @if ($adaSalah)
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="ubah-panggilan" class="block text-sm font-medium">Nama panggilan <span class="font-normal text-gray-500">(opsional)</span></label>
                    <input id="ubah-panggilan" name="nama_panggilan" type="text" maxlength="30" value="{{ $nilai('nama_panggilan', $anak['nama_panggilan']) }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                    @if ($adaSalah)
                        @error('nama_panggilan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div>
                    <label for="ubah-lahir" class="block text-sm font-medium">Tanggal lahir</label>
                    <input id="ubah-lahir" name="tanggal_lahir" type="date" required value="{{ $nilai('tanggal_lahir', $anak['tanggal_lahir']) }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                    @if ($adaSalah)
                        @error('tanggal_lahir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div>
                <label for="ubah-usia" class="block text-sm font-medium">Usia</label>
                {{-- Dihitung otomatis dari tanggal lahir, tidak ikut dikirim (tanpa name). --}}
                <input id="ubah-usia" type="text" readonly tabindex="-1" data-usia-dari="ubah-lahir"
                    placeholder="Terisi otomatis dari tanggal lahir"
                    class="mt-1 block w-full cursor-not-allowed rounded border border-gray-300 bg-gray-100 px-3 py-2 text-gray-500">
            </div>

            <div>
                <label for="ubah-kelas" class="block text-sm font-medium">Kelas</label>
                <select id="ubah-kelas" name="kelas_id" required
                    class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas['id'] }}" @selected($kelas['id'] === $kelasDipilih)>{{ $kelas['nama'] }}</option>
                    @endforeach
                </select>
                @if ($adaSalah)
                    @error('kelas_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm">
                <span>Terhubung: <strong>{{ $anak['jumlah_observasi'] }} observasi</strong></span>
                <a href="{{ route('guru.riwayat-observasi', $anak['id']) }}" class="font-medium text-indigo-900 underline">Lihat</a>
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
