{{--
    PBI-2: tab Data Master. Pakai: @include('data-master._tabs', ['tabAktif' => 'aktivitas'|'strategi'|'aspek'])
    Tab Aspek Perkembangan (PBI-4) hanya untuk admin dan hanya muncul jika rute data-master.aspek.index sudah ada.
--}}
@php
    $tabAktif ??= null;
    $daftarTab = [
        ['kunci' => 'aktivitas', 'label' => 'Aktivitas', 'rute' => 'data-master.aktivitas.index'],
        ['kunci' => 'strategi', 'label' => 'Strategi Pembelajaran', 'rute' => 'data-master.strategi.index'],
    ];
    if (auth()->user()?->peran === 'admin' && \Illuminate\Support\Facades\Route::has('data-master.aspek.index')) {
        $daftarTab[] = ['kunci' => 'aspek', 'label' => 'Aspek Perkembangan', 'rute' => 'data-master.aspek.index'];
    }
@endphp
<nav class="mt-6 flex gap-8 overflow-x-auto border-b border-gray-200 text-sm" aria-label="Tab data master">
    @foreach ($daftarTab as $tab)
        <a href="{{ route($tab['rute']) }}" dusk="tab-{{ $tab['kunci'] }}"
            @class([
                '-mb-px whitespace-nowrap border-b-2 pb-3',
                'border-indigo-900 font-semibold text-indigo-900' => $tabAktif === $tab['kunci'],
                'border-transparent text-gray-600 hover:text-gray-900' => $tabAktif !== $tab['kunci'],
            ])
            @if ($tabAktif === $tab['kunci']) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
