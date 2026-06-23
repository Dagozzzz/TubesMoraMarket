<x-filament-panels::page>
{{--
    BUKU BESAR — General Ledger Report
    Read-only. Auto-refresh saat tanggal berubah.
    Grouped by COA Category → Per Account → Transaction Table → Saldo Akhir.
--}}

{{-- ══════════════════════════════════════════════════════════
     FILTER PERIODE (hanya tanggal, tidak ada tombol submit)
══════════════════════════════════════════════════════════ --}}
<div class="rounded-xl bg-white ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 px-6 py-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
        <div class="flex-1">
            {{ $this->form }}
        </div>
        @if ($reportData)
            <p class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap pb-1">
                Periode: {{ $this->getPeriodeLabel() }}
            </p>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     LAPORAN
══════════════════════════════════════════════════════════ --}}

@if (empty($reportData))

    {{-- Empty state --}}
    <div class="rounded-xl bg-white ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 py-20 flex flex-col items-center gap-3 text-center">
        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-300 dark:text-gray-600" />
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada transaksi pada periode ini</p>
        <p class="text-xs text-gray-400 dark:text-gray-500">Ubah periode di atas untuk menampilkan laporan</p>
    </div>

@else

    {{-- ── Kop Laporan ──────────────────────────────────────── --}}
    <div class="rounded-xl bg-white ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 overflow-hidden">

        {{-- Header --}}
        <div class="px-8 py-5 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white uppercase">
                    Buku Besar Umum
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">General Ledger</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">MoraMarket</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $this->getPeriodeLabel() }}</p>
            </div>
        </div>

        {{-- ── PER KATEGORI ──────────────────────────────────── --}}
        @foreach ($reportData as $groupIdx => $group)

            @php
                $badgeClass = match ($group['kategori']) {
                    'Aset'                  => 'bg-sky-100    text-sky-800    dark:bg-sky-900/40    dark:text-sky-200',
                    'Liabilitas'            => 'bg-rose-100   text-rose-800   dark:bg-rose-900/40   dark:text-rose-200',
                    'Ekuitas'               => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
                    'Pendapatan'            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    'Harga Pokok Penjualan' => 'bg-amber-100  text-amber-800  dark:bg-amber-900/40  dark:text-amber-200',
                    'Beban'                 => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
                    default                 => 'bg-gray-100   text-gray-700   dark:bg-gray-700      dark:text-gray-200',
                };
            @endphp

            {{-- Pemisah antar kategori (kecuali pertama) --}}
            @if ($groupIdx > 0)
                <div class="border-t-4 border-double border-gray-300 dark:border-gray-600 mx-8 my-0"></div>
            @endif

            {{-- Header Kategori --}}
            <div class="px-8 pt-6 pb-3 flex items-center gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-widest {{ $badgeClass }}">
                    {{ $group['kategori'] }}
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ count($group['accounts']) }} akun
                </span>
            </div>

            {{-- ── PER AKUN ──────────────────────────────────── --}}
            @foreach ($group['accounts'] as $accIdx => $account)

                {{-- Pemisah antar akun dalam kategori --}}
                @if ($accIdx > 0)
                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700 mx-8 my-0"></div>
                @endif

                <div class="px-8 pb-6 pt-4">

                    {{-- Sub-header Akun --}}
                    <div class="mb-3 flex items-baseline gap-2">
                        <span class="font-mono text-sm font-bold text-gray-500 dark:text-gray-400">
                            {{ $account['kode'] }}
                        </span>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $account['nama'] }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            (Saldo Normal: {{ $account['saldo_normal'] }})
                        </span>
                    </div>

                    {{-- Tabel Transaksi --}}
                    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                        <table class="w-full text-xs">

                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    <th class="px-4 py-2.5 text-left font-semibold w-24">Tanggal</th>
                                    <th class="px-4 py-2.5 text-left font-semibold w-40">No. Jurnal</th>
                                    <th class="px-4 py-2.5 text-left font-semibold">Keterangan</th>
                                    <th class="px-4 py-2.5 text-right font-semibold w-32">Debit</th>
                                    <th class="px-4 py-2.5 text-right font-semibold w-32">Kredit</th>
                                    <th class="px-4 py-2.5 text-right font-semibold w-36">Saldo</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">

                                {{-- Baris Saldo Awal --}}
                                <tr class="bg-sky-50/60 dark:bg-sky-950/20">
                                    <td class="px-4 py-2 text-gray-400">—</td>
                                    <td class="px-4 py-2 text-gray-400">—</td>
                                    <td class="px-4 py-2 font-semibold text-sky-700 dark:text-sky-300 italic">
                                        Saldo Awal
                                    </td>
                                    <td class="px-4 py-2 text-right text-gray-400">—</td>
                                    <td class="px-4 py-2 text-right text-gray-400">—</td>
                                    <td class="px-4 py-2 text-right font-bold text-sky-700 dark:text-sky-300">
                                        {{ $this->rp($account['saldo_awal']) }}
                                    </td>
                                </tr>

                                {{-- Baris Mutasi --}}
                                @foreach ($account['lines'] as $line)
                                    <tr class="hover:bg-primary-50/30 dark:hover:bg-primary-950/10 transition-colors duration-75">
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $line['tanggal'] }}
                                        </td>
                                        <td class="px-4 py-2 font-mono text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                            {{ $line['no_jurnal'] }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                            {{ $line['keterangan'] }}
                                        </td>
                                        <td class="px-4 py-2 text-right {{ $line['debit'] > 0 ? 'font-medium text-emerald-600 dark:text-emerald-400' : 'text-gray-300 dark:text-gray-600' }}">
                                            {{ $line['debit'] > 0 ? $this->rp($line['debit']) : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right {{ $line['kredit'] > 0 ? 'font-medium text-rose-500 dark:text-rose-400' : 'text-gray-300 dark:text-gray-600' }}">
                                            {{ $line['kredit'] > 0 ? $this->rp($line['kredit']) : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-semibold whitespace-nowrap
                                            {{ $line['saldo'] >= 0 ? 'text-gray-800 dark:text-gray-100' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $this->rp($line['saldo']) }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>

                            {{-- Saldo Akhir akun --}}
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                                    <td colspan="5" class="px-4 py-2.5 text-right text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                                        Saldo Akhir — {{ $account['kode'] }} {{ $account['nama'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-sm font-bold
                                        {{ $account['saldo_akhir'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $this->rp($account['saldo_akhir']) }}
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                    {{-- end tabel --}}

                </div>
                {{-- end per akun --}}

            @endforeach
            {{-- end foreach accounts --}}

        @endforeach
        {{-- end foreach groups --}}

    </div>
    {{-- end card laporan --}}

@endif

</x-filament-panels::page>
