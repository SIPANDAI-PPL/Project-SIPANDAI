@php
    $gayaKategoriModal = [
        'BSB' => 'border-[#86EFAC] bg-[#DCFCE7] text-[#16A34A]',
        'BSH' => 'border-[#93C5FD] bg-[#DBEAFE] text-[#1D4ED8]',
        'MB' => 'border-[#FDBA74] bg-[#FFEDD5] text-[#EA580C]',
        'BB' => 'border-[#D1D5DB] bg-[#F3F4F6] text-[#4B5563]',
    ];
@endphp

<div id="modal-simpan" data-modal class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-simpan">
    <div class="absolute inset-0 bg-[#111827]/50 backdrop-blur-sm" data-tutup-modal></div>

    <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start gap-3 border-b border-[#F3F4F6] p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EDE9FE] text-[#4F3BD8]">
                <svg class="h-5 w-5"><use href="#ikon-centang" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="judul-modal-simpan" class="text-base font-bold text-[#111827]">Simpan perubahan observasi?</h2>
                <p class="mt-1 text-xs leading-relaxed text-[#6B7280]">Pastikan ringkasan perubahan indikator sudah sesuai sebelum memperbarui data riwayat capaian anak.</p>
            </div>
            <button type="button" data-tutup-modal class="rounded-lg p-1.5 text-[#9CA3AF] transition hover:bg-[#F3F4F6] hover:text-[#4B5563]" aria-label="Tutup">
                <svg class="h-5 w-5"><use href="#ikon-silang" /></svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5">
            <div class="flex items-center gap-3 rounded-xl bg-[#F5F3FF] p-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#4F3BD8] text-xs font-bold text-white">
                    {{ $observasi['anak']['inisial'] }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#111827]">{{ $observasi['anak']['nama'] }}</p>
                </div>
                <p class="shrink-0 text-xs text-[#6B7280]">{{ $observasi['anak']['kelas'] }} &bull; {{ $observasi['tanggal_tampil'] }}</p>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-[#E5E7EB]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F9FAFB] text-[10px] uppercase tracking-wider text-[#9CA3AF]">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold">Indikator</th>
                            <th class="px-3 py-2.5 font-semibold">Sebelum</th>
                            <th class="px-3 py-2.5 font-semibold">Sesudah</th>
                        </tr>
                    </thead>
                    <tbody data-ringkasan-perubahan class="divide-y divide-[#F3F4F6]">
                        @foreach ($observasi['indikator'] as $indikator)
                            @if ($indikator['nilai'] !== $indikator['nilai_awal'])
                                <tr>
                                    <td class="px-3 py-3 font-medium text-[#374151]">{{ $indikator['judul'] }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex rounded-md border border-[#DDD6FE] bg-[#EDE9FE] px-2 py-0.5 text-[11px] font-semibold text-[#4F3BD8]">{{ $indikator['nilai_awal'] }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $gayaKategoriModal[$indikator['nilai']] }}">
                                            <svg class="h-3 w-3"><use href="#ikon-centang" /></svg>{{ $indikator['nilai'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl bg-[#F5F3FF] p-3.5">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-[#6D5BD0]">Catatan Log Audit</p>
                <p class="mt-1 text-xs italic leading-relaxed text-[#4B5563]" data-alasan-modal>Alasan: {{ $observasi['alasan'] }}</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-[#F3F4F6] bg-[#F9FAFB] px-5 py-4">
            <button type="button" data-tutup-modal class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-[#F3F4F6]">
                Periksa Lagi
            </button>
            <button type="button" data-simpan-perubahan class="inline-flex items-center gap-2 rounded-lg bg-[#4F3BD8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3F2FBF]">
                <svg class="h-4 w-4"><use href="#ikon-centang" /></svg>
                Ya, Simpan
            </button>
        </div>
    </div>
</div>
