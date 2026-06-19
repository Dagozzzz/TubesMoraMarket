<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Filament\Widgets\BarangChartWidget;
use App\Filament\Widgets\BarangMaterialChart;
use App\Models\Barang;
use App\Models\BarangAiAnalysis;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;
use Throwable;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refreshAiInsights')
                ->label('Analisis AI Barang')
                ->visible(true)
                ->icon('heroicon-m-sparkles')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Buat Analisis AI Barang')
                ->modalDescription(
                    'AI akan membaca data dari tabel barang dan menyimpan ringkasan analisis terbaru.'
                )
                ->action(function (): void {
                    $apiKey = env('GEMINI_API_KEY');

                    if (blank($apiKey)) {
                        Notification::make()
                            ->title('GEMINI_API_KEY belum diisi')
                            ->body('Analisis AI barang belum bisa dibuat tanpa API key.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $barangs = Barang::query()
                        ->select([
                            'kode_barang',
                            'nama_barang',
                            'kategori',
                            'satuan',
                            'harga_beli',
                            'harga_jual',
                        ])
                        ->orderBy('kode_barang')
                        ->get();

                    if ($barangs->isEmpty()) {
                        Notification::make()
                            ->title('Data barang masih kosong')
                            ->body('Tambahkan master barang terlebih dahulu sebelum membuat analisis AI.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $kategoriSummary = $barangs
                        ->groupBy('kategori')
                        ->map(fn ($items): int => $items->count())
                        ->sortDesc();

                    $barangData = $barangs
                        ->map(fn (Barang $barang): array => [
                            'kode_barang' => $barang->kode_barang,
                            'nama_barang' => $barang->nama_barang,
                            'kategori' => $barang->kategori,
                            'satuan' => $barang->satuan,
                            'harga_beli' => (float) $barang->harga_beli,
                            'harga_jual' => (float) $barang->harga_jual,
                        ])
                        ->values();

                    $metadata = [
                        'kategori' => $kategoriSummary->all(),
                        'harga_jual_min' => (float) $barangs->min('harga_jual'),
                        'harga_jual_max' => (float) $barangs->max('harga_jual'),
                        'harga_jual_rata_rata' => round((float) $barangs->avg('harga_jual'), 2),
                        'margin_rata_rata' => round(
                            (float) $barangs->avg(
                                fn (Barang $barang): float => (float) $barang->harga_jual - (float) $barang->harga_beli
                            ),
                            2
                        ),
                    ];

                    $barangJson = $barangData->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $prompt = <<<PROMPT
Analisis data master barang berikut.

Aturan:
- Gunakan hanya data yang diberikan.
- Jangan memakai asumsi di luar data master barang.
- Jika ada kesimpulan yang belum bisa dipastikan dari data, tulis "perlu konfirmasi".
- Fokus pada ringkasan kondisi barang, kategori, harga jual, dan margin dari harga beli ke harga jual.
- Jangan membahas transaksi atau modul lain.
- Kembalikan JSON murni tanpa markdown.

Format JSON:
{
  "judul": "Analisis AI Master Barang",
  "ringkasan": "",
  "analisis": "",
  "saran": ""
}

Ringkasan angka dari sistem:
{$metadataJson}

Data barang:
{$barangJson}
PROMPT;

                    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key='
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
                                ->title('Gagal membuat analisis AI barang')
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

                        BarangAiAnalysis::create([
                            'judul' => trim((string) ($data['judul'] ?? 'Analisis AI Master Barang'))
                                ?: 'Analisis AI Master Barang',
                            'total_barang' => $barangs->count(),
                            'total_kategori' => $kategoriSummary->count(),
                            'ringkasan' => $ringkasan,
                            'analisis' => $analisis,
                            'saran' => trim((string) ($data['saran'] ?? '')) ?: null,
                            'metadata' => $metadata,
                        ]);

                        Notification::make()
                            ->title('Analisis AI Barang Berhasil')
                            ->body('Hasil terbaru sudah tersimpan dan tampil di panel admin barang.')
                            ->success()
                            ->send();

                        $this->redirect(BarangResource::getUrl('index'));
                    } catch (Throwable) {
                        Notification::make()
                            ->title('Gagal membuat analisis AI barang')
                            ->body('Periksa koneksi internet atau API key Gemini.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BarangChartWidget::class,
            BarangMaterialChart::class,
        ];
    }
}
