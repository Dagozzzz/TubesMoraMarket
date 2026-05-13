<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransSnapService
{
    public function createTransaction(array $cart, array $summary, array $customer): array
    {
        $serverKey = config('services.midtrans.server_key');

        if (! $serverKey) {
            throw new RuntimeException('Server key Midtrans belum tersedia.');
        }

        $orderId = 'MORA-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
        $endpoint = rtrim(config('services.midtrans.snap_url'), '/') . '/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $summary['total'],
            ],
            'customer_details' => [
                'first_name' => $customer['nama'] ?? 'Kasir Mora',
                'email' => $customer['email'] ?? 'kasir@mora-market.test',
            ],
            'item_details' => collect($cart)->map(function ($item) {
                return [
                    'id' => (string) $item['kode_barang'],
                    'price' => (int) $item['harga_jual'],
                    'quantity' => (int) $item['qty'],
                    'name' => Str::limit($item['nama_barang'], 50, ''),
                ];
            })->values()->all(),
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.');
        }

        return [
            'order_id' => $orderId,
            'token' => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
        ];
    }
}
