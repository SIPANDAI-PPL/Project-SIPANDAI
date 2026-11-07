@php
    $warnaBadge = match ($anak['kategori']) {
        'Terlibat aktif' => 'bg-green-100 text-green-800',
        'Kurang tertarik' => 'bg-orange-100 text-orange-800',
        'Terlibat dengan pendampingan' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<a href="{{ route('guru.anak.show', $anak['id']) }}"
    class="flex items-center gap-4 border-b border-gray-200 py-3 last:border-b-0 hover:bg-gray-50">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-800">
        {{ $anak['inisial'] }}
    </span>
    <span class="min-w-0 flex-1">
        <span class="block font-medium">
            {{ $anak['panggilan'] }}
            <span class="text-sm font-normal text-gray-500">· {{ $anak['usia'] }}</span>
        </span>
        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $warnaBadge }}">
            {{ $anak['kategori'] ?? 'Belum diobservasi' }}
        </span>
    </span>
    <span class="text-sm text-gray-500">{{ $anak['waktu'] ?? '' }}</span>
</a>
