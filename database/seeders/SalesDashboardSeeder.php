<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesDashboardSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            $categories = [
                ['kode_kategori' => 'KB001', 'nama_barang' => 'Mukena', 'jenis_kategori' => 'Mukena'],
                ['kode_kategori' => 'KB002', 'nama_barang' => 'Aksesoris', 'jenis_kategori' => 'Aksesoris'],
                ['kode_kategori' => 'KB003', 'nama_barang' => 'Pakaian', 'jenis_kategori' => 'Pakaian'],
                ['kode_kategori' => 'KB004', 'nama_barang' => 'Hijab', 'jenis_kategori' => 'Hijab'],
                ['kode_kategori' => 'KB005', 'nama_barang' => 'Perlengkapan Ibadah', 'jenis_kategori' => 'Perlengkapan Ibadah'],
            ];

            foreach ($categories as $category) {
                DB::table('kategori_barang')->updateOrInsert(
                    ['kode_kategori' => $category['kode_kategori']],
                    $category + ['created_at' => $now, 'updated_at' => $now],
                );
            }

            $products = [
                ['kode_barang' => 'BRG001', 'nama_barang' => 'Mukena Dewasa Polos', 'kategori' => 'Mukena', 'satuan' => 'pcs', 'harga_beli' => 85000, 'harga_jual' => 120000],
                ['kode_barang' => 'BRG002', 'nama_barang' => 'Sajadah Travel', 'kategori' => 'Aksesoris', 'satuan' => 'pcs', 'harga_beli' => 25000, 'harga_jual' => 40000],
                ['kode_barang' => 'BRG003', 'nama_barang' => 'Gamis Anak', 'kategori' => 'Pakaian', 'satuan' => 'pcs', 'harga_beli' => 65000, 'harga_jual' => 95000],
                ['kode_barang' => 'BRG004', 'nama_barang' => 'Hijab Pashmina', 'kategori' => 'Hijab', 'satuan' => 'pcs', 'harga_beli' => 35000, 'harga_jual' => 55000],
                ['kode_barang' => 'BRG005', 'nama_barang' => 'Tasbih Digital', 'kategori' => 'Aksesoris', 'satuan' => 'pcs', 'harga_beli' => 12000, 'harga_jual' => 25000],
                ['kode_barang' => 'BRG006', 'nama_barang' => 'Al-Quran Travel', 'kategori' => 'Perlengkapan Ibadah', 'satuan' => 'pcs', 'harga_beli' => 50000, 'harga_jual' => 75000],
                ['kode_barang' => 'BRG007', 'nama_barang' => 'Sarung Premium', 'kategori' => 'Pakaian', 'satuan' => 'pcs', 'harga_beli' => 78000, 'harga_jual' => 110000],
                ['kode_barang' => 'BRG008', 'nama_barang' => 'Ciput Rajut', 'kategori' => 'Hijab', 'satuan' => 'pcs', 'harga_beli' => 9000, 'harga_jual' => 18000],
            ];

            foreach ($products as $product) {
                DB::table('barang')->updateOrInsert(
                    ['kode_barang' => $product['kode_barang']],
                    $product + ['created_at' => $now, 'updated_at' => $now],
                );
            }

            $customers = [
                ['id_customer' => 'FF01', 'nama_customer' => 'Nurul Huda', 'email' => 'nurul@example.com', 'no_telepon' => '081111111111'],
                ['id_customer' => 'FF02', 'nama_customer' => 'Rina Sari', 'email' => 'rina@example.com', 'no_telepon' => '082222222222'],
                ['id_customer' => 'FF03', 'nama_customer' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'no_telepon' => '083333333333'],
                ['id_customer' => 'FF04', 'nama_customer' => 'Ayu Permata', 'email' => 'ayu@example.com', 'no_telepon' => '084444444444'],
                ['id_customer' => 'FF05', 'nama_customer' => 'Fitri Amalia', 'email' => 'fitri@example.com', 'no_telepon' => '085555555555'],
            ];

            foreach ($customers as $customer) {
                DB::table('customer')->updateOrInsert(
                    ['id_customer' => $customer['id_customer']],
                    $customer + ['created_at' => $now, 'updated_at' => $now],
                );
            }

            $productRows = DB::table('barang')
                ->whereIn('kode_barang', array_column($products, 'kode_barang'))
                ->get()
                ->keyBy('kode_barang');

            $kasirId = DB::table('users')->value('id');
            $baseMonth = CarbonImmutable::now()->startOfMonth()->subMonths(5);

            $sales = [
                ['kode' => 'PJL-00001', 'month' => 0, 'day' => 3, 'time' => [10, 15], 'status' => 'lunas', 'customer' => 'FF01', 'items' => [['BRG001', 1], ['BRG003', 1]]],
                ['kode' => 'PJL-00002', 'month' => 0, 'day' => 18, 'time' => [14, 20], 'status' => 'pending', 'customer' => 'FF02', 'items' => [['BRG002', 2]]],
                ['kode' => 'PJL-00003', 'month' => 1, 'day' => 5, 'time' => [9, 45], 'status' => 'lunas', 'customer' => 'FF03', 'items' => [['BRG004', 1], ['BRG005', 2]]],
                ['kode' => 'PJL-00004', 'month' => 1, 'day' => 21, 'time' => [16, 5], 'status' => 'gagal', 'customer' => 'FF04', 'items' => [['BRG006', 1]]],
                ['kode' => 'PJL-00005', 'month' => 2, 'day' => 2, 'time' => [11, 30], 'status' => 'lunas', 'customer' => 'FF02', 'items' => [['BRG001', 1], ['BRG007', 3]]],
                ['kode' => 'PJL-00006', 'month' => 2, 'day' => 17, 'time' => [13, 10], 'status' => 'lunas', 'customer' => 'FF05', 'items' => [['BRG008', 2], ['BRG002', 1]]],
                ['kode' => 'PJL-00007', 'month' => 3, 'day' => 4, 'time' => [10, 0], 'status' => 'lunas', 'customer' => 'FF01', 'items' => [['BRG003', 2], ['BRG005', 1]]],
                ['kode' => 'PJL-00008', 'month' => 3, 'day' => 12, 'time' => [15, 25], 'status' => 'pending', 'customer' => 'FF03', 'items' => [['BRG004', 2]]],
                ['kode' => 'PJL-00009', 'month' => 3, 'day' => 25, 'time' => [17, 40], 'status' => 'lunas', 'customer' => 'FF04', 'items' => [['BRG002', 3], ['BRG006', 1]]],
                ['kode' => 'PJL-00010', 'month' => 4, 'day' => 3, 'time' => [9, 5], 'status' => 'lunas', 'customer' => 'FF05', 'items' => [['BRG001', 2], ['BRG005', 2]]],
                ['kode' => 'PJL-00011', 'month' => 4, 'day' => 15, 'time' => [12, 15], 'status' => 'gagal', 'customer' => 'FF02', 'items' => [['BRG007', 1]]],
                ['kode' => 'PJL-00012', 'month' => 4, 'day' => 29, 'time' => [18, 0], 'status' => 'lunas', 'customer' => 'FF03', 'items' => [['BRG008', 1], ['BRG003', 1], ['BRG002', 1]]],
                ['kode' => 'PJL-00013', 'month' => 5, 'day' => 4, 'time' => [10, 45], 'status' => 'lunas', 'customer' => 'FF01', 'items' => [['BRG006', 2], ['BRG005', 3]]],
                ['kode' => 'PJL-00014', 'month' => 5, 'day' => 10, 'time' => [13, 35], 'status' => 'lunas', 'customer' => 'FF04', 'items' => [['BRG001', 1], ['BRG004', 1]]],
                ['kode' => 'PJL-00015', 'month' => 5, 'day' => 17, 'time' => [16, 55], 'status' => 'pending', 'customer' => 'FF05', 'items' => [['BRG008', 2]]],
            ];

            foreach ($sales as $sale) {
                $detailRows = [];
                $totalHarga = 0;
                $tanggal = $baseMonth
                    ->addMonths($sale['month'])
                    ->addDays($sale['day'] - 1)
                    ->setTime($sale['time'][0], $sale['time'][1]);

                foreach ($sale['items'] as [$kodeBarang, $jumlah]) {
                    $product = $productRows[$kodeBarang];
                    $subtotal = (float) $product->harga_jual * $jumlah;
                    $totalHarga += $subtotal;

                    $detailRows[] = [
                        'id_barang' => $product->id,
                        'kode_barang' => $product->kode_barang,
                        'nama_barang' => $product->nama_barang,
                        'kategori' => $product->kategori,
                        'satuan' => $product->satuan,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $product->harga_jual,
                        'subtotal' => $subtotal,
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ];
                }

                DB::table('transaksi_penjualan')->updateOrInsert(
                    ['kode_penjualan' => $sale['kode']],
                    [
                        'id_customer' => $sale['customer'],
                        'id_kasir' => $kasirId,
                        'tanggal_penjualan' => $tanggal,
                        'status_pembayaran' => $sale['status'],
                        'metode_pembayaran' => 'midtrans',
                        'midtrans_order_id' => 'MID-' . $sale['kode'],
                        'midtrans_snap_token' => null,
                        'midtrans_redirect_url' => null,
                        'total_harga' => $totalHarga,
                        'catatan' => 'Data dummy dashboard penjualan',
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ],
                );

                $transactionId = DB::table('transaksi_penjualan')
                    ->where('kode_penjualan', $sale['kode'])
                    ->value('id');

                DB::table('detail_transaksi_penjualan')
                    ->where('id_transaksi_penjualan', $transactionId)
                    ->delete();

                foreach ($detailRows as $detailRow) {
                    DB::table('detail_transaksi_penjualan')->insert(
                        ['id_transaksi_penjualan' => $transactionId] + $detailRow,
                    );
                }
            }
        });
    }
}
