<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Seed the chart of accounts for a small grocery store.
     */
    public function run(): void
    {
        $accounts = [
            ['kode_akun' => '1000', 'nama_akun' => 'Aset / Aktiva', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1101', 'nama_akun' => 'Kas Toko', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1102', 'nama_akun' => 'Kas Kecil', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1110', 'nama_akun' => 'Bank BCA', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1111', 'nama_akun' => 'Bank Mandiri', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1120', 'nama_akun' => 'Piutang Pelanggan', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1130', 'nama_akun' => 'Persediaan Barang Dagang', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1140', 'nama_akun' => 'Inventaris Toko', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1210', 'nama_akun' => 'Peralatan Toko', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1220', 'nama_akun' => 'Kendaraan Operasional', 'kategori' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1290', 'nama_akun' => 'Akumulasi Penyusutan Aset Tetap', 'kategori' => 'Aset', 'saldo_normal' => 'Kredit'],

            ['kode_akun' => '2000', 'nama_akun' => 'Kewajiban / Liabilitas', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2101', 'nama_akun' => 'Hutang Usaha Supplier', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2102', 'nama_akun' => 'Hutang Gaji Karyawan', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2103', 'nama_akun' => 'Hutang Pajak', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2104', 'nama_akun' => 'Hutang Listrik dan Air', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2201', 'nama_akun' => 'Pinjaman Pemilik', 'kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit'],

            ['kode_akun' => '3000', 'nama_akun' => 'Ekuitas / Modal', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3101', 'nama_akun' => 'Modal Pemilik', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3201', 'nama_akun' => 'Prive Pemilik', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '3301', 'nama_akun' => 'Laba Ditahan', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3901', 'nama_akun' => 'Laba Tahun Berjalan', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Kredit'],

            ['kode_akun' => '4000', 'nama_akun' => 'Pendapatan', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4101', 'nama_akun' => 'Penjualan Barang Kelontong', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4102', 'nama_akun' => 'Penjualan Rokok dan Minuman', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4103', 'nama_akun' => 'Penjualan Produk Rumah Tangga', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4201', 'nama_akun' => 'Pendapatan Jasa Titip / Antar', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4301', 'nama_akun' => 'Retur Penjualan', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Debit'],

            ['kode_akun' => '5000', 'nama_akun' => 'Harga Pokok Penjualan (HPP)', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5101', 'nama_akun' => 'HPP Barang Kelontong', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5102', 'nama_akun' => 'HPP Rokok dan Minuman', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5103', 'nama_akun' => 'HPP Produk Rumah Tangga', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5201', 'nama_akun' => 'Ongkos Angkut Pembelian', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5301', 'nama_akun' => 'Retur Pembelian', 'kategori' => 'Harga Pokok Penjualan', 'saldo_normal' => 'Kredit'],

            ['kode_akun' => '6000', 'nama_akun' => 'Beban Operasional', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6101', 'nama_akun' => 'Beban Gaji Karyawan', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6102', 'nama_akun' => 'Beban Sewa Toko', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6103', 'nama_akun' => 'Beban Listrik', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6104', 'nama_akun' => 'Beban Air', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6105', 'nama_akun' => 'Beban Internet dan Pulsa', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6106', 'nama_akun' => 'Beban Plastik dan Kemasan', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6107', 'nama_akun' => 'Beban Kebersihan Toko', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6108', 'nama_akun' => 'Beban Transportasi / BBM', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6109', 'nama_akun' => 'Beban Perlengkapan Toko', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6110', 'nama_akun' => 'Beban Perbaikan dan Pemeliharaan', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6111', 'nama_akun' => 'Beban Penyusutan Peralatan Toko', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6112', 'nama_akun' => 'Beban Penyusutan Kendaraan', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6113', 'nama_akun' => 'Beban Pajak dan Retribusi', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6114', 'nama_akun' => 'Beban Administrasi Bank', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6115', 'nama_akun' => 'Beban Selisih Kas', 'kategori' => 'Beban', 'saldo_normal' => 'Debit'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['kode_akun' => $account['kode_akun']],
                $account,
            );
        }
    }
}
