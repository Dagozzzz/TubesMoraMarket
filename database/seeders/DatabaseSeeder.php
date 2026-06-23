<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

<<<<<<< HEAD
        DB::table('kategori_supplier')->insert([
            [
                'id_kategori' => 'KS001',
                'nama_kategori' => 'Toko',
                'deskripsi' => 'Supplier dari toko atau distributor umum',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_kategori' => 'KS002',
                'nama_kategori' => 'Grosir',
                'deskripsi' => 'Supplier dengan pembelian partai besar',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('supplier')->insert([
            [
                'kode_supplier' => 'SP001',
                'nama_supplier' => 'CV Sumber Jaya',
                'no_handphone' => '081234567890',
                'id_kategori_supplier' => 'KS001',
                'gambar' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_supplier' => 'SP002',
                'nama_supplier' => 'PT Maju Bersama',
                'no_handphone' => '082233445566',
                'id_kategori_supplier' => 'KS002',
                'gambar' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('kategori_barang')->insert([
            [
                'kode_kategori' => 'KB001',
                'jenis_kategori' => 'Mukena',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_kategori' => 'KB002',
                'jenis_kategori' => 'Aksesoris',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_kategori' => 'KB003',
                'jenis_kategori' => 'Pakaian',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('barang')->insert([
            [
                'kode_barang' => 'BRG001',
                'nama_barang' => 'Mukena Dewasa Polos',
                'kategori' => 'Mukena',
                'satuan' => 'Pcs',
                'harga_beli' => 85000.00,
                'harga_jual' => 120000.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_barang' => 'BRG002',
                'nama_barang' => 'Sajadah Travel',
                'kategori' => 'Aksesoris',
                'satuan' => 'Pcs',
                'harga_beli' => 25000.00,
                'harga_jual' => 40000.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_barang' => 'BRG003',
                'nama_barang' => 'Gamis Anak',
                'kategori' => 'Pakaian',
                'satuan' => 'Pcs',
                'harga_beli' => 65000.00,
                'harga_jual' => 95000.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('jabatan')->insert([
            [
                'nama_jabatan' => 'Admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_jabatan' => 'Kasir',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_jabatan' => 'Gudang',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('karyawan')->insert([
            [
                'kode_karyawan' => 'KRY001',
                'nama' => 'Siti Aisyah',
                'tempat_lahir' => 'Pati',
                'tanggal_lahir' => '2000-01-10',
                'alamat' => 'Jl. Mawar No. 1',
                'nik' => '3320011001000001',
                'nip' => 'NIP001',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Kasir',
                'foto' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_karyawan' => 'KRY002',
                'nama' => 'Ahmad Fauzi',
                'tempat_lahir' => 'Jepara',
                'tanggal_lahir' => '1998-05-20',
                'alamat' => 'Jl. Kenanga No. 2',
                'nik' => '3320011001000002',
                'nip' => 'NIP002',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Gudang',
                'foto' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('gaji')->insert([
            [
                'no_slip' => 'SLIP001',
                'karyawan_id' => 1,
                'tgl' => now()->toDateString(),
                'gaji_pokok' => 3000000,
                'tunjangan' => 250000,
                'potongan' => 50000,
                'total_gaji' => 3200000,
                'status' => 'draft',
                'keterangan' => 'Gaji awal seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('customer')->insert([
            [
                'id_customer' => 'FF01',
                'nama_customer' => 'Nurul Huda',
                'email' => 'nurul@example.com',
                'no_telepon' => '081111111111',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_customer' => 'FF02',
                'nama_customer' => 'Rina Sari',
                'email' => 'rina@example.com',
                'no_telepon' => '082222222222',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->call(SalesDashboardSeeder::class);
=======
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            ChartOfAccountSeeder::class,
        ]);
>>>>>>> 30e18ec61165785859429a03d61923966881af1f
    }
}
