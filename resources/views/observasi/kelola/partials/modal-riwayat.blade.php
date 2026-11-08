@php
    $gayaAksi = [
        'UPDATE' => 'border-[#DDD6FE] bg-[#EDE9FE] text-[#4F3BD8]',
        'DELETE' => 'border-[#FECACA] bg-[#FEF2F2] text-[#B91C1C]',
        'DIBUAT' => 'border-[#E5E7EB] bg-[#F3F4F6] text-[#6B7280]',
    ];
    $gayaKategoriRiwayat = [
        'BSB' => 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
        'BSH' => 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
        'MB' => 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
        'BB' => 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
    ];
    $riwayatKosong = request('demo') === 'kosong';
@endphp

<div id="modal-riwayat" data-modal class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-riwayat">
    <div class="absolute inset-0 bg-[#111827]/50 backdrop-blur-sm" data-tutup-modal></div>

    <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-3 border-b border-[#F3F4F6] p-5">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EDE9FE] text-[#4F3BD8]">
                    <svg class="h-5 w-5"><use href="#ikon-jam" /></svg>
                </span>
                <div>
                    <h2 id="judul-modal-riwayat" class="text-base font-bold text-[#111827]">Riwayat Perubahan Observasi</h2>
                    <p class="mt-0.5 font-mono text-xs text-[#6B7280]">{{ $observasi['kode'] }}</p>
                </div>
            </div>
            <button type="button" data-tutup-modal class="rounded-lg p-1.5 text-[#9CA3AF] transition hover:bg-[#F3F4F6] hover:text-[#4B5563]" aria-label="Tutup">
                <svg class="h-5 w-5"><use href="#ikon-silang" /></svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5">
            @if ($riwayatKosong)
                <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#F3F4F6] text-[#9CA3AF]">
                        <svg class="h-7 w-7"><use href="#ikon-jam" /></svg>
                    </span>
                    <p class="mt-4 text-sm font-semibold text-[#374151]">Belum ada riwayat perubahan untuk observasi ini.</p>
                    <p class="mt-1 max-w-sm text-xs leading-relaxed text-[#9CA3AF]">Setiap perubahan indikator, catatan, atau tanggal pengamatan akan tercatat otomatis di sini.</p>
                </div>
            @else
                <ol class="relative space-y-6 before:absolute before:bottom-3 before:left-[15px] before:top-3 before:w-px before:bg-[#E5E7EB]">
                    @foreach ($observasi['riwayat'] as $entri)
                        <li class="relative pl-10">
                            <span class="absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-sm
                                {{ $entri['aksi'] === 'DELETE' ? 'bg-[#FEE2E2] text-[#B91C1C]' : ($entri['aksi'] === 'UPDATE' ? 'bg-[#EDE9FE] text-[#4F3BD8]' : 'bg-[#F3F4F6] text-[#6B7280]') }}">
                                @if ($entri['aksi'] === 'DELETE')
                                    <svg class="h-4 w-4"><use href="#ikon-tempat-sampah" /></svg>
                                @elseif ($entri['aksi'] === 'UPDATE')
                                    <svg class="h-4 w-4"><use href="#ikon-pensil" /></svg>
                                @else
                                    <svg class="h-4 w-4"><use href="#ikon-dokumen" /></svg>
                                @endif
                            </span>

                            <div class="rounded-xl border border-[#E5E7EB] bg-white p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-semibold text-[#111827]">{{ $entri['waktu'] }}</p>
                                        <p class="mt-0.5 text-[11px] text-[#6B7280]">oleh {{ $entri['oleh'] }}</p>
                                    </div>
                                    <span class="inline-flex rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $gayaAksi[$entri['aksi']] ?? $gayaAksi['DIBUAT'] }}">
                                        {{ $entri['aksi'] }}
                                    </span>
                                </div>

                                <p class="mt-2.5 text-xs leading-relaxed text-[#4B5563]">
                                    <span class="font-semibold text-[#374151]">Alasan:</span> {{ $entri['alasan'] }}
                                </p>

                                @if (count($entri['perubahan']) > 0)
                                    <div class="mt-3 overflow-hidden rounded-lg border border-[#E5E7EB]">
                                        <table class="w-full text-left text-[11px]">
                                            <thead class="bg-[#F9FAFB] text-[10px] uppercase tracking-wider text-[#9CA3AF]">
                                                <tr>
                                                    <th class="px-3 py-2 font-semibold">Field</th>
                                                    <th class="px-3 py-2 font-semibold">Sebelum</th>
                                                    <th class="px-3 py-2 font-semibold">Sesudah</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#F3F4F6]">
                                                @foreach ($entri['perubahan'] as $perubahan)
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium text-[#4B5563]">{{ $perubahan['field'] }}</td>
                                                        <td class="px-3 py-2">
                                                            @if ($perubahan['tipe'] === 'kategori')
                                                                <span class="inline-flex rounded-md border px-2 py-0.5 font-semibold {{ $gayaKategoriRiwayat[$perubahan['sebelum']] ?? $gayaKategoriRiwayat['BB'] }}">{{ $perubahan['sebelum'] }}</span>
                                                            @else
                                                                <span class="text-[#6B7280]">{{ $perubahan['sebelum'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex items-center gap-1.5">
                                                                <svg class="h-3 w-3 shrink-0 text-[#9CA3AF]"><use href="#ikon-chevron-kanan" /></svg>
                                                                @if ($perubahan['tipe'] === 'kategori')
                                                                    <span class="inline-flex rounded-md border px-2 py-0.5 font-semibold {{ $gayaKategoriRiwayat[$perubahan['sesudah']] ?? $gayaKategoriRiwayat['BB'] }}">{{ $perubahan['sesudah'] }}</span>
                                                                @else
                                                                    <span class="font-medium text-[#374151]">{{ $perubahan['sesudah'] }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="mt-2 text-[11px] italic text-[#9CA3AF]">Data observasi awal dibuat dengan 6 indikator capaian dan catatan anekdot.</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <div class="flex items-center justify-end border-t border-[#F3F4F6] bg-[#F9FAFB] px-5 py-4">
            <button type="button" data-tutup-modal class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-[#F3F4F6]">
                Tutup
            </button>
        </div>
    </div>
</div>
