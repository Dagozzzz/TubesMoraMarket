<?php

namespace App\Filament\Widgets;

use App\Models\DetailTransaksiPenjualan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BarangTerlarisTable extends BaseWidget
{
    protected static ?string $heading = 'Barang Terlaris';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTopBarangQuery())
            ->columns([
                TextColumn::make('nama_barang_display')
                    ->label('Barang')
                    ->description(fn ($record): string => $record->kode_barang_display ?? '')
                    ->wrap(),

                TextColumn::make('total_jumlah')
                    ->label('Qty Terjual')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('total_nilai')
                    ->label('Total Nilai')
                    ->money('IDR', locale: 'id')
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->recordUrl(null)
            ->emptyStateHeading('Belum ada penjualan lunas');
    }

    private function getTopBarangQuery(): Builder
    {
        return DetailTransaksiPenjualan::query()
            ->join('transaksi_penjualan', 'transaksi_penjualan.id', '=', 'detail_transaksi_penjualan.id_transaksi_penjualan')
            ->leftJoin('barang', 'barang.id', '=', 'detail_transaksi_penjualan.id_barang')
            ->where('transaksi_penjualan.status_pembayaran', 'lunas')
            ->selectRaw("
                MIN(detail_transaksi_penjualan.id) as id,
                MIN(detail_transaksi_penjualan.id_barang) as id_barang,
                MAX(COALESCE(barang.kode_barang, NULLIF(detail_transaksi_penjualan.kode_barang, ''), '(Barang Dihapus)')) as kode_barang_display,
                MAX(COALESCE(barang.nama_barang, NULLIF(detail_transaksi_penjualan.nama_barang, ''), '(Barang Dihapus)')) as nama_barang_display,
                SUM(detail_transaksi_penjualan.jumlah) as total_jumlah,
                SUM(detail_transaksi_penjualan.subtotal) as total_nilai
            ")
            ->groupByRaw("
                CASE
                    WHEN detail_transaksi_penjualan.id_barang IS NULL
                        THEN CONCAT('snapshot:', COALESCE(NULLIF(detail_transaksi_penjualan.kode_barang, ''), ''), '|', COALESCE(NULLIF(detail_transaksi_penjualan.nama_barang, ''), ''))
                    ELSE CONCAT('barang:', detail_transaksi_penjualan.id_barang)
                END
            ")
            ->orderByDesc('total_jumlah')
            ->orderByDesc('total_nilai')
            ->limit(5);
    }
}
