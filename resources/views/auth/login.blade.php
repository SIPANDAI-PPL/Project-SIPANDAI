@php
    $kelasInput = 'mt-1.5 block h-12 w-full rounded-[10px] border bg-white px-3.5 text-sm placeholder:text-gray-400 transition-[border-color,box-shadow] duration-150 focus:outline-none focus:ring-4 motion-reduce:transition-none';
    $kelasInputNormal = 'border-[#C5CECB] focus:border-[#2E2580] focus:ring-[#2E2580]/15';
    $kelasInputSalah = 'border-red-500 focus:border-red-500 focus:ring-red-500/15';
@endphp
<x-layouts.guest title="Masuk">
    <h1 class="text-2xl font-bold text-gray-900">Masuk ke SIPANDAI</h1>
    <p class="mt-1.5 text-sm text-gray-600">Silakan masuk dengan akun Admin atau Guru.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="login" class="block text-sm font-medium text-gray-800">Email atau nama pengguna</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}"
                placeholder="Masukkan email atau nama pengguna"
                autocomplete="username" autofocus required
                @error('login') aria-invalid="true" @enderror
                class="{{ $kelasInput }} {{ $errors->has('login') ? $kelasInputSalah : $kelasInputNormal }}">
            @error('login')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="kata_sandi" class="block text-sm font-medium text-gray-800">Kata sandi</label>
            <x-input-sandi name="kata_sandi" placeholder="Masukkan kata sandi"
                autocomplete="current-password" required :salah="$errors->has('kata_sandi')" />
            @error('kata_sandi')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="h-12 w-full rounded-[10px] bg-[#2E2580] px-4 text-sm font-semibold text-white transition-colors duration-150 hover:bg-[#251E68] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#2E2580]/30 active:bg-[#1F1958] motion-reduce:transition-none">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-gray-500">Butuh bantuan? Hubungi admin sekolah.</p>
</x-layouts.guest>
