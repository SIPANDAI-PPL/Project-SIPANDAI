<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 p-4 font-sans text-gray-900">
    <main class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
        <h1 class="text-2xl font-semibold">SIPANDAI</h1>
        <p class="mt-1 text-sm text-gray-600">Masuk untuk melanjutkan.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-sm font-medium">Email atau nama pengguna</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}"
                    autocomplete="username" autofocus required
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                @error('login')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kata_sandi" class="block text-sm font-medium">Kata sandi</label>
                <input id="kata_sandi" name="kata_sandi" type="password"
                    autocomplete="current-password" required
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                @error('kata_sandi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                Masuk
            </button>
        </form>
    </main>
</body>
</html>
