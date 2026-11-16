{{-- Fitur: PBI-12 Ringkasan Pola Respons Anak --}}
<x-layouts.app title="Pola Respons Anak">
    <div class="mx-auto w-full max-w-6xl space-y-5">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Ringkasan Pola Respons Anak</h1>
            <p class="mt-1 text-sm text-gray-500">Pilih anak untuk melihat ringkasan pola respons dari catatan observasi.</p>
        </div>

        @include('guru.pola-respons._penanda')

        @forelse ($daftarKelas as $kelas)
            <section class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5" aria-labelledby="kelas-{{ $kelas->id }}">
                <div class="flex items-baseline justify-between gap-3 px-5 pt-5">
                    <h2 id="kelas-{{ $kelas->id }}" class="text-base font-semibold text-gray-900">{{ trim(implode(' - ', array_filter([$kelas->kelompok, $kelas->nama_kelas]))) }}</h2>
                    <span class="text-xs text-gray-500">{{ $kelas->anak->count() }} anak · {{ $kelas->tahun_ajaran }}</span>
                </div>

                @if ($kelas->anak->isEmpty())
                    <p class="px-5 py-6 text-sm text-gray-500">Belum ada anak di kelas ini.</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100">
                        @foreach ($kelas->anak as $anak)
                            <li>
                                <a href="{{ route('guru.pola-respons.anak', $anak) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none">
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#EDE9FE] text-sm font-semibold text-[#221177]" aria-hidden="true">{{ mb_strtoupper(mb_substr($anak->nama_panggilan ?: $anak->nama, 0, 1)) }}</span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-gray-900">{{ $anak->nama_panggilan ?: $anak->nama }}</span>
                                            <span class="block truncate text-xs text-gray-500">{{ $anak->nama }}@unless ($anak->aktif) · Nonaktif @endunless</span>
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-xs font-medium text-gray-600">{{ $anak->observasi_count }} observasi</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @empty
            <section class="rounded-2xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-black/5">
                <h2 class="text-base font-semibold text-gray-900">Belum ada kelas yang Anda ampu</h2>
                <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">Hubungi admin untuk penugasan kelas. Setelah itu, anak di kelas Anda akan tampil di sini.</p>
            </section>
        @endforelse
    </div>
</x-layouts.app>
