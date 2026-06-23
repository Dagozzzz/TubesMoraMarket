<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiPenjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualanStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = TransaksiPenjualan::query()
            ->where('status_pembayaran', 'lunas');

        $totalPenjualan = (float) (clone $query)->sum('total_harga');
        $jumlahTransaksi = (int) (clone $query)->count();
        $rataRata = $jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0;

        return [
            Stat::make('Total Penjualan', $this->rupiah($totalPenjualan))
                ->description('Transaksi lunas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Jumlah Transaksi', number_format($jumlahTransaksi, 0, ',', '.'))
                ->description('Transaksi lunas')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('info')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Rata-rata per Transaksi', $this->rupiah($rataRata))
                ->description('Total dibagi jumlah transaksi')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning')
                ->icon('heroicon-o-scale'),
        ];
    }

    private function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
