{{-- Fitur: PBI-12 Ringkasan Pola Respons Anak --}}
{{--
    Kartu "Tren respons per aspek" (mockup profil anak).
    Dipakai halaman ringkasan pola respons dan bisa di-@include di profil anak
    agar tampilan tren tetap konsisten.

    Variabel:
    - $tren: hasil RingkasanPolaResponsService::trenPerAspek() / ['perAspek'].
    - $keteranganPeriode: teks kecil di kanan judul, misalnya "Semua periode".
    - $tautanLengkap (opsional): URL "Lihat pola respons lengkap".
--}}
@php
    $warnaKategori = \App\Services\RingkasanPolaResponsService::WARNA;
@endphp
<section id="tren-aspek" aria-labelledby="judul-tren-aspek" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
    <div class="flex items-baseline justify-between gap-3">
        <h2 id="judul-tren-aspek" class="text-base font-semibold text-gray-900">Tren respons per aspek</h2>
        <span class="text-xs text-gray-500">{{ $keteranganPeriode }}</span>
    </div>

    @if (count($tren) === 0)
        <p class="mt-3 text-sm text-gray-500">Belum ada catatan untuk ditampilkan.</p>
    @else
        <ul class="mt-4 space-y-4">
            @foreach ($tren as $baris)
                <li>
                    <div class="flex items-baseline justify-between gap-3 text-sm">
                        <span class="font-medium text-gray-800">{{ $baris['nama'] }}</span>
                        <span class="text-xs text-gray-500">{{ $baris['ringkasan'] }}</span>
                    </div>
                    <div class="mt-1.5 flex h-2 w-full overflow-hidden rounded-full bg-gray-100"
                        role="img"
                        aria-label="{{ $baris['nama'] }}: {{ collect(\App\Enums\KategoriRespons::cases())->map(fn ($k) => $k->label().' '.$baris['frekuensi'][$k->value])->join(', ') }}">
                        @foreach ($baris['frekuensi'] as $kategori => $jumlah)
                            @if ($jumlah > 0)
                                <span class="h-full" style="width: {{ round($jumlah / $baris['total'] * 100, 2) }}%; background-color: {{ $warnaKategori[$kategori] }}"></span>
                            @endif
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <ul class="mt-4 flex flex-wrap gap-2" aria-label="Keterangan warna">
        @foreach (\App\Enums\KategoriRespons::cases() as $kategori)
            <li class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2.5 py-1 text-xs text-gray-700 ring-1 ring-gray-200">
                <span class="h-2 w-2 rounded-full" style="background-color: {{ $warnaKategori[$kategori->value] }}" aria-hidden="true"></span>
                {{ $kategori->label() }}
            </li>
        @endforeach
    </ul>

    @isset($tautanLengkap)
        <a href="{{ $tautanLengkap }}" class="mt-4 inline-flex text-sm font-semibold text-[#221177] underline-offset-2 hover:underline">
            Lihat pola respons lengkap <span aria-hidden="true">&nbsp;→</span>
        </a>
    @endisset
</section>
