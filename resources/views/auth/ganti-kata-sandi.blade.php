<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Kata Sandi - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 p-4 font-sans text-gray-900">
    <main class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
        <h1 class="text-2xl font-semibold">Ganti Kata Sandi</h1>
        <p class="mt-1 text-sm text-gray-600">Anda masih memakai kata sandi sementara. Buat kata sandi baru untuk melanjutkan.</p>

        <form method="POST" action="{{ route('kata-sandi.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="kata_sandi_baru" class="block text-sm font-medium">Kata sandi baru</label>
                <input id="kata_sandi_baru" name="kata_sandi_baru" type="password"
                    autocomplete="new-password" minlength="8" autofocus required
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
                @error('kata_sandi_baru')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kata_sandi_baru_confirmation" class="block text-sm font-medium">Konfirmasi kata sandi baru</label>
                <input id="kata_sandi_baru_confirmation" name="kata_sandi_baru_confirmation" type="password"
                    autocomplete="new-password" minlength="8" required
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                Simpan kata sandi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full rounded px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Keluar</button>
        </form>
    </main>
</body>
</html>
