<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Data Anak - SIPANDAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 p-4 font-sans text-gray-900">
    <main class="mx-auto w-full max-w-4xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Kelola Data Anak</h1>
            <button type="button" data-modal-buka="modal-tambah"
                class="rounded bg-indigo-900 px-4 py-2 font-medium text-white hover:bg-indigo-800">
                + Tambah anak
            </button>
        </div>

        <div class="mt-4">
            <label for="kelas" class="block text-sm font-medium">Kelas</label>
            <select id="kelas"
                class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2">
                @foreach ($daftarKelas as $kelas)
                    <option value="{{ $kelas['id'] }}" @selected($kelas['id'] === $kelasTerpilih['id'])>{{ $kelas['label'] }}</option>
                @endforeach
            </select>
        </div>

        <input type="search" placeholder="Cari nama atau nama panggilan"
            class="mt-4 block w-full rounded border border-gray-300 bg-white px-3 py-2">

        <section class="mt-4 rounded-lg border border-gray-300 bg-white p-2 shadow">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" data-tab="aktif"
                    class="rounded border border-gray-300 bg-indigo-900 px-4 py-2 text-sm font-medium text-white">
                    Aktif ({{ count($anakAktif) }})
                </button>
                <button type="button" data-tab="nonaktif"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium">
                    Nonaktif ({{ count($anakNonaktif) }})
                </button>
            </div>

            <div data-panel="aktif" class="px-4 py-2">
                @forelse ($anakAktif as $anak)
                    @include('guru.anak._baris-anak')
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Belum ada anak aktif.</p>
                @endforelse
            </div>

            <div data-panel="nonaktif" class="hidden px-4 py-2">
                @forelse ($anakNonaktif as $anak)
                    @include('guru.anak._baris-anak')
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Belum ada anak nonaktif.</p>
                @endforelse
            </div>
        </section>
    </main>

    @include('guru.anak._modal-tambah', ['daftarKelas' => $daftarKelas])
    @include('guru.anak._script-modal')
</body>
</html>
