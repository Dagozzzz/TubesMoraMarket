<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Hasil Analisis AI Barang
        </x-slot>

        @if ($analysis)
            <div class="space-y-4 text-sm text-gray-700 dark:text-gray-200">
                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $analysis->created_at?->format('d M Y H:i') }}</span>
                    <span>{{ $analysis->total_barang }} barang</span>
                    <span>{{ $analysis->total_kategori }} kategori</span>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-950 dark:text-white">{{ $analysis->judul }}</h3>
                    <p class="mt-1 whitespace-pre-line">{{ $analysis->ringkasan }}</p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-950 dark:text-white">Analisis</h4>
                    <p class="mt-1 whitespace-pre-line">{{ $analysis->analisis }}</p>
                </div>

                @if ($analysis->saran)
                    <div>
                        <h4 class="font-medium text-gray-950 dark:text-white">Saran</h4>
                        <p class="mt-1 whitespace-pre-line">{{ $analysis->saran }}</p>
                    </div>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada hasil analisis AI barang.
            </p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
