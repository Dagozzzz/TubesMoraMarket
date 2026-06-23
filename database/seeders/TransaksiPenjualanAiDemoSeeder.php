<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailTransaksiPenjualan;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiPenjualanAiDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $customers = collect([
            [
                'id_customer' => 'AID001',
                'nama_customer' => 'Aisyah Collection',
                'email' => 'aisyah.collection@example.test',
                'no_telepon' => '081200000001',
            ],
            [
                'id_customer' => 'AID002',
                'nama_customer' => 'Toko Barokah',
                'email' => 'barokah@example.test',
                'no_telepon' => '081200000002',
            ],
            [
                'id_customer' => 'AID003',
                'nama_customer' => 'Nur Fashion',
                'email' => 'nur.fashion@example.test',
                'no_telepon' => '081200000003',
            ],
        ])->map(function (array $customer) use ($now): Customer {
            return Customer::query()->updateOrCreate(
                ['id_customer' => $customer['id_customer']],
                [
                    'nama_customer' => $customer['nama_customer'],
                    'email' => $customer['email'],
                    'no_telepon' => $customer['no_telepon'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        });

        $products = collect([
            [
                'kode_barang' => 'AIB001',
                'nama_barang' => 'Mukena Rayon Premium',
                'kategori' => 'Mukena',
                'satuan' => 'pcs',
                'harga_beli' => 85000,
                'harga_jual' => 135000,
            ],
            [
                'kode_barang' => 'AIB002',
                'nama_barang' => 'Sajadah Travel Lipat',
                'kategori' => 'Aksesoris',
                'satuan' => 'pcs',
                'harga_beli' => 28000,
                'harga_jual' => 45000,
            ],
            [
                'kode_barang' => 'AIB003',
                'nama_barang' => 'Gamis Anak Motif',
                'kategori' => 'Pakaian',
                'satuan' => 'pcs',
                'harga_beli' => 65000,
                'harga_jual' => 98000,
            ],
            [
                'kode_barang' => 'AIB004',
                'nama_barang' => 'Ciput Rajut',
                'kategori' => 'Aksesoris',
                'satuan' => 'pcs',
                'harga_beli' => 9000,
                'harga_jual' => 18000,
            ],
        ])->mapWithKeys(function (array $product) use ($now): array {
            DB::table('barang')->updateOrInsert(
                ['kode_barang' => $product['kode_barang']],
                [
                    'nama_barang' => $product['nama_barang'],
                    'kategori' => $product['kategori'],
                    'satuan' => $product['satuan'],
                    'harga_beli' => $product['harga_beli'],
                    'harga_jual' => $product['harga_jual'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $barang = Barang::query()
                ->where('kode_barang', $product['kode_barang'])
                ->firstOrFail();

            return [$product['kode_barang'] => $barang];
        });

        $sales = [
            [
                'kode_penjualan' => 'PJL-AI-001',
                'customer' => 'AID001',
                'tanggal_penjualan' => now()->subMonths(5)->setDay(8)->setTime(9, 30),
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'tunai',
                'items' => [
                    ['kode_barang' => 'AIB001', 'jumlah' => 4],
                    ['kode_barang' => 'AIB002', 'jumlah' => 6],
                ],
            ],
            [
                'kode_penjualan' => 'PJL-AI-002',
                'customer' => 'AID002',
                'tanggal_penjualan' => now()->subMonths(4)->setDay(14)->setTime(14, 15),
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'transfer',
                'items' => [
                    ['kode_barang' => 'AIB003', 'jumlah' => 3],
                    ['kode_barang' => 'AIB004', 'jumlah' => 12],
                ],
            ],
            [
                'kode_penjualan' => 'PJL-AI-003',
                'customer' => 'AID003',
                'tanggal_penjualan' => now()->subMonths(3)->setDay(21)->setTime(11, 0),
                'status_pembayaran' => 'pending',
                'metode_pembayaran' => 'midtrans',
                'items' => [
                    ['kode_barang' => 'AIB001', 'jumlah' => 2],
                    ['kode_barang' => 'AIB004', 'jumlah' => 8],
                ],
            ],
            [
                'kode_penjualan' => 'PJL-AI-004',
                'customer' => 'AID001',
                'tanggal_penjualan' => now()->subMonths(2)->setDay(9)->setTime(16, 45),
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'midtrans',
                'items' => [
                    ['kode_barang' => 'AIB001', 'jumlah' => 5],
                    ['kode_barang' => 'AIB003', 'jumlah' => 2],
                    ['kode_barang' => 'AIB004', 'jumlah' => 10],
                ],
            ],
            [
                'kode_penjualan' => 'PJL-AI-005',
                'customer' => 'AID002',
                'tanggal_penjualan' => now()->subMonth()->setDay(18)->setTime(10, 20),
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'transfer',
                'items' => [
                    ['kode_barang' => 'AIB002', 'jumlah' => 9],
                    ['kode_barang' => 'AIB004', 'jumlah' => 15],
                ],
            ],
            [
                'kode_penjualan' => 'PJL-AI-006',
                'customer' => 'AID003',
                'tanggal_penjualan' => now()->setDay(5)->setTime(13, 10),
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'tunai',
                'items' => [
                    ['kode_barang' => 'AIB001', 'jumlah' => 6],
                    ['kode_barang' => 'AIB002', 'jumlah' => 4],
                    ['kode_barang' => 'AIB003', 'jumlah' => 4],
                ],
            ],
        ];

        foreach ($sales as $sale) {
            $total = collect($sale['items'])->sum(function (array $item) use ($products): float {
                $product = $products[$item['kode_barang']];

                return (float) $product->harga_jual * (int) $item['jumlah'];
            });

            $transaction = TransaksiPenjualan::query()->updateOrCreate(
                ['kode_penjualan' => $sale['kode_penjualan']],
                [
                    'id_customer' => $sale['customer'],
                    'id_kasir' => null,
                    'tanggal_penjualan' => Carbon::parse($sale['tanggal_penjualan']),
                    'status_pembayaran' => $sale['status_pembayaran'],
                    'metode_pembayaran' => $sale['metode_pembayaran'],
                    'midtrans_order_id' => null,
                    'midtrans_snap_token' => null,
                    'midtrans_redirect_url' => null,
                    'total_harga' => $total,
                    'catatan' => 'Data demo untuk uji widget dan Analisis AI Penjualan.',
                ],
            );

            DetailTransaksiPenjualan::query()
                ->where('id_transaksi_penjualan', $transaction->id)
                ->delete();

            foreach ($sale['items'] as $item) {
                $product = $products[$item['kode_barang']];
                $quantity = (int) $item['jumlah'];
                $price = (float) $product->harga_jual;

                DetailTransaksiPenjualan::query()->create([
                    'id_transaksi_penjualan' => $transaction->id,
                    'id_barang' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'kategori' => $product->kategori,
                    'satuan' => $product->satuan,
                    'jumlah' => $quantity,
                    'harga_satuan' => $price,
                    'subtotal' => $quantity * $price,
                ]);
            }
        }
    }
}
