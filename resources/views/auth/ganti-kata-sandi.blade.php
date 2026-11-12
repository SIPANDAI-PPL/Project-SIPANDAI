<x-layouts.guest title="Ganti Kata Sandi">
    <h1 class="text-2xl font-bold text-gray-900">Ganti Kata Sandi</h1>
    <p class="mt-1.5 text-sm text-gray-600">Anda masih memakai kata sandi sementara. Buat kata sandi baru untuk melanjutkan.</p>

    <form method="POST" action="{{ route('kata-sandi.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="kata_sandi_baru" class="block text-sm font-medium text-gray-800">Kata sandi baru</label>
            <x-input-sandi name="kata_sandi_baru" placeholder="Masukkan kata sandi baru"
                autocomplete="new-password" minlength="8" autofocus required :salah="$errors->has('kata_sandi_baru')" />
            @error('kata_sandi_baru')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-gray-500">Minimal 8 karakter.</p>
            @enderror
        </div>

        <div>
            <label for="kata_sandi_baru_confirmation" class="block text-sm font-medium text-gray-800">Konfirmasi kata sandi baru</label>
            <x-input-sandi name="kata_sandi_baru_confirmation" placeholder="Ulangi kata sandi baru"
                autocomplete="new-password" minlength="8" required />
        </div>

        <button type="submit"
            class="h-12 w-full rounded-[10px] bg-[#2E2580] px-4 text-sm font-semibold text-white transition-colors duration-150 hover:bg-[#251E68] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#2E2580]/30 active:bg-[#1F1958] motion-reduce:transition-none">
            Simpan kata sandi
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit"
            class="h-11 w-full rounded-[10px] text-sm font-medium text-gray-600 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#2E2580]/20 motion-reduce:transition-none">Keluar</button>
    </form>
</x-layouts.guest>
