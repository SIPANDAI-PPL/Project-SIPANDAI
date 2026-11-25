{{--
    PBI-16 Satu kartu alternatif strategi di Mode Fokus.
    Butuh: $saran (AlternatifStrategi dengan relasi strategi), $nomor (urutan kartu),
    $adaDipilih (bool, anak sudah punya alternatif dipilih).
    Tombol "Pilih Strategi Ini" dan "Tinjau Ulang dan Pilih" hanya menandai kartu di browser;
    penyimpanan lewat bilah keputusan (_keputusan.blade.php).
--}}
@php
    $manual = $saran->aturan_id === null;
    $dipilih = $saran->status === 'dipilih';
    $kelasBadge = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium';
    $kelasTombolUtama = 'inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-indigo-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50';
    $kelasTombolSekunder = 'inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50';
@endphp
<article dusk="kartu-saran-{{ $saran->id }}" data-kartu-alternatif="{{ $saran->id }}"
    data-nama="{{ $saran->strategi->nama }}" data-url="{{ route('guru.alternatif-strategi.status', $saran) }}"
    @class([
        'flex flex-col rounded-2xl bg-white p-5',
        'border-2 border-indigo-900' => $dipilih,
        'border border-gray-200' => ! $dipilih,
    ])>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-800">Alternatif {{ $nomor }}</span>
            @if ($manual)
                <span class="{{ $kelasBadge }} border-indigo-200 bg-indigo-50 text-indigo-900" dusk="penanda-manual">Ditambahkan guru</span>
            @else
                <span class="{{ $kelasBadge }} border-blue-200 bg-blue-50 text-blue-800">Dari aturan</span>
            @endif
            <span class="{{ $kelasBadge }} hidden border-indigo-300 bg-indigo-50 text-indigo-900" data-penanda-ditandai>Ditandai</span>
        </div>
        @include('alternatif-strategi._badge-status', ['status' => $saran->status])
    </div>

    <h3 class="mt-4 text-lg font-semibold text-indigo-900">{{ $saran->strategi->nama }}</h3>
    @if ($saran->strategi->deskripsi)
        <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $saran->strategi->deskripsi }}</p>
    @endif

    {{-- Alternatif buatan guru selalu menampilkan kotak catatan; jika kosong, hanya tampil "Belum ada catatan." tanpa label. --}}
    @if (filled($saran->dasar_saran))
        <div class="mt-4 rounded-r-lg border-l-4 border-indigo-200 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-700">
            <p class="font-semibold uppercase tracking-wide text-gray-800">{{ $manual ? 'Catatan guru:' : 'Dasar saran:' }}</p>
            <p class="mt-1">{{ $saran->dasar_saran }}</p>
        </div>
    @elseif ($manual)
        <div class="mt-4 rounded-r-lg border-l-4 border-indigo-200 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-700">
            <p class="italic text-gray-500" dusk="catatan-kosong">Belum ada catatan.</p>
        </div>
    @endif

    <div class="mt-auto space-y-2 pt-5">
        @if ($dipilih)
            {{-- TODO PBI-17: aktifkan tombol ini dan arahkan ke formulir tindak lanjut. --}}
            <button type="button" disabled aria-describedby="keterangan-tindak-lanjut-{{ $saran->id }}" class="{{ $kelasTombolSekunder }}">Buat Tindak Lanjut</button>
            <p id="keterangan-tindak-lanjut-{{ $saran->id }}" class="text-center text-xs text-gray-500">Tersedia setelah fitur tindak lanjut selesai.</p>
        @else
            <button type="button" data-tandai aria-pressed="false" @disabled($adaDipilih) dusk="tandai-{{ $saran->id }}"
                class="{{ $saran->status === 'diabaikan' ? $kelasTombolSekunder : $kelasTombolUtama }}">
                {{ $saran->status === 'diabaikan' ? 'Tinjau Ulang dan Pilih' : 'Pilih Strategi Ini' }}
            </button>
            @if ($saran->status === 'disarankan')
                <button type="button" data-abaikan dusk="abaikan-{{ $saran->id }}" class="{{ $kelasTombolSekunder }}">Abaikan</button>
            @endif
            @if ($adaDipilih)
                <p class="text-center text-xs text-gray-500">Sudah ada alternatif yang dipilih untuk anak ini.</p>
            @endif
        @endif
    </div>
</article>
