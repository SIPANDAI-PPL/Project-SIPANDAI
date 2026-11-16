{{--
    PBI-16 Badge status alternatif strategi. Satu-satunya sumber gaya badge status,
    dipakai di halaman daftar dan Mode Fokus. Butuh: $status (disarankan, dipilih, diabaikan).
    Status selalu ditulis sebagai teks, tidak hanya warna.
--}}
@php
    $gayaStatus = [
        'disarankan' => ['border-amber-200 bg-amber-50 text-amber-800', 'bg-amber-500'],
        'dipilih' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'bg-emerald-500'],
        'diabaikan' => ['border-gray-200 bg-gray-100 text-gray-600', 'bg-gray-400'],
    ];
    [$kelasWarna, $kelasTitik] = $gayaStatus[$status] ?? $gayaStatus['diabaikan'];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $kelasWarna }}" dusk="badge-status" data-status="{{ $status }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $kelasTitik }}" aria-hidden="true"></span>Status: {{ ucfirst($status) }}
</span>
