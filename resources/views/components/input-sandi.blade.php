{{--
    PBI-22 Input kata sandi dengan tombol lihat kata sandi. Dipakai di layout guest.
    Pakai: <x-input-sandi name="kata_sandi" autocomplete="current-password" placeholder="..." required />
--}}
@props(['name', 'salah' => false])
<div class="relative mt-1.5">
    <input id="{{ $name }}" name="{{ $name }}" type="password"
        {{ $attributes->class([
            'block h-12 w-full rounded-[10px] border bg-white pl-3.5 pr-12 text-sm placeholder:text-gray-400 transition-[border-color,box-shadow] duration-150 focus:outline-none focus:ring-4 motion-reduce:transition-none',
            'border-[#C5CECB] focus:border-[#2E2580] focus:ring-[#2E2580]/15' => ! $salah,
            'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $salah,
        ]) }}
        @if ($salah) aria-invalid="true" @endif>
    <button type="button" data-lihat-sandi="{{ $name }}" aria-controls="{{ $name }}" aria-pressed="false" aria-label="Tampilkan kata sandi"
        class="absolute inset-y-0 right-1 my-auto flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 transition-colors duration-150 hover:bg-gray-100 hover:text-[#2E2580] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#2E2580]/40 motion-reduce:transition-none">
        <svg data-ikon="mata" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5c4.64 0 8.57 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.64 19.5 12 19.5c-4.64 0-8.57-3.01-9.96-7.18Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <svg data-ikon="mata-coret" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.48 10.48 0 0 0 2.04 12c1.38 4.17 5.32 7.18 9.96 7.18 1.99 0 3.86-.55 5.45-1.52M6.23 6.23A10.45 10.45 0 0 1 12 4.5c4.64 0 8.57 3.01 9.96 7.18a10.5 10.5 0 0 1-4.2 5.53M6.23 6.23 3 3m3.23 3.23 3.65 3.65m7.89 7.89L21 21m-3.23-3.23-3.65-3.65m0 0a3 3 0 1 0-4.24-4.24m4.24 4.24L9.88 9.88" />
        </svg>
    </button>
</div>
