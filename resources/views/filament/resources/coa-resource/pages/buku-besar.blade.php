<x-filament-panels::page>

    {{-- Filter Form --}}
    <x-filament::section>
        <form wire:submit="tampilkan">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit">
                    Tampilkan
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Tabel Buku Besar --}}
    @if ($lines->isNotEmpty())
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">No. Jurnal</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left">Keterangan</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">Debit</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">Kredit</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $saldo_berjalan = 0; @endphp
                        @foreach ($lines as $line)
                            @php
                                if ($saldo_normal === 'Debit') {
                                    $saldo_berjalan += $line->debit - $line->kredit;
                                } else {
                                    $saldo_berjalan += $line->kredit - $line->debit;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    {{ $line->journalEntry->tanggal->format('d/m/Y') }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    {{ $line->journalEntry->nomor_jurnal }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2">
                                    {{ $line->keterangan ?? $line->journalEntry->keterangan }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                    @if ($line->debit > 0)
                                        {{ number_format($line->debit, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                    @if ($line->kredit > 0)
                                        {{ number_format($line->kredit, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right font-medium">
                                    {{ number_format(abs($saldo_berjalan), 2, ',', '.') }}
                                    {{ $saldo_berjalan < 0 ? '(K)' : '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="3" class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                Total
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                {{ number_format($total_debit, 2, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                {{ number_format($total_kredit, 2, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-right">
                                {{ number_format(abs($saldo_akhir), 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
    @elseif ($lines->isEmpty() && $kode_akun)
        <x-filament::section>
            <p class="text-gray-500 text-center py-4">Tidak ada transaksi untuk akun dan periode ini.</p>
        </x-filament::section>
    @endif

</x-filament-panels::page>
