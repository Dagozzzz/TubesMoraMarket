<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use App\Filament\Resources\TransaksiPenjualanResource\Widgets\PenjualanBulananChart;
use App\Filament\Resources\TransaksiPenjualanResource\Widgets\TopProdukTerjualChart;
use App\Models\DetailTransaksiPenjualan;
use App\Models\TransaksiPenjualan;
use App\Models\TransaksiPenjualanAiAnalysis;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;
use Throwable;

class ListTransaksiPenjualans extends ListRecords
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refreshAiPenjualan')
                ->label('Analisis AI Penjualan')
                ->icon('heroicon-m-sparkles')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Buat Analisis AI Penjualan')
                ->modalDescription(
                    'AI akan membaca data transaksi penjualan dan menyimpan ringkasan analisis terbaru.'
                )
                ->action(function (): void {
                    $apiKey = config('services.gemini.api_key');

                    if (blank($apiKey)) {
                        Notification::make()
                            ->title('GEMINI_API_KEY belum diisi')
                            ->body('Analisis AI penjualan belum bisa dibuat tanpa API key.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $transactions = TransaksiPenjualan::query()
                        ->with(['customer', 'detailTransaksi'])
                        ->orderByDesc('tanggal_penjualan')
                        ->limit(100)
                        ->get();

                    if ($transactions->isEmpty()) {
                        Notification::make()
                            ->title('Data penjualan masih kosong')
                            ->body('Tambahkan transaksi penjualan terlebih dahulu sebelum membuat analisis AI.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $paidTransactions = $transactions
                        ->where('status_pembayaran', 'lunas');

                    $topProducts = DetailTransaksiPenjualan::query()
                        ->whereHas(
                            'transaksiPenjualan',
                            fn ($query) => $query->where('status_pembayaran', 'lunas')
                        )
                        ->selectRaw('nama_barang, SUM(jumlah) as total_terjual, SUM(subtotal) as total_penjualan')
                        ->groupBy('nama_barang')
                        ->orderByDesc('total_terjual')
                        ->limit(10)
                        ->get();

                    $monthlySales = TransaksiPenjualan::query()
                        ->where('tanggal_penjualan', '>=', now()->subMonths(11)->startOfMonth())
                        ->where('status_pembayaran', 'lunas')
                        ->get(['tanggal_penjualan', 'total_harga'])
                        ->groupBy(fn (TransaksiPenjualan $transaction): string => $transaction->tanggal_penjualan->format('Y-m'))
                        ->map(fn ($items): float => round((float) $items->sum('total_harga'), 2));

                    $metadata = [
                        'status_pembayaran' => $transactions
                            ->groupBy('status_pembayaran')
                            ->map(fn ($items): int => $items->count())
                            ->all(),
                        'penjualan_per_bulan' => $monthlySales->all(),
                        'top_produk' => $topProducts
                            ->map(fn ($item): array => [
                                'nama_barang' => (string) $item->nama_barang,
                                'total_terjual' => (int) $item->total_terjual,
                                'total_penjualan' => (float) $item->total_penjualan,
                            ])
                            ->values()
                            ->all(),
                    ];

                    $transactionData = $transactions
                        ->map(fn (TransaksiPenjualan $transaction): array => [
                            'kode_penjualan' => $transaction->kode_penjualan,
                            'tanggal_penjualan' => $transaction->tanggal_penjualan?->format('Y-m-d H:i'),
                            'customer' => $transaction->customer?->nama_customer,
                            'status_pembayaran' => $transaction->status_pembayaran,
                            'metode_pembayaran' => $transaction->metode_pembayaran,
                            'total_harga' => (float) $transaction->total_harga,
                            'jumlah_item' => (int) $transaction->detailTransaksi->sum('jumlah'),
                            'detail' => $transaction->detailTransaksi
                                ->map(fn ($detail): array => [
                                    'nama_barang' => $detail->nama_barang,
                                    'kategori' => $detail->kategori,
                                    'jumlah' => (int) $detail->jumlah,
                                    'harga_satuan' => (float) $detail->harga_satuan,
                                    'subtotal' => (float) $detail->subtotal,
                                ])
                                ->values()
                                ->all(),
                        ])
                        ->values();

                    $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $transactionJson = $transactionData->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $prompt = <<<PROMPT
Analisis data transaksi penjualan berikut.

Aturan:
- Gunakan hanya data yang diberikan.
- Fokus pada tren penjualan, produk terjual, status pembayaran, dan peluang peningkatan penjualan.
- Untuk nilai rupiah, gunakan format ringkas dan mudah dipahami.
- Jika ada kesimpulan yang belum bisa dipastikan dari data, tulis "perlu konfirmasi".
- Jangan menyarankan perubahan data transaksi.
- Kembalikan JSON murni tanpa markdown.

Format JSON:
{
  "judul": "Analisis AI Penjualan",
  "ringkasan": "",
  "analisis": "",
  "saran": ""
}

Ringkasan angka dari sistem:
{$metadataJson}

Data transaksi penjualan terbaru:
{$transactionJson}
PROMPT;

                    $model = config('services.gemini.model', 'gemini-2.5-flash');
                    $url = 'https://generativelanguage.googleapis.com/v1/models/'.$model.':generateContent?key='
                        . $apiKey;

                    try {
                        $response = Http::timeout(45)->post($url, [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt],
                                    ],
                                ],
                            ],
                        ]);

                        if (! $response->successful()) {
                            Notification::make()
                                ->title('Gagal membuat analisis AI penjualan')
                                ->body('Gemini mengembalikan status '.$response->status().'.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $rawText = data_get($response->json(), 'candidates.0.content.parts.0.text');

                        if (! is_string($rawText) || trim($rawText) === '') {
                            Notification::make()
                                ->title('Respons AI kosong')
                                ->body('Gemini tidak mengembalikan teks analisis.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $cleanJson = trim(str_replace(['```json', '```JSON', '```'], '', $rawText));
                        $data = json_decode($cleanJson, true);

                        if (! is_array($data)) {
                            Notification::make()
                                ->title('Gagal membaca JSON dari AI')
                                ->body($rawText)
                                ->danger()
                                ->send();

                            return;
                        }

                        $ringkasan = trim((string) ($data['ringkasan'] ?? ''));
                        $analisis = trim((string) ($data['analisis'] ?? ''));

                        if ($ringkasan === '' || $analisis === '') {
                            Notification::make()
                                ->title('Format analisis AI belum lengkap')
                                ->body('AI harus mengisi ringkasan dan analisis.')
                                ->danger()
                                ->send();

                            return;
                        }

                        TransaksiPenjualanAiAnalysis::create([
                            'judul' => trim((string) ($data['judul'] ?? 'Analisis AI Penjualan'))
                                ?: 'Analisis AI Penjualan',
                            'total_transaksi' => $transactions->count(),
                            'total_penjualan' => (float) $paidTransactions->sum('total_harga'),
                            'total_item' => (int) $transactions->sum(
                                fn (TransaksiPenjualan $transaction): int => (int) $transaction->detailTransaksi->sum('jumlah')
                            ),
                            'total_produk' => $topProducts->count(),
                            'periode_awal' => $transactions->min('tanggal_penjualan')?->toDateString(),
                            'periode_akhir' => $transactions->max('tanggal_penjualan')?->toDateString(),
                            'ringkasan' => $ringkasan,
                            'analisis' => $analisis,
                            'saran' => trim((string) ($data['saran'] ?? '')) ?: null,
                            'metadata' => $metadata,
                        ]);

                        Notification::make()
                            ->title('Analisis AI Penjualan Berhasil')
                            ->body('Hasil analisis terbaru sudah tersimpan.')
                            ->success()
                            ->send();

                        $this->redirect(TransaksiPenjualanResource::getUrl('index'));
                    } catch (Throwable) {
                        Notification::make()
                            ->title('Gagal membuat analisis AI penjualan')
                            ->body('Periksa koneksi internet, API key Gemini, atau migration tabel analisis.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->label('New Penjualan')
                ->icon('heroicon-m-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PenjualanBulananChart::class,
            TopProdukTerjualChart::class,
        ];
    }
}
